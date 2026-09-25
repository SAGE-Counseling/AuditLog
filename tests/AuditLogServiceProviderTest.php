<?php

namespace SageCounseling\AuditLog\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use RuntimeException;
use SageCounseling\AuditLog\Actor;
use SageCounseling\AuditLog\ActorResolver;
use SageCounseling\AuditLog\DeniedAccessBackstop;

class AuditLogServiceProviderTest extends TestCase
{
    public function test_resolving_the_backstop_without_a_configured_actor_resolver_throws(): void
    {
        $this->app['config']->set('audit-log.actor_resolver', null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No audit-log actor resolver configured');

        $this->app->make(DeniedAccessBackstop::class);
    }

    public function test_resolving_the_backstop_with_a_configured_actor_resolver_succeeds(): void
    {
        $this->app->bind('test-actor-resolver', function () {
            return new class implements ActorResolver {
                public function resolve(Authenticatable $user): Actor
                {
                    return new Actor(1, 'Jane Doe', 'jane@example.com', ['admin']);
                }
            };
        });

        $this->app['config']->set('audit-log.actor_resolver', 'test-actor-resolver');

        $backstop = $this->app->make(DeniedAccessBackstop::class);

        $this->assertInstanceOf(DeniedAccessBackstop::class, $backstop);
    }
}
