# Hosting preparation

The owner plans to use hosting but has not selected a provider or domain.
The deployment destination is pending. No files have been uploaded and no
production database or scheduled jobs have been created.

## Hosting requirements

- PHP with PDO MySQL, OpenSSL, DOM, Mbstring, Fileinfo, GD and cURL.
  `composer check-platform-reqs --no-dev` checks dependency requirements;
  it does not verify all application features. Local verification used
  PHP 7.4.30; that branch is unsupported. Choose a supported PHP version
  from <https://www.php.net/supported-versions.php> and verify the project
  in isolation on the exact destination version before publication.
- MariaDB with InnoDB, enforced CHECK constraints and foreign keys. The
  current database was verified with MariaDB 10.4.25. A provider offering
  MySQL instead needs separate schema/import verification; do not assume
  compatibility from a similar product name.
- HTTPS for the public domain, a valid CA bundle for PHP and outbound SMTP
  access to the configured host/port. Local Gmail TLS and authentication
  succeeded; the hosting network and delivery are still unverified.
- Apache 2.4 honoring `.htaccess`, or equivalent rules on the hosting
  server. The current root `php_flag` directives assume the XAMPP PHP
  module; some hosting configurations using PHP-FPM reject them. Configure
  PHP error settings through the provider's supported mechanism instead.
- PHP write permission on `images` and protected local backup storage.
  Uploaded images remain limited to 5 MiB and 20 million pixels, subject
  to the hosting upload limits. Do not make the whole application writable.
- Composer access through SSH or a reproducible local production install.
  Migration/backup tools require CLI and the ability to create an isolated
  restore database; confirm provider permissions before using them there.

## Release files and configuration

1. Deploy from the reviewed Git revision. Exclude `.git`, local development
   folders, `node_modules`, tests, root README/review report and all local
   backups. Retain migration instructions privately, outside public access.
2. Install the locked production dependencies with
   `composer install --no-dev --prefer-dist --optimize-autoloader` in the
   release environment. Avoid updating the lockfile during deployment.
   A clean clone requires this install; tracked Composer metadata alone
   does not contain all dependency files.
3. Local frontend distributions are committed in `bower_components` and
   `dist`; Node is unnecessary on the hosting server. If rebuilding,
   use `npm ci --ignore-scripts` and `npm run sync:frontend` before packaging.
4. Create destination-only `includes/config.php` from the example. Use the
   destination database credentials and HTTPS APP_URL. Set explicit alert
   and contact recipients, SMTP credentials and CA configuration. Never
   commit or place credentials in chat or public documentation.
5. Update the maintenance condition in root `.htaccess` if `/PGCV` is not
   the application path under DocumentRoot. Verify HTTP 503 before relying
   on it. Stop external writers and drain in-flight requests separately.

## Database choice

- For an empty installation, import only `migrations/000_schema.sql` into
  a new empty database, then create the administrator through the CLI tool.
  The schema contains no customer/product data or credentials. Do not apply
  the column-creation scripts again over the clean schema.
- To retain the existing catalog, accounts and history, transfer a fresh
  verified protected backup through a private channel. Import it only into
  an empty destination database, verify contents and point the destination
  configuration to it. Do not import the clean schema over that backup.
- The local historical migration is complete: 5 carts are archived, 17
  sales retain original missing account references and all six additional
  foreign keys are active. Do not rerun it with the original counts.
- Keep a verified backup outside the web server. Backup files contain real
  data and must remain outside Git and public HTTP access.

## Destination verification before publication

Check HTTPS and certificate trust, HTTP denial for `.git`, `vendor`, tools,
tests, migrations, backups and manifests, image script denial, login/roles,
catalog, cart prices/discounts, billing summary and historical PDF/report
access. Verify database integrity and application behavior on the hosting
PHP/database versions. Use an isolated copy for checks that write data.

Order confirmation and real email remain separately authorized operations.
The administrative dashboard can automatically request a stock alert;
plan that check with a controlled mail transport or explicit send permission.
The SMTP diagnostic only connects/authenticates and never sends messages.

Bootstrap/AdminLTE and TCPDF still have the support limitations recorded in
the review. Frontend runtime mitigation does not remove the npm Bootstrap
finding. Payment gateway integration is also outside this release.

Publication requires a selected provider/domain, verified destination,
reviewed release and explicit upload/publication authorization.
