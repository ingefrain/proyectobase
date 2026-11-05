<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OutboxMessage;
use App\Services\Outbox\OutboxDispatcher;
use App\Support\AdminAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(OutboxMessage::STATUSES)],
            'channel' => ['nullable', Rule::in(['email'])],
            'q' => ['nullable', 'string', 'max:320'],
        ]);
        $term = trim((string) ($filters['q'] ?? ''));
        $query = OutboxMessage::query()
            ->when($filters['status'] ?? null, fn ($builder, string $status) => $builder->where('status', $status))
            ->when($filters['channel'] ?? null, fn ($builder, string $channel) => $builder->where('channel', $channel));

        if ($term !== '') {
            $query->where(function ($builder) use ($term): void {
                $builder->where('uuid', $term)->orWhere('correlation_id', $term);
                if (filter_var(mb_strtolower($term), FILTER_VALIDATE_EMAIL) !== false) {
                    $builder->orWhere('recipient_hash', hash('sha256', mb_strtolower($term)));
                }
            });
        }

        $messages = $query->latest('created_at')->paginate(20)->withQueryString();

        return view('admin.notifications.index', compact('messages', 'filters'));
    }

    public function show(OutboxMessage $outboxMessage): View
    {
        $outboxMessage->load('attemptsHistory');

        return view('admin.notifications.show', compact('outboxMessage'));
    }

    public function retry(
        OutboxMessage $outboxMessage,
        OutboxDispatcher $dispatcher,
        AdminAuditLogger $audit,
    ): RedirectResponse {
        $retried = DB::transaction(function () use ($outboxMessage, $dispatcher, $audit): bool {
            $updated = OutboxMessage::query()->whereKey($outboxMessage->id)
                ->where('status', OutboxMessage::FAILED)
                ->update([
                    'status' => OutboxMessage::PENDING,
                    'attempts' => 0,
                    'retry_generation' => DB::raw('retry_generation + 1'),
                    'available_at' => now(),
                    'lease_token' => null,
                    'lease_expires_at' => null,
                    'processed_at' => null,
                    'last_error_type' => null,
                    'last_error_code' => null,
                    'last_error_message' => null,
                ]);
            if ($updated !== 1) {
                return false;
            }

            $audit->record('notification.retried', 'outbox_message', $outboxMessage->id, null, [
                'uuid' => $outboxMessage->uuid,
                'retry_generation' => $outboxMessage->retry_generation + 1,
            ]);
            $dispatcher->dispatchAfterCommit($outboxMessage->id);

            return true;
        });

        if (! $retried) {
            return back()->withErrors(['status' => 'Solo se pueden reintentar mensajes fallidos.']);
        }

        return redirect()->route('admin.notifications.show', $outboxMessage)->with('status', 'Se solicitó el reintento del mensaje.');
    }

    public static function maskedRecipient(string $recipient): string
    {
        [$local, $domain] = array_pad(explode('@', $recipient, 2), 2, '');
        if ($local === '' || $domain === '') {
            return '—';
        }
        $domainParts = explode('.', $domain);
        $domainParts[0] = mb_substr($domainParts[0], 0, 1).'***';

        return mb_substr($local, 0, 1).'***@'.implode('.', $domainParts);
    }
}
