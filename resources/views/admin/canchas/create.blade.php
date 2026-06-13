<x-app-layout>
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                
                <h3 class="text-xl font-black text-gray-800 mb-6">➕ Registrar Nueva Cancha</h3>

                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 font-bold text-sm rounded">
                        Por favor corrige los errores del formulario.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.canchas.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-1">Nombre Completo de la Cancha</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-[#0b3b24] focus:ring focus:ring-green-200 focus:ring-opacity-50 font-medium" placeholder="Ej: Cancha Central N°1" required>
                        @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-1">Tipo de Superficie</label>
                        <select name="superficie" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-[#0b3b24] focus:ring font-medium" required>
                            <option value="Arcilla" {{ old('superficie') == 'Arcilla' ? 'selected' : '' }}>Arcilla / Polvo de Ladrillo</option>
                            <option value="Césped" {{ old('superficie') == 'Césped' ? 'selected' : '' }}>Césped Natural</option>
                            <option value="Sintética" {{ old('superficie') == 'Sintética' ? 'selected' : '' }}>Césped Sintético</option>
                            <option value="Rápida" {{ old('superficie') == 'Rápida' ? 'selected' : '' }}>Cancha Rápida (Cemento)</option>
                        </select>
                        @error('superficie') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-1">Modalidad de Juego Permitida</label>
                        <select name="tipo_partido" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-[#0b3b24] focus:ring font-medium" required>
                            <option value="Ambos (Singles y Dobles)" {{ old('tipo_partido') == 'Ambos (Singles y Dobles)' ? 'selected' : '' }}>Ambos (Singles y Dobles)</option>
                            <option value="Singles" {{ old('tipo_partido') == 'Singles' ? 'selected' : '' }}>Solo Singles (Individuales)</option>
                            <option value="Dobles" {{ old('tipo_partido') == 'Dobles' ? 'selected' : '' }}>Solo Dobles (Parejas)</option>
                        </select>
                        @error('tipo_partido') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-black text-gray-700 mb-1">¿Cuenta con Reflectores de Luz?</label>
                        <select name="tiene_luz" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-[#0b3b24] focus:ring font-medium" required>
                            <option value="1" {{ old('tiene_luz') == '1' ? 'selected' : '' }}>Sí, apta para partidos nocturnos</option>
                            <option value="0" {{ old('tiene_luz') == '0' ? 'selected' : '' }}>No, solo partidos diurnos</option>
                        </select>
                        @error('tiene_luz') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('admin.canchas.index') }}" class="bg-gray-100 text-gray-600 font-bold py-2 px-4 rounded-xl text-sm hover:bg-gray-200 transition">Cancelar</a>
                        <button type="submit" class="bg-[#0b3b24] text-white font-bold py-2 px-5 rounded-xl text-sm hover:bg-black transition">Guardar Cancha</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>