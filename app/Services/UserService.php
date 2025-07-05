<?php

namespace App\Services;

use App\Models\User;
use App\Models\Note;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserService
{
    public function createUser(array $data): array
    {
        try {
            $validated = $this->validateUserData($data);
            $validated['password'] = Hash::make($validated['password']);

            $user = User::create($validated);

            if (!empty($validated['notes'])) {
                $this->createUserNote($user, $validated['notes']);
            }

            return [
                'success' => true,
                'user' => $user,
                'message' => 'Usuario creado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage()
            ];
        }
    }

    public function validateUserData(array $data, ?int $userId = null): array
    {
        return validator($data, [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userId),
            ],
            'phone' => 'nullable|string|max:20',
            'curp' => 'nullable|string|max:18',
            'rfc' => 'nullable|string|max:13',
            'address' => 'nullable|string|max:255',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:50',
            'role' => ['required', Rule::in(['carrier', 'admin'])],
            'password' => $userId ? 'nullable|string|min:8|confirmed' : 'required|string|min:8|confirmed',
            'notes' => 'nullable|string|max:1000',
        ])->validate();
    }

    public function updateUser(User $user, array $data): array
    {
        try {
            // Remove empty password fields before validation
            if (empty($data['password'])) {
                unset($data['password'], $data['password_confirmation']);
            }

            $validated = $this->validateUserData($data, $user->id);

            // Only hash and update password if it was provided
            if (!empty($data['password'])) {
                $validated['password'] = Hash::make($data['password']);
            }

            $user->update($validated);

            return [
                'success' => true,
                'user' => $user->fresh(),
                'message' => 'Usuario actualizado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage()
            ];
        }
    }


    public function deleteUser(User $user, User $currentUser): array
    {
        if ($user->id === $currentUser->id) {
            return [
                'success' => false,
                'message' => 'No puedes eliminar tu propio usuario.'
            ];
        }

        try {
            // Soft delete the user
            $user->delete();

            // Optionally, add a note about the deletion
            $this->createUserNote($user, "Usuario eliminado por {$currentUser->name} el " . now()->format('d/m/Y H:i'));

            return [
                'success' => true,
                'message' => 'Usuario eliminado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar usuario: ' . $e->getMessage()
            ];
        }
    }

    public function restoreUser(User $user): array
    {
        try {
            $user->restore();

            $this->createUserNote($user, "Usuario restaurado el " . now()->format('d/m/Y H:i'));

            return [
                'success' => true,
                'message' => 'Usuario restaurado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al restaurar usuario: ' . $e->getMessage()
            ];
        }
    }

    public function forceDeleteUser(User $user, User $currentUser): array
    {
        if ($user->id === $currentUser->id) {
            return [
                'success' => false,
                'message' => 'No puedes eliminar permanentemente tu propio usuario.'
            ];
        }

        try {
            // Permanently delete the user
            $user->forceDelete();

            return [
                'success' => true,
                'message' => 'Usuario eliminado permanentemente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar permanentemente el usuario: ' . $e->getMessage()
            ];
        }
    }

    public function searchUsers(string $search = '', string $sortField = 'name', string $sortDirection = 'asc', int $perPage = 10, bool $includeDeleted = false)
    {
        $query = User::query();

        if ($includeDeleted) {
            $query->withTrashed();
        }

        return $query
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);
    }

    public function getDeletedUsers(string $search = '', string $sortField = 'name', string $sortDirection = 'asc', int $perPage = 10)
    {
        return User::onlyTrashed()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);
    }

    private function createUserNote(User $user, string $content): void
    {
        Note::create([
            'user_id' => auth()->id(),
            'content' => $content,
            'type' => 'user',
            'notable_id' => $user->id,
            'notable_type' => User::class,
        ]);
    }
}
