# Configurable DB connections and actor resolution, not hardcoded to compliance-portal's names

When porting `app/AuditLog/*` from `compliance-portal`, the source hardcoded its isolated-database connection
names (`'audit'`, `'audit_migrator'`) directly into `AuditLogEntry` and the migrations, and built an `Actor`
for `DeniedAccessBackstop` by calling `$user->getRoleNames()->toArray()` — a `spatie/laravel-permission`-specific
method — directly in package code. Both were safe assumptions in a single-app namespace, but this package is
meant to serve a second consumer (RPS) with its own connection names and possibly its own role package.

Decided to make both configurable instead of porting as-is:

- `AuditLogEntry::getConnectionName()` and both migrations read `config('audit-log.connection')` /
  `config('audit-log.migration_connection')` rather than hardcoding `'audit'`/`'audit_migrator'`. Each
  consuming app names its own isolated-database connection(s) and points these two config keys at them.
- `DeniedAccessBackstop` depends on a `SageCounseling\AuditLog\ActorResolver` interface, bound in
  `AuditLogServiceProvider` from `config('audit-log.actor_resolver')` (a class the consuming app provides). The
  package throws immediately if this isn't configured, rather than silently assuming a specific role package.

Rejected: porting the hardcoded names/coupling as-is and revisiting only once RPS integration actually breaks.
This was rejected because both are public API surface — baked into every consuming app's config and container
bindings once adopted — and a second consumer arriving during this same handoff (rather than hypothetically
later) made the trade-off concrete enough to resolve now rather than defer.
