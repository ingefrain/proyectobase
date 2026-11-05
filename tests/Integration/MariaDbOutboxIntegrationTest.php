<?php

namespace Tests\Integration;

use App\Models\OutboxMessage;
use App\Services\Outbox\OutboxDispatcher;
use App\Services\Outbox\OutboxProcessor;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\Support\TestDatabaseSafety;
use Tests\TestCase;

class MariaDbOutboxIntegrationTest extends TestCase
{
    private const CONNECTION = 'outbox_mariadb_test';

    protected function setUp(): void
    {
        parent::setUp();
        if (getenv('OUTBOX_MARIADB_TEST') !== '1') {
            $this->markTestSkipped('Set OUTBOX_MARIADB_TEST=1 to run against the isolated MariaDB test database.');
        }
        if (! getenv('OUTBOX_MARIADB_DATABASE') || ! getenv('OUTBOX_MARIADB_USERNAME') || ! getenv('OUTBOX_MARIADB_PASSWORD')) {
            throw new \RuntimeException('The dedicated MariaDB test database and runner credentials are required.');
        }

        $connection = [
            'driver' => 'mysql',
            'url' => null,
            'host' => getenv('OUTBOX_MARIADB_HOST') ?: '127.0.0.1',
            'port' => getenv('OUTBOX_MARIADB_PORT') ?: '3307',
            'database' => getenv('OUTBOX_MARIADB_DATABASE') ?: '',
            'username' => getenv('OUTBOX_MARIADB_USERNAME') ?: '',
            'password' => getenv('OUTBOX_MARIADB_PASSWORD') ?: '',
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => [],
        ];
        TestDatabaseSafety::assertConnection($connection);
        config([
            'database.connections.'.self::CONNECTION => $connection,
            'database.default' => self::CONNECTION,
        ]);
        app('db')->purge(self::CONNECTION);
        Artisan::call('migrate:fresh', ['--database' => self::CONNECTION, '--force' => true]);
    }

    public function test_mariadb_uses_persistent_atomic_state_to_prevent_repeated_delivery(): void
    {
        Mail::fake();
        $message = app(OutboxDispatcher::class)->email('mariadb@example.test', [
            'subject' => 'Prueba aislada',
            'message' => 'Prueba de estado MariaDB.',
        ]);

        $this->assertTrue(app(OutboxProcessor::class)->process($message->id));
        $this->assertFalse(app(OutboxProcessor::class)->process($message->id));
        $this->assertSame(OutboxMessage::SENT, $message->fresh()->status);
        $this->assertSame(1, $message->attemptsHistory()->count());
    }

    public function test_mariadb_reclaims_an_expired_lease_without_reusing_its_attempt_record(): void
    {
        Mail::fake();
        $message = app(OutboxDispatcher::class)->email('mariadb@example.test', [
            'subject' => 'Prueba de lease',
            'message' => 'Recuperación de lease expirado.',
        ]);
        $token = (string) Str::uuid();
        $message->forceFill([
            'status' => OutboxMessage::PROCESSING,
            'attempts' => 1,
            'lease_token' => $token,
            'lease_expires_at' => now()->subSecond(),
        ])->save();
        $message->attemptsHistory()->create([
            'attempt_number' => 1,
            'lease_token' => $token,
            'started_at' => now()->subMinutes(6),
        ]);

        $this->assertTrue(app(OutboxProcessor::class)->process($message->id));
        $this->assertSame(OutboxMessage::SENT, $message->fresh()->status);
        $this->assertSame(2, $message->attemptsHistory()->count());
        $this->assertDatabaseHas('outbox_attempts', [
            'outbox_message_id' => $message->id,
            'attempt_number' => 1,
            'result' => 'abandoned',
        ]);
    }
}
