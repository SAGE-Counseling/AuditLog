# SAGE Counseling AuditLog

[![Tests](https://github.com/SAGE-Counseling/AuditLog/actions/workflows/tests.yml/badge.svg)](https://github.com/SAGE-Counseling/AuditLog/actions/workflows/tests.yml)

HIPAA-oriented access-audit logging package for SAGE Counseling apps — records who accessed what, and why.

Extracted from `compliance-portal`'s `app/AuditLog/*`; see [`docs/audit-log-handoff.md`](docs/audit-log-handoff.md)
for the extraction plan and open design questions still being resolved.

## Installation

```bash
composer require sage-counseling/audit-log
```

## Usage

Publish the config and migrations, then run them against your app's isolated audit database:

```bash
php artisan vendor:publish --tag=audit-log-config
php artisan vendor:publish --tag=audit-log-migrations
php artisan migrate
```

Set `config/audit-log.php`:

- `connection` / `migration_connection` — the DB connection names your app defines for the isolated audit
  database (a restricted/INSERT-only credential for `connection`, a more privileged one for
  `migration_connection`, used by schema migrations and the archive command).
- `actor_resolver` — a class implementing `SageCounseling\AuditLog\ActorResolver`, turning your app's
  authenticated user into an `Actor`. Required before `DeniedAccessBackstop` can be used.
- `resource_types`, `purpose_of_use_by_role`, `default_purpose_of_use`, `denial_backstop_routes`, `retention`
  — see the comments in the published config file.

Then, from a call site:

```php
app(SageCounseling\AuditLog\AuditLogger::class)->log(
    SageCounseling\AuditLog\ActionType::Read,
    'client',
    (string) $client->id,
    new SageCounseling\AuditLog\Actor($user->id, $user->name, $user->email, $user->roles->toArray()),
    new SageCounseling\AuditLog\NetworkContext($request->ip(), $request->userAgent()),
    true,
    200,
);
```

Wire `DeniedAccessBackstop` from your exception handler's `render()` step to catch denials that never reach a
call site's own logging line, and schedule `php artisan audit-log:archive` for retention/archival.

See [`docs/audit-log-handoff.md`](docs/audit-log-handoff.md) for the extraction plan this was ported from, and
[`docs/adr/`](docs/adr/) for the design decisions made during the port.

## Testing

```bash
composer install
vendor/bin/phpunit
```
