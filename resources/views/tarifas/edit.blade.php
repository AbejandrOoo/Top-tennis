<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Tarifa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Si algo sale mal cuando intentan guardar los cambios en el sistema --}}
            {{-- aca les mostramos un mensaje de alerta para que corrijan el error --}}
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
                    {{-- Todo este bloque sirve para modificar los valores monetarios que ya teniamos --}}
                    {{-- usando casi la misma logica y los mismos pasos que cuando creamos uno nuevo --}}
                    <form action="{{ route('tarifas.update', $tarifa) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Aca la persona tiene que buscar y marcar cual es la pista especifica --}}
                        {{-- a la que le vamos a hacer el cambio en su precio de alquiler --}}
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Seleccionar Cancha:</label>
                            <select name="cancha_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($canchas as $cancha)
                                    <option value="{{ $cancha->id }}" {{ $tarifa->cancha_id == $cancha->id ? 'selected' : '' }}>
                                        {{ $cancha->nombre }} - Superficie: {{ $cancha->superficie }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- En esta parte se puede cambiar si el precio aplica para la manana --}}
                        {{-- para la tarde o para el turno de la noche segun convenga ahora --}}
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Turno:</label>
                            <select name="turno" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="Mañana" {{ $tarifa->turno == 'Mañana' ? 'selected' : '' }}>Mañana</option>
                                <option value="Tarde" {{ $tarifa->turno == 'Tarde' ? 'selected' : '' }}>Tarde</option>
                                <option value="Noche" {{ $tarifa->turno == 'Noche' ? 'selected' : '' }}>Noche</option>
                            </select>
                        </div>

                        {{-- Este es el campo principal donde finalmente colocamos la nueva cantidad --}}
                        {{-- de plata que vamos a pedirle a los clientes por jugar una hora entera --}}
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Precio por Hora en Soles:</label>
                            <input type="number" name="precio_hora" value="{{ old('precio_hora', $tarifa->precio_hora) }}" step="0.01" min="0" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        {{-- Por ultimo estos botones mandan la instruccion definitiva al servidor --}}
                        {{-- o simplemente cancelan toda la movida si al final se arrepienten --}}
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <button type="submit" style="background-color: #2563eb !important; color: #ffffff !important; font-weight: bold !important; padding: 10px 20px !important; border-radius: 8px !important; border: none !important; cursor: pointer !important; font-size: 14px !important;">
                                Actualizar Tarifa
                            </button>
                            <a href="{{ route('tarifas.index') }}" style="color: #4b5563 !important; font-weight: bold !important; text-decoration: none !important;">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>