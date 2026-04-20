@php
    $pathLogo = public_path('images/kemenpppa.png');
    $base64Fav = '';

    if (file_exists($pathLogo)) {
        $dataLogo = file_get_contents($pathLogo);
        $base64Fav = 'data:image/png;base64,' . base64_encode($dataLogo);
    }
@endphp

@if($base64Fav)
    <link rel="icon" type="image/png" href="{{ $base64Fav }}">
    <link rel="shortcut icon" type="image/png" href="{{ $base64Fav }}">
@else
    <link rel="icon" href="data:,">
@endif