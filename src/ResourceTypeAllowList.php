<?php

namespace SageCounseling\AuditLog;

/**
 * The set of resource_type values the audit log will accept, registered by the
 * consuming app (not hardcoded here) so each consumer can register its own resource
 * types without modifying this package.
 */
final class ResourceTypeAllowList
{
    /**
     * @param  array<int, string>  $allowed
     */
    public function __construct(private readonly array $allowed)
    {
    }

    public function isAllowed(string $resourceType): bool
    {
        return in_array($resourceType, $this->allowed, true);
    }

    public function assertAllowed(string $resourceType): void
    {
        if (! $this->isAllowed($resourceType)) {
            throw new InvalidResourceTypeException($resourceType);
        }
    }
}
