<?php

namespace SageCounseling\AuditLog;

final class NetworkContext
{
    public function __construct(
        public readonly string $ipAddress,
        public readonly string $userAgent,
    ) {
    }
}
