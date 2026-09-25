<?php

namespace SageCounseling\AuditLog;

use SageCounseling\AuditLog\Models\AuditLogEntry;

/**
 * Writes HIPAA access-audit rows. Self-contained: no reference to any host-app
 * Eloquent model — callers pass a resource_type (validated against an app-registered
 * allow-list) and resource_id as plain strings. Writes are synchronous, not queued.
 */
class AuditLogger
{
    public function __construct(
        private readonly ResourceTypeAllowList $resourceTypes,
        private readonly PurposeOfUseResolver $purposeOfUseResolver,
    ) {
    }

    public function log(
        ActionType $actionType,
        string $resourceType,
        string $resourceId,
        Actor $actor,
        NetworkContext $network,
        bool $succeeded,
        int $statusCode,
    ): AuditLogEntry {
        $this->resourceTypes->assertAllowed($resourceType);

        if (app()->bound('request')) {
            request()->attributes->set('audit_log_written', true);
        }

        return AuditLogEntry::create([
            'user_id' => $actor->id,
            'user_name' => $actor->name,
            'email' => $actor->email,
            'roles' => $actor->roles,
            'action_type' => $actionType,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'occurred_at' => now('UTC'),
            'ip_address' => $network->ipAddress,
            'user_agent' => $network->userAgent,
            'succeeded' => $succeeded,
            'status_code' => $statusCode,
            'purpose_of_use' => $this->purposeOfUseResolver->resolve($actor->roles),
        ]);
    }

    /**
     * Batches a single audit row per resource for a "many resources at once" read (a
     * list/search view) — one insert, not one row-by-row create() call per rendered
     * item.
     *
     * @param  array<int, string>  $resourceIds
     */
    public function logMany(
        ActionType $actionType,
        string $resourceType,
        array $resourceIds,
        Actor $actor,
        NetworkContext $network,
        bool $succeeded,
        int $statusCode,
    ): void {
        $this->resourceTypes->assertAllowed($resourceType);

        if (empty($resourceIds)) {
            return;
        }

        if (app()->bound('request')) {
            request()->attributes->set('audit_log_written', true);
        }

        $occurredAt = now('UTC');
        $purposeOfUse = $this->purposeOfUseResolver->resolve($actor->roles);

        $rows = array_map(fn (string $resourceId) => [
            'user_id' => $actor->id,
            'user_name' => $actor->name,
            'email' => $actor->email,
            'roles' => json_encode($actor->roles),
            'action_type' => $actionType->value,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'occurred_at' => $occurredAt,
            'ip_address' => $network->ipAddress,
            'user_agent' => $network->userAgent,
            'succeeded' => $succeeded,
            'status_code' => $statusCode,
            'purpose_of_use' => $purposeOfUse->value,
        ], $resourceIds);

        AuditLogEntry::insert($rows);
    }
}
