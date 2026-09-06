# CLAUDE.md — Sales_Inventory_CRM

## Backend (`backend/`, run inside container per README)
- Dev: `composer run dev` (runs `php artisan serve`, `queue:listen`, `pail`, `npm run dev` concurrently) — from `backend/composer.json` `scripts.dev`.
- Test: `composer run test` (`artisan config:clear` then `artisan test`) — from `backend/composer.json` `scripts.test`.
- Setup: `composer run setup` — `composer install`, copy `.env`, `key:generate`, `migrate --force`, `npm install --ignore-scripts`, `npm run build` — from `backend/composer.json` `scripts.setup`.

## Frontend (`frontend/`)
- Dev: `npm run dev` (vite) — from `frontend/package.json` `scripts.dev`.
- Build: `npm run build` (`tsc -b && vite build`) — from `frontend/package.json` `scripts.build`.
- Lint: `npm run lint` (oxlint) — from `frontend/package.json` `scripts.lint`.

## Rules
- Env files (`.env`, `backend/.env`, `frontend/.env`) are git-ignored; copy from the matching `.env.example` before running (README setup step 1).
- `docker-compose.yml` loads `backend/.env` via `env_file`; APP_KEY is generated automatically on first container boot.
- Two separate Vite setups exist: `frontend/` (top-level SPA) and `backend/resources/js` (Laravel's own asset pipeline, referenced by `backend/vite.config.js`) — don't conflate them.

## Files worth reading first
- `README.md` — features, tech stack, Docker setup, demo credentials.
- `backend/composer.json` — backend deps and scripts.
- `frontend/src/App.tsx` — frontend route map / feature module list.

Architecture: see ARCHITECTURE.md — read before structural changes
