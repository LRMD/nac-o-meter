# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

NAC-O-Meter is a Symfony 5.4 web application that displays LYAC (Lithuanian Activity Contest) claimed scores for amateur radio operators. It parses and displays contest results, allows operators to submit logs via email, and provides statistics and rankings.

## Common Commands

### Development Environment

```bash
# Start the development environment (runs app on port 8080)
podman compose up

# Run tests
./run_tests.sh

# Or manually in container:
podman compose exec -w /app app php bin/phpunit --testdox
```

### Asset Building

```bash
# Development build
yarn dev

# Watch mode
yarn watch

# Production build
yarn build
```

### Production Deployment

```bash
podman compose exec app composer install --no-dev --optimize-autoloader
podman compose exec app yarn encore production
```

### Database

```bash
# Run migrations
podman compose exec app php bin/console doctrine:migrations:migrate

# Clear cache
podman compose exec app php bin/console cache:clear
```

## Architecture

### Core Entities

- **Log**: Contest log submissions containing callsign, band, date, and location (WWL)
- **QsoRecord**: Individual QSO (contact) records within a log, including callsign, time, signal reports, and gridsquare
- **Round**: Contest rounds by date and band
- **Callsign**: Amateur radio operator callsigns
- **Band/BandGroup**: Radio frequency bands (144MHz, 432MHz, 1296MHz, microwave bands)

### Key Components

- **ResultParser** (`src/Utils/ResultParser.php`): Parses yearly results from CSV files in `results/` directory. Handles scoring calculations including "best 9 of 12 months" and position multipliers for rankings.
- **MailjetService** (`src/Service/MailjetService.php`): Sends submitted logs via Mailjet email API
- **Custom DQL Functions** (`src/Utils/`): Grid2Lat, Grid2Lon, QRB (distance calculation) functions for Maidenhead locator coordinate conversion

### Directory Structure

- `results/`: CSV files with yearly results per band (format: `YYYY_BAND.csv`, semicolon-delimited)
- `sql/lyac.sql`: Database initialization script (auto-loaded by MariaDB container)
- `translations/`: Localization files (Lithuanian default locale)
- `public_html/`: Web root directory (Symfony configured via `extra.public-dir`)

### Frontend

- Webpack Encore with SCSS support
- Bootstrap 5, Chart.js for statistics
- OpenLayers (ol) for maps
- Stimulus controllers for interactivity

### Contest Scoring

Scores are calculated from CSV result files using "best 9 months" rule. Position multipliers for yearly rankings: 1st=10, 2nd=8, 3rd=6, 4th=5, 5th=4, 6th=3, 7th=2, 8th=1. Microwave bands (2G4, 5G7, 10G) are combined into a single category.

## Environment Variables

Required in `.env` or container environment:
- `DATABASE_URL`: MySQL/MariaDB connection string
- `MAILJET_API_KEY`, `MAILJET_API_SECRET`: Mailjet credentials for log submission
- `MAILJET_RECIPIENT_EMAIL`: Email address to receive submitted logs
