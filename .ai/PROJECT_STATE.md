# Project State

Live snapshot only — not a history log. Task history lives in closed issues; query the tracker instead of
appending to this file indefinitely. When this file grows a long changelog, trim it back to recent entries
and point further back at the issue tracker (a past cleanup that's worth repeating periodically, not a
one-time task).

## Current status

Pre-code. This repo holds the handoff plan (`docs/audit-log-handoff.md`) for extracting the `AuditLog`
module out of `compliance-portal`'s `app/AuditLog/*` into a standalone Composer package. No package
scaffold, no source code, no tests exist yet.

## What's working

- Repo is connected to GitHub (`SAGE-Counseling/AuditLog`, default branch `master`).
- Agent config scaffolded: issue tracker, triage labels, and domain-docs conventions
  (`docs/agents/*.md`), plus this `.ai/` protocol layer.

## What's broken / blocked

- No Composer package skeleton yet (no `composer.json`, no PSR-4 autoload, no PHPUnit setup).
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
