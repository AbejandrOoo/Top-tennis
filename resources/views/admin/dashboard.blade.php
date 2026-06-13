<x-app-layout>
    <div x-data="{ tabActiva: 'pendientes' }" class="py-8 bg-gray-50 min-h-screen relative">
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

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-600 text-red-800 font-bold rounded-r-xl shadow-sm">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-8 bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                <div class="flex space-x-6 border-b border-gray-200 mb-6 pb-2">
                    <button @click="tabActiva = 'pendientes'" :class="tabActiva === 'pendientes' ? 'text-purple-700 border-b-2 border-purple-700' : 'text-gray-400 hover:text-gray-600'" class="pb-2 font-black text-lg transition duration-200">
                        📱 Pagos por Aprobar 
                        @if($pendientes->count() > 0) <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $pendientes->count() }}</span> @endif
                    </button>
                    <button @click="tabActiva = 'agenda'" :class="tabActiva === 'agenda' ? 'text-[#0b3b24] border-b-2 border-[#0b3b24]' : 'text-gray-400 hover:text-gray-600'" class="pb-2 font-black text-lg transition duration-200">
                        📅 Agenda de Hoy ({{ date('d/m/Y') }})
                    </button>
                    <button @click="tabActiva = 'configuracion'" :class="tabActiva === 'configuracion' ? 'text-blue-700 border-b-2 border-blue-700' : 'text-gray-400 hover:text-gray-600'" class="pb-2 font-black text-lg transition duration-200">
                        ⚙️ Configuración
                    </button>
                </div>

                {{-- Bloque para revisar pagos que entraron por Yape y siguen pendientes --}}
                {{-- Desde aqui el administrador aprueba o rechaza sin entrar a otra pantalla --}}
                <div x-show="tabActiva === 'pendientes'">
                    @if($pendientes->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($pendientes as $reserva)
                                <div class="p-5 rounded-xl border border-purple-200 bg-purple-50 shadow-sm relative">
                                    @if($reserva->metodo_pago === 'yape')
                                        <div class="absolute top-4 right-4 text-purple-800 text-xs font-black px-2 py-1 bg-purple-200 rounded-full">
                                            Expira en {{ max(0, 30 - \Carbon\Carbon::now()->diffInMinutes($reserva->created_at)) }} min
                                        </div>
                                    @else
                                        <div class="absolute top-4 right-4 text-green-800 text-xs font-black px-2 py-1 bg-green-200 rounded-full">
                                            En Caja
                                        </div>
                                    @endif
                                    <h4 class="font-black text-lg text-gray-800 mb-1">{{ $reserva->cancha->nombre }}</h4>
                                    <p class="text-sm font-bold text-gray-600 mb-4">👤 {{ $reserva->user->name ?? 'Usuario Oculto' }}</p>
                                    
                                    <div class="bg-white p-3 rounded-lg border border-purple-100 mb-4 text-center">
                                        @if($reserva->metodo_pago === 'yape')
                                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">N° Operación Yape</p>
                                            <p class="text-xl font-black text-purple-700">{{ $reserva->numero_operacion }}</p>
                                        @else
                                            <p class="text-xs text-gray-500 uppercase font-bold mb-1">Pago en Efectivo</p>
                                            <p class="text-xl font-black text-green-700">A Cobrar en Caja</p>
                                        @endif
                                        <p class="mt-2 text-sm text-gray-600 font-bold">Monto esperado: S/. {{ number_format($reserva->total, 2) }}</p>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <form method="POST" action="{{ route('admin.reservas.aprobar', $reserva->id) }}">
                                            @csrf
                                            <button type="submit" class="w-full bg-[#7cb518] hover:bg-[#689f15] text-white font-bold py-2 rounded-lg text-sm transition">✅ Aprobar</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.reservas.rechazar', $reserva->id) }}" onsubmit="return confirm('¿Rechazar este pago? La reserva se cancelará y la cancha quedará libre.')">
                                            @csrf
                                            <button type="submit" class="w-full bg-red-100 hover:bg-red-200 text-red-600 font-bold py-2 rounded-lg border border-red-300 text-sm transition">❌ Rechazar</button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-10">
                            <span class="text-4xl block mb-2">😎</span>
                            <p class="text-gray-500 font-bold">No hay pagos pendientes por revisar. Todo está al día.</p>
                        </div>
                    @endif
                </div>

                <!-- PESTAÑA: AGENDA DE HOY -->
                {{-- Agenda del dia para ver reservas que se deben atender en el local --}}
                {{-- Tambien permite marcar el ingreso cuando los jugadores llegan --}}
                <div x-show="tabActiva === 'agenda'" style="display: none;">
                    <div class="flex justify-end mb-4">
                        <a href="{{ route('admin.reservas.showscan') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition text-sm shadow-sm">
                            Verificar por Código 🔎
                        </a>
                    </div>
                    @if($agendaHoy->count() > 0)
                        <div class="overflow-x-auto bg-white rounded-xl shadow border border-gray-200">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-100 text-gray-700 text-sm">
                                        <th class="p-4 font-black border-b border-gray-200">Hora</th>
                                        <th class="p-4 font-black border-b border-gray-200">Cancha</th>
                                        <th class="p-4 font-black border-b border-gray-200">Cliente</th>
                                        <th class="p-4 font-black border-b border-gray-200">Estado / Pago</th>
                                        <th class="p-4 font-black border-b border-gray-200 text-center">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($agendaHoy as $reserva)
                                        <tr class="border-b border-gray-100 hover:bg-gray-50 {{ $reserva->ingresado ? 'bg-green-50 opacity-70' : '' }}">
                                            <td class="p-4 font-bold text-[#0b3b24]">{{ substr($reserva->hora_inicio, 0, 5) }} - {{ substr($reserva->hora_fin, 0, 5) }}</td>
                                            <td class="p-4 font-semibold text-gray-700">{{ $reserva->cancha->nombre }}</td>
                                            <td class="p-4 font-semibold text-gray-700">{{ $reserva->user->name ?? 'Usuario' }}</td>
                                            <td class="p-4">
                                                @if($reserva->estado === 'Verificado')
                                                    <span class="bg-green-100 text-green-700 text-xs font-bold px-2 py-1 rounded">✅ Verificado</span>
                                                @else
                                                    <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2 py-1 rounded">⏳ Pendiente Yape</span>
                                                @endif
                                                <div class="text-xs text-gray-500 mt-1">S/. {{ number_format($reserva->total, 2) }}</div>
                                            </td>
                                            <td class="p-4 text-center">
                                                @if($reserva->estado === 'Verificado' && !$reserva->ingresado)
                                                    <form method="POST" action="{{ route('admin.reservas.checkin', $reserva->id) }}">
                                                        @csrf
                                                        <button type="submit" class="bg-[#0b3b24] text-white text-xs font-bold py-2 px-4 rounded-lg hover:bg-black transition">
                                                            📍 Marcar Llegada
                                                        </button>
                                                    </form>
                                                @elseif($reserva->ingresado)
                                                    <span class="text-xs text-green-600 font-black">✔ En Cancha</span>
                                                @else
                                                    <span class="text-xs text-gray-400 font-bold">Falta Validar Pago</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-gray-500 italic text-sm text-center py-6">No hay partidos programados para el día de hoy.</p>
                    @endif
                </div>

                <!-- PESTAÑA: CONFIGURACIÓN -->
                <div x-show="tabActiva === 'configuracion'" style="display: none;">
                    <div class="bg-white p-6 rounded-xl border border-gray-200">
                        <h3 class="text-lg font-black text-gray-800 mb-4">Configuración de Pagos</h3>
                        <form method="POST" action="{{ route('admin.config.yape') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-sm font-bold text-gray-700 mb-2">Código QR de Yape</label>
                                @if(\Storage::disk('public')->exists('yape_qr.png'))
                                    <div class="mb-3">
                                        <img src="{{ asset('storage/yape_qr.png') }}?v={{ time() }}" alt="QR Yape" class="w-32 h-32 border border-gray-200 rounded">
                                    </div>
                                @endif
                                <input type="file" name="qr_yape" accept="image/*" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                <p class="text-xs text-gray-500 mt-2">Sube una imagen con tu código QR de Yape. Esta imagen se mostrará a los clientes al realizar una reserva.</p>
                            </div>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition">Guardar Configuración</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout> 
