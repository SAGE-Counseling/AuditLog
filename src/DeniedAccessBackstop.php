<?php

namespace SageCounseling\AuditLog;

use Illuminate\Http\Request;

/**
 * Global backstop for denied/failed PHI access attempts that occur before any
 * controller-level call site had resolved enough resource identity to log its own
 * failure (e.g. `findOrFail` 404s on a cross-agency resource before the controller
 * ever reaches a logging line). Meant to be wired from a consuming app's exception
 * handler, after the response status is known.
 *
 * Route wiring (which routes carry sensitive data and what identifies the resource)
 * is config-driven (`audit-log.denial_backstop_routes`) rather than hardcoded here, so
 * this package stays free of references to host-app models/routes.
 */
class DeniedAccessBackstop
{
    /**
     * @param  array<string, array{resource_type: string, route_parameter: string}>  $routes
     */
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly ActorResolver $actorResolver,
        private readonly array $routes,
    ) {
    }

    public function handle(Request $request, int $statusCode): void
    {
        if ($statusCode >= 200 && $statusCode < 300) {
            return;
        }

        if ($request->attributes->get('audit_log_written') === true) {
            return;
        }

        $user = $request->user();

        if (! $user) {
            return;
        }

        $route = $request->route();

        if (! $route || ! isset($this->routes[$route->getName()])) {
            return;
        }

        $config = $this->routes[$route->getName()];
        $resourceId = (string) $route->parameter($config['route_parameter']);

        $this->auditLogger->log(
            ActionType::Read,
            $config['resource_type'],
            $resourceId,
            $this->actorResolver->resolve($user),
            new NetworkContext($request->ip() ?? '', $request->userAgent() ?? ''),
            false,
            $statusCode,
        );
    }
}
