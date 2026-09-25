# Guardrails

These rules are always in effect unless a task explicitly overrides them.

## Critical (must not be violated)

- No secrets in code, state files, logs, commits, or tests.
- Changes to logic, validation, orchestration, or security boundaries require tests.
  - Exception: schema migrations that only add/modify/drop columns, tables, or indexes do not themselves
    need a test. If a migration ships alongside logic that reads/writes the changed schema, that logic
    still needs tests per the normal rule.
- One task per branch. One branch per task.
- Do not change behavior outside the task scope.
- **NO MAIN/MASTER COMMITS:** Under no circumstances should work be performed directly on the main branch
  — **except** the interactive-session exception below.
- **BRANCH VALIDATION:** Before any file modification, the agent must verify it is on a branch matching the
  pattern `ai/claude/<issue-number>-<slug>`.
- **FAILURE STATE:** If the agent is on the main branch, it must STOP and request the user to create a
  branch, provide the command to create one, or create the branch and switch to it — unless the
  interactive-session exception below applies.
- **INTERACTIVE-SESSION EXCEPTION:** This rule exists to protect unsupervised/AFK agent runs and
  multi-agent collision avoidance, where a branch is the only checkpoint before a bad change lands. It is
  not needed when a human is live in the session watching each tool call. In a live interactive session,
  the agent may commit directly to main — skipping the issue/branch/PR flow — only when **all** of the
  following hold:
  - The user is present in the session and explicitly authorizes the direct commit (a standing instruction
    from an earlier turn does not count; ask each time).
  - The change is low-risk and small in scope: documentation, comments, or config tweaks — not logic,
    validation, orchestration, security boundaries, or anything requiring tests per the Critical rule above.
  - If there is any doubt about whether a change qualifies, treat it as out of scope for this exception and
    use the normal branch/issue/PR flow instead.
- **NO DESTRUCTIVE DB/STATE COMMANDS OUTSIDE THE TEST RUNNER:** Never run a full-reset/wipe command (or its
  programmatic equivalent, e.g. from a REPL/console) against real data — even one written specifically to
  "verify" something. See "Database safety" below for why this keeps happening.

## Important (default expectations)

- Keep diffs small and scoped to the task.
- No drive-by refactors unless explicitly allowed by the task.
- Every change must report:
  - Files changed
  - How to test (exact commands)
- If this repo has a human-facing narrative-docs directory separate from agent context (e.g. a
  `docs/vault/`), don't read or search it when researching code changes. Use it only if explicitly asked to
  update it.

## Progress tracking

- Update `.ai/PROJECT_STATE.md` and post a completion comment on the issue before marking a task done;
  close the issue (or reference it so the merge closes it).
- If progress stalls, comment on the issue with the reason. Leave yourself assigned if you expect to
  resume; unassign if abandoning the approach.

## Git rules

- Do not run git commands unless explicitly instructed by the user or task.
- Do not merge into main without explicit instruction.
- Do not use:
  - `git push --force`
  - `git merge --squash`
  - `git rebase`
  - `git commit --amend`
    unless explicitly instructed and you understand the consequences.
- If unsure, stop and ask.

## PHP/Laravel guardrails (PHP projects only)

This package is scaffolded and its core module ported: `sage-counseling/audit-log`, namespace
`SageCounseling\AuditLog\*`, PHPUnit + Testbench for tests. Conventions below apply now, not just once
scaffolded:

- Follow framework conventions before inventing abstractions.
- Validation: keep validation logic (e.g. `ResourceTypeAllowList` checks, `PurposeOfUseResolver`) in
  dedicated classes, not scattered inline — this package has no HTTP layer of its own, so there are no Form
  Requests; validation belongs in the domain classes that already exist for it.
- Database access: `AuditLogEntry` is an Eloquent model — prefer the ORM/query builder; raw SQL only when
  necessary, parameterized, and justified.
- Prefer dependency injection over static facade calls in new service-style classes (`AuditLogger`,
  `PurposeOfUseResolver`, etc.) — this matters more here than in an app, since a package with static-only
  entry points is harder for consuming apps (compliance-portal, RPS) to override or test against.
- Config-driven extension points (`ResourceTypeAllowList`, per-app resource-type registration) are the
  package's whole reason to exist as a package — don't hardcode anything a second consumer would need to
  vary.
- Migrations: this package should ship a publishable migration for `AuditLogEntry`'s table per the handoff
  doc's open question #2, rather than expecting each consuming app to own the schema — confirm this is still
  the decision before writing one, since it's listed as an open question, not yet settled.

## Database safety

- This package has no deployed database of its own (only Testbench's disposable sqlite `:memory:` in tests) —
  the real-world risk applies once it's wired into `compliance-portal` or `RPS` as a consumer. At that point: a
  Laravel package's
  PHPUnit tests are isolated via Orchestra Testbench, which boots its own in-memory sqlite connection per
  test run (typically configured in `tests/TestCase.php`'s `getEnvironmentSetUp()`) — that isolation only
  applies inside the test runner, not to a `php artisan tinker` or raw `php artisan migrate:fresh` invocation
  run from this repo or a consuming app.
- Practical consequence: a "quick verification" script or REPL command that resets/wipes state does **not**
  automatically run against a disposable database just because the intent was testing — if Testbench's
  environment isn't active, it hits whatever real connection is configured.
- To verify a migration, seeder, or model against a disposable database, do one of:
  - Write it as an actual test using Testbench's isolation and run it via `vendor/bin/phpunit`.
  - If you must inspect state ad hoc, only ever run **read-only** operations — never a reset/wipe, and
    never anything that could resolve to one dynamically (e.g. a command name built from a variable).
- If a destructive command against real local data is genuinely needed (not test verification), say so
  explicitly and confirm with the user first — per the general destructive-action rules.

## Test stance

- Default test command: `vendor/bin/phpunit` (not yet configured — will exist once the package is
  scaffolded; `composer test` may also be added as an alias).
- HTTP/request-level behavior isn't in scope for this package (it has no routes of its own) — most changes
  will need unit or Testbench-based integration tests instead. `DeniedAccessBackstop`'s wiring is invoked by
  a *consuming* app's exception handler, so testing it fully requires a consumer-side integration test, not
  just a unit test in this repo. Schema migrations don't require a dedicated test (see Critical rules);
  code that consumes the changed schema still does.

## Quality tools (optional)

- Formatter: not configured
- Static analysis: not configured
- Do not introduce new tools unless instructed.
- If not configured, note "not configured" in task output rather than inventing an invocation.

## Version compatibility

PHP ^8.2, `illuminate/*` ^10|^11|^12 — matches `compliance-portal` (Laravel 10) and `RPS` (Laravel 12), the two
intended consumers. See `.ai/CONTEXT.md`.
