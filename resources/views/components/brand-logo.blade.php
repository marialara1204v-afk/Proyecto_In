@php
    $candidates = [
        'images/logo-patrona.png',
        'images/logo-patrona.jpg',
        'images/logo-patrona.jpeg',
        'images/logo-patrona.webp',
    ];

    $logoAsset = null;

    foreach ($candidates as $candidate) {
        if (file_exists(public_path($candidate))) {
            $logoAsset = asset($candidate);
            break;
        }
    }

    $class = $class ?? 'h-10 w-auto';
@endphp

@if ($logoAsset)
    <img src="{{ $logoAsset }}" alt="La Patrona" class="{{ $class }}" loading="eager">
@else
    <img src="{{ asset('images/logo-patrona.svg') }}" alt="La Patrona" class="{{ $class }}" loading="eager">
@endif
