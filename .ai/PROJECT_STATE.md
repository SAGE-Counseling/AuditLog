# Project State

Live snapshot only — not a history log. Task history lives in closed issues; query the tracker instead of
appending to this file indefinitely. When this file grows a long changelog, trim it back to recent entries
and point further back at the issue tracker (a past cleanup that's worth repeating periodically, not a
one-time task).

## Current status

Composer package skeleton is scaffolded (issue #1). No `AuditLog` source has been ported from
`compliance-portal` yet — `src/` is empty. Handoff plan lives in `docs/audit-log-handoff.md`.

## What's working

- Repo is connected to GitHub (`SAGE-Counseling/AuditLog`, default branch `master`).
- Agent config scaffolded: issue tracker, triage labels, and domain-docs conventions
  (`docs/agents/*.md`), plus this `.ai/` protocol layer.
- Composer package skeleton: `composer.json` (`sage-counseling/audit-log`, PSR-4
  `SageCounseling\AuditLog\` → `src/`), `phpunit.xml`, `.github/workflows/tests.yml` CI, `README.md` —
  mirrors `sage-counseling/helpers`' setup. Verified locally: `composer install` succeeds, `vendor/bin/phpunit`
  runs clean (no tests yet, since no source exists).

## What's broken / blocked

- No source code or tests exist yet — `src/` and `tests/` are empty placeholders.
- Whether this package needs a framework dependency (e.g. `illuminate/database` for the `AuditLogEntry`
  Eloquent model) is unresolved — deliberately left out of `composer.json` until the porting step actually
  needs it, per this repo's "resolve open questions as they're hit" approach.
- Several open design questions block a clean extraction — see
  `docs/audit-log-handoff.md`'s "Open questions to resolve before/during extraction" section (relationship
  to `bi-reflector`'s `HasPhiAuditLogging`, migration ownership, resource-type registration API,
  `DeniedAccessBackstop` wiring, naming/namespace, versioning independence).

## Next milestone

Scaffold the package (Composer skeleton, PSR-4 autoload, PHPUnit, CI) and port `app/AuditLog/*` from
`compliance-portal` largely as-is, per `docs/audit-log-handoff.md`'s suggested next steps — resolving open
questions as they're hit rather than up front.

## Recent decisions

- Decided *not* to fold this into `sage-counseling/helpers`; it gets its own package, repo, and release
  lifecycle (see `docs/audit-log-handoff.md`, "Why this isn't happening inside `helpers`").
