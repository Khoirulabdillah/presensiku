@props(['type' => 'info', 'title' => '', 'message' => ''])

@php
    $styles = [
        'success' => 'bg-green-100 border-green-400 text-green-700',
        'error' => 'bg-red-100 border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-800',
        'info' => 'bg-blue-100 border-blue-400 text-blue-700'
    ];

    $icon = [
        'success' => 'fa-check-circle',
        'error' => 'fa-exclamation-circle',
        'warning' => 'fa-exclamation-triangle',
        'info' => 'fa-info-circle'
    ];
@endphp

<div class="{{ $styles[$type] }} border px-4 py-3 rounded relative transition-all duration-300 ease-in-out" role="alert">
    <div class="flex items-center">
        <i class="fas {{ $icon[$type] }} mr-2"></i>
        <div>
            @if($title)
                <strong class="font-bold">{{ $title }}</strong>
            @endif
            <span class="block sm:inline {{ $title ? 'ml-1' : '' }}">{{ $message }}</span>
        </div>
    </div>
</div>