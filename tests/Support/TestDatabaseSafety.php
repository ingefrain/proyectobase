<?php

namespace Tests\Support;

use Illuminate\Database\Connectors\ConnectionFactory;
use RuntimeException;

final class TestDatabaseSafety
{
    public static function assertConnection(array $config): void
    {
        if (! empty($config['url']) || ! empty($config['read']) || ! empty($config['write'])
            || ! empty($config['unix_socket']) || ! empty($config['prefix'])) {
            throw new RuntimeException('Test database URLs and read/write overrides are forbidden.');
        }

        if (($config['driver'] ?? '') === 'sqlite' && ($config['database'] ?? '') === ':memory:') {
            return;
        }

        if (in_array($config['driver'] ?? '', ['mysql', 'mariadb'], true)
            && ($config['database'] ?? '') === 'proyectobase_test'
            && in_array($config['host'] ?? '', ['127.0.0.1', 'localhost', '::1'], true)
            && (string) ($config['port'] ?? '') === '3307'
            && ($config['username'] ?? '') === 'proyectobase_test_runner') {
            return;
        }

        throw new RuntimeException('Unsafe test database connection rejected before opening PDO.');
    }

    public static function install($app): void
    {
        if (! $app->environment('testing')) {
            throw new RuntimeException('Tests require APP_ENV=testing.');
        }

        $manager = $app->make('db');
        foreach (['sqlite', 'mysql', 'mariadb', 'pgsql', 'sqlsrv'] as $driver) {
            $manager->extend($driver, function ($config, $name) use ($app) {
                self::assertConnection($config);

                return (new ConnectionFactory($app))->make($config, $name);
            });
        }
        self::assertConnection(config('database.connections.'.config('database.default')));
    }
}
