<?php

namespace App\Livewire\Alerts;

use Livewire\Component;

class MessageBanner extends Component
{
    protected $listeners = [
        'show-message-banner' => 'showBanner',
    ];

    public string $text = '';
    public string $type = 'info'; // 'success', 'exception', 'validation-exception', 'info'
    public bool $show = false;
    public string $bannerId = 'global';
    public int $duration = 5000;

    public function mount(string $bannerId = 'global'): void
    {
        $this->bannerId = $bannerId;
    }

    public function showBanner($response): void
    {
        if (!is_array($response)) {
            $this->text = (string) $response;
            $this->type = 'info';
            $this->duration = 5000;
            $this->show = true;
            return;
        }

        $targetBannerId = $response['bannerId'] ?? 'global';

        if ($targetBannerId !== $this->bannerId) {
            return;
        }

        $this->text = (string) ($response['text'] ?? 'Operación completada');
        $this->type = (string) ($response['type'] ?? 'info');
        $this->duration = (int) ($response['duration'] ?? 5000);
        $this->show = true;
    }

    public function render()
    {
        return view('livewire.alerts.message-banner');
    }

}