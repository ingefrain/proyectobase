<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Support\TestDatabaseSafety;

class TestDatabaseSafetyTest extends TestCase
{
    public function test_in_memory_sqlite_and_the_dedicated_local_mariadb_database_are_allowed(): void
    {
        TestDatabaseSafety::assertConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        TestDatabaseSafety::assertConnection([
            'driver' => 'mysql',
            'database' => 'proyectobase_test',
            'host' => '127.0.0.1',
            'port' => '3307',
            'username' => 'proyectobase_test_runner',
        ]);

        $this->assertTrue(true);
    }

    public function test_non_test_databases_remote_hosts_urls_and_overrides_are_rejected(): void
    {
        $safe = [
            'driver' => 'mysql',
            'database' => 'proyectobase_test',
            'host' => '127.0.0.1',
            'port' => '3307',
            'username' => 'proyectobase_test_runner',
        ];

        foreach ([
            ['database' => 'proyectobase_dev'],
            ['database' => 'clap_dev'],
            ['database' => 'acerca_prod'],
            ['host' => 'db.example.com'],
            ['port' => '3306'],
            ['username' => 'root'],
            ['url' => 'mysql://localhost/proyectobase_test'],
            ['read' => ['host' => 'localhost']],
            ['write' => ['host' => 'localhost']],
            ['unix_socket' => 'C:\\mysql.sock'],
            ['prefix' => 'production_'],
        ] as $override) {
            try {
                TestDatabaseSafety::assertConnection(array_replace($safe, $override));
                $this->fail('Unsafe test database configuration was accepted.');
            } catch (RuntimeException) {
                $this->assertTrue(true);
            }
        }
    }
}
