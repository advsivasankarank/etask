# Phase 8 Production Deployment Report

Date: 14 July 2026
Environment: Live production (`https://etask.etaxadv.com`)
Branch: `production-live`

## Outcome

Phase 8 production deployment and live smoke validation completed successfully. The cPanel production checkout was updated from GitHub, the live application was verified through authenticated desktop and mobile routes, and the production web root continued to block private application paths.

The deployment exposed and corrected one environment-specific rewrite rule that prevented the bare subdomain and clean routes from reaching `public/index.php`. The corrected production release is operational.

## Deployment state

- cPanel repository: `e-Tasks Production`
- Repository path: `/home7/etaxadv/etask`
- Checked-out branch: `production-live`
- Runtime fix commit: `63064ac` (`Fix production front-controller rewrite`)
- Deployment model: the subdomain serves the repository's `public/` checkout directly
- cPanel `.cpanel.yml` deployment action: not configured or required for this direct-checkout model

## Production correction

`public/.htaccess` contained a local-only `RewriteBase /etask/public/`. On the production subdomain this rewrote requests toward a nonexistent nested path and caused the bare domain to return 404.

The hardcoded rewrite base was removed and `DirectoryIndex index.php` was made explicit. Per-directory rewriting now resolves through the deployed `public/index.php` in both local subdirectory and production subdomain environments.

## Authenticated desktop smoke

The following 12 production routes rendered without a server error, document-level overflow, missing main landmark, or duplicate/missing page heading:

1. Dashboard
2. Client Register
3. Service Order Register
4. Document Register
5. DSC Register
6. Workforce Dashboard
7. Accounts Dashboard
8. Reports Dashboard
9. Enterprise Search
10. Reminders
11. Roles & Permissions
12. Security Settings

## Mobile smoke

At a 390 × 844 viewport, the following six production routes rendered without horizontal document overflow or unnamed form controls:

1. Dashboard
2. Client Register
3. Create Client
4. Create Service Order
5. Reports Dashboard
6. Roles & Permissions

Each screen retained one `h1` and the accessible mobile navigation control.

## Live security verification

- Bare `https://etask.etaxadv.com/` returns the application successfully.
- Clean `https://etask.etaxadv.com/dashboard` routing works without `index.php`.
- HTTPS is active and the application returns HSTS with a one-year max age and subdomain coverage.
- `X-Content-Type-Options`, `X-Frame-Options`, and `Referrer-Policy` headers are present.
- `/.env` and `/.git/HEAD` return 403 without exposing content.
- `/storage/` returns the application's safe not-found page.
- Root application, database-script, temporary cleanup, and log paths did not expose source or private content during the pre-deployment exposure sweep.
- The live Security Settings screen shows audit logging enabled and a 120-minute session timeout.
- Secret values were deliberately not displayed or copied during verification.

## Error-log review

The cPanel Apache error viewer showed only expected file-not-found entries produced by the controlled private-path exposure checks and unrelated internet scanning. No authenticated smoke route produced an application 500 screen.

## Automated protection

`database/scripts/run_phase8_production_deployment_contract.php` protects seven deployment contracts covering portable front-controller rewriting, directory index behavior, dotfile protection, disabled directory indexes, baseline browser security headers, and production HSTS.

Phase 7 and Phase 6 contracts remained green after the production correction.

## Final status

Phase 8 status: **PASS**

The production application is deployed, clean-route capable, responsive on the tested screens, and synchronized with the `production-live` release process. Business UAT remains the next recommended phase.
