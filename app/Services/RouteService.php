<?php

namespace App\Services;

use App\Models\Route;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RouteService
{
    /**
     * Create a new route with proper validation and permissions
     */
    public function createRoute(array $data): array
    {
        try {
            $validated = $this->validateRouteData($data);

            // Set the carrier_id to current user if not provided
            if (!isset($validated['carrier_id'])) {
                $validated['carrier_id'] = auth()->id();
            }

            // Set default status
            $validated['status'] = $validated['status'] ?? Route::STATUS_ACTIVE;

            $route = Route::create($validated);

            if (!empty($validated['notes'])) {
                $this->createRouteNote($route, $validated['notes']);
            }

            return [
                'success' => true,
                'route' => $route,
                'message' => 'Ruta creada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear ruta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Edit a route with proper validation and permissions
     */
    public function editRoute(Route $route, array $data): array
    {
        // Check permissions
        if (!$this->canEditRoute($route)) {
            return [
                'success' => false,
                'message' => 'No tienes permiso para editar esta ruta.'
            ];
        }

        try {
            $validated = $this->validateRouteData($data, $route->id);
            $route->update($validated);

            return [
                'success' => true,
                'message' => 'Ruta actualizada exitosamente!',
                'route' => $route->fresh()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la ruta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Close a route with proper validation and permissions
     */
    public function closeRoute(Route $route): array
    {
        // Check permissions
        if (!$this->canEditRoute($route)) {
            return [
                'success' => false,
                'message' => 'No tienes permiso para cerrar esta ruta.'
            ];
        }

        if (!$route->isActive()) {
            return [
                'success' => false,
                'message' => 'Esta ruta ya está cerrada.'
            ];
        }

        try {
            $route->update([
                'status' => Route::STATUS_CLOSED,
                'closed_at' => now(),
            ]);

            $this->createRouteNote($route, "Ruta cerrada el " . now()->format('d/m/Y H:i'));

            return [
                'success' => true,
                'message' => 'Ruta cerrada exitosamente!',
                'route' => $route->fresh()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cerrar la ruta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete a route (soft delete)
     */
    public function deleteRoute(Route $route): array
    {
        if (!$this->canEditRoute($route)) {
            return [
                'success' => false,
                'message' => 'No tienes permiso para eliminar esta ruta.'
            ];
        }

        try {
            // Check if route has any sales
            if ($this->hasActiveSales($route)) {
                return [
                    'success' => false,
                    'message' => 'No se puede eliminar la ruta porque tiene ventas registradas.'
                ];
            }

            // Ensure the route is closed before deleting
            if ($route->isActive()) {
                return [
                    'success' => false,
                    'message' => 'La ruta debe estar cerrada antes de eliminarla.'
                ];
            }

            // Soft delete the route
            $route->delete();

            $this->createRouteNote($route, "Ruta eliminada el " . now()->format('d/m/Y H:i'));

            return [
                'success' => true,
                'message' => 'Ruta eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar ruta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Search routes with filters
     */
    public function searchRoutes(string $search = '', string $sortField = 'created_at', string $sortDirection = 'desc', int $perPage = 10, bool $includeDeleted = false, ?string $statusFilter = null, ?int $carrierId = null)
    {
        $query = Route::query();

        if ($includeDeleted) {
            $query->withTrashed();
        }

        // Filter by carrier for non-admin users
        $user = auth()->user();
        if ($user->role === 'carrier') {
            $query->where('carrier_id', $user->id);
        } elseif ($carrierId) {
            $query->where('carrier_id', $carrierId);
        }

        // Apply status filter
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        return $query
            ->with(['carrier'])
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhereHas('carrier', function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%');
                    });
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);
    }

    /**
     * Get route statistics
     */
    public function getRouteStats(Route $route): array
    {
        return [
            'total_sales' => $route->sales()->count(),
            'total_revenue' => $route->sales()->sum('total_amount'),
            'duration_hours' => $route->closed_at ?
                $route->created_at->diffInHours($route->closed_at) :
                $route->created_at->diffInHours(now()),
            'status' => $route->status,
            'carrier_name' => $route->carrier->name ?? 'Sin asignar',
        ];
    }

    /**
     * Check if user can edit the route.
     */
    public function canEditRoute(Route $route): bool
    {
        $user = Auth::user();

        // Admin and coordinators can edit any route
        if (in_array($user->role, ['admin', 'coordinator'])) {
            return true;
        }

        // Carriers can only edit their own active routes
        return $route->carrier_id === $user->id && $route->isActive();
    }

    /**
     * Check if user can view the route.
     */
    public function canViewRoute(Route $route): bool
    {
        $user = Auth::user();

        // Admin and coordinators can view all routes
        if (in_array($user->role, ['admin', 'coordinator'])) {
            return true;
        }

        // Carriers can only view their own routes
        return $route->carrier_id === $user->id;
    }

    /**
     * Validate route data
     */
    private function validateRouteData(array $data, ?int $routeId = null): array
    {
        $rules = [
            'title' => ['nullable', 'string', 'max:255'],
            'carrier_id' => !$routeId ? ['required', 'exists:users,id'] : ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'string', 'in:active,closed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        return validator($data, $rules)->validate();
    }

    /**
     * Create a note for the route
     */
    private function createRouteNote(Route $route, string $content): void
    {
        Note::create([
            'user_id' => auth()->id(),
            'content' => $content,
            'type' => 'route',
            'notable_id' => $route->id,
            'notable_type' => Route::class,
        ]);
    }

    /**
     * Check if route has active sales
     */
    private function hasActiveSales(Route $route): bool
    {
        return $route->sales()->exists();
    }
}
