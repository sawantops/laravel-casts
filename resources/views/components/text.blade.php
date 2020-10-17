<x-form-group
    :class="$groupClasses"
    :errors="$errors"
>
    @if ($label)
        <x-form-label
            :field="$name"
            :value="$label"
            class="{{ $labelClasses }}"
        />
    @endif

    <input
        type="text"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ $value }}"
        {{ $attributes }}
    >
</x-form-group>
