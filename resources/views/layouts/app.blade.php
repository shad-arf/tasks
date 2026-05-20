<!DOCTYPE html>
<html
    lang="@yield('html_lang', str_replace('_', '-', app()->getLocale()))"
    dir="@yield('dir', 'ltr')"
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
        <link
            href="https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@300;400;500;600;700&display=swap"
            rel="stylesheet"
        >

        @vite(['resources/css/app.css'])

        @php($appName = config('app.name', 'Laravel'))
        <title>@hasSection('title')@yield('title') - {{ $appName }}@else{{ $appName }}@endif</title>

        @stack('head')
    </head>
    <body
        class="@yield('body_class', 'font-sans antialiased')"
        style="font-family: 'Noto Kufi Arabic', sans-serif"
    >
        @yield('content')

        @stack('scripts')
    </body>
</html>
