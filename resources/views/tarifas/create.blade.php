<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Registrar Nueva Tarifa') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Aca verificamos si paso algo raro o si el sistema devolvio alguna queja --}}
            {{-- para avisarle a la persona antes de que siga con lo suyo en la pagina --}}
            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <strong>¡Uy! Hubo un problema:</strong>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{-- Todo esto es la estructura para mandar los datos de cuanto vamos a cobrar dependiendo del horario --}}
                    {{-- y a donde pertenece ese precio ademas cuidamos que no se nos mezclen los horarios repetidos --}}
                    <form action="{{ route('tarifas.store') }}" method="POST">
                        @csrf

                        {{-- Esta parte es vital porque necesitamos saber exactamente a cual de todas las pistas --}}
                        {{-- le estamos poniendo este valor para que luego al cobrar salga bien la cuenta --}}
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Seleccionar Cancha:</label>
                            <select name="cancha_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Elige una cancha --</option>
                                @foreach($canchas as $cancha)
                                    <option value="{{ $cancha->id }}">{{ $cancha->nombre }} - Superficie: {{ $cancha->superficie }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Aca definimos el momento del dia en que aplica el cobro ya sea por la manana --}}
                        {{-- por la tarde o por la noche para mantener un orden bien claro --}}
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Turno:</label>
                            <select name="turno" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Elige un turno --</option>
                                <option value="Mañana">Mañana</option>
                                <option value="Tarde">Tarde</option>
                                <option value="Noche">Noche</option>
                            </select>
                        </div>

                        {{-- Este es el valor monetario que pedimos por sesion de sesenta minutos y es re importante --}}
                        {{-- porque con esto el sistema hace toda la matematica para sacar la cuenta al final del dia --}}
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Precio por Hora en Soles:</label>
                            <input type="number" name="precio_hora" step="0.01" min="0" placeholder="Ej: 50.00" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        {{-- Al final dejamos las opciones de accion para que todo el esfuerzo de llenar la informacion --}}
                        {{-- se guarde de verdad o por si deciden arrepentirse y echar todo para atras --}}
                        <div style="display: flex; gap: 15px; align-items: center;">
                            <button type="submit" style="background-color: #2563eb !important; color: #ffffff !important; font-weight: bold !important; padding: 10px 20px !important; border-radius: 8px !important; border: none !important; cursor: pointer !important; font-size: 14px !important;">
                                Guardar Tarifa
                            </button>
                            <a href="{{ route('tarifas.index') }}" style="color: #4b5563 !important; font-weight: bold !important; text-decoration: none !important;">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>