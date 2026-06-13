<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro | Top Tennis Digital</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,700,900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased bg-gray-100 flex items-center justify-center min-h-screen py-8" style="font-family: 'figtree', sans-serif;">

    <div class="max-w-lg w-full bg-white rounded-3xl shadow-2xl overflow-hidden mx-4">
        
        <div class="bg-[#0b3b24] pt-12 pb-8 px-6 text-center relative border-b-8 border-[#7cb518]">
            <h1 class="text-3xl font-black text-white tracking-widest relative z-10">TOP TENNIS DIGITAL</h1>
            <p class="text-xs font-bold text-[#a7c957] tracking-[0.2em] mt-3 relative z-10">CREA TU CUENTA Y RESERVA</p>
        </div>

        <div class="p-8">
            
            <div class="flex border-b border-gray-200 mb-8">
                <a href="{{ url('/') }}" class="w-1/2 text-center pb-4 text-gray-400 font-semibold hover:text-gray-600 transition">
                    Iniciar Sesión
                </a>
                <div class="w-1/2 text-center pb-4 border-b-4 border-[#7cb518]">
                    <span class="text-[#0b3b24] font-bold text-lg">Registrarse</span>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-600 text-red-700 rounded-lg shadow-sm">
                    <strong class="font-bold text-sm">Por favor corrige lo siguiente:</strong>
                    <ul class="mt-2 list-disc list-inside text-xs font-semibold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nombre Completo</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-[#7cb518] focus:ring-[#7cb518] py-3 px-4 text-sm">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Correo Electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="ejemplo@correo.com" class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-[#7cb518] focus:ring-[#7cb518] py-3 px-4 text-sm">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Contraseña</label>
                    <input type="password" name="password" required placeholder="••••••••" class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-[#7cb518] focus:ring-[#7cb518] py-3 px-4 text-sm">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" required placeholder="••••••••" class="w-full border border-gray-300 rounded-lg shadow-sm focus:border-[#7cb518] focus:ring-[#7cb518] py-3 px-4 text-sm">
                </div>

                <button type="submit" class="w-full bg-[#7cb518] hover:bg-[#689f15] text-white font-bold py-4 rounded-xl text-lg transition duration-300 shadow-lg shadow-green-200">
                    Completar Registro
                </button>
            </form>

        </div>
    </div>

</body>
</html>