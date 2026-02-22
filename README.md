# egamipeaks.com

Music release management site built with Laravel 12 + Filament 5.

## Requirements

- PHP 8.4
- Composer
- Node.js / npm
- SQLite (local dev)

## Setup

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

Admin login (after seeding): `admin@egamipeaks.com` / `password`

## Custom Artisan Commands

### `import-release`

Import a folder of MP3 files as a new Release with Tracks. Reads song title, track number, duration, and embedded cover art from ID3 tags. Artist and release title are collected interactively.

```bash
php artisan import-release "/path/to/mp3 folder"
```

Prompts:
1. **Release title** — defaults to the folder name
2. **Release date** — optional, YYYY-MM-DD format
3. **Artist** — choose from existing artists or create a new one

---

### `release:export`

Export a release (artist, tracks, assets) to a JSON file at `storage/app/exports/release-{slug}.json`. Used as the first step of `release:push`.

```bash
php artisan release:export <id-or-slug>
```

---

### `release:import`

Import a release from a JSON export file, upserting all records (artist, assets, release, tracks). Used on production after `release:push` uploads the file.

```bash
php artisan release:import /path/to/release-slug.json
```

---

### `release:push`

Full pipeline: exports the release locally → SCPs the JSON file to production → runs `release:import` on the remote server.

```bash
php artisan release:push <id-or-slug>
```

Requires SSH config in `.env`:

```env
PROD_SSH_HOST=your-server.com
PROD_SSH_USER=forge
PROD_SSH_PATH=/home/forge/egamipeaks.com
PROD_SSH_KEY=/path/to/private-key   # optional
```

---

### `db:pull`

Pull the production SQLite database down to your local environment, overwriting `database/database.sqlite`.

```bash
php artisan db:pull
```

Uses the same SSH config as `release:push`. Prompts for confirmation before overwriting.

---

## Development

```bash
composer run dev   # starts server + queue + logs
php artisan test --compact
vendor/bin/pint    # code style fixer
```
