<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Top Tennis Digital') }}</title>

        <!-- Fuentes generales del proyecto -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,900&display=swap" rel="stylesheet" />

        <!-- Tailwind se carga para mantener los estilos del panel -->
        <script src="https://cdn.tailwindcss.com"></script>

        <!-- Archivos propios de Laravel y Vite -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50">
        <div class="min-h-screen bg-gray-50">

            <!-- Barra principal que se comparte en las pantallas internas -->
            @include('layouts.navigation')

            <!-- Cabecera que algunas pantallas usan para mostrar titulo -->
            @if (isset($header))
                <header class="bg-white shadow border-b border-gray-200">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Aqui se coloca el contenido de cada pantalla -->
            <main>
                {{ $slot }}
            </main>

        </div>
    </body>
</html>
