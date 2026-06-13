<x-app-layout>
    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-black text-gray-800">🎾 Mantenimiento de Canchas</h2>
                <a href="{{ route('admin.canchas.create') }}" class="bg-[#0b3b24] hover:bg-black text-white font-bold py-2 px-4 rounded-xl transition shadow-sm text-sm">
                    ➕ Agregar Nueva Cancha
                </a>
            </div>

            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-600 text-green-800 font-bold rounded-r-xl shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Tabla de canchas que administra el club --}}
            {{-- Desde aqui se puede editar o sacar una cancha de disponibilidad --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 text-sm">
                            <th class="p-4 font-black border-b border-gray-200">#</th>
                            <th class="p-4 font-black border-b border-gray-200">Nombre de la Cancha</th>
                            <th class="p-4 font-black border-b border-gray-200">Superficie</th>
                            <th class="p-4 font-black border-b border-gray-200">Modalidad</th> <th class="p-4 font-black border-b border-gray-200">Iluminación</th>
                            <th class="p-4 font-black border-b border-gray-200">Estado de Uso</th>
                            <th class="p-4 font-black border-b border-gray-200 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($canchas as $cancha)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 text-sm">
                                <td class="p-4 font-bold text-gray-400">{{ $loop->iteration }}</td>
                                <td class="p-4 font-bold text-gray-800">{{ $cancha->nombre }}</td>
                                <td class="p-4 text-gray-600 font-semibold">{{ $cancha->superficie }}</td>
                                <td class="p-4 text-gray-700 font-medium">{{ $cancha->tipo_partido }}</td> <td class="p-4">
                                    @if($cancha->iluminacion === 'Con iluminación') <span class="text-amber-600 bg-amber-50 px-2 py-0.5 rounded font-bold text-xs border border-amber-200">💡 Con Luz</span>
                                    @else
                                        <span class="text-gray-400 bg-gray-50 px-2 py-0.5 rounded font-bold text-xs border border-gray-200">🌙 Sin Luz</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    @if($cancha->estado === 'Disponible') <span class="bg-green-100 text-green-700 text-xs font-black px-2 py-1 rounded-full">Disponible</span>
                                    @else
                                        <span class="bg-red-100 text-red-700 text-xs font-black px-2 py-1 rounded-full">En Mantenimiento</span>
                                    @endif
                                </td>
                                <td class="p-4 text-center flex justify-center space-x-2">
                                    <a href="{{ route('admin.canchas.edit', $cancha->id) }}" class="bg-blue-50 text-blue-600 border border-blue-200 font-bold py-1 px-3 rounded-lg hover:bg-blue-100 text-xs transition">
                                        ✏ Editar
                                    </a>
                                    
                                    <form method="POST" action="{{ route('admin.canchas.deshabilitar', $cancha->id) }}">
                                        @csrf
                                        <button type="submit" class="font-bold py-1 px-3 rounded-lg text-xs transition {{ $cancha->estado === 'Disponible' ? 'bg-amber-50 text-amber-600 border border-amber-200 hover:bg-amber-100' : 'bg-green-50 text-green-600 border border-green-200 hover:bg-green-100' }}">
                                            {{ $cancha->estado === 'Disponible' ? '🛠 Deshabilitar' : '✔ Habilitar' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
