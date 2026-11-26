@if ($paginator->hasPages())
    @php
        $botonBase = 'inline-flex items-center justify-center h-9 min-w-[2.25rem] px-3 rounded-lg text-sm font-semibold border transition-colors';
        $botonNormal = $botonBase . ' bg-white dark:bg-transparent border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/5';
        $botonApagado = $botonBase . ' bg-gray-50 dark:bg-white/[0.03] border-gray-200 dark:border-white/10 text-gray-400 dark:text-gray-500 cursor-not-allowed';
        $botonActivo = $botonBase . ' bg-indigo-600 border-indigo-600 text-white';
    @endphp

    <nav role="navigation" aria-label="Paginación" class="flex flex-col sm:flex-row items-center justify-between gap-3">

        {{-- Resumen --}}
        <p class="text-sm text-gray-500 dark:text-gray-400 tabular-nums">
            Mostrando
            <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $paginator->firstItem() }}</span>
            a
            <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $paginator->lastItem() }}</span>
            de
            <span class="font-semibold text-gray-700 dark:text-gray-200">{{ $paginator->total() }}</span>
            resultados
        </p>

        <div class="flex items-center gap-1">

            {{-- Anterior --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" class="{{ $botonApagado }}">Anterior</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $botonNormal }}">Anterior</a>
            @endif

            {{-- En pantallas chicas solo se dice en qué página va --}}
            <span class="sm:hidden px-2 text-sm text-gray-500 dark:text-gray-400 tabular-nums">
                Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}
            </span>

            {{-- Números de página (solo en pantallas medianas y grandes) --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="hidden sm:inline-flex items-center justify-center h-9 min-w-[2.25rem] px-1 text-sm text-gray-400 dark:text-gray-500">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="hidden sm:inline-flex tabular-nums {{ $botonActivo }}">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" aria-label="Ir a la página {{ $page }}" class="hidden sm:inline-flex tabular-nums {{ $botonNormal }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Siguiente --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $botonNormal }}">Siguiente</a>
            @else
                <span aria-disabled="true" class="{{ $botonApagado }}">Siguiente</span>
            @endif
        </div>
    </nav>
@endif
