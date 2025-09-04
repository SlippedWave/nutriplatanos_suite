<?php

namespace App\Livewire\Settings\Users\Tables;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class UsersTable extends Component
{
    use WithPagination;

    public string $search = '';
    public $perPage = 10;
    public string $sortField = 'name';
    public string $sortDirection = 'asc';
    public bool $includeDeleted = false;
    
    protected $listeners = [
        'users-info-updated' => '$refresh',
        'show-users-table-message' => 'showUsersTableMessage',
        'flash-users-table-message' => 'flashUsersTableMessage'
    ];

    
    protected UserService $userService;

    public function boot(UserService $userService)
    {
        $this->userService = $userService;
    }
    public function showUsersTableMessage($result)
    {
        $this->flashUsersTableMessage($result['message'], $result['success'] ? 'success' : 'error');
        $this->resetPage();
    }

    public function flashUsersTableMessage(string $text, string $type = 'success'): void
    {
        session()->flash('message', [
            'header' => 'users-table',
            'text' => $text,
            'type' => $type,
        ]);
    }

    public function toggleIncludeDeleted()
    {
        $this->includeDeleted = !$this->includeDeleted;
        $this->resetPage();
    }

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

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.settings.users.tables.users-table', [
            'users' => $this->userService->searchUsers(
                $this->search,
                $this->sortField,
                $this->sortDirection,
                $this->perPage,
                $this->includeDeleted
            )
        ]);
    }
}
