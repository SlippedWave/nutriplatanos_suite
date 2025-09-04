<?php

namespace App\Livewire\Settings\Users;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ViewUserModal extends Component
{
    public bool $showViewModal = false;

    public ?User $selectedUser = null;

    protected $listeners = [
        'open-view-user-modal' => 'openViewUserModal'
    ];

    public function openViewUserModal(int $userId)
    {
        $this->selectedUser = User::findOrFail($userId);
        $this->showViewModal = true;
    }

    public function render()
    {
        return view('livewire.settings.users.view-user-modal');
    }
}
