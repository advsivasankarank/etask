# Regression Coverage Summary

## Scope

The lightweight regression suite covers these production-critical areas:

| Area | Coverage Type |
| --- | --- |
| Authentication | Login success and failure |
| RBAC | Permission presence and restricted access checks |
| Client Master | Create client, contact creation, encrypted Aadhaar storage |
| Portal Credentials | Standard + custom portal credential storage with encryption |
| Client Portal / PSO | Portal-linked PSO creation |
| Service Orders | Direct SO creation and numbering |
| Workflow | Stage movement, payment stage, acknowledgement, procedural closure |
| Billing | Disbursement, invoice, payment, receipt |
| Documents | Secure access allow/deny + audit logging |
| Search | Global search and search history logging |
| Reminders | Overview + scheduler execution |
| Reports | Client, SO, invoice, receipt, GST, and document access reporting |

## Not Covered

| Area | Reason |
| --- | --- |
| Browser rendering | Deliberately excluded from lightweight suite |
| Real SMTP delivery | Avoids sending external messages during smoke runs |
| Full upload browser lifecycle | CLI runner cannot emulate `is_uploaded_file()` browser semantics cleanly |
| Performance benchmarking | Separate concern from smoke correctness |

## Assurance Level

This suite is designed to catch:

- Broken bootstrap/config issues
- Permission regressions
- Business flow breakage in core service paths
- Billing chain failures
- Search/report wiring regressions
- Secure document access regressions

It is not intended to replace:

- Full UAT
- Exploratory testing
- Security penetration testing
- Performance/load testing
