<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Route;
use App\Models\Sale;
use App\Models\User;
use App\Services\RefundService;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * Covers the link between a refund and the sale it belongs to: a discount or
 * product refund must reduce the amount owed, respect the business rules
 * (discounts only on pending/partial, never exceed the pending amount), and be
 * reversed when the refund is deleted or preserved when the sale is edited.
 */
class RefundServiceTest extends TestCase
{
    use RefreshDatabase;

    private SaleService $saleService;
    private RefundService $refundService;
    private User $admin;
    private Customer $customer;
    private Route $route;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->saleService = app(SaleService::class);
        $this->refundService = app(RefundService::class);

        $this->admin = User::factory()->create(['role' => 'admin', 'active' => true]);
        $this->customer = Customer::factory()->create();
        $this->route = Route::factory()->create();
        $this->product = Product::factory()->create();

        Auth::login($this->admin);
    }

    /**
     * Create a sale of $100 (10 units @ $10) with an optional upfront payment.
     */
    private function makeSale(string $status = 'pending', float $paidAmount = 0.0): Sale
    {
        $result = $this->saleService->createSale([
            'customer_id' => $this->customer->id,
            'route_id' => $this->route->id,
            'payment_status' => $status,
            'total_amount' => 100,
            'total_amount_excluding_refunds' => 100,
            'paid_amount' => $paidAmount,
            'products' => [[
                'product_id' => $this->product->id,
                'quantity' => 10,
                'price_per_unit' => 10,
            ]],
        ]);

        $this->assertTrue($result['success'], 'sale creation failed: ' . ($result['message'] ?? ''));

        return $result['sale']->fresh();
    }

    private function createDiscount(Sale $sale, float $amount): array
    {
        return $this->refundService->createRefund([
            'user_id' => $this->admin->id,
            'sale_id' => $sale->id,
            'refund_method' => 'discount',
            'refunded_amount' => $amount,
            'reason' => 'test discount',
            'products' => [],
        ]);
    }

    public function test_discount_reduces_amount_owed_on_pending_sale(): void
    {
        $sale = $this->makeSale('pending');

        $result = $this->createDiscount($sale, 30);
        $this->assertTrue($result['success']);

        $sale->refresh();
        $this->assertEqualsWithDelta(30, (float) $sale->refunded_amount, 0.01);
        $this->assertEqualsWithDelta(70, (float) $sale->total_amount_excluding_refunds, 0.01);
        $this->assertEqualsWithDelta(70, $sale->remaining_balance, 0.01);
        // total_amount stays the gross product value.
        $this->assertEqualsWithDelta(100, (float) $sale->total_amount, 0.01);
        $this->assertSame('pending', $sale->payment_status);
    }

    public function test_refund_exceeding_pending_amount_is_rejected(): void
    {
        $sale = $this->makeSale('pending');

        $result = $this->createDiscount($sale, 150);

        $this->assertFalse($result['success']);
        $this->assertSame('validation-exception', $result['type']);
        $this->assertArrayHasKey('refunded_amount', $result['validation-errors']);
        // Nothing was written to the sale.
        $this->assertEqualsWithDelta(0, (float) $sale->fresh()->refunded_amount, 0.01);
    }

    public function test_deleting_a_refund_reverses_it_on_the_sale(): void
    {
        $sale = $this->makeSale('pending');
        $result = $this->createDiscount($sale, 30);
        $refundId = $result['refund']->id;

        $sale->refresh();
        $this->assertEqualsWithDelta(70, (float) $sale->total_amount_excluding_refunds, 0.01);

        $delete = $this->refundService->deleteRefund($refundId);
        $this->assertTrue($delete['success']);

        $sale->refresh();
        $this->assertEqualsWithDelta(0, (float) $sale->refunded_amount, 0.01);
        $this->assertEqualsWithDelta(100, (float) $sale->total_amount_excluding_refunds, 0.01);
        $this->assertEqualsWithDelta(100, $sale->remaining_balance, 0.01);
    }

    public function test_product_refund_reduces_amount_owed(): void
    {
        $sale = $this->makeSale('partial', 40);

        $result = $this->refundService->createRefund([
            'user_id' => $this->admin->id,
            'sale_id' => $sale->id,
            'refund_method' => 'product',
            'refunded_amount' => 20,
            'reason' => 'returned product',
            'products' => [[
                'product_id' => $this->product->id,
                'quantity' => 2,
                'price_per_unit' => 10,
            ]],
        ]);

        $this->assertTrue($result['success']);

        $sale->refresh();
        $this->assertEqualsWithDelta(20, (float) $sale->refunded_amount, 0.01);
        $this->assertEqualsWithDelta(80, (float) $sale->total_amount_excluding_refunds, 0.01);
        $this->assertEqualsWithDelta(40, $sale->remaining_balance, 0.01);
        $this->assertSame('partial', $sale->payment_status);
    }

    public function test_discount_on_fully_paid_sale_is_rejected(): void
    {
        $sale = $this->makeSale('paid', 100);
        $this->assertSame('paid', $sale->payment_status);

        $result = $this->createDiscount($sale, 10);

        $this->assertFalse($result['success']);
        $this->assertSame('validation-exception', $result['type']);
        $this->assertArrayHasKey('refund_method', $result['validation-errors']);
    }

    public function test_editing_a_discounted_sale_preserves_the_discount(): void
    {
        $sale = $this->makeSale('pending');
        $this->createDiscount($sale, 25);

        // Double the products: gross goes 100 -> 200, discount must survive.
        $update = $this->saleService->updateSale($sale->fresh(), [
            'customer_id' => $this->customer->id,
            'route_id' => $this->route->id,
            'total_amount' => 200,
            'total_amount_excluding_refunds' => 200,
            'products' => [[
                'product_id' => $this->product->id,
                'quantity' => 20,
                'price_per_unit' => 10,
            ]],
        ]);

        $this->assertTrue($update['success'], 'sale update failed: ' . ($update['message'] ?? ''));

        $sale->refresh();
        $this->assertEqualsWithDelta(25, (float) $sale->refunded_amount, 0.01);
        $this->assertEqualsWithDelta(175, (float) $sale->total_amount_excluding_refunds, 0.01);
        $this->assertEqualsWithDelta(175, $sale->remaining_balance, 0.01);
    }

    public function test_updating_a_refund_re_syncs_the_sale(): void
    {
        $sale = $this->makeSale('pending');
        $result = $this->createDiscount($sale, 30);

        $update = $this->refundService->updateRefund($result['refund'], [
            'user_id' => $this->admin->id,
            'sale_id' => $sale->id,
            'refund_method' => 'discount',
            'refunded_amount' => 60,
            'reason' => 'bigger discount',
            'products' => [],
        ]);

        $this->assertTrue($update['success']);

        $sale->refresh();
        $this->assertEqualsWithDelta(60, (float) $sale->refunded_amount, 0.01);
        $this->assertEqualsWithDelta(40, (float) $sale->total_amount_excluding_refunds, 0.01);
    }
}
