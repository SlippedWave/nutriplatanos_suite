<?php

namespace App\Services;

use App\Models\Route;
use App\Models\Note;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RouteService
{
    public function createBoxMovements(Route $route, array $data)
    {
        if (!empty($data)) {
            $boxMovementService = app(BoxMovementService::class);
            foreach ($data as $movementData) {
                $movementData['route_id'] = $route->id;
                $movementData['moved_at'] = $movementData['moved_at'] ?? now();

                // Normalize fields that only apply to specific movement types so
                // stale values from the editor don't leak across types.
                if (($movementData['movement_type'] ?? null) === 'route_to_route') {
                    $movementData['camera_id'] = null;
                } else {
                    $movementData['related_route_id']   = null;
                    $movementData['transfer_direction'] = null;
                }

                $response = $boxMovementService->createBoxMovement($movementData);
                if (!($response['success'] ?? false)) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'Error al crear movimiento de caja: ' . ($response['message'] ?? 'desconocido'),
                        'errors' => $response['errors'] ?? null,
                        'type' => $response['type'] ?? 'exception'
                    ];
                }
            }
        }
    }

    /**
     * Create a new route with proper validation and permissions
     */
    public function createRoute(array $data): array
    {
        try {
            if (Auth::user()->activeRoute()) {
                return [
                    'success' => false,
                    'message' => 'Usuario ya tiene ruta activa.',
                    'type' => 'exception'
                ];
            }

            // Validate input and surface field-level errors when possible
            try {
                $validated = $this->validateRouteData($data);
            } catch (\Illuminate\Validation\ValidationException $ve) {
                return [
                    'success' => false,
                    'message' => 'Datos inválidos para crear la ruta. Hay ' . count($ve->errors()) . ' error(es).',
                    'validation-errors' => $ve->errors(),
                    'type' => 'validation-exception'
                ];
            }

            DB::beginTransaction();


            // Set the carrier_id to current user if not provided
            if (!isset($validated['carrier_id'])) {
                $validated['carrier_id'] = Auth::id();
            }

            // Set default status
            $validated['status'] = $validated['status'] ?? Route::STATUS_ACTIVE;

            $routeData = collect($validated)->except('boxMovements', 'notes')->toArray();
            $route = Route::create($routeData);

            // Create box movements if provided
            $this->createBoxMovements($route, $validated['boxMovements'] ?? []);

            if (!empty($validated['notes'])) {
                $this->createRouteNote($route, $validated['notes']);
            }

            DB::commit();

            return [
                'success' => true,
                'route' => $route,
                'message' => 'Ruta creada exitosamente.'
            ];
        } catch (\Exception $e) {
            // Ensure transaction is rolled back on any exception
            try {
                if (\Illuminate\Support\Facades\DB::transactionLevel() > 0) {
                    DB::rollBack();
                }
            } catch (\Throwable $te) {
                // Log transaction rollback failure if needed
                Log::error('Error al revertir transacción: ' . $te->getMessage());
            }
            return [
                'success' => false,
                'message' => 'Error al crear ruta: ' . $e->getMessage(),
                'type' => 'exception'
            ];
        }
    }

    /**
     * Edit a route with proper validation and permissions
     */
    public function updateRoute(Route $route, array $data): array
    {
        // Check permissions
        if (!$this->canEditRoute($route)) {
            return [
                'success' => false,
                'message' => 'No tienes permiso para editar esta ruta.',
                'type' => 'insufficient-permissions'
            ];
        }

        try {

            try {
                $validated = $this->validateRouteData($data);
            } catch (\Illuminate\Validation\ValidationException $ve) {
                return [
                    'success' => false,
                    'message' => 'Datos inválidos para actualizar la ruta. Hay ' . count($ve->errors()) . ' error(es).',
                    'validation-errors' => $ve->errors(),
                    'type' => 'validation-exception'
                ];
            }

            DB::beginTransaction();

            $routeData = collect($validated)->except('boxMovements', 'notes')->toArray();
            $route->update($routeData);

            $this->createRouteNote($route, "Ruta actualizada el " . now()->format('d/m/Y H:i') . " por " . Auth::user()->name);
            // Update box movements if provided
            $route->boxMovements()->delete();
            $this->createBoxMovements($route, $validated['boxMovements'] ?? []);

            if (!empty($validated['notes'])) {
                $this->createRouteNote($route, $validated['notes']);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Ruta actualizada exitosamente!',
                'route' => $route->fresh()
            ];
        } catch (\Exception $e) {
            Log::error('Error al actualizar la ruta: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al actualizar la ruta: ' . $e->getMessage(),
                'type' => 'exception'
            ];
        }
    }

    /**
     * Close a route with proper validation and permissions
     */
    public function closeRoute(Route $route, array $data): array
    {
        // Check permissions
        if (!$this->canEditRoute($route)) {
            return [
                'success' => false,
                'message' => 'No tienes permiso para cerrar esta ruta.',
                'type' => 'insufficient-permissions'
            ];
        }

        if (!$route->isActive()) {
            return [
                'success' => false,
                'message' => 'Esta ruta ya está cerrada.',
                'type' => 'already-closed'
            ];
        }

        try {
            $route->update([
                'status' => Route::STATUS_CLOSED,
                'closed_at' => now(),
            ]);

            $this->createBoxMovements($route, $data['boxMovements'] ?? []);
            $this->createRouteNote($route, "Ruta cerrada el " . now()->format('d/m/Y H:i'));

            return [
                'success' => true,
                'message' => 'Ruta cerrada exitosamente!',
                'route' => $route->fresh()
            ];
        } catch (\Exception $e) {
            Log::error('Error al cerrar la ruta: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al cerrar la ruta: ' . $e->getMessage(),
                'type' => 'exception'
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
                    'message' => 'No se puede eliminar la ruta porque tiene ventas registradas.',
                    'type' => 'has-sales'
                ];
            }

            // Ensure the route is closed before deleting
            if ($route->isActive()) {
                return [
                    'success' => false,
                    'message' => 'La ruta debe estar cerrada antes de eliminarla.',
                    'type' => 'not-closed-route'
                ];
            }

            DB::beginTransaction();

            // Soft delete the route
            $route->delete();

            $this->createRouteNote($route, "Ruta eliminada el " . now()->format('d/m/Y H:i'));

            DB::commit();

            return [
                'success' => true,
                'message' => 'Ruta eliminada exitosamente.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al eliminar ruta: ' . $e->getMessage(),
                'type' => 'error'
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
        $user = Auth::user();
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
    private function validateRouteData(array $data): array
    {
        $rules = [
            'title' => ['nullable', 'string', 'max:255'],
            'carrier_id' => ['required', 'exists:users,id'],
            'status' => ['nullable', 'string', 'in:active,closed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'boxMovements' => ['nullable', 'array'],
            'boxMovements.*.camera_id' => ['nullable', 'exists:cameras,id'],
            'boxMovements.*.related_route_id' => ['nullable', 'exists:routes,id'],
            'boxMovements.*.transfer_direction' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(\App\Models\BoxMovement::TRANSFER_DIRECTIONS))],
            'boxMovements.*.movement_type' => ['required_with:boxMovements', \Illuminate\Validation\Rule::in(array_keys(\App\Models\BoxMovement::MOVEMENT_TYPES))],
            'boxMovements.*.quantity' => ['required_with:boxMovements', 'integer', 'min:1'],
            'boxMovements.*.box_content_status' => ['required_with:boxMovements', \Illuminate\Validation\Rule::in(array_keys(\App\Models\BoxMovement::BOX_CONTENT_STATUSES))],
        ];

        return validator($data, $rules)->validate();
    }

    /**
     * Create a note for the route
     */
    private function createRouteNote(Route $route, string $content): void
    {
        Note::create([
            'user_id' => Auth::id(),
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
