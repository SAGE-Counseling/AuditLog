This file defines mandatory context for AI-assisted development in this repository: environment facts and
hot spots. It is distinct from the root `CONTEXT.md`, which is a domain-language glossary (see
`docs/agents/domain.md`) — the two are not duplicates despite the shared filename. The root `CONTEXT.md`
doesn't exist yet; `docs/agents/domain.md` says it's created lazily by `/domain-modeling` once domain terms
or decisions actually get resolved.

# Project Context

## Project type

Standalone PHP Composer package holding a HIPAA-oriented audit-logging module — access-audit (who looked at
what, and why), ported from `compliance-portal`'s `app/AuditLog/*`. See `docs/audit-log-handoff.md` for the
full plan and `docs/adr/` for decisions made during the port.

## Framework and versions

A real Laravel package: PSR-4 autoload, `AuditLogServiceProvider` (container bindings, publishable
config/migrations, command registration), `AuditLogEntry` as an Eloquent model. Targets PHP ^8.2,
`illuminate/*` ^10|^11|^12 (compliance-portal runs Laravel 10, RPS runs Laravel 12 — both PHP ^8.2).

## Tooling

- Test command: `vendor/bin/phpunit` (Testbench-backed; see `tests/TestCase.php`)
- Formatter: not configured
- Static analysis: not configured

## Hot spots

None yet — no code exists. Append here once a task uncovers a file or pattern that causes conflicts or
regressions, naming the specific failure rather than a general warning.

## Local Development Environment

Windows 11 (win32), PowerShell as primary shell; a POSIX-shell (Git Bash) tool is also available. Path
separators and shell syntax differ between the two — scripts and commands added to this repo should note
which shell they assume, or work in both.

## Server Environment

Not yet applicable — this is a library package with no deployment of its own. Once consumed by
`compliance-portal` and `RPS`, defer to those apps' own `.ai/CONTEXT.md` (if they have one) for server
environment facts.

## Databases

The package ships 2 publishable migrations (`audit_logs`, `audit_log_archives`) rather than requiring each
consuming app to define the schema itself. Connection names are config-driven
(`audit-log.connection`/`audit-log.migration_connection`), not hardcoded — see
`docs/adr/0001-configurable-connections-and-actor-resolution.md`. Tests run both against the same in-memory
sqlite connection; verifying the production privilege-separation (an actual INSERT-only DB role) is each
consuming app's responsibility, not this package's.

## Languages and Frameworks

PHP, targeting Laravel package conventions (PSR-4, Eloquent, service providers). No JS/TS, no other language
runtime involved.

## Tooling Expectations

Write any setup or verification scripts to work from PowerShell first (the primary shell on this machine);
a POSIX-compatible equivalent is a bonus, not a requirement, unless CI needs one.

## Schema/Data Authority

No schema exists yet. Once the `AuditLogEntry` migration is written, that migration file is the schema's
source of truth — don't infer its shape from `compliance-portal`'s copy without checking it hasn't drifted
since the handoff doc was written.
