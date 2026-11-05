<?php

namespace App\Services\Outbox;

use App\Contracts\OutboxEmailSender;
use App\Models\OutboxAttempt;
use App\Models\OutboxMessage;
use App\Support\OutboxPayload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class OutboxProcessor
{
    public const MAX_ATTEMPTS = 6;

    public const LEASE_SECONDS = 300;

    private const RETRY_DELAYS = [60, 300, 900, 3600, 21600];

    public function __construct(private readonly OutboxEmailSender $emailSender) {}

    public function process(int $messageId): bool
    {
        $claim = $this->claim($messageId);
        if ($claim === null) {
            return false;
        }

        /** @var OutboxMessage $message */
        $message = $claim['message'];
        /** @var OutboxAttempt $attempt */
        $attempt = $claim['attempt'];
        $leaseToken = $claim['lease_token'];

        try {
            if ($message->channel !== 'email' || $message->template !== 'generic.email.v1') {
                throw new InvalidArgumentException('Unsupported outbox message type.');
            }

            $this->emailSender->send($message->recipient, OutboxPayload::normalize($message->payload));
        } catch (Throwable $exception) {
            $permanent = $exception instanceof InvalidArgumentException;
            $errorType = $permanent ? 'configuration' : 'delivery';
            $errorMessage = $permanent
                ? 'Delivery configuration or message is invalid; details redacted.'
                : 'Email delivery failed; details redacted.';
            $exhausted = $message->attempts >= self::MAX_ATTEMPTS;
            $terminal = $permanent || $exhausted;
            $delay = self::RETRY_DELAYS[min(max($message->attempts - 1, 0), count(self::RETRY_DELAYS) - 1)];

            $this->finishFailed(
                $message->id,
                $attempt->id,
                $leaseToken,
                $terminal,
                $errorType,
                null,
                $errorMessage,
                $terminal ? null : now()->addSeconds($delay),
            );
            Log::warning('outbox.delivery_failed', [
                'message_id' => $message->id,
                'attempt_number' => $attempt->attempt_number,
                'error_type' => $errorType,
            ]);

            return false;
        }

        return $this->finishSent($message->id, $attempt->id, $leaseToken);
    }

    /** @return array{message: OutboxMessage, attempt: OutboxAttempt, lease_token: string}|null */
    private function claim(int $messageId): ?array
    {
        $claim = DB::transaction(function () use ($messageId): ?array {
            $now = now();
            $leaseToken = (string) Str::uuid();
            $updated = OutboxMessage::query()->whereKey($messageId)
                ->where(function ($query) use ($now): void {
                    $query->where(function ($pending) use ($now): void {
                        $pending->where('status', OutboxMessage::PENDING)->where('available_at', '<=', $now);
                    })->orWhere(function ($expired) use ($now): void {
                        $expired->where('status', OutboxMessage::PROCESSING)->where('lease_expires_at', '<=', $now);
                    });
                })
                ->update([
                    'status' => OutboxMessage::PROCESSING,
                    'attempts' => DB::raw('attempts + 1'),
                    'lease_token' => $leaseToken,
                    'lease_expires_at' => $now->copy()->addSeconds(self::LEASE_SECONDS),
                    'processed_at' => null,
                ]);

            if ($updated !== 1) {
                return null;
            }

            OutboxAttempt::query()->where('outbox_message_id', $messageId)->whereNull('finished_at')
                ->update([
                    'result' => 'abandoned',
                    'error_type' => 'lease_expired',
                    'error_message' => 'Previous processing lease expired; details unavailable.',
                    'finished_at' => $now,
                ]);

            $attemptNumber = (int) OutboxAttempt::query()
                ->where('outbox_message_id', $messageId)->max('attempt_number') + 1;
            $attempt = OutboxAttempt::query()->create([
                'outbox_message_id' => $messageId,
                'attempt_number' => $attemptNumber,
                'lease_token' => $leaseToken,
                'started_at' => $now,
            ]);

            return ['attempt_id' => $attempt->id, 'lease_token' => $leaseToken];
        });

        if ($claim === null) {
            return null;
        }

        return [
            'message' => OutboxMessage::query()->findOrFail($messageId),
            'attempt' => OutboxAttempt::query()->findOrFail($claim['attempt_id']),
            'lease_token' => $claim['lease_token'],
        ];
    }

    private function finishSent(int $messageId, int $attemptId, string $leaseToken): bool
    {
        return DB::transaction(function () use ($messageId, $attemptId, $leaseToken): bool {
            $now = now();
            $updated = OutboxMessage::query()->whereKey($messageId)->where('status', OutboxMessage::PROCESSING)->where('lease_token', $leaseToken)
                ->update([
                    'status' => OutboxMessage::SENT,
                    'lease_token' => null,
                    'lease_expires_at' => null,
                    'processed_at' => $now,
                    'sent_at' => $now,
                    'last_error_type' => null,
                    'last_error_code' => null,
                    'last_error_message' => null,
                ]);
            if ($updated !== 1) {
                return false;
            }
            OutboxAttempt::query()->whereKey($attemptId)->where('lease_token', $leaseToken)->whereNull('finished_at')
                ->update(['result' => 'sent', 'finished_at' => $now]);

            return true;
        });
    }

    private function finishFailed(
        int $messageId,
        int $attemptId,
        string $leaseToken,
        bool $terminal,
        string $errorType,
        ?string $errorCode,
        string $errorMessage,
        mixed $availableAt,
    ): bool {
        return DB::transaction(function () use ($messageId, $attemptId, $leaseToken, $terminal, $errorType, $errorCode, $errorMessage, $availableAt): bool {
            $now = now();
            $updated = OutboxMessage::query()->whereKey($messageId)->where('status', OutboxMessage::PROCESSING)->where('lease_token', $leaseToken)
                ->update([
                    'status' => $terminal ? OutboxMessage::FAILED : OutboxMessage::PENDING,
                    'lease_token' => null,
                    'lease_expires_at' => null,
                    'available_at' => $availableAt ?? $now,
                    'processed_at' => $terminal ? $now : null,
                    'last_error_type' => $errorType,
                    'last_error_code' => $errorCode,
                    'last_error_message' => $errorMessage,
                ]);
            if ($updated !== 1) {
                return false;
            }
            OutboxAttempt::query()->whereKey($attemptId)->where('lease_token', $leaseToken)->whereNull('finished_at')
                ->update([
                    'result' => 'failed',
                    'error_type' => $errorType,
                    'error_code' => $errorCode,
                    'error_message' => $errorMessage,
                    'finished_at' => $now,
                ]);

            return true;
        });
    }
}
