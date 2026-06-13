<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-[#0b3b24] leading-tight">
            {{ __('Editar Cancha') }}
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
                    <form method="POST" action="{{ route('admin.canchas.update', $cancha->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-6">
                            <label class="block text-[#0b3b24] text-sm font-black mb-2">Nombre Completo de la Cancha:</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $cancha->nombre) }}" class="w-full border border-gray-300 rounded-xl py-3 px-4 text-sm font-medium focus:border-[#7cb518] focus:ring-[#7cb518] transition shadow-sm" required>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-[#0b3b24] text-sm font-black mb-2">Tipo de Superficie:</label>
                                <select name="superficie" required class="w-full border border-gray-300 rounded-xl py-3 px-4 text-sm font-medium focus:border-[#7cb518] focus:ring-[#7cb518] transition shadow-sm">
                                    <option value="Arcilla" {{ old('superficie', $cancha->superficie) == 'Arcilla' ? 'selected' : '' }}>Arcilla / Polvo de Ladrillo</option>
                                    <option value="Césped" {{ old('superficie', $cancha->superficie) == 'Césped' ? 'selected' : '' }}>Césped Natural</option>
                                    <option value="Sintética" {{ old('superficie', $cancha->superficie) == 'Sintética' ? 'selected' : '' }}>Césped Sintético</option>
                                    <option value="Rápida" {{ old('superficie', $cancha->superficie) == 'Rápida' ? 'selected' : '' }}>Cancha Rápida (Cemento)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[#0b3b24] text-sm font-black mb-2">Modalidad de Juego Permitida:</label>
                                <select name="tipo_partido" required class="w-full border border-gray-300 rounded-xl py-3 px-4 text-sm font-medium focus:border-[#7cb518] focus:ring-[#7cb518] transition shadow-sm">
                                    <option value="Ambos (Singles y Dobles)" {{ old('tipo_partido', $cancha->tipo_partido) == 'Ambos (Singles y Dobles)' ? 'selected' : '' }}>Ambos (Singles y Dobles)</option>
                                    <option value="Solo Singles" {{ old('tipo_partido', $cancha->tipo_partido) == 'Solo Singles' ? 'selected' : '' }}>Solo Singles (Individuales)</option>
                                    <option value="Solo Dobles" {{ old('tipo_partido', $cancha->tipo_partido) == 'Solo Dobles' ? 'selected' : '' }}>Solo Dobles (Parejas)</option>
                                    <option value="Singles" {{ old('tipo_partido', $cancha->tipo_partido) == 'Singles' ? 'selected' : '' }}>Singles (Individuales)</option>
                                    <option value="Dobles" {{ old('tipo_partido', $cancha->tipo_partido) == 'Dobles' ? 'selected' : '' }}>Dobles (Parejas)</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-[#0b3b24] text-sm font-black mb-2">¿Cuenta con Reflectores de Luz?</label>
                            <select name="iluminacion" required class="w-full border border-gray-300 rounded-xl py-3 px-4 text-sm font-medium focus:border-[#7cb518] focus:ring-[#7cb518] transition shadow-sm">
                                <option value="Con iluminación" {{ old('iluminacion', $cancha->iluminacion) == 'Con iluminación' ? 'selected' : '' }}>Con iluminación (apta para nocturnos)</option>
                                <option value="Sin iluminación" {{ old('iluminacion', $cancha->iluminacion) == 'Sin iluminación' ? 'selected' : '' }}>Sin iluminación (solo diurnos)</option>
                            </select>
                        </div>

                        <div class="mb-8">
                            <label class="block text-[#0b3b24] text-sm font-black mb-2">Foto de la Cancha (Opcional):</label>
                            <input type="file" name="foto" class="w-full border border-gray-300 rounded-xl py-2 px-4 text-sm font-medium focus:border-[#7cb518] focus:ring-[#7cb518] transition shadow-sm bg-gray-50 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-[#e0ecc9] file:text-[#0b3b24] hover:file:bg-[#d0e0b3]">
                            @if($cancha->foto)
                                <p class="text-sm text-gray-500 mt-2 font-bold">Actualmente tiene una foto asignada.</p>
                            @endif
                        </div>

                        <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
                            <button type="submit" class="bg-[#7cb518] hover:bg-[#689f15] text-white font-bold py-3 px-8 rounded-xl shadow-sm transition">
                                Actualizar Cancha
                            </button>
                            <a href="{{ route('admin.canchas.index') }}" class="text-gray-500 hover:text-gray-700 font-bold transition">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
