<?php

namespace App\Livewire\Routes;

use Livewire\Component;
use App\Services\RouteService;
use App\Models\Camera;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CreateRouteModal extends Component
{
    public bool $showCreateModal = false;

    public array $boxMovements = [];
    public Collection $cameras;
    public array $routes = [];

    public string $title = '';
    public ?string $notes = null;
    public ?int $carrier_id = null;

    public User $user;

    public Collection $carriers;

    protected RouteService $routeService;

    public function boot()
    {
        $this->routeService = app(RouteService::class);
    }

    public function mount()
    {
        $this->user = Auth::user();
        $this->title = 'Ruta del día ' . now()->format('d M Y');
        $this->cameras = Camera::select('id', 'name')->orderBy('name')->get();
        $this->carriers = User::select('id', 'name')->orderBy('name')->get();
        $this->carrier_id = $this->user->id;
        $this->routes = \App\Models\Route::routeTransferOptions();
    }

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'boxMovements.*.camera_id' => 'nullable|integer|exists:cameras,id',
            'boxMovements.*.related_route_id' => 'nullable|integer|exists:routes,id',
            'boxMovements.*.transfer_direction' => 'nullable|string|in:' . implode(',', array_keys(\App\Models\BoxMovement::TRANSFER_DIRECTIONS)),
            'boxMovements.*.movement_type' => 'required|string|in:' . implode(',', array_keys(\App\Models\BoxMovement::MOVEMENT_TYPES)),
            'boxMovements.*.quantity' => 'required|integer|min:1',
            'boxMovements.*.box_content_status' => 'required|string|in:' . implode(',', array_keys(\App\Models\BoxMovement::BOX_CONTENT_STATUSES)),
        ];
    }

    private function resetFormFields(): void
    {
        $this->title = 'Ruta del día ' . now()->format('d M Y');
        $this->carrier_id = $this->user->id;
        $this->notes = null;
        $this->boxMovements = [];
    }

    private function getFormData(): array
    {
        $valid = array_filter($this->boxMovements, fn ($bm) => \App\Models\Route::isCompleteBoxMovementRow($bm));

        return [
            'title' => $this->title,
            'notes' => $this->notes,
            'boxMovements' => array_values($valid),
            'carrier_id' => $this->user->id,
            'status' => 'active',
        ];
    }

    public function createRoute()
    {
        try {
            $response = $this->routeService->createRoute($this->getFormData());

            $success = $response['success'] ?? false;
            $message = $response['message'] ?? ($success
                ? 'Ruta creada exitosamente'
                : 'Error al crear la ruta');
            $type = $success ? 'success' : ($response['type'] ?? 'exception');

            $this->dispatch('show-message-banner', [
                'text' => $message,
                'type' => $type,
                'duration' => 5000,
                'bannerId' => 'routes',
            ]);

            if ($success) {
                $this->resetValidation();

                session()->flash('banner', [
                    'text' => $message,
                    'type' => 'success',
                    'duration' => 5000,
                    'bannerId' => 'routes',
                ]);

                $this->showCreateModal = false;
                if (!empty($response['route'])) {
                    $this->redirect(route('routes.show', ['route' => $response['route']->id]), navigate: true);
                }
                return;
            }

            if (($type ?? 'exception') === 'validation-exception') {
                $this->setErrorBag(new \Illuminate\Support\MessageBag($response['errors'] ?? []));
                return;
            }
            $this->resetFormFields();

            return;


        } catch (\Exception $e) {
            $this->dispatch('show-message-banner', [
                'text' => 'Creación de ruta fallida: ' . $e->getMessage(),
                'type' => 'exception',
                'duration' => 5000,
                'bannerId' => 'routes',
            ]);
        }
    }

    public function render()
    {
        return view('livewire.routes.create-route-modal');
    }
}
