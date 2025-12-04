<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
    @endif
</head>

<body>
    <div class="p-1 max-w-6xl mx-auto ring-1 ring-gray-100/70">
        <!-- Header Principal e Logo -->
        <header
            class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-gray-100 pb-2 mb-2">
            <div class="space-y-1">
                {{ $header }}
            </div>
            <div>
                <img src="{{ asset('/logos/Logo LocSilva white.png') }}" alt="Logo LocSilva"
                    class="h-14 mt-4 sm:mt-0 opacity-85">
            </div>
        </header>

        <!-- Conteúdo Principal -->
        <main>
            {{ $slot }}
        </main>

        <!-- Footer Principal -->
        <footer class="mt-6 pt-4 border-t border-gray-100 text-center">
            <p class="text-xs text-gray-400">
                Relatório gerado em {{ now()->format('d/m/Y \à\s H:i:s') }} | Este é um documento interno
            </p>
        </footer>

    </div>
    <script>
        window.print();
    </script>
</body>
