<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Cancha') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <strong>¡Uy! Hubo un problema:</strong>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('admin.canchas.update', $cancha->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nombre Completo de la Cancha:</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $cancha->nombre) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Superficie:</label>
                            <select name="superficie" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="Arcilla" {{ old('superficie', $cancha->superficie) == 'Arcilla' ? 'selected' : '' }}>Arcilla / Polvo de Ladrillo</option>
                                <option value="Césped" {{ old('superficie', $cancha->superficie) == 'Césped' ? 'selected' : '' }}>Césped Natural</option>
                                <option value="Sintética" {{ old('superficie', $cancha->superficie) == 'Sintética' ? 'selected' : '' }}>Césped Sintético</option>
                                <option value="Rápida" {{ old('superficie', $cancha->superficie) == 'Rápida' ? 'selected' : '' }}>Cancha Rápida (Cemento)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Modalidad de Juego Permitida:</label>
                            <select name="tipo_partido" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="Ambos (Singles y Dobles)" {{ old('tipo_partido', $cancha->tipo_partido) == 'Ambos (Singles y Dobles)' ? 'selected' : '' }}>Ambos (Singles y Dobles)</option>
                                <option value="Solo Singles" {{ old('tipo_partido', $cancha->tipo_partido) == 'Solo Singles' ? 'selected' : '' }}>Solo Singles (Individuales)</option>
                                <option value="Solo Dobles" {{ old('tipo_partido', $cancha->tipo_partido) == 'Solo Dobles' ? 'selected' : '' }}>Solo Dobles (Parejas)</option>
                                <option value="Singles" {{ old('tipo_partido', $cancha->tipo_partido) == 'Singles' ? 'selected' : '' }}>Singles (Individuales)</option>
                                <option value="Dobles" {{ old('tipo_partido', $cancha->tipo_partido) == 'Dobles' ? 'selected' : '' }}>Dobles (Parejas)</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">¿Cuenta con Reflectores de Luz?</label>
                            <select name="iluminacion" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="Con iluminación" {{ old('iluminacion', $cancha->iluminacion) == 'Con iluminación' ? 'selected' : '' }}>Con iluminación (apta para nocturnos)</option>
                                <option value="Sin iluminación" {{ old('iluminacion', $cancha->iluminacion) == 'Sin iluminación' ? 'selected' : '' }}>Sin iluminación (solo diurnos)</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Foto de la Cancha (Opcional):</label>
                            <input type="file" name="foto" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        <div style="display: flex; gap: 15px; align-items: center;">
                            <button type="submit" style="background-color: #2563eb !important; color: #ffffff !important; font-weight: bold !important; padding: 10px 20px !important; border-radius: 8px !important; border: none !important; cursor: pointer !important; font-size: 14px !important;">
                                Actualizar Cancha
                            </button>
                            <a href="{{ route('admin.canchas.index') }}" style="color: #4b5563 !important; font-weight: bold !important; text-decoration: none !important;">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
