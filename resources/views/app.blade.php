<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title data-inertia>{{ config('app.name', 'A melhor oficina') }}</title>

        <link rel="manifest" href="{{ route('pwa.manifest') }}" crossorigin="use-credentials">

        <meta name="theme-color" content="#F5F5F5" media="(prefers-color-scheme: light)">
        <meta name="theme-color" content="#141414" media="(prefers-color-scheme: dark)">

        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">
        <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'A melhor oficina') }}">

                <link rel="icon" href="/assets/img/favicon-32.png" sizes="32x32">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <script>
            (function () {
                var salvo = null;
                try { salvo = localStorage.getItem('oficina-tema'); } catch (e) {}
                var escuro = salvo === 'dark'
                    || ((!salvo || salvo === 'system')
                        && window.matchMedia('(prefers-color-scheme: dark)').matches);
                document.documentElement.classList.toggle('dark', escuro);

                var barra = document.createElement('meta');
                barra.name = 'theme-color';
                barra.content = escuro ? '#141414' : '#F5F5F5';
                document.head.appendChild(barra);
            })();
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">

        <link
            href="https://fonts.bunny.net/css?family=big-shoulders-display:500,700,800,900|barlow-condensed:500,600,700|barlow:400,500,600&display=swap"
            rel="stylesheet"
        />

        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="bg-bg font-sans text-fg antialiased">
        @inertia
    </body>
</html>
