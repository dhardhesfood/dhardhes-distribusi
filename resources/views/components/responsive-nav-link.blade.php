@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-white text-start text-base font-medium text-white bg-red-700 focus:outline-none focus:text-white focus:bg-red-700 focus:border-white transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-white hover:text-yellow-200 hover:bg-red-500 hover:border-yellow-200 focus:outline-none focus:text-yellow-200 focus:bg-red-500 focus:border-yellow-200 transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>