# RBAC Cleanup Report

## Objective

Remove remaining scattered role-based authorization shortcuts and replace them with centralized permission and policy-based checks, without changing business behavior.

## Modified Files

- `app/Models/User.php`
- `app/Core/Auth.php`
- `app/Services/DocumentAccessService.php`
- `app/Services/SearchService.php`
- `app/Repositories/SearchRepository.php`
- `app/Middleware/RoleMiddleware.php`
- `modules/Users/UserController.php`
- `app/Services/UserService.php`

## Cleanup Performed

### 1. Centralized actor policy in auth/session

- Added `actor_type` to the authenticated session payload
- Introduced:
  - `Auth::actorType()`
  - `Auth::isPortalUser()`
  - `Auth::isConsultantUser()`

This moves portal/consultant identity handling into one policy surface instead of repeating raw role checks.

### 2. Document access cleanup

Replaced consultant and client access shortcuts in document authorization with policy-based actor checks:

- `Auth::isPortalUser()`
- `Auth::isConsultantUser()`

Business rules are unchanged:

- portal users still only access their own allowed client-linked documents
- consultant users still only access their own consultant-linked documents
- internal users still require document permissions

### 3. Search access cleanup

Replaced remaining `hasRole('CONSULTANT')` usage in search source visibility with policy checks.

Search context now carries:

- `actor_type`
- `permissions`

instead of relying on raw role arrays for consultant/client scope detection.

### 4. Search repository cleanup

Removed direct client/consultant role array authorization checks in repository scope helpers.

Repository access scoping now uses:

- `actor_type === 'PORTAL'`
- `actor_type === 'CONSULTANT'`
- permission checks

### 5. User management type inference cleanup

Replaced portal user inference based on role string comparison with contact linkage:

- `client_contact_id` present => `PORTAL`
- otherwise => `INTERNAL`

This keeps user management behavior intact while avoiding raw `CLIENT` role comparison in controller/service authorization branching.

### 6. Role middleware hardening

`RoleMiddleware` is not currently used by routes, but it was still refactored to avoid direct `Auth::hasRole()` dependence for authorization decisions.

It now uses:

- actor policy checks for portal/consultant
- permission mapping for remaining role aliases

## Remaining Role Usage

The following role-related code intentionally remains because it is part of administration or data modeling, not authorization shortcuts:

- role selection in user forms
- role persistence in user creation/update
- consultant/client linked module values in business data
- role-based reminder escalation targets
- role labels shown in UI
- centralized actor-type derivation inside `User` model

## Security Impact Assessment

### Positive Impact

1. Reduced authorization drift

Scattered `CLIENT` and `CONSULTANT` checks were consolidated into one policy layer, lowering the chance of inconsistent future behavior.

2. Improved auditability

Portal and consultant access paths are now easier to reason about because they flow through centralized helpers.

3. Safer repository/service boundaries

Search and document access now rely less on ad hoc role-array inspection and more on normalized actor context.

4. Better future migration path

This cleanup makes it easier to continue moving toward fully permission-driven or policy-driven access control without breaking workflows.

### Residual Risk

1. `Auth::hasRole()` still exists for compatibility

It is now normalized through actor policy for the portal/consultant cases, but should be treated as legacy compatibility rather than a preferred authorization API.

2. Actor type is still derived from underlying user characteristics

That derivation is now centralized, but consultant identity still ultimately comes from user role assignment at the model boundary.

3. Administrative role handling remains in user-management code

This is intentional and not a vulnerability, but it means role catalog concepts still exist where they are part of user provisioning.

## Validation

Regression suite executed after cleanup:

- `13/13` tests passed
- latest verified report:
  - `storage/reports/regression/regression_report_20260607_224131.html`

## Conclusion

The remaining authorization shortcuts for portal and consultant actor handling were successfully migrated to centralized policy-based checks without changing business logic.
