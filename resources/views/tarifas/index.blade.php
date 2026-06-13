<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de Tarifas') }}
            </h2>
            <a href="{{ route('tarifas.create') }}" style="background-color: #2563eb !important; color: #ffffff !important; font-weight: bold !important; padding: 8px 16px !important; border-radius: 8px !important; text-decoration: none !important; display: inline-block !important; font-size: 14px !important;">
                + Nueva Tarifa
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Aca revisamos si acabamos de hacer algun cambio importante de manera correcta --}}
            {{-- para pintar un cartel verde y que la persona sepa que todo salio super bien --}}
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Todo este pedazo de codigo sirve para armar el cuadro principal donde mostramos --}}
                    {{-- la lista completita de precios que tenemos guardados por cada turno y por cada cancha --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            {{-- Esta es la cabecera del cuadro donde le ponemos nombre a cada columna --}}
                            {{-- para que la persona entienda que informacion esta viendo en la fila de abajo --}}
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Cancha</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Turno</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Precio / Hora</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                {{-- Aca el sistema da un monton de vueltas por cada uno de los precios guardados --}}
                                {{-- y va pintando una linea nueva con sus datos para llenar toda la tabla --}}
                                @forelse($tarifas as $tarifa)
                                    <tr class="hover:bg-gray-50 transition duration-150">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            {{ $tarifa->cancha->nombre }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $tarifa->turno }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-green-600">
                                            S/. {{ number_format($tarifa->precio_hora, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            {{-- Y finalmente aca ponemos los botones magicos para arreglar algun --}}
                                            {{-- precio que quedo mal o de plano para borrarlo si ya no nos sirve mas --}}
                                            <div style="display: flex !important; justify-content: center !important; align-items: center !important;">
                                                <a href="{{ route('tarifas.edit', $tarifa) }}" style="color: #4f46e5 !important; font-weight: bold !important; margin-right: 25px !important; text-decoration: none !important;">
                                                    Editar
                                                </a>
                                                <form action="{{ route('tarifas.destroy', $tarifa) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar esta tarifa?')" style="display: inline !important;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" style="color: #dc2626 !important; font-weight: bold !important; background: none !important; border: none !important; padding: 0 !important; cursor: pointer !important;">
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    {{-- Este mensaje salvavidas aparece solito cuando el sistema no encuentra --}}
                                    {{-- absolutamente nada para mostrar y asi la tabla no queda completamente en blanco --}}
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-center text-sm text-gray-500">
                                            No hay tarifas registradas todavía.
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