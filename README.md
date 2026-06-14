# E-commerce Order Management System

A modular Laravel application that demonstrates **clean module boundaries** and
**decoupled cross-module communication**. It is split into independent modules
(via `nwidart/laravel-modules`) that never reference each other's internals — the
Order module works with products purely through a contract published by a neutral
Shared kernel.

## Tech stack

| Concern | Choice |
| --- | --- |
| Framework | Laravel 13 (PHP 8.3+) |
| Modules | `nwidart/laravel-modules` 13 |
| Admin UI | Filament 5 |
| Interactive frontend | Livewire 4 |
| Database | PostgreSQL 18 |
| Local environment | Laravel Sail (Docker) |
| Tests | Pest 4 (feature + arch + unit) |
| Code style | Laravel Duster |
| Static analysis | Larastan (PHPStan) level 6 |
| Docs assistance | Laravel Boost (version-accurate ecosystem docs) |

## Architecture overview

### Modules

```
Modules/
├── Shared/    → domain kernel: contracts, DTOs, the Money value object,
│                a cast and the OrderPlaced event. No models, HTTP, or views.
├── Catalog/   → products & categories, Filament admin, public storefront,
│                implements the ProductCatalog contract.
└── Order/     → orders & order items, status workflow, Filament admin,
│                storefront order creation, consumes the ProductCatalog contract.
```

Dependency direction (both modules depend on the abstraction, never on each other):

```
Catalog ──implements──▶ Shared ◀──consumes── Order
                     (contracts + DTOs)
        Order  ⊥  Catalog   (no direct references — enforced by an arch test)
```

### How cross-module communication works

The core requirement is that **Order must work with product data without ever
touching `Catalog\Models\Product` or any Catalog service**. This is solved with
the **Dependency Inversion Principle**, realised through Laravel's container:

1. The Shared kernel publishes a [`ProductCatalog`](Modules/Shared/app/Contracts/ProductCatalog.php)
   interface and a neutral [`ProductData`](Modules/Shared/app/DataTransferObjects/ProductData.php)
   DTO. Everything that crosses the boundary is a DTO, never an Eloquent model —
   an anti-corruption layer.
2. Catalog implements the contract in
   [`CatalogProductService`](Modules/Catalog/app/Services/CatalogProductService.php)
   and binds it in its service provider:
   `$this->app->bind(ProductCatalog::class, CatalogProductService::class)`.
3. Order resolves `ProductCatalog` from the container (e.g. in
   [`CreateOrderAction`](Modules/Order/app/Actions/CreateOrderAction.php)) and
   gets Catalog's implementation **without any compile-time dependency** on the
   Catalog module.

Two complementary mechanisms are demonstrated:

- **Synchronous (the contract)** — listing products, checking availability and
  decrementing stock. Stock decrement runs inside the order's database
  transaction with a pessimistic `lockForUpdate` to avoid overselling.
- **Event-driven (`OrderPlaced`)** — after an order is persisted, the Shared
  `OrderPlaced` event is dispatched. Catalog reacts via
  [`RecordProductSales`](Modules/Catalog/app/Listeners/RecordProductSales.php)
  with zero coupling to the Order module (it only knows the Shared event).

### Data consistency: snapshotting

`order_items` stores a **snapshot** of each product at purchase time
(`product_name`, `unit_price`, `line_total`) and references the catalog product
by a plain `product_id` — **deliberately not a foreign key**, so the Order
module's schema stays independent of the Catalog tables. Orders therefore remain
historically accurate even if a product's price changes or it is deleted.

### Money handling

All money is stored as **integer minor units (cents)** and represented in the
domain by an immutable [`Money`](Modules/Shared/app/ValueObjects/Money.php) value
object via a reusable [`MoneyCast`](Modules/Shared/app/Casts/MoneyCast.php). There
is no float/decimal arithmetic anywhere, so there are no rounding errors. Filament
forms accept dollars and convert to cents at the page boundary.

### Order status workflow

`OrderStatus` is a backed enum modelled as a linear state machine
(`pending → confirmed → shipped → delivered`). `canTransitionTo()` guards every
transition, and the Filament admin only ever offers the single valid next step.

---

## Setup instructions

### Requirements

- [Docker](https://www.docker.com/) (Laravel Sail runs the app and PostgreSQL in
  containers — no local PHP or PostgreSQL needed).

### Installation

```bash
# 1. Clone and enter the project
git clone <repository-url> e-commerce-order-management-system
cd e-commerce-order-management-system

# 2. Create the environment file
cp .env.example .env

# 3. Install PHP dependencies (uses a one-off Composer container)
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# 4. Start the containers (app + PostgreSQL)
./vendor/bin/sail up -d

# 5. Generate the app key, migrate and seed, build assets
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

> Tip: add a shell alias `alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'`
> so you can type `sail …` instead of `./vendor/bin/sail …`.

---

## Running the application

With `sail up -d` running, the app is served at **http://localhost**.

| Area | URL | Notes |
| --- | --- | --- |
| Storefront (browse) | http://localhost/catalog | Public Livewire product browser |
| Storefront (order) | http://localhost/orders/create | Public Livewire order creation |
| Admin panel | http://localhost/admin | Filament |

**Admin login** (seeded): `admin@example.com` / `password`

### Try the main flow

1. Visit `/orders/create`, add a few products to the cart, enter customer
   details and place the order.
2. Open `/admin` → **Orders** and confirm the new order appears with its line
   items and a `pending` status. Use **Advance to …** to move it through the
   workflow.
3. Open `/admin` → **Products** and confirm the ordered products' stock has
   decreased (proof that Order decremented Catalog stock through the contract).

---

## Running tests

Tests run against an isolated `testing` PostgreSQL database (created
automatically by the Sail container).

```bash
# All tests
./vendor/bin/sail pest

# A single module
./vendor/bin/sail pest Modules/Catalog
./vendor/bin/sail pest Modules/Order

# A single file / filter
./vendor/bin/sail pest --filter="decrements catalog stock"
```

Test coverage includes Product CRUD (Filament), product display (Livewire),
order creation (Livewire), order management & status transitions (Filament),
the cross-module flow (contract + atomic rollback), **architecture tests** that
fail if Order and Catalog ever reference each other, and unit tests for the
Money value object and the status state machine.

## Code quality

```bash
./vendor/bin/sail composer lint      # Laravel Duster (style check)
./vendor/bin/sail composer fix       # Laravel Duster (auto-fix)
./vendor/bin/sail composer analyse   # Larastan / PHPStan level 6
```

All three — Duster, Larastan (level 6, zero errors) and Pest — also run in CI on
every push and pull request (`.github/workflows/ci.yml`).

## Additional patterns & trade-offs

- **Shared kernel module** rather than a Catalog-published API, so the Order
  module contains zero `Modules\Catalog` references (the strictest boundary).
- **No cross-module foreign key** on `order_items.product_id`: chosen to keep
  module schemas independent, accepting that referential integrity for that link
  is enforced in the domain (snapshot + contract) rather than the database.
- **Stock decrement is synchronous** (correctness/immediate feedback) while the
  `OrderPlaced` event is reserved for decoupled, async-capable side effects.
