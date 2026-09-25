<?php

use SageCounseling\AuditLog\PurposeOfUse;

return [

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | This package expects the audit log to live in an isolated database reachable
    | only by a restricted credential (e.g. an INSERT-only Postgres role) — it never
    | hardcodes a connection name. Define the connection(s) in your own
    | config/database.php and point these two keys at them.
    |
    | `connection`: used for normal runtime writes (AuditLogger, AuditLogEntry).
    |   Expected to use the restricted/INSERT-only credential in production.
    | `migration_connection`: used for schema migrations and the archive command's
    |   read/delete operations, which need more than INSERT.
    |
    */

    'connection' => env('AUDIT_LOG_CONNECTION', 'audit'),

    'migration_connection' => env('AUDIT_LOG_MIGRATION_CONNECTION', 'audit_migrator'),

    /*
    |--------------------------------------------------------------------------
    | Actor Resolver
    |--------------------------------------------------------------------------
    |
    | DeniedAccessBackstop needs to turn an authenticated user into an Actor when it
    | logs a denial no call site logged directly. This package makes no assumption
    | about which role package (if any) your app uses — provide a class implementing
    | SageCounseling\AuditLog\ActorResolver that knows how to build an Actor from your
    | app's User model.
    |
    */

    'actor_resolver' => null,

    /*
    |--------------------------------------------------------------------------
    | Resource Type Allow-List
    |--------------------------------------------------------------------------
    |
    | AuditLogger validates resource_type against this list rather than hardcoding
    | one itself, so each consuming app can register its own resource types without
    | modifying this package.
    |
    */

    'resource_types' => [],

    /*
    |--------------------------------------------------------------------------
    | Purpose of Use by Role
    |--------------------------------------------------------------------------
    |
    | purpose_of_use is always derived automatically from the acting user's role(s)
    | — never a manual picker. Checked in the order listed below (most specific
    | first), since a user can hold more than one role at once.
    |
    */

    'purpose_of_use_by_role' => [],

    'default_purpose_of_use' => PurposeOfUse::HealthcareOperations,

    /*
    |--------------------------------------------------------------------------
    | Denial Backstop Routes
    |--------------------------------------------------------------------------
    |
    | Named routes that read sensitive data, mapped to the resource_type to log and
    | the route parameter holding the best-available resource identity.
    | DeniedAccessBackstop uses this to log a failed audit row for denials that
    | happen before a controller-level call site could log its own failure. Only
    | routes listed here are covered by the backstop.
    |
    */

    'denial_backstop_routes' => [],

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | primary_days: how long a row stays in the primary audit table before being
    | archived. archive_days: total retention (from occurred_at) in the archive
    | table before it is purged. See SageCounseling\AuditLog\Console\Commands\
    | ArchiveAuditLogs.
    |
    */

    'retention' => [
        'primary_days' => env('AUDIT_LOG_PRIMARY_RETENTION_DAYS', 365),
        'archive_days' => env('AUDIT_LOG_ARCHIVE_RETENTION_DAYS', 2190),
    ],

];
