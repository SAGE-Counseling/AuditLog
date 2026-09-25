# Project State

Live snapshot only — not a history log. Task history lives in closed issues; query the tracker instead of
appending to this file indefinitely. When this file grows a long changelog, trim it back to recent entries
and point further back at the issue tracker (a past cleanup that's worth repeating periodically, not a
one-time task).

## Current status

The `AuditLog` module has been ported from `compliance-portal` as a real Laravel package (issue #3): service
provider, publishable config + migrations, and the archive command. Not yet wired into any consuming app —
that's handoff doc steps 3–4 (compliance-portal, then RPS), not started.

## What's working

- Repo is connected to GitHub (`SAGE-Counseling/AuditLog`, default branch `master`).
- Agent config scaffolded: issue tracker, triage labels, and domain-docs conventions
  (`docs/agents/*.md`), plus this `.ai/` protocol layer.
- Composer package skeleton (issue #1): PSR-4 `SageCounseling\AuditLog\` → `src/`, PHPUnit 10 +
  `orchestra/testbench` 9, `.github/workflows/tests.yml` CI (PHP 8.2/8.3), `README.md`.
- Full module ported (issue #3): `AuditLogger`, `Actor`, `NetworkContext`, `ActionType`, `PurposeOfUse(Resolver)`,
  `ResourceTypeAllowList`, `InvalidResourceTypeException`, `DeniedAccessBackstop`, `Models\AuditLogEntry`,
  `Console\Commands\ArchiveAuditLogs`, `AuditLogServiceProvider`, publishable `config/audit-log.php`, 2
  publishable migrations (`audit_logs`, `audit_log_archives`). 22 tests pass via Testbench + sqlite
  (`vendor/bin/phpunit`).
- Two deliberate deviations from compliance-portal's source, both recorded in `docs/adr/0001-*.md`: DB
  connection names (`connection`/`migration_connection`) and actor resolution (`ActorResolver` interface) are
  config-driven, not hardcoded — see that ADR for why.
- `composer.json` sets `config.policy.advisories.block: false` — this Composer version blocks dependency
  resolution for any `laravel/framework`/`orchestra/testbench` version affected by an unfixed advisory, which
  made every viable version combination unresolvable. This only affects which versions `composer update` is
  willing to pick during local resolution, not runtime behavior.

## What's broken / blocked

- No consuming app uses this package yet — compliance-portal still has its own local `app/AuditLog/*`, and RPS
  has never been wired (handoff doc steps 3–4).
- Every consuming app must supply its own `actor_resolver` config value (a class implementing `ActorResolver`)
  before `DeniedAccessBackstop` can be used — there is no default, and the package throws a clear error if it's
  unset.
- Remaining open design questions from `docs/audit-log-handoff.md` not yet touched by this port: relationship
  to `bi-reflector`'s `HasPhiAuditLogging` (explicitly deferred per the doc), and anything about actually
  registering resource types / denial-backstop routes for a second real consumer (RPS) — the config surface
  exists but no app has populated it yet.

## Next milestone

Wire `compliance-portal` to consume this package in place of its local `app/AuditLog/*` (handoff doc step 3),
verifying no behavior change — including migrating its existing `audit`/`audit_migrator` connection config to
this package's config keys.

## Recent decisions

- Decided *not* to fold this into `sage-counseling/helpers`; it gets its own package, repo, and release
  lifecycle (see `docs/audit-log-handoff.md`, "Why this isn't happening inside `helpers`").
- DB connection names and actor resolution are config-driven rather than hardcoded/Spatie-coupled — see
  `docs/adr/0001-configurable-connections-and-actor-resolution.md`.
- Migration history squashed to 2 files (not compliance-portal's 3) since this package has no deployed schema
  of its own yet to preserve history for.
- The archive command/table/retention mechanism was ported in the same pass as the core module, not split into
  a follow-up — it's one coherent mechanism per compliance-portal's `docs/adr/0009-hipaa-audit-log-mechanism.md`.
