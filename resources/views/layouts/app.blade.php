<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $metadata = App\Models\Metadata::first();
    @endphp
    <title>@yield('title', $metadata->site_title ?? 'Khám Phá Tiền Tệ')</title>
    <meta name="description" content="{{ $metadata->site_description ?? 'Khám phá tiền tệ Đông Nam Á' }}">
    <meta name="keywords" content="{{ $metadata->site_keywords ?? 'tiền tệ, Đông Nam Á, khám phá' }}">
    <meta name="author" content="{{ $metadata->author ?? 'Khám Phá Tiền Tệ' }}">
    @if($metadata && $metadata->og_image)
        <meta property="og:image" content="{{ asset('storage/' . $metadata->og_image) }}">
    @endif
    @if($metadata && $metadata->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $metadata->favicon) }}">
    @endif
    <link rel="stylesheet" href="{{ asset('static/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @include('layouts.sidebar')
    <main>
        @yield('content')
    </main>
    @include('layouts.footer')
    <script src="{{ asset('static/scripts.js') }}"></script>
</body>
</html>