<?php

namespace SageCounseling\AuditLog;

/**
 * Full CRUD enum, not read-only. The first consumer (compliance-portal) only ever
 * emits Read (plus login/login-failure events, logged as Read against a
 * session/auth resource type), but this package is shared with other consumers
 * that may have genuine PHI write paths.
 */
enum ActionType: string
{
    case Create = 'create';
    case Read = 'read';
    case Update = 'update';
    case Delete = 'delete';
}
