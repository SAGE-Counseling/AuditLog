<?php

namespace SageCounseling\AuditLog;

/**
 * The identity of the user whose action is being audited, denormalized at write time
 * (the audit log is expected to live in an isolated database with no cross-database
 * FK to a consuming app's `users` table).
 *
 * id/name are nullable for a failed login attempt: there is no authenticated user to
 * attach, only the submitted email — callers must not invent a user_id.
 */
final class Actor
{
    /**
     * @param  array<int, string>  $roles  The user's full role list at the time of the
     *                                      action, not a single derived role.
     */
    public function __construct(
        public readonly ?int $id,
        public readonly ?string $name,
        public readonly string $email,
        public readonly array $roles,
    ) {
    }
}
