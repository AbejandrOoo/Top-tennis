<x-app-layout>
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-xl shadow-sm">
                    <strong class="font-bold">Atención:</strong> Revisa los siguientes problemas:
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                <h2 class="text-xl font-black text-[#0b3b24] mb-6">Editar Cancha</h2>

                {{-- Formulario para actualizar los datos visibles de una cancha --}}
                {{-- Si se sube una foto nueva el controlador reemplaza la anterior --}}
                <form method="POST" action="{{ route('admin.canchas.update', $cancha->id) }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-bold text-[#0b3b24] mb-2">Nombre de la Cancha</label>
                        <input type="text" name="nombre" value="{{ old('nombre', $cancha->nombre) }}" class="w-full border border-gray-300 rounded-xl py-2 px-4 text-sm font-medium focus:ring-[#0b3b24] focus:border-[#0b3b24]">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-[#0b3b24] mb-2">Superficie</label>
                            <input type="text" name="superficie" value="{{ old('superficie', $cancha->superficie) }}" class="w-full border border-gray-300 rounded-xl py-2 px-4 text-sm font-medium focus:ring-[#0b3b24] focus:border-[#0b3b24]">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-[#0b3b24] mb-2">Iluminación</label>
                            <select name="iluminacion" class="w-full border border-gray-300 rounded-xl py-2 px-4 text-sm font-medium focus:ring-[#0b3b24] focus:border-[#0b3b24]">
                                <option value="Con iluminación" {{ old('iluminacion', $cancha->iluminacion) == 'Con iluminación' ? 'selected' : '' }}>Con iluminación</option>
                                <option value="Sin iluminación" {{ old('iluminacion', $cancha->iluminacion) == 'Sin iluminación' ? 'selected' : '' }}>Sin iluminación</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-[#0b3b24] mb-2">Modalidad Permitida</label>
                        <select name="tipo_partido" class="w-full border border-gray-300 rounded-xl py-2 px-4 text-sm font-medium focus:ring-[#0b3b24] focus:border-[#0b3b24]">
                            <option value="Ambos (Singles y Dobles)" {{ old('tipo_partido', $cancha->tipo_partido) == 'Ambos (Singles y Dobles)' ? 'selected' : '' }}>Ambos (Singles y Dobles)</option>
                            <option value="Solo Singles" {{ old('tipo_partido', $cancha->tipo_partido) == 'Solo Singles' ? 'selected' : '' }}>Solo Singles</option>
                            <option value="Solo Dobles" {{ old('tipo_partido', $cancha->tipo_partido) == 'Solo Dobles' ? 'selected' : '' }}>Solo Dobles</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-[#0b3b24] mb-2">Foto de la Cancha (Opcional)</label>
                        <input type="file" name="foto" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('admin.canchas.index') }}" class="bg-white text-gray-700 border border-gray-300 font-bold py-2.5 px-6 rounded-xl text-sm hover:bg-gray-50 transition">
                            Cancelar
                        </a>
                        <button type="submit" class="bg-[#0b3b24] hover:bg-[#072718] text-white font-bold py-2.5 px-6 rounded-xl text-sm shadow-md transition">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
