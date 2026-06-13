<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Canchas') }}
            </h2>
            <a href="{{ route('admin.canchas.create') }}" style="background-color: #2563eb !important; color: #ffffff !important; font-weight: bold !important; padding: 8px 16px !important; border-radius: 8px !important; text-decoration: none !important; display: inline-block !important; font-size: 14px !important;">
                + Nueva Cancha
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre de la Cancha</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Superficie</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Modalidad</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Iluminación</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Estado de Uso</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($canchas as $cancha)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $loop->iteration }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $cancha->nombre }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $cancha->superficie }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $cancha->tipo_partido }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($cancha->iluminacion === 'Con iluminación') Con Luz
                                            @else Sin Luz
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            @if($cancha->estado === 'Disponible') <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded">Disponible</span>
                                            @else <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded">Mantenimiento</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <div style="display: flex !important; justify-content: center !important; align-items: center !important;">
                                                <a href="{{ route('admin.canchas.edit', $cancha->id) }}" style="color: #4f46e5 !important; font-weight: bold !important; margin-right: 25px !important; text-decoration: none !important;">
                                                    Editar
                                                </a>
                                                <form action="{{ route('admin.canchas.deshabilitar', $cancha->id) }}" method="POST" style="display: inline !important;">
                                                    @csrf
                                                    <button type="submit" style="color: {{ $cancha->estado === 'Disponible' ? '#d97706' : '#16a34a' }} !important; font-weight: bold !important; background: none !important; border: none !important; padding: 0 !important; cursor: pointer !important;">
                                                        {{ $cancha->estado === 'Disponible' ? 'Deshabilitar' : 'Habilitar' }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">
                                            No hay canchas registradas todavía.
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
app-layout>
