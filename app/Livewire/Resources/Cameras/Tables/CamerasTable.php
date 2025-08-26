<?php

namespace App\Livewire\Resources\Cameras\Tables;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Camera;

class CamerasTable extends Component
{
    use WithPagination;

    public $perPage = 3;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    public function updatePerPage($perPage)
    {
        $this->perPage = $perPage;
        $this->resetPage();
    }

    protected $listeners = [
        'cameras-info-updated' => '$refresh',
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function render()
    {
        $cameras = Camera::orderBy($this->sortField, $this->sortDirection)->paginate($this->perPage);
        return view('livewire.resources.cameras.tables.cameras-table', compact('cameras'));
    }
}
