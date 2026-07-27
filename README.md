# Sales, Inventory & CRM

A full-stack Sales, Inventory & CRM system built as a **modular monolith**: a
Laravel (PHP) JSON API and a React + TypeScript SPA, running entirely on Docker.

It manages a product catalog and per-branch stock, records sales with **atomic
inventory deduction that makes overselling impossible under concurrency**,
maintains per-customer purchase history, detects lost customers, assigns them to
employees for recovery, and credits employee KPI **idempotently** when a
won-back customer buys again.

---

## Completed features

**Core — Sales & Inventory**
- ✅ Product catalog — name, SKU (unique), price, and stock quantity (per branch).
- ✅ Record a sale (header + line items) with a per-branch, race-safe invoice number.
- ✅ Automatic stock deduction on sale, written to an append-only ledger.
- ✅ Oversell prevention — atomic transaction + `SELECT … FOR UPDATE` row locks +
  a `CHECK (quantity >= 0)` DB backstop; proven with a two-process race test.

**Core — CRM**
- ✅ Per-customer purchase history: records, purchase frequency, last purchase date.
- ✅ Lost-customer detection with a **configurable** threshold (default 90 days),
  runnable on demand and scheduled daily.
- ✅ Customer re-engagement — queued **email + SMS** promotional notification
  (email + a database audit row always; SMS when a phone and Vonage are configured).
- ✅ Employee assignment — admins assign lost customers to employees (admin-only, policy-enforced).
- ✅ KPI tracking — an assigned customer's next purchase **automatically** increases
  the assigned employee's KPI score, **idempotently** (a second purchase adds nothing).

**Bonus**
- ✅ Multi-branch support — multiple locations, branch-specific inventory, sales by branch.
- ✅ Email invoices — an HTML email with a generated **PDF** attachment, sent
  automatically after a purchase (queued, after commit).
- ✅ E-commerce integration API — a token-secured, rate-limited REST feed exposing
  only `{ sku, name, price, available_stock }`.

**Engineering**
- ✅ React SPA (auth, POS, products, sales, customers, CRM, KPI dashboards).
- ✅ Sanctum cookie auth across origins (SPA ↔ API), RBAC via spatie/laravel-permission.
- ✅ Ledger-backed counters (stock, KPI, customer aggregates) that are rebuildable.
- ✅ 16-test Pest suite against MySQL, including the concurrency oversell race.
- ✅ Seeders that create realistic Products, Customers, Employees, **Sales, and
  Transactions** — the app is testable immediately after seeding.

---

## Tech stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13 (PHP 8.3+) |
| Database | MySQL 8 |
| Cache / Queue | Redis |
| Mail (SMTP) | Mailpit locally (Mailtrap-compatible — see [Email](#email-smtp--mailtrap)) |
| Frontend | React 19 + TypeScript + Vite + Tailwind |
| Infra | Docker Compose (nginx, php-fpm, queue worker, scheduler, mysql, redis, mailpit, vite) |

---

## Prerequisites

- Docker + Docker Compose. **Nothing else** is required on the host (PHP,
  Composer, Node, MySQL, and Redis all run in containers).

---

## Setup (from a fresh clone)

```bash
# 1. Create the env files the containers read (they are git-ignored).
cp .env.example .env
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env

# 2. Build and start all services. On first boot the app container runs
#    `composer install` and generates APP_KEY automatically.
docker compose up -d --build

# 3. Create the schema and load realistic sample data.
docker compose exec app php artisan migrate --seed
```

That's it. Open the app:

| Service | URL |
|---|---|
| React SPA | http://localhost:5173 |
| Laravel API | http://localhost:8000 |
| Mailpit (email inbox) | http://localhost:8025 |

> **Note:** step 1 is required because `docker-compose.yml` loads `backend/.env`
> via `env_file`, and env files are git-ignored (they hold credentials). The app
> container generates `APP_KEY` on first boot, so no manual `key:generate` is needed.

### Demo credentials

| User | Email | Password | Role |
|---|---|---|---|
| Admin | `admin@salescrm.test` | `password` | admin |
| Employees | `alice@salescrm.test`, `bob@…`, `carol@…` | `password` | employee |

The seeder also prints an e-commerce **`store:read` API token** to the console —
copy it for the feed demo, or mint one any time (see [E-commerce feed](#e-commerce-feed)).

---

## Environment configuration

Three env files, each copied from a committed `.env.example`:

- **`.env`** (repo root) — Docker Compose defaults (DB name/user/passwords used to
  provision MySQL). Dev-only throwaway values.
- **`backend/.env`** — Laravel config. Key values already set for Docker:
  `DB_HOST=mysql`, `REDIS_HOST=redis`, `MAIL_HOST=mailpit`, `QUEUE_CONNECTION=redis`,
  and the Sanctum cross-origin block (`SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173`,
  `SESSION_DOMAIN=localhost`, `FRONTEND_URL=http://localhost:5173`). `APP_KEY` is
  auto-generated on first container boot.
- **`frontend/.env`** — `VITE_API_URL=http://localhost:8000` (the API origin the SPA calls).

### Email (SMTP / Mailtrap)

Mail is sent over **real SMTP**. The default target is the bundled **Mailpit**
service (inbox at http://localhost:8025) so the stack is self-contained and needs
no external account. To send through **Mailtrap** instead, edit `backend/.env` —
no code changes needed:

```env
MAIL_SCHEME=tls
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=<your-mailtrap-user>
MAIL_PASSWORD=<your-mailtrap-pass>
```

(A commented Mailtrap block is already present in `backend/.env.example`.)

---

## Database: migrations & seeders

```bash
# Run migrations only
docker compose exec app php artisan migrate

# Migrate + seed in one step (used in setup above)
docker compose exec app php artisan migrate --seed

# Re-seed without re-migrating
docker compose exec app php artisan db:seed

# Rebuild everything from scratch (drops all tables, re-migrates, re-seeds)
docker compose exec app php artisan migrate:fresh --seed
```

### What the seeders create

Running the seeders produces a ready-to-test dataset:

- **3 branches** (Main Store, North Branch, Online) with per-branch inventory and
  opening-balance ledger rows.
- **30 products** across all branches.
- **5 employees** (1 admin + Alice/Bob/Carol) and 1 machine user for the store feed.
- **25 customers** spanning every lifecycle state — active, active-but-stale
  (so `customers:detect-lost` flags them), lost, and lost-and-assigned (so a new
  sale immediately triggers a KPI recovery credit).
- **~90 sales with ~130 line items and matching stock-movement transactions**,
  dated to each customer's history so purchase history, KPI/branch reporting, and
  the inventory ledger all carry real data. Customer aggregates are recomputed
  from these real sales (cache == ledger).

---

## Running & useful commands

```bash
docker compose up -d --build                              # start everything
docker compose ps                                         # service status
docker compose exec app php artisan test                  # run the Pest suite
docker compose exec app php artisan customers:detect-lost # flag lost customers now
docker compose exec app php artisan inventory:rebuild     # rebuild stock cache from the ledger
docker compose exec app php artisan kpi:rebuild           # rebuild KPI scores from the ledger
docker compose restart queue                              # reload the queue worker after editing queued code
docker compose logs -f app                                # tail API logs
```

---

## The end-to-end demo (proves every requirement)

Sign in as **admin** at http://localhost:5173, then:

1. **Record a sale** — *Point of Sale* → pick a customer, add products, *Complete sale*.
   Stock decrements immediately and the invoice detail opens. (a)
2. **Oversell is impossible** — set a product's stock to 1 and check out the same
   unit from two tabs at once: one succeeds, the other gets `INSUFFICIENT_STOCK`,
   and stock never goes negative. Proven deterministically by the concurrency test. (b)
3. **Lost detection** — `docker compose exec app php artisan customers:detect-lost`
   flags customers past the threshold as `lost`. (c)
4. **Assign** — *Lost Customers* → *Assign* a lost customer to an employee. (d)
5. **Recovery, live** — record a sale for that assigned customer → their status
   flips to **recovered** and the assigned employee's **KPI rises on the leaderboard**.
   A second purchase adds nothing (idempotent). (e)
6. **Invoice email** — check Mailpit; the customer received an invoice with a PDF attachment. (f)
7. **Re-engagement campaign** — *Lost Customers* → *Re-engage* (all or selected):
   emails queued (SMS too when Vonage is configured) + a database audit row per customer. (g)
8. **E-commerce feed** — see below; only `{sku, name, price, available_stock}` behind
   a token ability and a dedicated rate limit. (h)

---

## E-commerce feed

Read-only product feed for a third party, authenticated by a Sanctum **token**
with the `store:read` ability (not the SPA session), throttled separately:

```bash
# Mint a token (or copy the one the seeder printed):
docker compose exec app php artisan tinker --execute="echo App\Models\User::where('email','store@salescrm.test')->first()->createToken('feed',['store:read'])->plainTextToken;"

# Call the feed:
curl -s http://localhost:8000/api/v1/store/products \
  -H "Accept: application/json" -H "Authorization: Bearer <TOKEN>"
```

The response contains only the whitelisted fields — never cost, internal ids, or
per-branch breakdown.

---

## Testing

The suite runs against a **dedicated MySQL schema** (`sales_crm_testing`), not
SQLite, so `FOR UPDATE` row locks and the `CHECK` constraint have production
fidelity. It never touches the dev database.

```bash
docker compose exec app php artisan test
```

Covers the critical paths: the two-process **oversell race**, the CHECK backstop,
ledger correctness, price snapshotting, after-commit event dispatch, lost
detection + settings override, **KPI recovery idempotency**, the assignment
policy, and the feed's field whitelist + ability gate.

---

## Architecture

**Guiding principle:** data-integrity operations are **synchronous + transactional**;
side effects are **asynchronous + queued (after commit)**.

- **Mutable counters are caches of append-only ledgers.** `inventories.quantity`,
  `employees.kpi_score`, and customer aggregates are all rebuildable from
  `stock_movements` / `kpi_events` / `sales`. The ledger is the source of truth.
- **Oversell is impossible.** `CreateSaleAction` runs one DB transaction that
  locks every involved inventory row (ordered by `product_id`, deadlock-safe),
  validates all lines before mutating, then decrements — with a DB `CHECK` as the
  final backstop.
- **Prices are snapshotted.** `sale_items.unit_price` is captured at sale time, so
  later price changes never rewrite historical invoices.
- **Side effects fire after commit.** `SaleCompleted` (`ShouldDispatchAfterCommit`)
  → queued listeners update customer stats, credit recovery KPI, and email the invoice.
- **KPI crediting is idempotent.** Gated by an assignment state machine
  (pending/contacted → recovered) with a unique key on `kpi_events.assignment_id`.
- **Cache invalidation is the frontend's coherence mechanism.** After a sale the SPA
  invalidates the `products`, `sales`, `customers`, and `kpi` query keys.

Domain code is organized under `backend/app/Domain/{Catalog,Inventory,Sales,Crm,Support}`.
Key files: [`CreateSaleAction`](backend/app/Domain/Sales/Actions/CreateSaleAction.php),
[`KpiService`](backend/app/Domain/Crm/Services/KpiService.php),
[oversell test](backend/tests/Concurrency/OversellPreventionTest.php),
[`api-client.ts`](frontend/src/lib/api-client.ts).

### Sanctum cross-origin auth

The SPA (`:5173`) and API (`:8000`) are different origins but the **same site**
(`localhost`), so `SameSite=Lax` session cookies work over plain HTTP. It is wired
in [`backend/.env`](backend/.env.example), [`config/cors.php`](backend/config/cors.php)
(`supports_credentials`, explicit origins), and the axios client
(`withCredentials`, `withXSRFToken`, CSRF-cookie priming). A Vite proxy fallback
is documented in [`vite.config.ts`](frontend/vite.config.ts).

### Operational notes

- **Migrations are manual** (`migrate --seed`) so container restarts stay idempotent.
- **The queue worker caches code** — after editing a queued job/listener/notification,
  run `docker compose restart queue`.
- The daily `customers:detect-lost` scan is declared in `routes/console.php` and run
  by the `scheduler` container.
