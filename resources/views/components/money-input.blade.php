<flux:input
    type="text"
    inputmode="decimal"
    autocomplete="off"
    placeholder="{{ $attributes->get('placeholder', '0.00') }}"
    x-on:keydown="
        if ($event.ctrlKey || $event.metaKey || $event.altKey) return;
        const nav = ['Backspace', 'Delete', 'Tab', 'Escape', 'Enter', 'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End'];
        if (nav.includes($event.key)) return;
        if ($event.key >= '0' && $event.key <= '9') return;
        if ($event.key === '.' && !$event.currentTarget.value.includes('.')) return;
        $event.preventDefault();
    "
    x-on:paste.prevent="
        const text = ($event.clipboardData || window.clipboardData).getData('text');
        const clean = text.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
        const el = $event.target;
        const start = el.selectionStart ?? el.value.length;
        const end = el.selectionEnd ?? el.value.length;
        const merged = el.value.slice(0, start) + clean + el.value.slice(end);
        el.value = merged.replace(/(\..*)\./g, '$1');
        el.dispatchEvent(new Event('input', { bubbles: true }));
    "
    {{ $attributes->except('placeholder') }}
/>
