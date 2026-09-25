<?php

namespace SageCounseling\AuditLog;

use InvalidArgumentException;

final class InvalidResourceTypeException extends InvalidArgumentException
{
    public function __construct(string $resourceType)
    {
        parent::__construct("Resource type [{$resourceType}] is not registered with the audit log allow-list.");
    }
}
