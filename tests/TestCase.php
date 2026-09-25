<?php

namespace SageCounseling\AuditLog\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use SageCounseling\AuditLog\AuditLogServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [AuditLogServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        // Both the runtime and migration connections point at the same in-memory
        // sqlite database for tests. Verifying the production privilege separation
        // (an actual INSERT-only DB role) is the consuming app's responsibility, not
        // this package's — sqlite has no such grant model.
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('audit-log.connection', 'testing');
        $app['config']->set('audit-log.migration_connection', 'testing');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
