<?php

namespace SageCounseling\AuditLog\Tests;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use SageCounseling\AuditLog\ActorResolver;
use SageCounseling\AuditLog\Actor;
use SageCounseling\AuditLog\AuditLogger;
use SageCounseling\AuditLog\DeniedAccessBackstop;
use SageCounseling\AuditLog\PurposeOfUse;
use SageCounseling\AuditLog\PurposeOfUseResolver;
use SageCounseling\AuditLog\ResourceTypeAllowList;

class DeniedAccessBackstopTest extends TestCase
{
    private function makeBackstop(array $routes = []): DeniedAccessBackstop
    {
        $logger = new AuditLogger(
            new ResourceTypeAllowList(['client']),
            new PurposeOfUseResolver([], PurposeOfUse::HealthcareOperations),
        );

        $resolver = new class implements ActorResolver {
            public function resolve(Authenticatable $user): Actor
            {
                return new Actor((int) $user->getAuthIdentifier(), 'Jane Doe', 'jane@example.com', ['admin']);
            }
        };

        return new DeniedAccessBackstop($logger, $resolver, $routes ?: [
            'client.show' => ['resource_type' => 'client', 'route_parameter' => 'clientId'],
        ]);
    }

    private function fakeUser(): Authenticatable
    {
        return new class implements Authenticatable {
            public function getAuthIdentifierName()
            {
                return 'id';
            }

            public function getAuthIdentifier()
            {
                return 1;
            }

            public function getAuthPasswordName()
            {
                return 'password';
            }

            public function getAuthPassword()
            {
                return 'hashed';
            }

            public function getRememberToken()
            {
                return null;
            }

            public function setRememberToken($value) {}

            public function getRememberTokenName()
            {
                return 'remember_token';
            }
        };
    }

    private function requestFor(?Authenticatable $user, string $routeName, array $parameters = []): Request
    {
        $request = Request::create('/clients/'.($parameters['clientId'] ?? '1'));
        $request->setUserResolver(fn () => $user);

        $route = new Route('GET', 'clients/{clientId}', []);
        $route->name($routeName);
        $route->bind($request);
        foreach ($parameters as $key => $value) {
            $route->setParameter($key, $value);
        }
        $request->setRouteResolver(fn () => $route);

        return $request;
    }

    public function test_logs_a_denial_for_a_covered_route(): void
    {
        $request = $this->requestFor($this->fakeUser(), 'client.show', ['clientId' => '4502']);

        $this->makeBackstop()->handle($request, 403);

        $this->assertDatabaseHas('audit_logs', [
            'resource_type' => 'client',
            'resource_id' => '4502',
            'succeeded' => false,
            'status_code' => 403,
        ], 'testing');
    }

    public function test_does_not_log_a_successful_response(): void
    {
        $request = $this->requestFor($this->fakeUser(), 'client.show', ['clientId' => '4502']);

        $this->makeBackstop()->handle($request, 200);

        $this->assertDatabaseCount('audit_logs', 0, 'testing');
    }

    public function test_does_not_double_log_when_a_call_site_already_logged(): void
    {
        $request = $this->requestFor($this->fakeUser(), 'client.show', ['clientId' => '4502']);
        $request->attributes->set('audit_log_written', true);

        $this->makeBackstop()->handle($request, 403);

        $this->assertDatabaseCount('audit_logs', 0, 'testing');
    }

    public function test_does_not_log_when_there_is_no_authenticated_user(): void
    {
        $request = $this->requestFor(null, 'client.show', ['clientId' => '4502']);

        $this->makeBackstop()->handle($request, 403);

        $this->assertDatabaseCount('audit_logs', 0, 'testing');
    }

    public function test_does_not_log_a_route_not_covered_by_the_backstop(): void
    {
        $request = $this->requestFor($this->fakeUser(), 'visit.show', ['clientId' => '4502']);

        $this->makeBackstop()->handle($request, 403);

        $this->assertDatabaseCount('audit_logs', 0, 'testing');
    }
}
