@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center">
    <flux:heading size="lg" class="text-[var(--color-text)]">{{ $title }}</flux:heading>
    <flux:subheading class="text-[var(--color-gray-700)]">{{ $description }}</flux:subheading>
</div>
