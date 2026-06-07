# NutriPlátanos Suite — CLAUDE.md

## What This Project Is

Delivery-route management and sales-tracking app for a banana/produce distribution company. Carriers go on routes, sell product, collect payments, and track box movements between cold-storage cameras and their truck. Coordinators and admins manage customers, users, accounting, and resources from a back-office view.

Full system documentation lives in `SYSTEM.md`.

---

## Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 (PHP 8.2+) |
| UI / Reactivity | Livewire 3 + Livewire Flux + Livewire Volt |
| Assets | Vite + Tailwind CSS v4 |
| Database | SQLite (configurable) |
| Auth | Laravel built-in session auth |

---

## Development Commands

```bash
composer run dev          # start everything: artisan serve, queue:listen, pail, vite
composer test             # clear config + run PHPUnit

php artisan migrate:fresh --seed   # reset DB with seed data
npm run build             # production asset build
```

Makefile aliases also available (`make test`, `make migrate`, `make seed`, etc.).

---

## User Roles

| Role | Spanish | Access |
|---|---|---|
| `admin` | Administrador | Everything including user management |
| `coordinator` | Coordinador | Customers, accounting, resources — no user management |
| `carrier` | Transportista | Own routes and their sales/expenses only |

Enforced at two levels: route middleware (`CheckRoleMiddleware`) and service-level (`Auth::user()->role` checks).

---

## Architecture Patterns

### Service Layer (`app/Services/`)

All business logic lives here. Every service method returns:

```php
[
    'success'           => bool,
    'message'           => string,
    'type'              => 'success' | 'exception' | 'validation-exception' | 'authorization' | ...,
    'validation-errors' => array,   // only on validation-exception
    '<entity>'          => Model,   // on success
]
```

All mutations use `DB::beginTransaction()` / `DB::commit()` / `DB::rollBack()`.

### Livewire Component Pattern

Every entity follows this structure:

```
<Entity>Table         — paginated, searchable, sortable list
Create<Entity>Modal   — form modal, dispatches create to service
Update<Entity>Modal   — loads entity, form modal, dispatches update
Delete<Entity>Modal   — confirmation modal, dispatches delete
View<Entity>Modal     — read-only detail modal
```

Reusable composite editors: `ProductListEditor`, `BoxMovementsEditor`, `RefundVisualizer`.

### Event Bus

| Event | Fired by | Listened by |
|---|---|---|
| `routes-info-updated` | Route modals after CRUD | `RoutesTable`, `BoxMovementsEditor` |
| `show-message-banner` | Any modal after service call | `MessageBanner` |

`MessageBanner` is mounted in the app layout. Accepts `{ text, type, bannerId, duration }`. Types: `success`, `exception`, `validation-exception`, `info`.

### Notes (Audit Trail)

Every Service auto-appends a `Note` record on create, update, and delete. `notes` is polymorphic (`notable_type` / `notable_id`).

---

## Key Domain Rules

- A carrier can have at most **one active route** at a time.
- A route with sales **cannot be deleted**. Must be closed first, then soft-deleted.
- Customers with any sales **cannot be deleted**.
- Carriers can only edit payments **they created within the last 24 hours**; admins have no restriction.
- `ProductList` is a polymorphic line-item table used by both Sales and Refunds.
- `payment_status` (`pending` / `partial` / `paid`) is recalculated automatically by `Sale::updatePaymentStatus()`.
- BoxMovements on a route are **fully replaced** on update (soft-deleted and recreated).

---

## URL Structure

```
/dashboard
/rutas                         — route list
/rutas/historial               — historical archive
/rutas/detalles/{route}        — route detail (sales, expenses, box movements tabs)
/clientes                      — customers (admin/coordinator, password-confirmed)
/clientes/detalles/{customer}
/contabilidad                  — expenses + payments
/recursos                      — cameras and products
/configuracion/perfil
/configuracion/clave
/configuracion/usuarios        — admin only, password-confirmed
```

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
box_movements       id, camera_id, route_id, movement_type, quantity, box_content_status, moved_at, (soft, no updated_at)
box_balances        id, customer_id, delivered_boxes, returned_boxes
products            id, name, description
notes               id, notable_type, notable_id, user_id, content, type
adjustments         id, sale_id, adjusted_weight_kg, adjustment_type, notes, (soft)
```
