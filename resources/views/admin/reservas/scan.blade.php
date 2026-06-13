<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-[#0b3b24] leading-tight">
            {{ __('Verificar Ticket de Reserva') }}
        </h2>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen relative">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            {{-- Formulario principal para escanear --}}
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-200 mb-8">
                <form method="POST" action="{{ route('admin.reservas.verifyqr') }}">
                    @csrf
                    <label for="codigo_acceso" class="block text-sm font-black text-[#0b3b24] mb-2">
                        Ingrese el Código de Acceso del Ticket
                    </label>
                    <div class="flex items-center gap-3">
                        <input type="text" id="codigo_acceso" name="codigo_acceso" 
                               value="{{ old('codigo_acceso') }}" 
                               class="w-full border border-gray-300 rounded-xl py-3 px-4 font-mono text-lg font-bold tracking-widest focus:border-[#7cb518] focus:ring-[#7cb518] transition shadow-sm"
                               placeholder="ABC-123" 
                               required 
                               autofocus>
                        <button type="submit" class="bg-[#0b3b24] hover:bg-[#072718] text-white font-bold py-3.5 px-8 rounded-xl shadow-md transition">
                            Verificar
                        </button>
                    </div>
                    @error('codigo_acceso')
                        <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </form>
            </div>

            {{-- Bloque para mostrar el resultado del escaneo --}}
            @if(session('success') || session('error'))
                <div class="p-6 rounded-2xl shadow-sm border {{ session('success') ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                    <p class="font-bold text-lg {{ session('success') ? 'text-green-800' : 'text-red-800' }}">
                        {{ session('success') ?? session('error') }}
                    </p>

                    @if(session('last_reserva'))
                        @php $reserva = session('last_reserva'); @endphp
                        <div class="mt-4 pt-4 border-t {{ session('success') ? 'border-green-200' : 'border-red-200' }}">
                            <p><span class="font-bold">Cliente:</span> {{ $reserva->user->name }}</p>
                            <p><span class="font-bold">Cancha:</span> {{ $reserva->cancha->nombre }}</p>
                            <p><span class="font-bold">Fecha:</span> {{ \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y') }}</p>
                            <p><span class="font-bold">Hora:</span> {{ substr($reserva->hora_inicio, 0, 5) }} - {{ substr($reserva->hora_fin, 0, 5) }}</p>
                            <p><span class="font-bold">Estado Actual:</span> 
                                @if($reserva->ingresado)
                                    <span class="font-black text-green-600">EN CANCHA</span>
                                @else
                                    {{ $reserva->estado }}
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
