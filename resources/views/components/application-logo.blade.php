@php
    $path = public_path('images/kemenpppa.png');
    $base64 = '';

    if (file_exists($path)) {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
@endphp

@if($base64)
    <img
        src="{{ $base64 }}"
        alt="Logo Kemen PPPA"
        class="h-14 w-auto object-contain"
        {{ $attributes }}
    />
@endif