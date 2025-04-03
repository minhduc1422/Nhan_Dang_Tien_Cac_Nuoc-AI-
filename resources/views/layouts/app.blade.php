<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Khám Phá Tiền Tệ')</title>
    
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    @include('layouts.sidebar')

    <header>
        <div class="logo">Khám Phá Tiền Tệ</div>
    </header>

    <main>
        @yield('content')
    </main>

    @include('layouts.footer')

    <script src="{{ asset('js/scripts.js') }}"></script>
</body>
</html>
