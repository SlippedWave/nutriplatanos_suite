<?php

namespace App\Livewire\Refunds;

use App\Models\Refund;
use Livewire\Component;

class RefundVisualizer extends Component
{
    public int $sale_id;
    public ?Refund $refund = null;

    protected $listeners = [
        '$refresh',
        'refund-created' => 'reload',
        'refund-updated' => 'reload',
        'refund-deleted' => 'reload',
        'refresh-refund-visualizer' => 'reload',
    ];

    public function mount(int $sale_id): void
    {
        $this->sale_id = $sale_id;
        $this->reload();
    }

    public function updatedSaleId($value): void
    {
        $this->reload();
    }

    public function reload(): void
    {
        $this->refund = Refund::where('sale_id', $this->sale_id)
            ->whereNull('deleted_at')
            ->first();
    }

    public function render()
    {
        return view('livewire.refunds.refund-visualizer');
    }
}