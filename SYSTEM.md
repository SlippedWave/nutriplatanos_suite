# NutriPlátanos Suite — System Documentation

## Overview

NutriPlátanos Suite is a delivery-route management and sales-tracking web application built for a banana/produce distribution company. Carriers (drivers) go out on routes, sell product to customers, collect payments, track the boxes they move between cold-storage locations and their truck, and log expenses. Coordinators and admins manage customers, users, accounting, and resources from a back-office view.

**Stack**

| Layer | Technology |
|---|---|
| Framework | Laravel 12 (PHP 8.2+) |
| UI / Reactivity | Livewire 3 + Livewire Flux + Livewire Volt |
| Assets | Vite + Tailwind CSS |
| Database | SQLite (configurable) |
| Auth | Laravel's built-in session auth |

---

## User Roles & Access Control

Three roles are defined on the `users` table (`role` column):

| Role | Spanish Label | What they can do |
|---|---|---|
| `admin` | Administrador | Everything, including user management |
| `coordinator` | Coordinador | Customers, accounting, resources — not user management |
| `carrier` | Transportista | Their own routes and the sales/expenses within them |

**Middleware enforcement** happens at two levels:

1. **Route-level** (`routes/web.php`) via the `role:` middleware alias wired to `CheckRoleMiddleware`. Routes under `role:admin` block everyone except admins; routes under `role:admin,coordinator` block carriers.
2. **Service-level** — every Service class checks `Auth::user()->role` before allowing mutations (e.g. `canEditRoute()`, `canEditSale()`, `canEditPayment()`). Carriers can only touch their own active routes and sales created within those routes.

Password confirmation (`password.confirm` middleware) is required before accessing the customer, accounting, resource, and user-management sections.

---

## URL Structure

```
/                              → redirect to /dashboard or /login
/login                         → login page (guest only)
/dashboard                     → main dashboard (all authenticated)
/rutas                         → route list (all authenticated)
/rutas/historial               → historical route archive
/rutas/detalles/{route}        → route detail page
/clientes                      → customer list (admin/coordinator, password-confirmed)
/clientes/detalles/{customer}  → customer detail page
/contabilidad                  → accounting view (expenses + payments)
/recursos                      → resources: cameras and products
/configuracion/perfil          → profile settings
/configuracion/clave           → password settings
/configuracion/usuarios        → user management (admin only, password-confirmed)
```

---

## Domain Model

### Entity Relationship Summary

```
User ──────────── Route ──────── Sale ──────── SalePayment
 |                  |              |
 |                  |──── Expense  |──── ProductList (morph)
 |                  |              |──── Refund ──── ProductList (morph)
 |                  |──── BoxMovement ──── Camera
 |
Customer ──────── Sale
 |──── BoxBalance
 |──── BoxMovement (via Sale → hasManyThrough)

Note (polymorphic) → Sale | Route | Customer | Expense | BoxMovement | User | Product | Camera | Refund
```

---

### Users (`users`)

Stores all application accounts. Fields: `name`, `email`, `phone`, `curp`, `rfc`, `address`, `role`, `active` (boolean), `password`, emergency contact fields, `last_login_at`, `last_login_ip`. Soft-deletable.

---

### Routes (`routes`)

A route represents a single delivery run. One carrier can have at most **one active route** at a time — this is enforced in `RouteService::createRoute()`.

| Field | Notes |
|---|---|
| `carrier_id` | FK → users |
| `title` | Human-readable name, defaults to "Ruta del día DD MMM YYYY" |
| `status` | `active` or `closed` |
| `closed_at` | Set when a route is closed |

A route aggregates: **Sales**, **Expenses**, **BoxMovements**, and **Notes**.

**Lifecycle:** `active` → closed via `CloseRouteModal`. A route with sales cannot be deleted. A route must be closed before it can be soft-deleted.

---

### Customers (`customers`)

Represent businesses or individuals the company sells to. Fields: `name`, `address`, `phone`, `email`, `rfc`, `active`. Soft-deletable. Cannot be deleted if they have any associated sales.

Each customer has one **BoxBalance** record tracking how many product boxes they owe back.

---

### Sales (`sales`)

A sale records what was sold to a customer on a specific route.

| Field | Notes |
|---|---|
| `customer_id` | FK → customers |
| `user_id` | FK → users (who created the sale) |
| `route_id` | FK → routes |
| `payment_status` | `pending`, `partial`, or `paid` |
| `total_amount` | Sum of all ProductList line-items (kept in sync) |
| `paid_amount` | Legacy total; kept in sync with payments |
| `refunded_amount` | Total refunded against this sale |
| `total_amount_excluding_refunds` | Pre-refund total |

`payment_status` is recalculated automatically by `Sale::updatePaymentStatus()` whenever a SalePayment is added, updated, or deleted.

---

### ProductList (`product_lists`)

A **polymorphic** line-item table used by both Sales and Refunds (`listable_type` / `listable_id`). Fields: `product_id`, `quantity` (decimal), `price_per_unit`, `total_price` (virtual computed column = quantity × price_per_unit).

---

### SalePayments (`sale_payments`)

A sale can have multiple partial payments over time.

| Field | Notes |
|---|---|
| `sale_id` | FK → sales |
| `amount` | Payment amount |
| `payment_date` | Date of payment (cannot be future) |
| `payment_method` | `cash`, `transfer`, `check`, `card`, `other` |
| `route_id` | Route where payment was collected (nullable) |
| `user_id` | Who collected it |
| `notes` | Optional free text |

The amount is validated so it cannot exceed the remaining balance (`total_amount - already_paid`). Carriers can only edit payments they created within the last 24 hours; admins have no restriction.

---

### Refunds (`refunds`)

Applied against a specific sale. Two methods:

- **`discount`** — reduces `total_amount` directly on the sale by the `refunded_amount`.
- **`product`** — specifies product quantities returned (stored as a ProductList morph); also stores a `refunded_amount`.

Fields: `sale_id`, `user_id`, `refunded_amount`, `refund_method`, `reason`. One refund per sale (HasOne on Sale). Soft-deletable.

---

### Expenses (`expenses`)

Route-level operational costs (fuel, tolls, etc.). Fields: `route_id`, `user_id`, `description`, `amount`. Soft-deletable.

---

### Cameras (`cameras`)

Physical cold-storage locations ("cámaras") that hold boxes. Fields: `name`, `location`, `box_stock` (integer count of boxes currently in that location). Soft-deletable.

`box_stock` is adjusted directly via `addBoxStock()` / `removeBoxStock()` when boxes leave or return.

---

### BoxMovements (`box_movements`)

Tracks every movement of physical boxes between cameras and routes. Each movement belongs to a **Camera** and a **Route**.

| Movement Type | Direction |
|---|---|
| `warehouse_to_route` | Camera → Truck |
| `route_to_warehouse` | Truck → Camera |
| `route_to_route` | Carried over from prior route |
| `truck_inventory` | Point-in-time truck count |

Fields: `camera_id`, `route_id`, `movement_type`, `quantity` (integer), `box_content_status` (`full` / `empty`), `moved_at`. No `updated_at` (`$timestamps = false`). Soft-deletable.

When a route is updated via `RouteService::updateRoute()`, all existing box movements are soft-deleted and recreated from the submitted form data.

---

### BoxBalance (`box_balances`)

One record per customer. Tracks the cumulative delta of boxes lent vs returned.

| Field | Meaning |
|---|---|
| `delivered_boxes` | Total boxes given to the customer across all sales |
| `returned_boxes` | Total boxes the customer has returned |

`getCurrentBalance()` = `delivered_boxes - returned_boxes`. Updated in `SaleService::createSale()` via `BoxBalanceService::updateBoxBalance()`.

---

### Products (`products`)

Simple catalogue: `name`, `description`. Used in ProductList entries for sales and refunds.

---

### Notes (`notes`)

Polymorphic audit log attached to any entity: `notable_type`, `notable_id`, `user_id`, `content`, `type`. Every Service class automatically appends notes on create, update, and delete operations — creating a full audit trail per record.

---

### Adjustments (`adjustments`)

Weight-based adjustments (discount or credit) on a sale. Fields: `sale_id`, `adjusted_weight_kg`, `adjustment_type` (`discount` / `credit`), `notes`. Soft-deletable. Primarily a data model — not yet wired into the main payment calculation flow.

---

## Service Layer

All business logic lives in `app/Services/`. Every service method returns an associative array:

```php
[
    'success'           => bool,
    'message'           => string,
    'type'              => 'success' | 'exception' | 'validation-exception' | 'authorization' | ...,
    'validation-errors' => array,   // only on validation-exception
    '<entity>'          => Model,   // on success
]
```

All mutations run inside `DB::beginTransaction()` / `DB::commit()` with `DB::rollBack()` on failure.

| Service | Responsibilities |
|---|---|
| `RouteService` | CRUD routes, close routes, permission checks, delegate BoxMovements |
| `SaleService` | CRUD sales, ProductList creation, payment status sync, revenue queries |
| `SalePaymentService` | Add/update/delete payments, mark-as-paid, payment history |
| `RefundService` | CRUD refunds with optional product lists |
| `CustomerService` | CRUD customers, soft/force delete, search |
| `BoxMovementService` | Create individual box movement records |
| `BoxBalanceService` | Update delivered/returned box counts per customer |
| `ExpenseService` | CRUD expenses, totals aggregation |
| `ProductService` | CRUD products |
| `CameraService` | CRUD cameras |
| `UserService` | CRUD users, role management, soft/force delete, password hashing |

---

## UI Architecture

### Livewire Component Pattern

Every entity follows a consistent UI pattern:

```
<Entity>Table         — paginated, searchable, sortable list
Create<Entity>Modal   — form in a modal, dispatches create to service
Update<Entity>Modal   — loads entity, form in a modal, dispatches update
Delete<Entity>Modal   — confirmation modal, dispatches delete
View<Entity>Modal     — read-only detail modal
```

Special composite editors:

- **`ProductListEditor`** — reusable line-item editor (add/remove products with quantity + price) used inside CreateSaleModal and UpdateSaleModal.
- **`BoxMovementsEditor`** — editor for attaching box movements to a route, used in Create/Update/CloseRouteModal.
- **`RefundVisualizer`** — displays refund details within the route/sale context.

### Event Bus

Components communicate via Livewire's browser event system:

| Event | Fired by | Listened by |
|---|---|---|
| `routes-info-updated` | Route modals after any CRUD | `RoutesTable`, `BoxMovementsEditor` |
| `show-message-banner` | Any modal after a service call | `MessageBanner` |

### MessageBanner

`MessageBanner` is a persistent Livewire component mounted in the app layout. It listens for `show-message-banner` events, receives `{ text, type, bannerId, duration }`, and auto-dismisses after `duration` ms. Types map to visual styles: `success` (green), `exception` / `validation-exception` (red), `info` (blue).

### Layouts

| Layout | Used for |
|---|---|
| `layouts/app` | All authenticated pages (sidebar + header) |
| `layouts/auth` | Login page |
| `layouts/routes/layout` | Route detail page (tabs for sales, expenses, box movements) |
| `layouts/errors` | HTTP error pages |

---

## Key Flows

### 1. Starting a Route

1. Carrier opens `/rutas` → clicks "Nueva Ruta".
2. `CreateRouteModal` pre-fills title with today's date and `carrier_id` with the current user.
3. Carrier optionally adds **BoxMovements** (which camera, how many boxes, full/empty, movement type).
4. On submit → `RouteService::createRoute()` validates, creates the Route, then iterates box movements through `BoxMovementService::createBoxMovement()`.
5. A note is created automatically. Event `routes-info-updated` refreshes the table.

### 2. Recording a Sale

1. From `/rutas/detalles/{route}`, carrier clicks "Nueva Venta".
2. `CreateSaleModal` shows customer picker, product line-item editor (`ProductListEditor`), and optional box balance delta (delivered / returned boxes).
3. On submit → `SaleService::createSale()`:
   - Validates all fields.
   - Creates the Sale.
   - Creates ProductList entries.
   - Calls `BoxBalanceService::updateBoxBalance()` to adjust the customer's box ledger.
   - Wraps everything in a transaction.

### 3. Collecting a Payment

1. From the sale table, click "Agregar Pago" → `AddPaymentModal`.
2. `SalePaymentService::addPayment()` validates amount ≤ remaining balance, creates `SalePayment`, and calls `Sale::updatePaymentStatus()` which recalculates `payment_status` and `paid_amount` in place.

### 4. Applying a Refund

1. Open `CreateRefundModal` linked to a sale.
2. Choose method: `discount` (enter amount) or `product` (select products with quantities).
3. `RefundService::createRefund()` creates the Refund (and ProductList if product-based).
4. If `discount`, `Sale::applyDiscountFromRefund()` reduces `total_amount` and `total_amount_excluding_refunds`.

### 5. Closing a Route

1. Carrier clicks "Cerrar Ruta" → `CloseRouteModal`.
2. Carrier may record final box movements (boxes returned to warehouse, truck inventory count).
3. `RouteService::closeRoute()` sets `status = closed`, `closed_at = now()`, persists closing box movements, and appends a closing note.

---

## Database Schema Quick Reference

```
users               id, name, email, phone, curp, rfc, address, role, active, password, ...
routes              id, carrier_id→users, title, status, closed_at, (soft)
customers           id, name, email, phone, address, rfc, active, (soft)
sales               id, customer_id, user_id, route_id, payment_status, paid_amount, total_amount, refunded_amount, total_amount_excluding_refunds, (soft)
product_lists       id, listable_type, listable_id, product_id, quantity, price_per_unit, total_price(virtual)
sale_payments       id, sale_id, amount, payment_date, payment_method, route_id, user_id, notes
refunds             id, sale_id, user_id, refunded_amount, refund_method, reason, (soft)
expenses            id, route_id, user_id, description, amount, (soft)
cameras             id, name, location, box_stock, (soft)
box_movements       id, camera_id, route_id, movement_type, quantity, box_content_status, moved_at, (soft, no timestamps)
box_balances        id, customer_id, delivered_boxes, returned_boxes
products            id, name, description
notes               id, notable_type, notable_id, user_id, content, type
adjustments         id, sale_id, adjusted_weight_kg, adjustment_type, notes, (soft)
```

---

## Configuration & Development

**Run locally:**
```bash
composer run dev
# starts: php artisan serve, queue:listen, pail (log viewer), npm run dev (Vite)
```

**Seed fresh database:**
```bash
php artisan migrate:fresh --seed
# Seeds: users, customers, cameras, products, routes
```

**Run tests:**
```bash
composer test
```

**Build assets:**
```bash
npm run build
```
