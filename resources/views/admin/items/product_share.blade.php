<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $item->name }}</title>

    @php
        $cleanDescription = \Illuminate\Support\Str::limit(strip_tags($item->description), 160);
        $currentUrl = str_replace('http://', 'https://', request()->fullUrl());
    @endphp

    <meta name="description" content="{{ $cleanDescription }}">

    <meta property="og:site_name" content="Morovski">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $item->name }}">
    <meta property="og:description" content="{{ $cleanDescription }}">
    <meta property="og:url" content="{{ $currentUrl }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:secure_url" content="{{ $ogImage }}">
    <meta property="og:image:type" content="{{ $imageType }}">
    <meta property="og:image:width" content="{{ $imageWidth }}">
    <meta property="og:image:height" content="{{ $imageHeight }}">
    <meta property="og:image:alt" content="{{ $item->name }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $item->name }}">
    <meta name="twitter:description" content="{{ $cleanDescription }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <script>
        // FIXED: Corrected redirect to hit your front-end store directly
        window.location.replace("{{ url('products/' . $item->slug) }}");
    </script>
</head>
<body>
    <div style="text-align:center; padding:50px; font-family:Arial, sans-serif; color: #333;">
        <h2>{{ $item->name }}</h2>
        <img src="{{ $ogImage }}" alt="{{ $item->name }}" style="max-width:400px; width:100%; height:auto; border-radius:8px;">
        <p style="margin-top:20px; font-size:16px;">Redirecting you to the product details...</p>
        <a href="{{ url('products/' . $item->slug) }}" style="color:#007bff; text-decoration:underline;">Click here if you are not redirected automatically</a>
    </div>
</body>
</html>