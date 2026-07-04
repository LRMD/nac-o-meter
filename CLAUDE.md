# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

NAC-O-Meter is a Symfony 6.4 / PHP 8.4 web application that displays LYAC
(Lithuanian Activity Contest) claimed scores for amateur radio operators. It
parses and displays contest results, allows operators to submit logs via
email, and provides statistics and rankings. Frontend assets are built with
Webpack Encore (Node 20, yarn 3 via corepack). `upgrade.md` documents the
PHP 7.4 / Symfony 5.4 → 8.4 / 6.4 migration and its remaining follow-ups.

## Common Commands

### Development Environment

```bash
# Start the dev environment (app on http://localhost:8080, MariaDB seeded from sql/lyac.sql)
podman compose up

# Run the test suite inside the dev container
podman compose exec -w /app app php bin/phpunit --testdox

# Run a single test class or method
podman compose exec -w /app app php bin/phpunit --filter RulesControllerTest
podman compose exec -w /app app php bin/phpunit --filter testRulesPageEnglish

# Self-contained test stack (own MariaDB with lyac_test database)
podman-compose -f compose-test.yaml up

# Clear cache — prefer a hard wipe: stale compiled Twig templates have
# survived cache:clear after vendor upgrades
podman compose exec app rm -rf /app/var/cache/dev
```

### Asset Building

```bash
yarn dev        # development build
yarn watch      # watch mode
yarn build      # production build
# or inside the container:
podman compose exec -w /app app yarn encore dev
```

### CI

`.github/workflows/ci.yaml` runs on every pull request: `php -l` lint on
`src/` + `tests/` (PHP 8.4) and the phpunit suite against a `mariadb:lts`
service seeded from `sql/lyac.sql`. The workflow aliases hostname `mysql`
to 127.0.0.1 because `phpunit.xml.dist` points there.

### Production Deployment

```bash
podman-compose -f compose-prod.yaml up   # build production artifacts
```

Deployment is a plain FTP upload via `lftp` scripts in the repository root
(each sources a local `.config` with `FTP_USERNAME`, `FTP_PASSWORD`,
`FTP_HOSTNAME`):

- `deploy.sh`: full mirror upload of `config/`, `src/`, `templates/`, `translations/`, `vendor/`, `public_html/` + prod cache clear
- `sync.sh`: uploads only files changed vs `master` (`git diff --name-only master..`)
- `backup.sh`: mirrors the remote site into `backup/YYYYMMDD/`

**`vendor/` is built locally and uploaded as-is**, so the container PHP
version (8.4) must match the production host's PHP.

### Database

The schema is initialized from `sql/lyac.sql` (auto-loaded by the MariaDB
container; it contains data and stored functions). Doctrine Migrations is
configured but `src/Migrations/` is empty — schema changes go into
`sql/lyac.sql`.

## Architecture

### Core Entities (attribute-mapped, `#[ORM\...]`)

- **Log**: Contest log submissions containing callsign, band, date, and location (WWL)
- **QsoRecord**: Individual QSO (contact) records within a log
- **Round**: Contest rounds by date and band (names: VHF, UHF, SHF, Microwave)
- **Callsign, Band/BandGroup, Country, Mode, Wwl, Message**: supporting entities

All columns/tables carry explicit `name:` mappings — the DB uses camelCase
column names (`logID`, `callsignID`), so don't rely on the naming strategy.
Entities are plain untyped legacy classes; associations are not mapped
(controllers join manually via DQL).

### Key Components

- **ResultParser** (`src/Utils/ResultParser.php`): Parses yearly results from
  CSV files in `results/` (format: `YYYY_BAND.csv`, semicolon-delimited).
  Implements "best 9 of 12 months" scoring and position multipliers
  (1st=10, 2nd=8, 3rd=6, 4th=5, 5th=4, 6th=3, 7th=2, 8th=1). Microwave
  bands (2G4, 5G7, 10G) are combined into one category.
- **MailjetService** (`src/Service/MailjetService.php`): Sends submitted logs
  via Mailjet API; credentials wired as parameters in `config/services.yaml`.
- **Custom DQL functions** (`src/Utils/`): `grid2lat`, `grid2lon`, `qrb`
  (Maidenhead locator → coordinates/distance), registered in
  `config/packages/doctrine.yaml` alongside beberlei/doctrineextensions.
- **Controllers** inject `ManagerRegistry` via constructor and use
  `#[Route]` attributes. Routes get locale prefixes (lt='', en, pl, uk)
  from `config/routes/annotations.yaml`.

### Frontend

- Webpack Encore, two entries: `app` (Bootstrap 5 via CDN in
  `base.html.twig`, Stimulus) and `map` (OpenLayers, used on round pages)
- Chart.js via symfony/ux-chartjs (home page charts)
- Icons are inline SVG symbols in templates (no icon font dependency)
- Localized month names come from the `months.m1`–`m12` translation keys
  (`translations/messages.{lt,en,pl,uk}.yaml`) — PHP `intl` is not available
  on the shared host, so don't use twig/intl-extra date formatting

### Test Setup Gotchas

- The dev container exports `APP_ENV=dev` as a real env var and Symfony's
  `KernelTestCase` checks `$_ENV` before `$_SERVER`, so `phpunit.xml.dist`
  force-sets `APP_ENV=test` via both `<server>` and `<env force="true">`.
- `DATABASE_URL` in `phpunit.xml.dist` is a **non-forced fallback**: the dev
  container tests run read-only against the seeded `lyac` database, the
  compose-test stack injects its own `lyac_test` URL, CI uses the fallback.
- `config/packages/test/webpack_encore.yaml` sets `strict_mode: false` so
  WebTestCase pages render without a webpack build present.

## Environment Variables

Required in `.env` or container environment:
- `APP_SECRET`
- `DATABASE_URL`: MySQL/MariaDB connection string
- `MAILJET_API_KEY`, `MAILJET_API_SECRET`: Mailjet credentials for log submission
- `MAILJET_RECIPIENT_EMAIL`: Email address to receive submitted logs
