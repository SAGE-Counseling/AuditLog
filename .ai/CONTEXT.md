This file defines mandatory context for AI-assisted development in this repository: environment facts and
hot spots. It is distinct from the root `CONTEXT.md`, which is a domain-language glossary (see
`docs/agents/domain.md`) — the two are not duplicates despite the shared filename. The root `CONTEXT.md`
doesn't exist yet; `docs/agents/domain.md` says it's created lazily by `/domain-modeling` once domain terms
or decisions actually get resolved.

# Project Context

## Project type

Standalone PHP Composer package (not yet scaffolded). Will hold a HIPAA-oriented audit-logging module —
access-audit (who looked at what, and why), extracted from `compliance-portal`'s `app/AuditLog/*` — see
`docs/audit-log-handoff.md` for the full plan.

## Framework and versions

Laravel package conventions expected (PSR-4 autoload, service provider, publishable config/migration), since
the source being ported (`AuditLogEntry` as an Eloquent model, `Handler::render()` integration) is Laravel-
shaped. Target PHP/Laravel version range not yet decided — see `docs/audit-log-handoff.md`, open question
"Versioning independence," and confirm against whatever `compliance-portal` and `RPS` (the two intended
consumers) currently run before scaffolding.

## Tooling

- Test command: not yet configured (`vendor/bin/phpunit` expected once scaffolded)
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

None owned by this repo yet. Per `docs/audit-log-handoff.md` open question #2, the package is expected to
ship a publishable migration for `AuditLogEntry`'s table (standard practice for a Laravel package) rather
than requiring each consuming app to define the schema itself — confirm this is still the decision before
writing the migration, since it's listed as open, not settled.

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
