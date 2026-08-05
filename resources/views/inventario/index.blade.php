@extends('layouts.app')

@section('header_title', 'Inventario y Catálogo')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    @if(session('success'))
        <div class="fixed top-5 right-5 z-50 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl shadow-lg flex items-center animate-fade-in">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="fixed top-5 right-5 z-50 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl shadow-lg flex items-center animate-fade-in">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            <span class="text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Inventario</h2>
            <p class="text-sm text-gray-500 mt-1">Gestión avanzada del catálogo general de mercancía.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <a href="{{ route('inventario.importar') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition shadow-sm">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Importar
            </a>

            <button onclick="openModal('modal-producto')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-50 transition shadow-sm">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nuevo producto
            </button>

            <button onclick="openModal('modal-entrada')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-semibold hover:bg-emerald-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Registrar entrada
            </button>

            <span class="hidden md:block w-px h-7 bg-gray-200 mx-1"></span>

            <div class="relative" x-data="{ abierto: false }">
                <button @click="abierto = !abierto" @click.away="abierto = false"
                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#0F0F0F] text-white rounded-lg text-sm font-semibold hover:bg-black transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Exportar
                    <svg class="w-3.5 h-3.5 transition-transform" :class="abierto ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <div x-show="abierto" x-cloak x-transition
                     class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
                    <a href="{{ route('inventario.exportar.excel', request()->query()) }}"
                       class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-emerald-50 transition">
                        <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <div>
                            <p class="font-semibold text-gray-800">Excel</p>
                            <p class="text-[11px] text-gray-400">Hoja de cálculo</p>
                        </div>
                    </a>
                    <div class="h-px bg-gray-100"></div>
                    <a href="{{ route('inventario.exportar.pdf', request()->query()) }}"
                       class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-red-50 transition">
                        <span class="w-8 h-8 rounded-lg bg-red-100 text-[#D32030] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </span>
                        <div>
                            <p class="font-semibold text-gray-800">PDF</p>
                            <p class="text-[11px] text-gray-400">Documento imprimible</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form id="filter-form" method="GET" action="{{ route('inventario.index') }}" class="bg-white p-5 rounded-xl border border-gray-200 shadow-sm space-y-4">

        @if(request('solo_nuevos') == '1')
            <input type="hidden" name="solo_nuevos" value="1">
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-4 items-end">

            <div class="lg:col-span-3">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Búsqueda Inteligente</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Medida, marca..." class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 block w-full p-2.5 outline-none">
            </div>

            <div class="lg:col-span-3">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Estado del Stock</label>
                <select name="stock_status" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 block w-full p-2.5 outline-none">
                    <option value="">Todos los niveles</option>
                    <option value="sin_stock" {{ request('stock_status') == 'sin_stock' ? 'selected' : '' }}>Sin Stock (0 uds)</option>
                    <option value="bajo_stock" {{ request('stock_status') == 'bajo_stock' ? 'selected' : '' }}>Bajo Mínimo</option>
                    <option value="ok" {{ request('stock_status') == 'ok' ? 'selected' : '' }}>Stock OK</option>
                </select>
            </div>

            <div class="lg:col-span-3">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Filtrar por Marca</label>
                <select name="marca_filtro" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 block w-full p-2.5 outline-none">
                    <option value="Todas">Todas las marcas</option>
                    @foreach($marcasDisponibles ?? [] as $m)
                        <option value="{{ $m }}" {{ request('marca_filtro') == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-3">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Ordenar Comercial</label>
                <select name="ordenar_precio" onchange="this.form.submit()" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-2 focus:ring-blue-500 block w-full p-2.5 outline-none">
                    <option value="">Por Defecto (Marca/Medida)</option>
                    <option value="publico_mayor" {{ request('ordenar_precio') == 'publico_mayor' ? 'selected' : '' }}>P. Público: Mayor a Menor</option>
                    <option value="publico_menor" {{ request('ordenar_precio') == 'publico_menor' ? 'selected' : '' }}>P. Público: Menor a Mayor</option>
                    <option value="mayoreo_mayor" {{ request('ordenar_precio') == 'mayoreo_mayor' ? 'selected' : '' }}>P. Mayoreo: Mayor a Menor</option>
                    <option value="mayoreo_menor" {{ request('ordenar_precio') == 'mayoreo_menor' ? 'selected' : '' }}>P. Mayoreo: Menor a Mayor</option>
                    <option value="costo_mayor" {{ request('ordenar_precio') == 'costo_mayor' ? 'selected' : '' }}>Costo Compra: Mayor a Menor</option>
                    <option value="costo_menor" {{ request('ordenar_precio') == 'costo_menor' ? 'selected' : '' }}>Costo Compra: Menor a Mayor</option>
                </select>
            </div>
        </div>

        <hr class="border-gray-100">

        <div class="flex flex-col gap-3">
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mr-1">Sucursal:</span>
                <label class="relative block cursor-pointer">
                    <input type="radio" name="sucursal_id" value="" onchange="this.form.submit()" class="peer absolute inset-0 opacity-0 cursor-pointer z-10" {{ !request()->filled('sucursal_id') ? 'checked' : '' }}>
                    <div class="px-3 py-1 rounded-full text-xs font-semibold border transition-all peer-checked:bg-emerald-600 peer-checked:border-emerald-600 peer-checked:text-white bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100">Todas</div>
                </label>
                @foreach($sucursales ?? [] as $suc)
                    <label class="relative block cursor-pointer">
                        <input type="radio" name="sucursal_id" value="{{ $suc->id }}" onchange="this.form.submit()" class="peer absolute inset-0 opacity-0 cursor-pointer z-10" {{ request('sucursal_id') == $suc->id ? 'checked' : '' }}>
                        <div class="px-3 py-1 rounded-full text-xs font-semibold border transition-all peer-checked:bg-emerald-600 peer-checked:border-emerald-600 peer-checked:text-white bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100">{{ $suc->nombre }}</div>
                    </label>
                @endforeach
            </div>

            <div class="flex flex-wrap items-center gap-1.5">
                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mr-1">Categoría:</span>
                @foreach(['Todos', 'Llanta', 'Rin', 'Accesorio', 'Servicio'] as $tipo)
                    <label class="relative block cursor-pointer">
                        <input type="radio" name="tipo" value="{{ $tipo }}" onchange="this.form.submit()" class="peer absolute inset-0 opacity-0 cursor-pointer z-10" {{ request('tipo', 'Todos') == $tipo ? 'checked' : '' }}>
                        <div class="px-3 py-1 rounded-full text-xs font-semibold border transition-all peer-checked:bg-indigo-600 peer-checked:border-indigo-600 peer-checked:text-white bg-gray-50 border-gray-200 text-gray-600 hover:bg-gray-100">{{ $tipo }}</div>
                    </label>
                @endforeach

                {{-- Chip especial: Llegó hoy --}}
                @php $totalNuevos = count($productosNuevosHoy ?? []); @endphp
                <a href="{{ request('solo_nuevos') == '1' ? route('inventario.index') : route('inventario.index', ['solo_nuevos' => '1']) }}"
                   class="px-3 py-1 rounded-full text-xs font-semibold border transition-all inline-flex items-center gap-1
                   {{ request('solo_nuevos') == '1' ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100' }}">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 16a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 7.323V16h2a1 1 0 110 2H7a1 1 0 110-2h2V7.323L6.237 8.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 16a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.616a1 1 0 01.894-1.79l1.599.8L9 4.323V3a1 1 0 011-1z"/></svg>
                    Llegó hoy
                    @if($totalNuevos > 0)
                        <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold {{ request('solo_nuevos') == '1' ? 'bg-white text-emerald-700' : 'bg-emerald-600 text-white' }}">{{ $totalNuevos }}</span>
                    @endif
                </a>
            </div>
        </div>

        @if(request()->hasAny(['sucursal_id', 'tipo', 'q', 'stock_status', 'marca_filtro', 'ordenar_precio', 'solo_nuevos']))
            <div class="flex justify-between items-center pt-2 border-t border-gray-50">
                <button type="submit" class="px-5 py-1.5 bg-blue-600 text-white font-medium rounded-lg text-xs hover:bg-blue-700 transition">Aplicar Filtros Manuales</button>
                <a href="{{ route('inventario.index') }}" class="inline-flex items-center text-xs font-semibold text-red-500 hover:text-red-700 transition">
                    Borrar todos los filtros aplicados
                </a>
            </div>
        @endif
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
        <div class="overflow-x-auto w-full">
            @php
                $viendoSoloServicios = request('tipo') === 'Servicio';
                $nuevosHoy = $productosNuevosHoy ?? [];
            @endphp

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-200">
                        @if($viendoSoloServicios)
                            <th class="p-4 font-semibold whitespace-nowrap">Servicio de Taller</th>
                            <th class="p-4 font-semibold whitespace-nowrap">Precio al Público</th>
                            <th class="p-4 font-semibold text-center whitespace-nowrap">Estado</th>
                        @else
                            <th class="p-4 font-semibold whitespace-nowrap">Producto</th>
                            <th class="p-4 font-semibold whitespace-nowrap">Medida</th>
                            <th class="p-4 font-semibold text-center whitespace-nowrap">Stock</th>
                            <th class="p-4 font-semibold whitespace-nowrap">Costo Compra</th>
                            <th class="p-4 font-semibold whitespace-nowrap">P. Público</th>
                            <th class="p-4 font-semibold whitespace-nowrap">P. Mayoreo</th>
                            <th class="p-4 font-semibold text-center whitespace-nowrap">Estado</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="text-sm text-gray-700 divide-y divide-gray-100">
                    @forelse($productos as $producto)
                        @php
                            $st = $producto->stock_cantidad ?? 0;
                            $min = $producto->stock_minimo ?? 5;
                            $esServicio = $producto->tipo === 'Servicio';
                            $esNuevo = in_array($producto->id, $nuevosHoy);
                        @endphp
                        <tr class="hover:bg-blue-50/40 transition-colors {{ $esNuevo ? 'bg-emerald-50/30' : '' }}">
                            @if($viendoSoloServicios)
                                <td class="p-4">
                                    <p class="font-bold text-gray-900 text-base">{{ $producto->descripcion }}</p>
                                </td>
                                <td class="p-4 font-bold text-emerald-700 text-base whitespace-nowrap">
                                    ${{ number_format($producto->precio_publico, 2) }}
                                </td>
                                <td class="p-4 text-center whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">Activo</span>
                                </td>
                            @else
                                <td class="p-4">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="inline-block px-2 py-0.5 {{ $esServicio ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700' }} text-[10px] rounded-full font-bold tracking-wide">
                                            {{ $producto->tipo }}
                                        </span>
                                        @if($esNuevo)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-500 text-white text-[10px] rounded-full font-bold tracking-wide animate-pulse">
                                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 16a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.715-5.349L11 7.323V16h2a1 1 0 110 2H7a1 1 0 110-2h2V7.323L6.237 8.582l1.715 5.349a1 1 0 01-.285 1.05A3.989 3.989 0 015 16a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.616a1 1 0 01.894-1.79l1.599.8L9 4.323V3a1 1 0 011-1z"/></svg>
                                                NUEVO
                                            </span>
                                        @endif
                                    </div>
                                    <p class="font-bold text-gray-900 leading-tight">
                                        {{ $esServicio ? $producto->descripcion : $producto->marca }}
                                    </p>
                                </td>

                                @if($esServicio)
                                    <td class="p-4"></td>
                                    <td class="p-4"></td>
                                    <td class="p-4"></td>
                                    <td class="p-4 font-semibold text-emerald-700 whitespace-nowrap">${{ number_format($producto->precio_publico, 2) }}</td>
                                    <td class="p-4"></td>
                                    <td class="p-4 text-center whitespace-nowrap">
                                        <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase bg-purple-50 text-purple-700 border border-purple-100">Activo</span>
                                    </td>
                                @else
                                    <td class="p-4 font-mono text-xs text-gray-600 whitespace-nowrap">{{ $producto->medida }}</td>
                                    <td class="p-4 text-center whitespace-nowrap w-24">
                                        <span class="w-16 px-3 py-1 bg-gray-100 border border-gray-200 text-gray-900 rounded-md text-sm font-bold block mx-auto">{{ $st }}</span>
                                    </td>
                                    <td class="p-4 text-gray-500 whitespace-nowrap">${{ number_format($producto->costo, 2) }}</td>
                                    <td class="p-4 font-semibold text-emerald-700 whitespace-nowrap">${{ number_format($producto->precio_publico, 2) }}</td>
                                    <td class="p-4 font-semibold text-indigo-700 whitespace-nowrap">${{ number_format($producto->precio_mayoreo, 2) }}</td>
                                    <td class="p-4 text-center whitespace-nowrap">
                                        @if($st == 0)
                                            <button type="button"
                                                    onclick="verDisponibilidad({{ $producto->id }}, '{{ addslashes($producto->marca . ' ' . $producto->medida) }}')"
                                                    class="px-2 py-1 rounded-full text-[10px] font-bold uppercase bg-red-50 text-red-700 border border-red-100 hover:bg-red-100 transition cursor-pointer">
                                                Agotado · Ver disponibilidad
                                            </button>
                                        @elseif($st < $min)
                                            <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase bg-amber-50 text-amber-700 border border-amber-100">Bajo</span>
                                        @else
                                            <span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">OK</span>
                                        @endif
                                    </td>
                                @endif
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $viendoSoloServicios ? 3 : 7 }}" class="p-12 text-center text-gray-400">
                                @if(request('solo_nuevos') == '1')
                                    No llegó mercancía nueva hoy.
                                @else
                                    Sin registros bajo esos filtros.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($productos->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $productos->links() }}
            </div>
        @endif
    </div>
</div>

<div id="modal-entrada" class="fixed inset-0 bg-black/40 z-[100] hidden flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-900">Registrar Entrada</h3>
            <button onclick="closeModal('modal-entrada')" class="text-gray-400 text-xl">&times;</button>
        </div>
        <form action="{{ route('inventario.entrada.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Producto</label>
                <select name="producto_id" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                    @foreach($productos as $p)
                        @if($p->tipo !== 'Servicio')
                            <option value="{{ $p->id }}">{{ $p->marca }} - {{ $p->medida }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Cantidad</label>
                    <input type="number" name="cantidad" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Costo Unitario ($)</label>
                    <input type="number" name="costo_unitario" step="0.01" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeModal('modal-entrada')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm">Registrar</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-producto" class="fixed inset-0 bg-black/40 z-[100] hidden flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold text-gray-900">Nuevo Producto</h3>
            <button onclick="closeModal('modal-producto')" class="text-gray-400 text-xl">&times;</button>
        </div>
        <form action="{{ route('inventario.producto.store') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tipo</label>
                    <select name="tipo" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                        <option>Llanta</option>
                        <option>Rin</option>
                        <option>Accesorio</option>
                        <option>Servicio</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Marca</label>
                    <input type="text" name="marca" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Medida</label>
                    <input type="text" name="medida" class="w-full p-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" onclick="closeModal('modal-producto')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div id="modal-disponibilidad" class="fixed inset-0 bg-black/40 z-[100] hidden flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl">
        <div class="flex justify-between items-center mb-4">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Disponibilidad en otras sucursales</h3>
                <p id="disp-producto-nombre" class="text-sm text-gray-500"></p>
            </div>
            <button onclick="closeModal('modal-disponibilidad')" class="text-gray-400 text-xl">&times;</button>
        </div>

        <div id="disp-loading" class="text-center py-6 text-sm text-gray-400 hidden">Buscando...</div>
        <ul id="disp-lista" class="space-y-2 max-h-80 overflow-y-auto"></ul>
        <p id="disp-vacio" class="text-sm text-gray-400 text-center py-6 hidden">No hay stock en ninguna otra sucursal.</p>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }

    async function verDisponibilidad(productoId, nombreProducto) {
        openModal('modal-disponibilidad');
        document.getElementById('disp-producto-nombre').textContent = nombreProducto;

        const lista = document.getElementById('disp-lista');
        const vacio = document.getElementById('disp-vacio');
        const loading = document.getElementById('disp-loading');

        lista.innerHTML = '';
        vacio.classList.add('hidden');
        loading.classList.remove('hidden');

        // Respeta el filtro de sucursal actual del index, si hay uno
        const sucursalIdFiltro = new URLSearchParams(window.location.search).get('sucursal_id');
        const url = new URL(`/inventario/${productoId}/disponibilidad`, window.location.origin);
        if (sucursalIdFiltro) url.searchParams.set('sucursal_id', sucursalIdFiltro);

        try {
            const res = await fetch(url);
            const data = await res.json();
            loading.classList.add('hidden');

            if (!data.disponible_en_otras || data.disponible_en_otras.length === 0) {
                vacio.classList.remove('hidden');
                return;
            }

            data.disponible_en_otras.forEach(s => {
                const li = document.createElement('li');
                li.className = 'flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded-lg';
                li.innerHTML = `
                    <div>
                        <p class="font-semibold text-gray-900 text-sm">${s.sucursal}</p>
                        <p class="text-xs text-gray-500">${s.cantidad} pzas disponibles</p>
                    </div>
                    <form action="{{ route('inventario.traspaso.store') }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="producto_id" value="${productoId}">
                        <input type="hidden" name="sucursal_origen" value="${s.sucursal_id}">
                        <input type="hidden" name="sucursal_destino" value="${data.sucursal_actual_id}">
                        <input type="number" name="cantidad" min="1" max="${s.cantidad}" value="1"
                               class="w-16 p-1.5 border border-gray-300 rounded-md text-xs text-center">
                        <button type="submit" class="px-3 py-1.5 bg-emerald-600 text-white rounded-md text-xs font-semibold hover:bg-emerald-700 transition">
                            Traer
                        </button>
                    </form>
                `;
                lista.appendChild(li);
            });
        } catch (e) {
            loading.classList.add('hidden');
            vacio.textContent = 'Error al consultar disponibilidad.';
            vacio.classList.remove('hidden');
        }
    }
</script>
@endsection