<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión | Top Tennis Digital</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,700,900&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="antialiased bg-gray-100 flex items-center justify-center min-h-screen py-8" style="font-family: 'figtree', sans-serif;">

    <div class="max-w-lg w-full bg-white rounded-3xl shadow-2xl overflow-hidden mx-4">
        
        <div class="bg-[#0b3b24] pt-12 pb-8 px-6 text-center relative border-b-8 border-[#7cb518]">
            <h1 class="text-3xl font-black text-white tracking-widest relative z-10">TOP TENNIS DIGITAL</h1>
            <p class="text-xs font-bold text-[#a7c957] tracking-[0.2em] mt-3 relative z-10">RESERVA TU CANCHA EN SEGUNDOS</p>
        </div>

        <div class="p-8">
            
            @auth
                <div class="text-center py-6">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <p class="text-lg font-bold text-gray-800 mb-2">¡Hola, {{ Auth::user()->name }}!</p>
                    <p class="text-sm text-gray-500 mb-6">Tu sesión está activa en este momento.</p>
                    
                    <a href="{{ url('/dashboard') }}" class="block w-full text-center bg-[#7cb518] hover:bg-[#689f15] text-white font-bold py-3 rounded-xl mb-4 transition">
                        Ir a mi Panel
                    </a>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-center border-2 border-red-500 text-red-500 hover:bg-red-50 font-bold py-3 rounded-xl transition">
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            @else
                <div class="flex border-b border-gray-200 mb-8">
                    <div class="w-1/2 text-center pb-4 border-b-4 border-[#7cb518]">
                        <span class="text-[#0b3b24] font-bold text-lg">Iniciar Sesión</span>
                    </div>
                    <a href="{{ route('register') }}" class="w-1/2 text-center pb-4 text-gray-400 font-semibold hover:text-gray-600 transition">
                        Registrarse
                    </a>
                </div>

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-600 text-red-700 rounded-lg shadow-sm">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <strong class="font-bold text-lg">¡Acceso Denegado!</strong>
                        </div>
                        <p class="mt-2 text-sm text-red-600 font-semibold">
                            El correo o la contraseña son incorrectos.
                        </p>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Correo Electrónico</label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="ejemplo@correo.com" class="w-full border @error('email') border-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:border-[#7cb518] focus:ring-[#7cb518] py-3 px-4 text-sm">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Contraseña</label>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full border @error('email') border-red-500 @else border-gray-300 @enderror rounded-lg shadow-sm focus:border-[#7cb518] focus:ring-[#7cb518] py-3 px-4 text-sm">
                    </div>

                    <button type="submit" class="w-full bg-[#7cb518] hover:bg-[#689f15] text-white font-bold py-4 rounded-xl text-lg transition duration-300 shadow-lg shadow-green-200">
                        Iniciar Sesión
                    </button>
                </form>
            @endauth

        </div>
    </div>

</body>
</html> 