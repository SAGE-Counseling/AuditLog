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

Not yet available — this package is pre-code; source is still being ported from `compliance-portal`. See
[`docs/audit-log-handoff.md`](docs/audit-log-handoff.md) for the current plan.

## Testing

```bash
composer install
vendor/bin/phpunit
```
