<?php

namespace Tests\Feature;

use App\Models\BoxMovement;
use App\Models\Camera;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Route;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Covers the full/empty split in a route's box accounting. Boxes leave the
 * camera in a known state, always go out to customers full, and always come
 * back from customers empty (refunded product is waste, never restocked), so
 * the two pools never mix on the truck. Only the full pool is deliverable.
 */
class RouteBoxAccountingTest extends TestCase
{
    use RefreshDatabase;

    private SaleService $saleService;
    private User $admin;
    private Customer $customer;
    private Route $route;
    private Camera $camera;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleService = app(SaleService::class);

        $this->admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $this->customer = Customer::factory()->create();
        $this->route = Route::factory()->create();
        $this->product = Product::factory()->create();
        $this->camera = Camera::create([
            'name' => 'Cámara 1',
            'location' => 'Bodega',
            'box_stock' => 1000,
        ]);

        Auth::login($this->admin);
    }

    private function move(string $type, int $quantity, string $contentStatus): BoxMovement
    {
        return BoxMovement::create([
            'camera_id' => $this->camera->id,
            'route_id' => $this->route->id,
            'movement_type' => $type,
            'quantity' => $quantity,
            'box_content_status' => $contentStatus,
            'moved_at' => now(),
        ]);
    }

    /**
     * Sell $boxes boxes' worth of product to the customer.
     */
    private function sell(int $delivered, int $returned = 0): array
    {
        return $this->saleService->createSale([
            'customer_id' => $this->customer->id,
            'route_id' => $this->route->id,
            'payment_status' => 'pending',
            'total_amount' => 100,
            'total_amount_excluding_refunds' => 100,
            'paid_amount' => 0,
            'box_balance_delivered' => $delivered,
            'box_balance_returned' => $returned,
            'products' => [[
                'product_id' => $this->product->id,
                'quantity' => 10,
                'price_per_unit' => 10,
            ]],
        ]);
    }

    public function test_empty_boxes_taken_from_camera_are_not_counted_as_deliverable(): void
    {
        $this->move('warehouse_to_route', 30, 'full');
        $this->move('warehouse_to_route', 50, 'empty');

        $summary = $this->route->getBoxSummary();

        $this->assertSame(30, $summary['net_full_on_truck']);
        $this->assertSame(50, $summary['net_empty_on_truck']);
        $this->assertSame(30, $this->route->getAvailableBoxesOnTruck());
    }

    public function test_delivered_boxes_draw_down_only_the_full_pool(): void
    {
        $this->move('warehouse_to_route', 40, 'full');
        $this->move('warehouse_to_route', 10, 'empty');

        $this->assertTrue($this->sell(delivered: 15)['success']);

        $summary = $this->route->fresh()->getBoxSummary();

        $this->assertSame(25, $summary['net_full_on_truck']);
        $this->assertSame(10, $summary['net_empty_on_truck']);
    }

    public function test_boxes_returned_by_customers_come_back_empty(): void
    {
        $this->move('warehouse_to_route', 40, 'full');

        $this->assertTrue($this->sell(delivered: 10, returned: 6)['success']);

        $summary = $this->route->fresh()->getBoxSummary();

        $this->assertSame(30, $summary['net_full_on_truck'], 'returns must not restock the deliverable pool');
        $this->assertSame(6, $summary['net_empty_on_truck']);
    }

    public function test_boxes_returned_to_camera_reduce_the_matching_pool(): void
    {
        $this->move('warehouse_to_route', 40, 'full');
        $this->move('warehouse_to_route', 20, 'empty');
        $this->move('route_to_warehouse', 15, 'full');
        $this->move('route_to_warehouse', 5, 'empty');

        $summary = $this->route->getBoxSummary();

        $this->assertSame(25, $summary['net_full_on_truck']);
        $this->assertSame(15, $summary['net_empty_on_truck']);
    }

    public function test_route_to_route_transfers_respect_content_status(): void
    {
        $other = Route::factory()->create();

        $this->move('warehouse_to_route', 50, 'full');

        // This route sends 20 full boxes away and receives 8 empties back.
        BoxMovement::create([
            'route_id' => $this->route->id,
            'related_route_id' => $other->id,
            'movement_type' => 'route_to_route',
            'transfer_direction' => 'out',
            'quantity' => 20,
            'box_content_status' => 'full',
            'moved_at' => now(),
        ]);
        BoxMovement::create([
            'route_id' => $this->route->id,
            'related_route_id' => $other->id,
            'movement_type' => 'route_to_route',
            'transfer_direction' => 'in',
            'quantity' => 8,
            'box_content_status' => 'empty',
            'moved_at' => now(),
        ]);

        $summary = $this->route->getBoxSummary();

        $this->assertSame(30, $summary['net_full_on_truck']);
        $this->assertSame(8, $summary['net_empty_on_truck']);
    }

    public function test_transfers_owned_by_the_counterpart_route_are_counted_by_status(): void
    {
        $other = Route::factory()->create();

        // Row owned by the other route: it sends 12 full boxes to our route.
        BoxMovement::create([
            'route_id' => $other->id,
            'related_route_id' => $this->route->id,
            'movement_type' => 'route_to_route',
            'transfer_direction' => 'out',
            'quantity' => 12,
            'box_content_status' => 'full',
            'moved_at' => now(),
        ]);

        $this->assertSame(12, $this->route->netFullBoxesOnTruck());
        $this->assertSame(0, $this->route->netEmptyBoxesOnTruck());
    }

    public function test_full_and_empty_pools_reconcile_to_the_combined_net(): void
    {
        $this->move('warehouse_to_route', 60, 'full');
        $this->move('warehouse_to_route', 25, 'empty');
        $this->move('route_to_warehouse', 10, 'full');
        $this->move('route_to_warehouse', 4, 'empty');

        $this->assertTrue($this->sell(delivered: 12, returned: 7)['success']);

        $summary = $this->route->fresh()->getBoxSummary();

        $this->assertSame(
            $summary['net_on_truck'],
            $summary['net_full_on_truck'] + $summary['net_empty_on_truck'],
            'the split must not change the combined total'
        );
    }

    public function test_delivery_is_rejected_when_only_empty_boxes_remain(): void
    {
        // 45 boxes on the truck in total, but only 5 of them hold product.
        $this->move('warehouse_to_route', 5, 'full');
        $this->move('warehouse_to_route', 40, 'empty');

        $result = $this->sell(delivered: 20);

        $this->assertFalse($result['success']);
        $this->assertSame('validation-exception', $result['type']);
        $this->assertArrayHasKey('box_balance_delivered', $result['validation-errors']);
    }

    public function test_delivery_up_to_the_full_box_count_is_allowed(): void
    {
        $this->move('warehouse_to_route', 5, 'full');
        $this->move('warehouse_to_route', 40, 'empty');

        $this->assertTrue($this->sell(delivered: 5)['success']);
        $this->assertSame(0, $this->route->fresh()->getAvailableBoxesOnTruck());
    }
}
