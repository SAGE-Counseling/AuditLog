<?php

namespace SageCounseling\AuditLog;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * Turns an authenticated user into an Actor. DeniedAccessBackstop needs this to log a
 * denial for a user it never received an Actor for directly — the package makes no
 * assumption about which role package (if any) a consuming app uses, so each
 * consuming app provides its own implementation and registers it via
 * config('audit-log.actor_resolver').
 */
interface ActorResolver
{
    public function resolve(Authenticatable $user): Actor;
}
