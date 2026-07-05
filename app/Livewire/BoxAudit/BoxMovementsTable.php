<?php

namespace App\Livewire\BoxAudit;

use App\Models\BoxMovement;
use App\Models\Camera;
use Livewire\Component;
use Livewire\WithPagination;

class BoxMovementsTable extends Component
{
    use WithPagination;

    public int $perPage = 15;
    public string $cameraFilter = '';
    public string $typeFilter = '';
    public string $startDate = '';
    public string $endDate = '';
    public bool $includeSuperseded = false;

    protected $listeners = ['box-audit-updated' => '$refresh'];

    public function updatedCameraFilter(): void { $this->resetPage(); }
    public function updatedTypeFilter(): void   { $this->resetPage(); }
    public function updatedStartDate(): void    { $this->resetPage(); }
    public function updatedEndDate(): void      { $this->resetPage(); }

    public function toggleSuperseded(): void
    {
        $this->includeSuperseded = !$this->includeSuperseded;
        $this->resetPage();
    }

    public function render()
    {
        $query = BoxMovement::query();

        if ($this->includeSuperseded) {
            $query->withTrashed();
        }

        if ($this->cameraFilter !== '') {
            $query->where('camera_id', $this->cameraFilter);
        }

        if ($this->typeFilter !== '') {
            $query->where('movement_type', $this->typeFilter);
        }

        if ($this->startDate) {
            $query->where('moved_at', '>=', $this->startDate . ' 00:00:00');
        }

        if ($this->endDate) {
            $query->where('moved_at', '<=', $this->endDate . ' 23:59:59');
        }

        $movements = $query
            ->with(['camera', 'route.carrier', 'relatedRoute'])
            ->orderBy('moved_at', 'desc')
            ->paginate($this->perPage);

        $cameras = Camera::orderBy('name')->get();

        return view('livewire.box-audit.box-movements-table', compact('movements', 'cameras'));
    }
}
