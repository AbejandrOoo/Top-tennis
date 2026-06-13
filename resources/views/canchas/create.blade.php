<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Nueva Cancha') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <strong>Atención</strong> Revisa los siguientes errores:
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('admin.canchas.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Nombre o Identificador:</label>
                                <input type="text" name="nombre" required placeholder="Ej: Cancha Central" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Tipo de Superficie:</label>
                                <select name="superficie" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="Arcilla / Polvo de Ladrillo">Arcilla / Polvo de Ladrillo</option>
                                    <option value="Cemento / Pista Dura">Cemento / Pista Dura</option>
                                    <option value="Césped Natural">Césped Natural</option>
                                    <option value="Césped Artificial">Césped Artificial</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Modalidad de Juego:</label>
                                <select name="tipo_partido" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="Ambos (Singles y Dobles)">Ambos (Singles y Dobles)</option>
                                    <option value="Solo Singles">Solo Singles</option>
                                    <option value="Solo Dobles">Solo Dobles</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Iluminación (Juego nocturno):</label>
                                <select name="iluminacion" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="Con iluminación LED">Con iluminación LED</option>
                                    <option value="Sin iluminación (Solo día)">Sin iluminación (Solo día)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Estado Inicial:</label>
                                <select name="estado" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="Disponible">Disponible (Lista para reservar)</option>
                                    <option value="En Mantenimiento">En Mantenimiento</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Fotografía de la cancha:</label>
                                <input type="file" name="foto" accept="image/*" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1">
                                <p class="text-xs text-gray-500 mt-1">Formatos: JPG, PNG, WEBP (Max: 2MB)</p>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Descripción General (Opcional):</label>
                            <textarea name="descripcion" rows="3" placeholder="Ej: Cancha techada, ubicada cerca de los vestuarios principales..." class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        <div style="display: flex; gap: 15px; align-items: center; justify-content: flex-end; border-top: 1px solid #e5e7eb; padding-top: 1rem;">
                            <a href="{{ route('admin.canchas.index') }}" style="color: #4b5563 !important; font-weight: bold !important; text-decoration: none !important;">
                                Cancelar
                            </a>
                            <button type="submit" style="background-color: #2563eb !important; color: #ffffff !important; font-weight: bold !important; padding: 10px 20px !important; border-radius: 8px !important; border: none !important; cursor: pointer !important; font-size: 14px !important;">
                                Guardar Cancha
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>