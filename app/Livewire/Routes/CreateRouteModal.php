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

        if (empty($this->boxMovements)) {
            $this->addBoxMovement();
        }
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

    public function addBoxMovement(): void
    {
        $defaultCameraId = null;
        if ($this->cameras instanceof \Illuminate\Support\Collection) {
            $defaultCameraId = optional($this->cameras->first())->id;
        } elseif (is_array($this->cameras) && !empty($this->cameras)) {
            $first = $this->cameras[0];
            $defaultCameraId = is_array($first) ? ($first['id'] ?? null) : ($first->id ?? null);
        }

        $this->boxMovements[] = [
            'camera_id' => $defaultCameraId,
            'movement_type' => array_key_first(\App\Models\BoxMovement::MOVEMENT_TYPES) ?? 'warehouse_to_route',
            'quantity' => 1,
            'box_content_status' => array_key_first(\App\Models\BoxMovement::BOX_CONTENT_STATUSES) ?? 'full',
        ];
    }

    public function removeBoxMovement(int $index): void
    {
        if (count($this->boxMovements) >= 1) {
            unset($this->boxMovements[$index]);
            $this->boxMovements = array_values($this->boxMovements);
        }
    }

    private function resetFormFields(): void
    {
        $this->title = 'Ruta del día ' . now()->format('d M Y');
        $this->notes = null;
        $this->boxMovements = [];
        $this->addBoxMovement();
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
            $this->validate();

            $result = $this->routeService->createRoute($this->getFormData());

            if (!($result['success'] ?? false)) {
                // Map service validation errors back to Livewire error bag
                if (!empty($result['errors']) && is_array($result['errors'])) {
                    foreach ($result['errors'] as $field => $messages) {
                        // Prefix nested boxMovements errors to match Livewire properties if needed
                        $this->addError($field, is_array($messages) ? implode("\n", $messages) : (string) $messages);
                    }
                }
                session()->flash('error', $result['message'] ?? 'No se pudo crear la ruta.');
                // Notify parent so it can show a toast/banner
                $this->dispatch('route-create-failed', message: $result['message'] ?? 'Fallo al crear la ruta', errors: $result['errors'] ?? null);
                return;
            }

            $this->resetFormFields();
            $this->showCreateModal = false;

            session()->flash('message', 'Ruta creada exitosamente!');
            $this->dispatch('route-created');

            // Redirect to the new route detail
            if (!empty($result['route'])) {
                return redirect()->route('routes.show', ['route' => $result['route']->id]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            session()->flash('error', 'Por favor, completa todos los campos requeridos correctamente.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Error al crear la ruta: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.routes.create-route-modal');
    }
}
