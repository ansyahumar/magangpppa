@props([
    'type' => 'button',
    'variant' => 'green',
    'disabled' => false,
])

@php
    $base = 'inline-flex items-center justify-center
             px-4 py-2 rounded-lg font-medium
             text-white shadow transition
             focus:outline-none focus:ring-2 focus:ring-offset-2';

    $variants = [
        'blue'   => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
        'green'  => 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
        'yellow' => 'bg-yellow-500 hover:bg-yellow-600 focus:ring-yellow-400',
        'red'    => 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
        'gray'   => 'bg-gray-500 hover:bg-gray-600 focus:ring-gray-400',
    ];
@endphp

<button
    type="{{ $type }}"
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => $base.' '.$variants[$variant]
    ]) }}
>
    {{ $slot }}
</button>
