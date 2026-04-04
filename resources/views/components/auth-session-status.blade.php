@props([
    'status',
])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-medium text-sm text-[var(--color-primary)]']) }}>
        {{ $status }}
    </div>
@endif
