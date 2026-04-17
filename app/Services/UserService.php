<?php

namespace App\Services;

use App\Models\User;
use App\Models\Note;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function createUser(array $data): array
    {
        try {
            $validated = $this->validateUserData($data);
            $validated['password'] = Hash::make($validated['password']);

            DB::beginTransaction();

            $user = User::create($validated);

            if (!empty($validated['notes'])) {
                $this->createUserNote($user, $validated['notes']);
            }

            DB::commit();

            return [
                'success' => true,
                'user' => $user,
                'message' => 'Usuario creado exitosamente.'
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error de validación. Por favor, revisa los datos ingresados. hay ' .  count($e->errors()) . ' error(es).',
                'validation-errors' => $e->errors(),
                'type' => 'validation-exception'
            ]; 
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage(),
                'type' => 'exception'
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
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
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

            DB::beginTransaction();

            $user->update($validated);
            $this->createUserNote($user, "Usuario actualizado el " . now()->format('d/m/Y H:i') . " por " . Auth::user()->name);

            DB::commit();

            return [
                'success' => true,
                'user' => $user->fresh(),
                'message' => 'Usuario actualizado exitosamente.'
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error de validación. Por favor, revisa los datos ingresados. hay ' .  count($e->errors()) . ' error(es).',
                'validation-errors' => $e->errors(),
                'type' => 'validation-exception'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al actualizar usuario: ' . $e->getMessage(),
                'type' => 'exception'
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

            DB::beginTransaction();

            $user->delete();

            // Optionally, add a note about the deletion
            $this->createUserNote($user, "Usuario eliminado por {$currentUser->name} el " . now()->format('d/m/Y H:i'));

            DB::commit();

            return [
                'success' => true,
                'message' => 'Usuario eliminado exitosamente.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al eliminar usuario: ' . $e->getMessage(),
                'type' => 'exception'
            ];
        }
    }

    public function restoreUser(User $user): array
    {
        try {
            DB::beginTransaction();

            $user->restore();

            $this->createUserNote($user, "Usuario restaurado el " . now()->format('d/m/Y H:i'));

            DB::commit();

            return [
                'success' => true,
                'message' => 'Usuario restaurado exitosamente.',
                'user' => $user->fresh()
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al restaurar usuario: ' . $e->getMessage(),
                'type' => 'exception'
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
            DB::beginTransaction();
            // Permanently delete the user
            $user->forceDelete();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Usuario eliminado permanentemente.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al eliminar permanentemente el usuario: ' . $e->getMessage(),
                'type' => 'error'
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
            'user_id' => Auth::id(),
            'content' => $content,
            'type' => 'user',
            'notable_id' => $user->id,
            'notable_type' => User::class,
        ]);
    }
}
