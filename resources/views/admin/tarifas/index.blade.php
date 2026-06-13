<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestión de Tarifas
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Mensajes de Alerta -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    {{ session('error') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <!-- Columna Izquierda: Formulario para Crear Tarifa -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 md:col-span-1 h-fit border border-gray-200">
                    <h3 class="text-lg font-bold mb-4 text-gray-800 border-b pb-2">Nueva Tarifa</h3>
                    
                    <form action="{{ route('tarifas.store') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Cancha</label>
                            <select name="cancha_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                                <option value="" disabled selected>Seleccione una cancha</option>
                                @foreach($canchas as $cancha)
                                    <option value="{{ $cancha->id }}">{{ $cancha->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Precio por Hora (S/.)</label>
                            <input type="number" step="0.01" name="precio_hora" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej. 60.00" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Hora de Inicio</label>
                            <input type="time" name="hora_inicio" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Hora de Fin</label>
                            <input type="time" name="hora_fin" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        </div>

                        <button type="submit" class="w-full bg-gray-800 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded shadow">
                            Guardar Tarifa
                        </button>
                    </form>
                </div>

                <!-- Columna Derecha: Tabla de Tarifas -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 md:col-span-2 border border-gray-200">
                    <h3 class="text-lg font-bold mb-4 text-gray-800 border-b pb-2">Tarifas Registradas</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto text-sm text-left">
                            <thead class="bg-gray-100 text-gray-600">
                                <tr>
                                    <th class="px-4 py-3 font-semibold rounded-tl-lg">Cancha</th>
                                    <th class="px-4 py-3 font-semibold">Horario</th>
                                    <th class="px-4 py-3 font-semibold">Precio (S/.)</th>
                                    <th class="px-4 py-3 font-semibold text-center">Estado</th>
                                    <th class="px-4 py-3 font-semibold text-center rounded-tr-lg">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($tarifas as $tarifa)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $tarifa->cancha->nombre }}</td>
                                        <td class="px-4 py-3 text-gray-700">
                                            {{ \Carbon\Carbon::parse($tarifa->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($tarifa->hora_fin)->format('H:i') }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-700 font-bold">
                                            {{ number_format($tarifa->precio_hora, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($tarifa->estado == 'Activa')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Activa
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Inactiva
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($tarifa->estado == 'Activa')
                                                <form action="{{ route('tarifas.destroy', $tarifa->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de desactivar esta tarifa? Esto no afectará las reservas pasadas.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium">
                                                        Desactivar
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-400 italic">Desactivada</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                            No hay tarifas registradas. Usa el formulario para crear la primera.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>