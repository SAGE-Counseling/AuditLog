<?php

namespace SageCounseling\AuditLog;

use Illuminate\Support\ServiceProvider;
use SageCounseling\AuditLog\Console\Commands\ArchiveAuditLogs;

class AuditLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/audit-log.php', 'audit-log');

        $this->app->singleton(ResourceTypeAllowList::class, function () {
            return new ResourceTypeAllowList(config('audit-log.resource_types', []));
        });

        $this->app->singleton(PurposeOfUseResolver::class, function () {
            return new PurposeOfUseResolver(
                config('audit-log.purpose_of_use_by_role', []),
                config('audit-log.default_purpose_of_use'),
            );
        });

        $this->app->bind(ActorResolver::class, function ($app) {
            $resolverClass = config('audit-log.actor_resolver');

            if (! $resolverClass) {
                throw new \RuntimeException(
                    'No audit-log actor resolver configured. Set config(\'audit-log.actor_resolver\') '.
                    'to a class implementing '.ActorResolver::class.'.'
                );
            }

            return $app->make($resolverClass);
        });

        $this->app->singleton(DeniedAccessBackstop::class, function ($app) {
            return new DeniedAccessBackstop(
                $app->make(AuditLogger::class),
                $app->make(ActorResolver::class),
                config('audit-log.denial_backstop_routes', []),
            );
        });
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/audit-log.php' => config_path('audit-log.php'),
        ], 'audit-log-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'audit-log-migrations');

        $this->commands([
            ArchiveAuditLogs::class,
        ]);
    }
}
