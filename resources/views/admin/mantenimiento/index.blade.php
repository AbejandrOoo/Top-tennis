<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestión de Mantenimientos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
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
                
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 md:col-span-1 h-fit border border-gray-200">
                    <h3 class="text-lg font-bold mb-4 text-gray-800 border-b pb-2">Programar Mantenimiento</h3>
                    <p class="text-xs text-red-600 mb-4">
                        * Advertencia: Las reservas que choquen con este horario serán canceladas automáticamente.
                    </p>
                    
                    <form action="{{ route('mantenimientos.store') }}" method="POST">
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
                            <label class="block text-gray-700 text-sm font-bold mb-2">Fecha y Hora de Inicio</label>
                            <input type="datetime-local" name="fecha_inicio" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Fecha y Hora de Fin</label>
                            <input type="datetime-local" name="fecha_fin" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Motivo</label>
                            <input type="text" name="motivo" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ej. Pintura del piso, Cambio de red..." required>
                        </div>

                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded shadow">
                            Guardar y Bloquear Cancha
                        </button>
                    </form>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 md:col-span-2 border border-gray-200">
                    <h3 class="text-lg font-bold mb-4 text-gray-800 border-b pb-2">Historial de Mantenimientos</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto text-sm text-left">
                            <thead class="bg-gray-100 text-gray-600">
                                <tr>
                                    <th class="px-4 py-3 font-semibold rounded-tl-lg">Cancha</th>
                                    <th class="px-4 py-3 font-semibold">Inicio</th>
                                    <th class="px-4 py-3 font-semibold">Fin</th>
                                    <th class="px-4 py-3 font-semibold">Motivo</th>
                                    <th class="px-4 py-3 font-semibold text-center">Estado</th>
                                    <th class="px-4 py-3 font-semibold text-center rounded-tr-lg">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($mantenimientos as $mantenimiento)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 font-medium text-gray-900">{{ $mantenimiento->cancha->nombre }}</td>
                                        <td class="px-4 py-3 text-gray-700">
                                            {{ \Carbon\Carbon::parse($mantenimiento->fecha_inicio)->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">
                                            {{ \Carbon\Carbon::parse($mantenimiento->fecha_fin)->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">{{ $mantenimiento->motivo }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if($mantenimiento->estado == 'Programado')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Programado</span>
                                            @elseif($mantenimiento->estado == 'En proceso')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">En proceso</span>
                                            @elseif($mantenimiento->estado == 'Finalizado')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Finalizado</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Cancelado</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if(in_array($mantenimiento->estado, ['Programado', 'En proceso']))
                                                <form action="{{ route('mantenimientos.destroy', $mantenimiento->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de cancelar este mantenimiento? Las reservas afectadas ya fueron canceladas y no se restaurarán automáticamente.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-xs">
                                                        Cancelar Mantenimiento
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-400 italic text-xs">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                            No hay mantenimientos registrados.
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