<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-[#0b3b24] leading-tight">
                {{ __('Gestión de Canchas') }}
            </h2>
            <a href="{{ route('admin.canchas.create') }}" class="bg-[#7cb518] hover:bg-[#689f15] text-white font-bold py-2.5 px-6 rounded-xl text-sm shadow-sm transition">
                + Nueva Cancha
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen relative">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-600 text-green-800 font-bold rounded-r-xl shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-600 text-red-800 font-bold rounded-r-xl shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-200">
                <div class="p-6 text-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">#</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Nombre de la Cancha</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Superficie</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Modalidad</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Iluminación</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider">Estado de Uso</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($canchas as $cancha)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ $loop->iteration }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-[#0b3b24]">{{ $cancha->nombre }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-600">{{ $cancha->superficie }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-600">{{ $cancha->tipo_partido }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-600">
                                            @if($cancha->iluminacion === 'Con iluminación') Con Luz
                                            @else Sin Luz
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            @if($cancha->estado === 'Disponible') <span class="bg-green-100 text-green-800 text-xs font-bold px-3 py-1 rounded-full shadow-sm">Disponible</span>
                                            @else <span class="bg-red-100 text-red-800 text-xs font-bold px-3 py-1 rounded-full shadow-sm">Mantenimiento</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <div class="flex justify-center items-center gap-4">
                                                <a href="{{ route('admin.canchas.edit', $cancha->id) }}" class="text-[#0b3b24] hover:text-[#072718] font-bold transition">
                                                    Editar
                                                </a>
                                                <form action="{{ route('admin.canchas.deshabilitar', $cancha->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="{{ $cancha->estado === 'Disponible' ? 'text-amber-600 hover:text-amber-800' : 'text-[#7cb518] hover:text-[#689f15]' }} font-bold transition">
                                                        {{ $cancha->estado === 'Disponible' ? 'Deshabilitar' : 'Habilitar' }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.canchas.destroy', $cancha->id) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta cancha?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 font-bold transition">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-10 text-center text-sm font-bold text-gray-500">
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
