# e-Task Lightweight Regression Suite

## Purpose

This suite provides a practical smoke-test layer for the production-readiness program without introducing a heavy testing framework.

It validates the highest-risk operational flows:

1. Authentication
2. RBAC permissions
3. Client creation
4. Portal credential creation
5. PSO creation
6. Service Order creation
7. Workflow progression
8. Invoice creation
9. Receipt creation
10. Secure document download
11. Search
12. Reminders
13. Reports

## One-Command Execution

```bash
C:\xampp\php\php.exe C:\xampp\htdocs\e-task\database\scripts\run_regression_suite.php
```

## Execution Model

- Runs from CLI
- Uses the real application bootstrap
- Replaces the default PDO connection with a nested-transaction PDO wrapper
- Opens one top-level transaction for the full run
- Runs each smoke test inside its own savepoint
- Rolls back the full database state after execution
- Deletes temporary regression files created for document tests

This keeps the suite fast, repeatable, and safe for localhost execution.

## Outputs

- CLI pass/fail report
- HTML report in:

```text
C:\xampp\htdocs\e-task\storage\reports\regression\
```

## Coverage Notes

- This is not line-coverage instrumentation.
- It is an operational smoke suite focused on production-critical business paths.
- It intentionally exercises the service layer, repositories, auth state, permissions, workflow transitions, billing, search, reminders, and report queries.

## Expected Runtime

- Target: under 5 minutes
- Typical localhost runtime should be well below that because it avoids browser automation and external network calls.

## Limitations

- No browser UI automation
- No full unit-test framework
- No SMTP live-send verification
- No file-upload `is_uploaded_file()` browser simulation; document security is tested with a seeded private file and secure download path validation

## When To Run

- Before major releases
- After schema or permission changes
- After workflow or billing changes
- Before production deployment signoff
