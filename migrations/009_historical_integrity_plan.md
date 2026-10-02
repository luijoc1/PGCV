# Historical integrity: policy applied on October 2, 2026

Read-only audit on October 2, 2026:

| Finding | Count |
| --- | ---: |
| Cart rows with missing user and existing product | 5 |
| Sales with missing user | 17 |
| Distinct missing user IDs referenced by those sales | 3 |
| Orphan sales without details | 0 |
| Details with missing product | 0 |
| Details without historical price snapshot | 50 |
| Details with historical price snapshot | 3 |
| Checkout requests with missing user or sale | 0 |

Repeat with `php tools/audit_historical_integrity.php`. The command runs a
read-only transaction and prints counts and foreign-key metadata, no
personal data. Existing foreign keys cover product/category and detail/sale.

## Policy accepted by the owner on October 2, 2026

1. Copy the five orphan cart rows, with their original IDs, user/product
   references, quantity and initial date, into a dedicated archive table.
   Include archive timestamp and reason. Verify an exact copy before removing
   them from the active cart table. Do not reassign them to another customer.
2. Preserve all 17 sales and their details, transaction numbers, totals,
   dates, status and billing information. Add nullable `sales.legacy_user_id`
   to preserve the original missing user ID; make `sales.user_id` nullable
   and set it to NULL only for confirmed missing users. Never create fake
   accounts or infer account ownership from names or email-like text.
3. Add foreign keys with `ON DELETE RESTRICT ON UPDATE RESTRICT`:
   `cart.user_id -> users.id`, `cart.product_id -> products.id`,
   `sales.user_id -> users.id`, `details.product_id -> products.id`,
   `checkout_requests.user_id -> users.id`, and
   `checkout_requests.sales_id -> sales.id`. NULL historical sale owners
   are allowed; checkout sale IDs already allow NULL while pending.
4. Reject deletion of referenced users with a clear administrative message.
   Existing user activation is not a deactivation workflow; implement a
   separate deliberate deactivation action if needed. Do not silently
   convert Delete into Deactivate. Product deletion already rejects
   references in cart/details; retain that rule and reinforce it in SQL.
5. Leave the 50 historical details without price snapshots unchanged.
   Current catalog prices cannot prove past prices. Keep the existing
   estimation notice and stored sale totals; reconstruct snapshots only
   from reliable transaction evidence in a separate reviewed operation.

This policy preserves historical sales, retains the original missing
account references for investigation, and prevents new orphan references.
It intentionally blocks deletion of accounts tied to sales instead of
silently breaking their ownership relationship.

## Required implementation and verification before live application

- The implementation is `includes/historical_integrity.php`, with explicit
  expected orphan counts, schema preflight, transactional archive/cleanup,
  and retryable DDL. The live application is recorded below.
- Run `php tools/verify_historical_integrity.php` to create a random isolated
  database from the clean schema, simulate legacy orphan references, verify
  migration/retry and restrictions, and remove only that test database.
  The current verification passes 29 MariaDB checks; the PHP suite passes
  278 tests and 3665 assertions, and the general integration suite passes
  49 checks. No live rows were migrated during these checks.
  The live migration CLI was subsequently executed against the daily
  database after owner authorization; see the application record below.
- `php tools/backup_and_verify_restore.php` creates a source backup under
  `storage/backups` using an InnoDB read-only repeatable-read snapshot, then
  reads that SQL file back into a randomly named isolated database. It
  compares every table's row count, ordered content digest and exact CREATE
  TABLE digest, writes a verification manifest, and drops the test database.
  It rejects non-InnoDB tables, views, triggers, routines and events rather
  than silently omitting unsupported objects. SQL and manifest contain
  protected data/evidence and must remain outside Git and public HTTP access.
  A SQL file without a matching successful manifest is not verified.
- A backup of all 14 current tables was restored and verified on October 2,
  2026 before live application. This preliminary snapshot does
  not replace a fresh verified backup while writes are paused immediately
  before live migration. Concurrent schema changes must also be paused.
- Pause application writes during backup, cleanup and DDL. MariaDB DDL
  commits implicitly: do not describe the entire migration as transactional.
- Create and validate a full protected backup; verify restoration in an
  isolated database. Save a copy outside the server before deployment.
- Archive and normalize the confirmed orphan rows under transaction and
  re-audit before DDL. Record affected IDs in protected local evidence,
  not in public logs or version-controlled documentation.
- Inspect exact column types, storage engines and existing constraint
  definitions. An existing constraint name alone does not prove equivalence.
- Support safe retry after each completed step. Stop on unexpected counts,
  incompatible existing constraints or newly appearing orphan references.
- Update the clean schema and user-deletion handling, then verify with
  fictitious data in isolated MariaDB: orphan inserts rejected, referenced
  deletions rejected, unrelated deletions allowed, archived values retained,
  historical sale queries/PDF still available to administrators, historical
  NULL owners inaccessible to customers, checkout and migration retry work.
- Apply live changes only after reviewing those results and confirming the
  concrete affected counts. No stock, prices or totals should be recalculated.

## Reviewed live command sequence (requires owner authorization)

1. Stop scheduled jobs, CLI processes and external database writers/schema
   changes. The Apache gate cannot stop these or requests already running.
2. Run `php tools/maintenance.php on`. Check that public and admin PHP URLs
   return HTTP 503. Wait for prior requests to finish. The root `.htaccess`
   gate assumes the `/PGCV` directory under Apache DocumentRoot; update that
   condition if the installation path changes. This does not work with the
   PHP development server, which ignores `.htaccess`.
3. Audit once more and, with the currently reviewed counts, run:

   ```text
   php tools/migrate_historical_integrity.php --apply --expected-cart=5 --expected-sales=17 --external-writers-paused
   ```

   The last option explicitly acknowledges the operator stopped other
   writers and drained prior requests. The command requires the HTTP marker,
   holds an exclusive maintenance control lock, rejects unexpected counts,
   creates and restores a new backup, checks source data/schema against the
   backup, and records original IDs in protected local migration evidence.
   Only then does it apply cleanup/DDL and re-audit all six constraints.
   Maintenance remains enabled after success or failure.
4. Review the audit and protected evidence, then explicitly reopen using
   `php tools/maintenance.php off` and resume paused jobs. Check public/admin
   availability. Order confirmation and real email are separate permissions.
5. On failure do not restore automatically over the current database. Keep
   maintenance active, inspect completed DDL and evidence, and determine
   safe retry counts. After completed cleanup, a retry may require explicit
   `--expected-cart=0 --expected-sales=0`; do not reuse old counts blindly.

`php tools/verify_historical_migration_cli.php` verifies this sequence with
fictitious data in a random database and temporary Apache fixture. Its 15
checks cover HTTP pause/reopen, missing gate, external writer acknowledgement,
unexpected counts, concurrent reopening rejection, verified backup plus full
migration, archived originals, historical owners, protected evidence and
idempotent retry. Temporary database and fixture are removed in `finally`.
The daily HTTP gate was never enabled during that verification.

## Live application record — October 2, 2026

The owner authorized the prepared operation after clarification that it
preserves existing sales and does not recover missing accounts or switch
databases. No related Windows scheduled tasks were found. Public/admin PHP
requests returned HTTP 503 during maintenance; two checks found no other
active connections to the application database before application.

The command created `history-preflight-20261002-184532-0e9dd1e6.sql`, restored
it into an isolated database, verified all 14 table structures and contents,
and removed the temporary database before applying the migration.

- Five orphan carts were copied exactly to `cart_orphan_archive` and removed
  from active carts.
- Seventeen existing sales now have NULL `user_id` and retain the original
  missing account ID in `legacy_user_id`. All other sale fields and details
  were preserved; no prices, stock or totals were recalculated.
- All six requested RESTRICT foreign keys were verified. Combined with the
  two existing foreign keys, the database now has eight relationships.
- A read-only comparison reconstructed the original cart/sale values from
  the archive/legacy references and verified all 14 original table content
  digests against the pre-migration backup. Protected migration evidence
  records this preservation verification and affected IDs.
- Both historical and general integrity audits reported zero invalid or
  orphan references. The 50 details without snapshots remain unchanged.
- Maintenance was removed; the public home and administrative home/users
  routes returned HTTP 200 (administrative requests without a session pass
  through the authentication guard). Backup and evidence return HTTP 403
  and remain ignored by Git.

No orders were confirmed, real emails sent, commits created or deployment
performed. Copying the protected backup outside the server remains a
deployment preparation task.

## Alternative considered before application

Keep the current orphan rows untouched and defer the affected foreign keys
while researching backups to recover the missing accounts. Enforce future
reference validation in application writes meanwhile. This avoids changing
historic owner IDs, but leaves incomplete database-level integrity. Do not
invent recovered accounts without evidence.
