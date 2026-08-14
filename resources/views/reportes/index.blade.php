@extends('layouts.app')

@section('header_title', 'Reportes y Estadísticas')

@section('content')
<!-- Contenedor principal con estado para animaciones de entrada -->
<div class="max-w-7xl mx-auto space-y-6 transition-colors duration-500" 
     x-data="{ 
         periodo: 'hoy', 
         cargado: false
     }" 
     x-init="setTimeout(() => cargado = true, 100);">

    {{-- Aviso Animado --}}
    <div class="bg-blue-50 dark:bg-[#121212] border-l-4 border-blue-400 dark:border-blue-500 rounded-r-xl px-5 py-4 flex items-start gap-3 shadow-sm transform transition-all duration-700 delay-100 hover:shadow-md"
         :class="cargado ? 'opacity-100 translate-x-0' : 'opacity-0 -translate-x-10'">
        <svg class="w-5 h-5 text-blue-500 dark:text-blue-400 shrink-0 mt-0.5 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>
            <p class="text-sm font-bold text-blue-900 dark:text-gray-200 transition-colors">Módulo en construcción</p>
            <p class="text-sm text-blue-700 dark:text-gray-400 mt-0.5 transition-colors">Los datos se conectarán con tus ventas e inventario reales. Por ahora todo está en cero.</p>
        </div>
    </div>

    {{-- Barra de periodo + exportar --}}
    <div class="bg-white dark:bg-[#151515] rounded-2xl border border-gray-200 dark:border-neutral-800 p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-4 shadow-sm transform transition-all duration-700 delay-200 hover:shadow-md"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-5'">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full md:w-auto">
            <span class="text-[11px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-wide">Periodo</span>
            <div class="flex items-center bg-gray-100 dark:bg-[#0A0A0A] rounded-xl p-1 w-full sm:w-auto overflow-x-auto border dark:border-neutral-800">
                <button @click="periodo = 'hoy'" :class="periodo === 'hoy' ? 'bg-[#818CF8] text-white shadow-md transform scale-105' : 'text-gray-600 dark:text-neutral-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-neutral-800'" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-300 w-full sm:w-auto">Hoy</button>
                <button @click="periodo = 'semana'" :class="periodo === 'semana' ? 'bg-[#818CF8] text-white shadow-md transform scale-105' : 'text-gray-600 dark:text-neutral-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-neutral-800'" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-300 w-full sm:w-auto">Semana</button>
                <button @click="periodo = 'mes'" :class="periodo === 'mes' ? 'bg-[#818CF8] text-white shadow-md transform scale-105' : 'text-gray-600 dark:text-neutral-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-neutral-800'" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-300 w-full sm:w-auto">Mes</button>
                <button @click="periodo = 'personalizado'" :class="periodo === 'personalizado' ? 'bg-[#818CF8] text-white shadow-md transform scale-105' : 'text-gray-600 dark:text-neutral-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200 dark:hover:bg-neutral-800'" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all duration-300 w-full sm:w-auto whitespace-nowrap">Personalizado</button>
            </div>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end">
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-bold text-gray-400 dark:text-neutral-500 uppercase tracking-wide hidden sm:block">Sucursal</span>
                <select class="px-3 py-2 bg-gray-50 dark:bg-[#0A0A0A] border border-gray-200 dark:border-neutral-800 rounded-xl text-sm text-gray-700 dark:text-neutral-300 focus:outline-none focus:ring-2 focus:ring-[#818CF8] transition-colors cursor-pointer hover:border-gray-300 dark:hover:border-neutral-600">
                    <option>Todas las sucursales</option>
                    <option>Administración General</option>
                    <option>Chalco</option>
                </select>
            </div>
            <button class="group inline-flex items-center gap-2 px-4 py-2 bg-[#818CF8] text-white rounded-xl text-sm font-semibold hover:bg-[#6366F1] hover:shadow-lg hover:shadow-indigo-500/30 transform hover:-translate-y-0.5 transition-all duration-300">
                <svg class="w-4 h-4 group-hover:animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Exportar
            </button>
        </div>
    </div>

    {{-- Métricas Animadas (Tarjetas) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Tarjeta 1 -->
        <div class="bg-white dark:bg-[#151515] rounded-2xl p-6 border border-gray-200 dark:border-neutral-800 shadow-sm hover:shadow-xl hover:border-emerald-200 dark:hover:border-emerald-900/50 transform hover:-translate-y-1 transition-all duration-500 delay-300"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="flex items-center justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-500 flex items-center justify-center transition-transform hover:rotate-12 duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-[10px] font-bold text-gray-400 dark:text-neutral-600 uppercase">vs. ayer</span>
            </div>
            <p class="text-xs text-gray-400 dark:text-neutral-500 font-semibold uppercase tracking-wide">Ingresos Totales</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1 transition-colors">$0.00</p>
            <p class="text-xs text-emerald-500 dark:text-emerald-500 font-medium mt-2 flex items-center gap-1 group">
                <span class="transform group-hover:translate-x-1 transition-transform">→</span> 0% incremento
            </p>
        </div>

        <!-- Tarjeta 2 -->
        <div class="bg-white dark:bg-[#151515] rounded-2xl p-6 border border-gray-200 dark:border-neutral-800 shadow-sm hover:shadow-xl hover:border-gray-400 dark:hover:border-neutral-600 transform hover:-translate-y-1 transition-all duration-500 delay-400"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="flex items-center justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-[#1A1A1A] dark:bg-[#2A2A2A] text-white flex items-center justify-center transition-transform hover:scale-110 duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                </div>
                <span class="text-[10px] font-bold text-gray-400 dark:text-neutral-600 uppercase">vs. ayer</span>
            </div>
            <p class="text-xs text-gray-400 dark:text-neutral-500 font-semibold uppercase tracking-wide">Ventas Realizadas</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1 transition-colors">0</p>
            <p class="text-xs text-gray-400 dark:text-neutral-500 font-medium mt-2 flex items-center gap-1 group">
                <span class="transform group-hover:translate-x-1 transition-transform">→</span> Sin cambios
            </p>
        </div>

        <!-- Tarjeta 3 -->
        <div class="bg-white dark:bg-[#151515] rounded-2xl p-6 border border-gray-200 dark:border-neutral-800 shadow-sm hover:shadow-xl hover:border-indigo-200 dark:hover:border-indigo-900/50 transform hover:-translate-y-1 transition-all duration-500 delay-500"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="flex items-center justify-between mb-4">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-[#818CF8] dark:text-[#818CF8] flex items-center justify-center transition-transform hover:-rotate-12 duration-300">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m9-1.13a4 4 0 100-5.4M9 14a4 4 0 100-8 4 4 0 000 8z"/></svg>
                </div>
                <span class="text-[10px] font-bold text-gray-400 dark:text-neutral-600 uppercase">vs. ayer</span>
            </div>
            <p class="text-xs text-gray-400 dark:text-neutral-500 font-semibold uppercase tracking-wide">Nuevos Clientes</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1 transition-colors">0</p>
            <p class="text-xs text-[#818CF8] dark:text-[#818CF8] font-medium mt-2 flex items-center gap-1 group">
                <span class="transform group-hover:translate-x-1 transition-transform">→</span> 0% tasa
            </p>
        </div>
    </div>

    {{-- Gráficas --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Gráfica 1 -->
        <div class="bg-white dark:bg-[#151515] rounded-2xl border border-gray-200 dark:border-neutral-800 p-6 shadow-sm hover:shadow-lg transition-all duration-500 delay-600 transform"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Ventas por Día</h3>
                <svg class="w-4 h-4 text-gray-300 dark:text-neutral-600 hover:text-gray-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/></svg>
            </div>
            <p class="text-xs text-gray-400 dark:text-neutral-500 mb-4">Tendencia de ingresos en el tiempo</p>
            <div class="h-56 border-2 border-dashed border-gray-200 dark:border-neutral-800 rounded-xl flex flex-col items-center justify-center text-gray-300 dark:text-neutral-700 group hover:border-[#818CF8] dark:hover:border-[#818CF8] hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 transition-all duration-300">
                <svg class="w-10 h-10 mb-2 group-hover:scale-110 group-hover:text-[#818CF8] transition-all duration-300 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 12l3-3 3 3 4-4M3 21h18"/></svg>
                <p class="text-sm font-medium group-hover:text-gray-700 dark:group-hover:text-neutral-300 transition-colors">Gráfico de líneas</p>
                <p class="text-xs">Se mostrará cuando haya ventas</p>
            </div>
        </div>

        <!-- Gráfica 2 -->
        <div class="bg-white dark:bg-[#151515] rounded-2xl border border-gray-200 dark:border-neutral-800 p-6 shadow-sm hover:shadow-lg transition-all duration-500 delay-700 transform"
             :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
            <div class="flex items-center justify-between mb-1">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Ventas por Sucursal</h3>
                <svg class="w-4 h-4 text-gray-300 dark:text-neutral-600 hover:text-gray-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"/></svg>
            </div>
            <p class="text-xs text-gray-400 dark:text-neutral-500 mb-4">Desempeño comparativo regional</p>
            <div class="h-56 border-2 border-dashed border-gray-200 dark:border-neutral-800 rounded-xl flex flex-col items-center justify-center text-gray-300 dark:text-neutral-700 group hover:border-[#818CF8] dark:hover:border-[#818CF8] hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 transition-all duration-300">
                <svg class="w-10 h-10 mb-2 group-hover:scale-110 group-hover:text-[#818CF8] transition-all duration-300 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6m4 6V9m4 10V5M3 21h18"/></svg>
                <p class="text-sm font-medium group-hover:text-gray-700 dark:group-hover:text-neutral-300 transition-colors">Gráfico de barras</p>
                <p class="text-xs">Esperando registros</p>
            </div>
        </div>
    </div>

    {{-- Productos más vendidos --}}
    <div class="bg-white dark:bg-[#151515] rounded-2xl border border-gray-200 dark:border-neutral-800 overflow-hidden shadow-sm hover:shadow-lg transition-all duration-500 delay-1000 transform"
         :class="cargado ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'">
        <div class="px-6 py-5 border-b border-gray-100 dark:border-neutral-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-200">Productos más Vendidos</h3>
                <p class="text-xs text-gray-400 dark:text-neutral-500 mt-0.5">Artículos con mayor rotación en inventario</p>
            </div>
            <a href="{{ route('inventario.index') }}" class="group text-xs font-semibold text-[#818CF8] hover:text-[#6366F1] dark:hover:text-[#a5b4fc] transition flex items-center gap-1">
                Ver catálogo completo 
                <span class="transform group-hover:translate-x-1 transition-transform">→</span>
            </a>
        </div>
        <div class="p-6">
            <div class="text-center py-12 group cursor-default">
                <div class="w-14 h-14 rounded-2xl bg-gray-100 dark:bg-[#0A0A0A] text-gray-300 dark:text-neutral-700 flex items-center justify-center mx-auto mb-3 group-hover:-translate-y-2 transition-transform duration-300 shadow-inner">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <p class="text-sm text-gray-400 dark:text-neutral-600 transition-colors">Aún no hay productos vendidos para mostrar</p>
            </div>
        </div>
    </div>

</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection