<?php

namespace App\Livewire\Routes;

use Livewire\Component;
use App\Services\RouteService;
use App\Models\Camera;
use Illuminate\Support\Facades\Auth;

class CreateRouteModal extends Component
{
    public bool $showCreateModal = false;

    public array $boxMovements = [];
    public $cameras = [];

    public string $title = '';
    public ?string $notes = null;

    public $user;

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
    }

    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'boxMovements.*.camera_id' => 'required|integer|exists:cameras,id',
            'boxMovements.*.movement_type' => 'required|string|in:' . implode(',', array_keys(\App\Models\BoxMovement::MOVEMENT_TYPES)),
            'boxMovements.*.quantity' => 'required|integer|min:1',
            'boxMovements.*.box_content_status' => 'required|string|in:' . implode(',', array_keys(\App\Models\BoxMovement::BOX_CONTENT_STATUSES)),
        ];
    }

    private function resetFormFields(): void
    {
        $this->title = 'Ruta del día ' . now()->format('d M Y');
        $this->notes = null;
        $this->boxMovements = [];
    }

    private function getFormData(): array
    {
        $valid = array_filter($this->boxMovements, function ($bm) {
            return isset($bm['camera_id'], $bm['movement_type'], $bm['quantity'], $bm['box_content_status'])
                && (int)$bm['quantity'] >= 1;
        });

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
                $this->dispatch('routes-info-updated');
                //! REVISA PARA QUE FUNCIONA ESTE PEDO
                $this->dispatch('route-created');
                $this->showCreateModal = false;
                if (!empty($result['route'])) {
                    return redirect()->route('routes.show', ['route' => $result['route']->id]);
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
