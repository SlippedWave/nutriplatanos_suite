
<div
    x-data="{
        show: $wire.entangle('show'),
        duration: $wire.entangle('duration'),
        timer: null,
        scheduleHide() {
            if (this.timer) {
                clearTimeout(this.timer);
            }
            this.timer = setTimeout(() => this.show = false, this.duration);
        }
    }"
    x-effect="if (show) scheduleHide()"
    x-show="show"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 -translate-y-1"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @class([
        'px-4 py-3 rounded-lg border flex justify-between items-center gap-3',
        'bg-green-50 border-green-200 text-green-700' => $type === 'success',
        'bg-yellow-50 border-yellow-200 text-yellow-700' => in_array($type, ['validation-exception', 'exception']),
        'bg-blue-50 border-blue-200 text-blue-700' => $type === 'info',
        'bg-red-50 border-red-200 text-red-700' => !in_array($type, ['success', 'validation-exception', 'exception', 'info']),
    ])
>
    <div>{{ $text }}</div>

    <button type="button" @click="show = false" class="opacity-70 hover:opacity-100">
        <span class="sr-only">Cerrar</span>
        <flux:icon.x-mark class="w-4 h-4" />
    </button>
</div>