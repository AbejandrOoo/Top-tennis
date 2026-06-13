<x-app-layout>
    <div x-data="{ 
        tabActiva: 'activas',
        showPaymentModal: false, 
        showReprogramarModal: false,
        canchaSeleccionada: '', 
        canchaId: '', 
        precioSeleccionado: '0.00',
        reservaId: '',
        metodoPago: 'yape',
        horaSeleccionada: '{{ request('hora', \Carbon\Carbon::now()->addHour()->format('H:00')) }}',
        duracionSeleccionada: '{{ request('duracion', '1') }}',
        
        // Modal de Reprogramación interactivo
        fechaReprogramar: '{{ date('Y-m-d') }}',
        fechaHoyServer: '{{ date('Y-m-d') }}',
        horaActualServer: {{ \Carbon\Carbon::now()->hour }}
    }" class="py-8 bg-gray-50 min-h-screen relative">
        
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-600 text-green-800 font-bold rounded-r-xl shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error') || isset($error))
                <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-600 text-red-800 font-bold rounded-r-xl shadow-sm">
                    {{ session('error') ?? $error }}
                </div>
            @endif

            @if(Auth::user()->rol !== 'admin')
                @php
                    $todasMisReservas = \App\Models\Reserva::where('user_id', Auth::id())
                        ->with('cancha')
                        ->orderBy('id', 'desc')
                        ->get();
                    
                    $activas = $todasMisReservas->whereIn('estado', ['Pendiente', 'Verificado']);
                    $historial = $todasMisReservas->whereIn('estado', ['Cancelada', 'Expirado', 'No_Show', 'Rechazado', 'Completado']);
                @endphp

                <div class="mb-10 bg-white p-6 rounded-2xl shadow-sm border border-gray-200">
                    <div class="flex space-x-6 border-b border-gray-200 mb-6 pb-2">
                        <button @click="tabActiva = 'activas'" :class="tabActiva === 'activas' ? 'text-[#0b3b24] border-b-2 border-[#0b3b24]' : 'text-gray-400 hover:text-gray-600'" class="pb-2 font-black text-lg transition duration-200">
                            🟢 Tickets Activos ({{ $activas->count() }})
                        </button>
                        <button @click="tabActiva = 'historial'" :class="tabActiva === 'historial' ? 'text-gray-800 border-b-2 border-gray-800' : 'text-gray-400 hover:text-gray-600'" class="pb-2 font-black text-lg transition duration-200">
                            📖 Historial y Cancelados
                        </button>
                    </div>

                    <div x-show="tabActiva === 'activas'">
                        @if($activas->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                                @foreach($activas as $reserva)
                                    @php
                                        $fechaReservaCompleta = \Carbon\Carbon::parse($reserva->fecha . ' ' . $reserva->hora_inicio);
                                        $createdAt = \Carbon\Carbon::parse($reserva->created_at);
                                        
                                        $esReciente = \Carbon\Carbon::now()->subMinutes(30)->lessThanOrEqualTo($createdAt);
                                        $faltaMasDe6Horas = \Carbon\Carbon::now()->diffInHours($fechaReservaCompleta, false) >= 6;
                                        $puedeOperar = $faltaMasDe6Horas || $esReciente;
                                        $yaIniciada = \Carbon\Carbon::now()->greaterThanOrEqualTo($fechaReservaCompleta);
                                        
                                        $fechaExpiracion = $createdAt->copy()->addMinutes(30);
                                        $segundosRestantes = $fechaExpiracion->isFuture() ? \Carbon\Carbon::now()->diffInSeconds($fechaExpiracion) : 0;
                                    @endphp

                                    <div class="p-4 rounded-xl border flex flex-col justify-between bg-white shadow-sm border-green-200 relative overflow-hidden">
                                        <div class="text-center pb-3 border-b border-gray-100">
                                            <p class="font-black text-base text-gray-800">{{ $reserva->cancha->nombre }}</p>
                                            <p class="text-xs text-gray-500 font-bold"> {{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}</p>
                                            <p class="text-xs text-[#0b3b24] font-black"> {{ substr($reserva->hora_inicio, 0, 5) }} - {{ substr($reserva->hora_fin, 0, 5) }}</p>
                                        </div>

                                        <div class="py-4 flex flex-col items-center justify-center min-h-[140px]">
                                            @if($reserva->estado === 'Verificado')
                                                <div class="bg-white p-1 rounded-lg border border-gray-200">
                                                    <img src="https://chart.googleapis.com/chart?chs=110x110&cht=qr&chl={{ urlencode($reserva->id) }}&choe=UTF-8" class="w-24 h-24">
                                                </div>
                                            @elseif($reserva->estado === 'Pendiente')
                                                <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-black rounded-full block mb-2">⏳ PENDIENTE</span>
                                                @if($reserva->metodo_pago === 'yape' && $segundosRestantes > 0)
                                                    <div class="text-xs font-bold text-red-600 bg-red-50 p-2 rounded-lg border border-red-100" 
                                                         x-data="{ seconds: {{ $segundosRestantes }}, formatTime() { let m = Math.floor(this.seconds / 60); let s = this.seconds % 60; return (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s; } }" 
                                                         x-init="setInterval(() => { if(seconds > 0) seconds--; else window.location.reload(); }, 1000)">
                                                        Expira en: <span class="font-mono font-black" x-text="formatTime()"></span>
                                                    </div>
                                                @else
                                                    <span class="text-[11px] text-gray-500 font-bold bg-gray-100 px-3 py-1.5 rounded-lg text-center">Pago registrado 👍</span>
                                                @endif
                                            @endif
                                        </div>

                                        <div class="mt-2 pt-2 border-t border-gray-100 grid grid-cols-2 gap-2">
                                            @if(!$yaIniciada && $puedeOperar && $reserva->reprogramaciones < 2)
                                                <button @click="showReprogramarModal = true; reservaId = '{{ $reserva->id }}'" class="text-xs font-bold bg-white text-[#0b3b24] border border-[#0b3b24] py-2 rounded-lg hover:bg-gray-50">🔄 Editar</button>
                                            @else
                                                <button disabled class="text-xs font-bold bg-gray-50 text-gray-400 border border-gray-200 py-2 rounded-lg" title="{{ $yaIniciada ? 'Partido en curso/pasado' : 'Reglas de tiempo excedidas' }}">🚫 Bloqueado</button>
                                            @endif

                                            @if(!$yaIniciada && $puedeOperar)
                                                <form method="POST" action="{{ route('reservas.cancelar', $reserva->id) }}" onsubmit="return confirm('¿Seguro que deseas cancelar?')">
                                                    @csrf
                                                    <button type="submit" class="w-full text-xs font-bold bg-red-50 text-red-600 border border-red-200 py-2 rounded-lg hover:bg-red-100">❌ Cancelar</button>
                                                </form>
                                            @else
                                                <button disabled class="w-full text-xs font-bold bg-gray-50 text-gray-400 border border-gray-200 py-2 rounded-lg">🚫 Bloqueado</button>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 italic text-sm">No tienes tickets activos en este momento.</p>
                        @endif
                    </div>

                    <div x-show="tabActiva === 'historial'" style="display: none;">
                        @if($historial->count() > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($historial as $reserva)
                                    <div class="p-4 rounded-xl border border-gray-200 bg-gray-50 flex justify-between items-center opacity-80">
                                        <div>
                                            <p class="font-bold text-gray-700">{{ $reserva->cancha->nombre }}</p>
                                            <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }} | {{ substr($reserva->hora_inicio, 0, 5) }}</p>
                                            <span class="text-[10px] font-black uppercase text-gray-600 bg-gray-200 px-2 py-0.5 rounded mt-1 inline-block">{{ $reserva->estado }}</span>
                                        </div>
                                        <div>
                                            <form method="POST" action="{{ route('reservas.eliminar', $reserva->id) }}" onsubmit="return confirm('¿Eliminar del historial?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:bg-red-100 p-2 rounded-lg transition" title="Eliminar registro">🗑️</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 italic text-sm">Tu historial está vacío.</p>
                        @endif
                    </div>
                </div>
            @endif

            <form method="GET" action="{{ route('dashboard') }}" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-200 mb-8 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-sm font-bold text-[#0b3b24] mb-2">Fecha</label>
                    <input type="date" name="fecha" min="{{ date('Y-m-d') }}" value="{{ $fecha }}" class="w-full border border-gray-300 rounded-xl py-3 px-4 text-sm font-medium">
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#0b3b24] mb-2">Hora Inicio</label>
                    <select name="hora" x-model="horaSeleccionada" class="w-full border border-gray-300 rounded-xl py-3 px-4 text-sm font-medium">
                        @for($h = 6; $h <= 22; $h++)
                            @php 
                                $hString = sprintf('%02d:00', $h); 
                                $esPasado = ($fecha == date('Y-m-d') && $h <= \Carbon\Carbon::now()->hour);
                            @endphp
                            @if(!$esPasado)
                                <option value="{{ $hString }}" {{ (isset($horaInicioInput) && $horaInicioInput == $hString) ? 'selected' : '' }}>
                                    {{ date('h:00 A', strtotime($hString)) }}
                                </option>
                            @endif
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-[#0b3b24] mb-2">Duración</label>
                    <select name="duracion" x-model="duracionSeleccionada" class="w-full border border-gray-300 rounded-xl py-3 px-4 text-sm font-medium">
                        <option value="1" {{ (isset($duracionInput) && $duracionInput == 1) ? 'selected' : '' }}>1 Hora</option>
                        <option value="2" {{ (isset($duracionInput) && $duracionInput == 2) ? 'selected' : '' }}>2 Horas</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full bg-[#0b3b24] hover:bg-[#072718] text-white font-bold py-3 rounded-xl shadow-md">Verificar Espacios</button>
                </div>
            </form>

            @if(isset($canchas) && $canchas->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($canchas as $cancha)
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 flex flex-col relative overflow-hidden">
                            <div class="absolute top-4 right-4 bg-[#7cb518] text-white text-xs font-black px-3 py-1 rounded-full z-10 shadow-md">Total: S/. {{ number_format($cancha->total_reserva, 2) }}</div>
                            <div class="h-48 bg-gray-100 border-b flex items-center justify-center">
                                @if($cancha->foto)
                                    <img src="{{ asset('storage/' . $cancha->foto) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-gray-400">Sin foto</span>
                                @endif
                            </div>
                            <div class="p-6 flex-grow">
                                <h3 class="text-xl font-black text-[#0b3b24] mb-2">{{ $cancha->nombre }}</h3>
                                <p class="text-sm font-semibold text-gray-600 mb-2">{{ $cancha->superficie }} - {{ $cancha->iluminacion }}</p>
                                
                                <div class="mt-1">
                                    <span class="inline-flex items-center text-xs font-bold text-emerald-800 bg-emerald-50 border border-emerald-100 px-2.5 py-1 rounded-lg">
                                        Modalidad: {{ $cancha->tipo_partido }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-6 pt-0 mt-auto">
                                <button @click="showPaymentModal = true; canchaSeleccionada = '{{ $cancha->nombre }}'; canchaId = '{{ $cancha->id }}'; precioSeleccionado = '{{ number_format($cancha->total_reserva, 2, '.', '') }}'" class="w-full bg-white border-2 border-[#7cb518] text-[#7cb518] hover:bg-[#7cb518] hover:text-white font-black py-3 rounded-xl transition duration-300">
                                    RESERVAR AHORA
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white p-10 rounded-2xl shadow-sm text-center border border-gray-200">
                    <h3 class="text-lg font-black text-gray-600">No hay canchas disponibles o el horario no es válido.</h3>
                </div>
            @endif
        </div>

        <div x-show="showPaymentModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div @click="showPaymentModal = false" class="fixed inset-0 bg-gray-900 bg-opacity-75"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <form method="POST" action="{{ route('reservas.store') }}" class="inline-block align-bottom bg-white rounded-2xl text-left shadow-2xl transform sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    @csrf
                    <input type="hidden" name="cancha_id" :value="canchaId">
                    <input type="hidden" name="fecha" value="{{ $fecha }}">
                    <input type="hidden" name="hora" :value="horaSeleccionada">
                    <input type="hidden" name="duracion" :value="duracionSeleccionada">
                    <input type="hidden" name="metodo_pago" :value="metodoPago">
                    
                    <div class="bg-[#0b3b24] px-6 py-4 border-b-4 border-[#7cb518] flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-black text-white">Pre-Reserva</h3>
                            <p class="text-xs text-[#a7c957]" x-text="canchaSeleccionada"></p>
                        </div>
                        <div class="text-right text-white"><span class="text-xl font-black">S/. <span x-text="precioSeleccionado"></span></span></div>
                    </div>
                    
                    <div class="p-6">
                        <div class="flex gap-4 mb-4">
                            <button @click="metodoPago = 'yape'" type="button" :class="metodoPago === 'yape' ? 'bg-[#0b3b24] text-white' : 'bg-white text-gray-600'" class="flex-1 py-3 border rounded-xl font-bold">Yape</button>
                            <button @click="metodoPago = 'efectivo'" type="button" :class="metodoPago === 'efectivo' ? 'bg-[#0b3b24] text-white' : 'bg-white text-gray-600'" class="flex-1 py-3 border rounded-xl font-bold">Efectivo en Caja</button>
                        </div>
                        <div x-show="metodoPago === 'yape'" class="text-center space-y-4">
                            <div class="text-left">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Código de Operación</label>
                                <input type="text" name="numero_operacion" placeholder="Número de transacción de Yape" class="w-full border-gray-300 rounded-xl shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3">
                        <button type="submit" class="bg-[#7cb518] hover:bg-[#689f15] text-white font-bold py-2.5 px-6 rounded-xl text-sm shadow-sm">Confirmar</button>
                        <button @click="showPaymentModal = false" type="button" class="bg-white text-gray-700 border border-gray-300 font-bold py-2.5 px-6 rounded-xl text-sm">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="showReprogramarModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div @click="showReprogramarModal = false" class="fixed inset-0 bg-gray-900 bg-opacity-75"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <form method="POST" :action="'/reservas/' + reservaId + '/reprogramar'" class="inline-block align-bottom bg-white rounded-2xl text-left shadow-2xl transform sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
                    @csrf
                    <div class="bg-[#0b3b24] px-6 py-4 border-b-4 border-[#7cb518]"><h3 class="text-lg font-black text-white">Cambiar Horario</h3></div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nueva Fecha</label>
                            <input type="date" name="nueva_fecha" x-model="fechaReprogramar" min="{{ date('Y-m-d') }}" class="w-full border-gray-300 rounded-xl text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nueva Hora</label>
                            <select name="nueva_hora" class="w-full border-gray-300 rounded-xl text-sm">
                                <template x-for="h in Array.from({length: 17}, (_, i) => i + 6)" :key="h">
                                    <option x-show="fechaReprogramar !== fechaHoyServer || h > horaActualServer" 
                                            :value="h.toString().padStart(2, '0') + ':00'" 
                                            x-text="(h <= 12 ? (h==12?12:h) : h-12) + ':00 ' + (h < 12 ? 'AM' : 'PM')">
                                    </option>
                                </template>
                            </select>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3">
                        <button type="submit" class="bg-[#0b3b24] text-white font-bold py-2.5 px-6 rounded-xl text-sm">Aplicar Cambio</button>
                        <button @click="showReprogramarModal = false" type="button" class="bg-white text-gray-700 border border-gray-300 font-bold py-2.5 px-6 rounded-xl text-sm">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
