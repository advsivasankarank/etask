# e-Tasks User Manual

## Overview

e-Tasks is a compliance and operations platform for managing:

- clients
- pre-service orders
- service orders
- workflow progression
- billing and receipts
- consultant activities
- secure documents
- enterprise search
- reminders and reports

## Login

1. Open the application URL
2. Enter username and password
3. Change password on first login if prompted

## Main Navigation

The primary header exposes:

- Dashboard
- Users
- Clients
- Service Orders
- Client Portal
- Billing
- Consultants
- Reports
- Search
- Reminders

Visible menu items depend on permissions.

## Users Module

Use this module to:

- create internal users
- create portal users
- edit user details
- archive or activate users
- reset passwords

## Clients Module

Use this module to:

- create clients
- edit client details
- assign CRM
- capture PAN, TAN, GSTIN, Aadhaar
- save portal credentials
- view linked service orders
- archive clients

## Client Portal / PSO

Portal users can:

- create PSO
- upload supporting documents
- view PSO status

Internal users can:

- review PSO
- recommend approval
- approve PSO
- reject PSO

Approved PSO converts into a Service Order.

## Service Orders

Use Service Orders to:

- create SO manually
- view stage and SLA
- capture work-basis metadata
- monitor closure status

Period behavior:

- ITR: assessment year
- GST monthly: month and year
- GST quarterly: quarter and year
- annual work: annual period label

## Workflow

Workflow actions include:

- advance stage
- record payment
- capture acknowledgement / ARN
- mark e-verification complete
- complete procedural closure
- complete accounting closure
- complete final closure

Important:

- acknowledgement is mandatory before procedural closure
- final closure is blocked if consultant payment is pending

## Billing

Use Billing to:

- add disbursements
- create invoices
- record payments
- generate receipts

Invoice total includes:

- service fee
- recoverable disbursements
- tax
- advance adjustment

## Consultants

Use Consultants to:

- assign consultant to case
- upload deliverables
- review deliverables
- upload consultant bill
- review consultant bill
- record consultant payment

## Documents

Documents are not opened through direct filesystem paths.

Use the provided document links inside the app. Access is permission- and ownership-controlled.

## Search

Available search modes:

- quick search
- global search
- advanced search
- search history

Search sources include:

- clients
- service orders
- portal credentials
- invoices
- receipts
- consultants
- documents

## Reports

RC1 report set:

- Client Register
- Service Order Register
- Invoice Register
- Receipt Register
- Outstanding Report
- GST Summary
- Revenue Report
- Document Access Report

## Reminders

Use Reminders to:

- review summary
- manage templates
- manage escalation rules
- run scheduler
- open reminder reports

## Good Practices

- archive instead of delete
- use search before creating duplicate records
- record notes clearly during workflow transitions
- review reminders daily
- verify financial entries before closure
