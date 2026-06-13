<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Canchas - Top Tennis') }}
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
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nombre de la Cancha</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tipo de Superficie</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Estado Actual</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($canchas as $cancha)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            {{ $cancha->nombre }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $cancha->superficie }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ $cancha->estado }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <div style="display: flex !important; justify-content: center !important; align-items: center !important;">
                                                
                                                <a href="{{ route('admin.canchas.edit', $cancha->id) }}" style="color: #4f46e5 !important; font-weight: bold !important; margin-right: 25px !important; text-decoration: none !important;">
                                                    Editar
                                                </a>
                                                
                                                <form action="{{ route('admin.canchas.deshabilitar', $cancha->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas deshabilitar esta cancha?')" style="display: inline !important;">
                                                    @csrf
                                                    <button type="submit" style="color: #dc2626 !important; font-weight: bold !important; background: none !important; border: none !important; padding: 0 !important; cursor: pointer !important;">
                                                        Deshabilitar
                                                    </button>
                                                </form>

                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">
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
<<<<<<< ours
<<<<<<< ours
</x-app-layout>
=======
</x-app-layout>
>>>>>>> theirs
=======
</x-app-layout>
>>>>>>> theirs
