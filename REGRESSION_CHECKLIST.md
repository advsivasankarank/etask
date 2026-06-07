# Regression Checklist

Use this checklist alongside the automated smoke run.

## Automated

- Run `C:\xampp\php\php.exe C:\xampp\htdocs\e-task\database\scripts\run_regression_suite.php`
- Confirm CLI summary shows `0 failed`
- Open latest HTML report in `C:\xampp\htdocs\e-task\storage\reports\regression\`
- Confirm runtime is under 5 minutes

## Manual Spot Checks

- Login screen loads in browser
- Dashboard loads for Super Admin
- Client list opens
- Service order list opens
- Billing list opens
- Search page opens
- Reminder page opens
- Reports page opens

## Release Gate

- Migrations are fully applied
- Environment doctor returns no errors
- Regression suite passes
- UAT signoff is complete for impacted modules
- Backup is taken before deployment
