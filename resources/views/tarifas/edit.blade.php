<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-[#0b3b24] leading-tight">
            {{ __('Editar Tarifa') }}
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen relative">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-600 text-red-800 font-bold rounded-r-xl shadow-sm">
                    <strong>¡Uy! Hubo un problema:</strong>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-200">
                <div class="p-8 text-gray-900">
                    <form action="{{ route('tarifas.update', $tarifa) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
                            <label class="block text-[#0b3b24] text-sm font-black mb-2">Seleccionar Cancha:</label>
                            <select name="cancha_id" required class="w-full border border-gray-300 rounded-xl py-3 px-4 text-sm font-medium focus:border-[#7cb518] focus:ring-[#7cb518] transition shadow-sm">
                                @foreach($canchas as $cancha)
                                    <option value="{{ $cancha->id }}" {{ $tarifa->cancha_id == $cancha->id ? 'selected' : '' }}>
                                        {{ $cancha->nombre }} - Superficie: {{ $cancha->superficie }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <label class="block text-[#0b3b24] text-sm font-black mb-2">Turno:</label>
                                <select name="turno" required class="w-full border border-gray-300 rounded-xl py-3 px-4 text-sm font-medium focus:border-[#7cb518] focus:ring-[#7cb518] transition shadow-sm">
                                    <option value="Mañana" {{ $tarifa->turno == 'Mañana' ? 'selected' : '' }}>Mañana</option>
                                    <option value="Tarde" {{ $tarifa->turno == 'Tarde' ? 'selected' : '' }}>Tarde</option>
                                    <option value="Noche" {{ $tarifa->turno == 'Noche' ? 'selected' : '' }}>Noche</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[#0b3b24] text-sm font-black mb-2">Precio por Hora en Soles:</label>
                                <input type="number" name="precio_hora" value="{{ old('precio_hora', $tarifa->precio_hora) }}" step="0.01" min="0" required class="w-full border border-gray-300 rounded-xl py-3 px-4 text-sm font-medium focus:border-[#7cb518] focus:ring-[#7cb518] transition shadow-sm">
                            </div>
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
                            <button type="submit" class="bg-[#7cb518] hover:bg-[#689f15] text-white font-bold py-3 px-8 rounded-xl shadow-sm transition">
                                Actualizar Tarifa
                            </button>
                            <a href="{{ route('tarifas.index') }}" class="text-gray-500 hover:text-gray-700 font-bold transition">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
