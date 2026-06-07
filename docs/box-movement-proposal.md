# Box Movement & Balance — Implementation Proposal

## Business Flow

```
Warehouse ──(route created)──► Route truck
Route truck ──(sale made)────► Client          ← box_balance goes up
Client ──────(pickup)────────► Route truck     ← box_balance goes down
Route truck ──(route closed)─► Warehouse
```

## Current State

Two models exist but are not fully connected:

| Model | Purpose | Currently triggered by |
|---|---|---|
| `BoxMovement` | Physical log of crates moving between warehouse / route / client | Route create, update, close |
| `BoxBalance` | Per-customer running balance (crates owed) | Manual modal on customer page; sale form fields exist in backend but have no UI |

**The gap:** when a sale is made and boxes are delivered to a client, nothing is recorded.
The `SaleService` already calls `BoxBalanceService::updateBoxBalance()` and the `box_balance_delivered` / `box_balance_returned` fields exist on both sale modals — but there are no inputs in the blade views, so they always submit as 0.

## Proposal

### Keep both systems, wire them together

They serve different scopes and should stay separate:

- `BoxMovement` → route-level accountability (does the warehouse math add up?)
- `BoxBalance` → client-level accountability (does each client owe boxes?)

### Changes needed

#### 1. Sale create & update modals — add a "Cajas" section

Add a small, optional collapsible section with two integer inputs:

- **Cajas entregadas** (`box_balance_delivered`) — boxes handed to the client with this sale
- **Cajas recogidas** (`box_balance_returned`) — boxes picked up from the client at the same visit

Both default to 0. On submit, `SaleService` should:

1. Call `BoxBalanceService::updateBoxBalance()` (already wired, just needs non-zero values)
2. Create a `BoxMovement` record of type `route_to_client` for deliveries and `client_to_route` for pickups (currently missing — `SaleService::createSale/updateSale` never calls `BoxMovementService`)

#### 2. SaleService — create BoxMovement records on sale

After updating `BoxBalance`, also persist a `BoxMovement` so the route-level ledger stays in sync:

```php
// In SaleService::createSale() / updateSale()
if ($boxDelivered > 0) {
    BoxMovement::create([
        'route_id'           => $validated['route_id'],
        'customer_id'        => $validated['customer_id'],
        'sale_id'            => $sale->id,
        'movement_type'      => 'route_to_client',
        'quantity'           => $boxDelivered,
        'box_content_status' => 'full',          // delivering product
        'moved_at'           => now(),
    ]);
}

if ($boxReturned > 0) {
    BoxMovement::create([
        'route_id'           => $validated['route_id'],
        'customer_id'        => $validated['customer_id'],
        'sale_id'            => $sale->id,
        'movement_type'      => 'client_to_route',
        'quantity'           => $boxReturned,
        'box_content_status' => 'empty',         // picking up empties
        'moved_at'           => now(),
    ]);
}
```

#### 3. No new modal needed for pickups

Box pickups happen naturally as part of the sale visit (deliver new, pick up old in one transaction). A standalone "fetch boxes" modal adds unnecessary friction.

#### 4. Route close — no changes needed

The existing `BoxMovementsEditor` in the close route modal already handles returning boxes to the warehouse.

## Files to touch

| File | Change |
|---|---|
| `resources/views/livewire/sales/create-sale-modal.blade.php` | Add "Cajas" collapsible section |
| `resources/views/livewire/sales/update-sale-modal.blade.php` | Same |
| `app/Services/SaleService.php` | Create `BoxMovement` records after `BoxBalanceService` call |
| `app/Services/SaleService.php` | Validate `box_balance_delivered/returned` as integers (currently `numeric`) |

## Out of scope for now

- Reporting / history of box movements per route or customer
- Reconciliation between `BoxMovement` totals and `BoxBalance` totals
- Alerting when a client's balance goes negative
