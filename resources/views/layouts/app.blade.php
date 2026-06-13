<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Top Tennis Digital') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />

        <!-- ESTO ES LO QUE FALTABA PARA QUE FUNCIONE EL DISEÑO VERDE -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Scripts de Laravel -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="min-h-screen bg-gray-50">
            
            <!-- Barra de Navegación -->
            @include('layouts.navigation')

            <!-- Cabecera de la página (Opcional) -->
            @if (isset($header))
                <header class="bg-white shadow border-b border-gray-200">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Contenido Principal (Aquí entra el Dashboard) -->
            <main>
                {{ $slot }}
            </main>
            
        </div>
    </body>
</html>