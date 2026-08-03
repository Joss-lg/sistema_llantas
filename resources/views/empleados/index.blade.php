@extends('layouts.app') {{-- O tu layout principal --}}

@section('content')

{{--
  ─────────────────────────────────────────────────────────────
  NOTA DE INSTALACIÓN
  ─────────────────────────────────────────────────────────────
  1. Fuentes (agrégalas una vez en el <head> de tu layout principal):

     <link rel="preconnect" href="https://fonts.googleapis.com">
     <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
     <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

  2. El modo oscuro se activa solo si tu layout agrega la clase `dark`
     al elemento <html> (el estándar de Tailwind). Por defecto se ve en
     modo claro, igual que el resto de tu panel. No hay botón ni JS.
  ─────────────────────────────────────────────────────────────
--}}

<div class="tv-scope">

    <div class="container mx-auto px-4 py-6 tv-fade-in">

        {{-- Header --}}
        <div class="tv-header flex justify-between items-center mb-8 tv-slide-down">
            <div>
                <h1 class="tv-title">Gestión de Empleados</h1>
                <p class="tv-subtitle">Administra el personal, sus sucursales y permisos de acceso.</p>
            </div>
            <a href="{{ route('empleados.create') }}" class="tv-btn-primary">
                <span class="tv-btn-sweep"></span>
                <svg class="w-5 h-5 tv-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Nuevo Empleado</span>
            </a>
        </div>

        {{-- Alertas --}}
        @if(session('success'))
            <div class="tv-alert tv-alert-success tv-slide-down">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="tv-alert tv-alert-error tv-slide-down">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Tabla --}}
        <div class="tv-card tv-fade-in-up">
            <div class="tv-tread-corner" aria-hidden="true"></div>

            <table class="w-full text-left border-collapse relative">
                <thead>
                    <tr class="tv-thead-row">
                        <th class="tv-th">Empleado</th>
                        <th class="tv-th">Sucursal</th>
                        <th class="tv-th">Rol</th>
                        <th class="tv-th">Estado</th>
                        <th class="tv-th text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="tv-tbody">
                    @forelse($empleados as $empleado)
                        <tr class="tv-row" style="animation-delay: {{ $loop->index * 70 }}ms">
                            <td class="tv-td">
                                <div class="flex items-center gap-3">
                                    <div class="tv-avatar">
                                        <span>{{ substr($empleado->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <div class="tv-name">{{ $empleado->name }}</div>
                                        <div class="tv-email">{{ $empleado->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="tv-td">
                                <span class="tv-badge tv-badge-neutral">
                                    {{ $empleado->sucursal ? $empleado->sucursal->nombre : 'Sin asignación' }}
                                </span>
                            </td>
                            <td class="tv-td">
                                <span class="tv-badge tv-badge-blue">
                                    {{ $empleado->rol ? $empleado->rol->nombre : 'Sin rol' }}
                                </span>
                            </td>
                            <td class="tv-td">
                                @if($empleado->activo)
                                    <span class="tv-badge tv-badge-green">
                                        <span class="tv-dot tv-dot-green"></span> Activo
                                    </span>
                                @else
                                    <span class="tv-badge tv-badge-red">
                                        <span class="tv-dot tv-dot-red"></span> Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="tv-td text-right">
                                <a href="{{ route('empleados.edit', $empleado->id) }}" class="tv-link tv-link-indigo">Editar</a>
                                @if($empleado->email !== 'admin@llantas.com')
                                    <form action="{{ route('empleados.destroy', $empleado->id) }}" method="POST" class="inline-block ml-3" onsubmit="return confirm('¿Seguro que deseas eliminar este empleado?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="tv-link tv-link-red">Eliminar</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="tv-empty">
                                <div class="tv-empty-inner">
                                    <svg class="tv-empty-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <circle cx="12" cy="12" r="8"/>
                                        <circle cx="12" cy="12" r="2.5"/>
                                        <path d="M12 4v2.5M12 17.5V20M20 12h-2.5M6.5 12H4M17 7l-1.8 1.8M8.8 15.2 7 17M17 17l-1.8-1.8M8.8 8.8 7 7"/>
                                    </svg>
                                    No hay empleados registrados.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* ══════════════════════════════════════════════════════════════
   TOKENS — paleta "taller / asfalto" para el panel de llantas
   ══════════════════════════════════════════════════════════════ */
.tv-scope {
    --tv-bg:        #F6F5F3;
    --tv-surface:   #FFFFFF;
    --tv-surface-2: #FBFAF9;
    --tv-border:    #E6E4E0;
    --tv-text:      #17181A;
    --tv-muted:     #71757C;
    --tv-red:       #DC1F2E;
    --tv-red-dark:  #A9101C;
    --tv-amber:     #C97A17;
    --tv-green-bg:  #E7F6EC;
    --tv-green-fg:  #1D8A4C;
    --tv-red-bg:    #FCE9E9;
    --tv-red-fg:    #C21E2E;
    --tv-blue-bg:   #EAF1FE;
    --tv-blue-fg:   #3159C4;
    --tv-shadow:    0 1px 2px rgba(20,20,20,.04), 0 8px 24px -8px rgba(20,20,20,.10);
    color-scheme: light;
}
html.dark .tv-scope,
.dark .tv-scope {
    --tv-bg:        #0A0B0D;
    --tv-surface:   #131519;
    --tv-surface-2: #17191E;
    --tv-border:    rgba(255,255,255,.08);
    --tv-text:      #F3F3F2;
    --tv-muted:     #8A8E96;
    --tv-red:       #EA2E3C;
    --tv-red-dark:  #B0121C;
    --tv-amber:     #E8A23A;
    --tv-green-bg:  rgba(52,211,153,.12);
    --tv-green-fg:  #45D68A;
    --tv-red-bg:    rgba(234,46,60,.12);
    --tv-red-fg:    #F0616C;
    --tv-blue-bg:   rgba(99,140,255,.12);
    --tv-blue-fg:   #7C9CFF;
    --tv-shadow:    0 1px 2px rgba(0,0,0,.3), 0 12px 32px -12px rgba(0,0,0,.6);
    color-scheme: dark;
}

.tv-scope {
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
    background: var(--tv-bg);
    color: var(--tv-text);
    min-height: 100%;
    position: relative;
    transition: background-color .35s ease, color .35s ease;
}

/* ── Tipografía ── */
.tv-title {
    font-family: 'Space Grotesk', ui-sans-serif, sans-serif;
    font-size: 1.6rem;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: var(--tv-text);
}
.tv-subtitle { color: var(--tv-muted); font-size: .875rem; margin-top: .2rem; }
.tv-name { font-weight: 700; color: var(--tv-text); }
.tv-email { font-family: 'JetBrains Mono', ui-monospace, monospace; font-size: .7rem; color: var(--tv-muted); }

/* ── Botón primario con "barrido" tipo reflejo de llanta ── */
.tv-btn-primary {
    position: relative;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    padding: .65rem 1.1rem;
    border-radius: .7rem;
    font-weight: 600;
    font-size: .875rem;
    color: #fff;
    background: linear-gradient(135deg, var(--tv-red), var(--tv-red-dark));
    box-shadow: 0 8px 20px -6px rgba(220,31,46,.45);
    transition: transform .25s ease, box-shadow .25s ease;
}
.tv-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 26px -6px rgba(220,31,46,.6); }
.tv-btn-primary:active { transform: translateY(0) scale(.97); }
.tv-btn-icon { transition: transform .35s ease; }
.tv-btn-primary:hover .tv-btn-icon { transform: rotate(90deg); }
.tv-btn-sweep {
    position: absolute;
    top: 0; left: -60%;
    width: 40%; height: 100%;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,.35), transparent);
    transform: skewX(-20deg);
    transition: left .6s ease;
}
.tv-btn-primary:hover .tv-btn-sweep { left: 130%; }

/* ── Alertas ── */
.tv-alert {
    display: flex; align-items: center; gap: .75rem;
    padding: 1rem; border-radius: .75rem;
    font-size: .875rem; margin-bottom: 1.5rem;
    border-left: 3px solid transparent;
    box-shadow: var(--tv-shadow);
}
.tv-alert-success { background: var(--tv-green-bg); color: var(--tv-green-fg); border-color: var(--tv-green-fg); }
.tv-alert-error   { background: var(--tv-red-bg); color: var(--tv-red-fg); border-color: var(--tv-red-fg); }

/* ── Card + textura de "banda de rodadura" ── */
.tv-card {
    position: relative;
    background: var(--tv-surface);
    border: 1px solid var(--tv-border);
    border-radius: 1.1rem;
    box-shadow: var(--tv-shadow);
    overflow: hidden;
    transition: background-color .35s ease, border-color .35s ease;
}
.tv-tread-corner {
    position: absolute;
    top: 0; right: 0;
    width: 130px; height: 130px;
    pointer-events: none;
    opacity: .5;
    background-image: repeating-linear-gradient(
        115deg,
        var(--tv-border) 0px, var(--tv-border) 2px,
        transparent 2px, transparent 12px
    );
    -webkit-mask-image: radial-gradient(circle at top right, black, transparent 70%);
    mask-image: radial-gradient(circle at top right, black, transparent 70%);
}

.tv-thead-row { background: var(--tv-surface-2); border-bottom: 1px solid var(--tv-border); }
.tv-th {
    padding: 1rem 1.5rem;
    font-size: .7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .08em; color: var(--tv-muted);
    font-family: 'JetBrains Mono', ui-monospace, monospace;
}
.tv-tbody { position: relative; }
.tv-row { border-top: 1px solid var(--tv-border); animation: tvRowIn .4s ease both; transition: background-color .2s ease; }
.tv-row:first-child { border-top: none; }
.tv-row:hover { background: var(--tv-surface-2); }
.tv-td { padding: 1rem 1.5rem; font-size: .875rem; vertical-align: middle; }

/* ── Avatar tipo "rin" ── */
.tv-avatar {
    width: 2.4rem; height: 2.4rem; border-radius: 999px;
    display: grid; place-items: center;
    background: linear-gradient(140deg, var(--tv-red), var(--tv-red-dark));
    color: #fff; font-weight: 700; font-size: .8rem; text-transform: uppercase;
    box-shadow: 0 0 0 3px var(--tv-surface), 0 0 0 4px var(--tv-border);
    transition: transform .3s ease, box-shadow .3s ease;
}
.tv-row:hover .tv-avatar { transform: scale(1.08) rotate(6deg); box-shadow: 0 0 0 3px var(--tv-surface), 0 0 0 4px var(--tv-red); }

/* ── Badges ── */
.tv-badge {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .3rem .65rem; border-radius: .5rem;
    font-size: .72rem; font-weight: 600;
    border: 1px solid transparent;
}
.tv-badge-neutral { background: var(--tv-surface-2); color: var(--tv-text); border-color: var(--tv-border); }
.tv-badge-blue    { background: var(--tv-blue-bg); color: var(--tv-blue-fg); }
.tv-badge-green   { background: var(--tv-green-bg); color: var(--tv-green-fg); border-radius: 999px; }
.tv-badge-red     { background: var(--tv-red-bg); color: var(--tv-red-fg); border-radius: 999px; }
.tv-dot { width: .4rem; height: .4rem; border-radius: 999px; }
.tv-dot-green { background: var(--tv-green-fg); animation: tvPulseDot 1.8s ease-in-out infinite; }
.tv-dot-red   { background: var(--tv-red-fg); }

/* ── Links de acción ── */
.tv-link { font-size: .75rem; font-weight: 600; position: relative; transition: color .2s ease; }
.tv-link-indigo { color: #6270E0; }
.tv-link-red { color: var(--tv-red-fg); }
.tv-link::after {
    content: ''; position: absolute; left: 0; right: 0; bottom: -2px; height: 1px;
    background: currentColor; transform: scaleX(0); transform-origin: right;
    transition: transform .25s ease;
}
.tv-link:hover::after { transform: scaleX(1); transform-origin: left; }

/* ── Estado vacío ── */
.tv-empty { padding: 4rem 1.5rem; text-align: center; color: var(--tv-muted); }
.tv-empty-inner { display: flex; flex-direction: column; align-items: center; gap: .75rem; }
.tv-empty-icon { width: 3rem; height: 3rem; opacity: .5; animation: tvSpinSlow 12s linear infinite; }

/* ══════════════════════════════════════════════════════════════
   ANIMACIONES
   ══════════════════════════════════════════════════════════════ */
@keyframes tvFadeIn    { from { opacity: 0; } to { opacity: 1; } }
@keyframes tvFadeInUp  { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
@keyframes tvSlideDown { from { opacity: 0; transform: translateY(-14px); } to { opacity: 1; transform: translateY(0); } }
@keyframes tvRowIn     { from { opacity: 0; transform: translateX(-10px); } to { opacity: 1; transform: translateX(0); } }
@keyframes tvPulseDot  { 0%,100% { box-shadow: 0 0 0 0 rgba(52,211,153,.5); } 50% { box-shadow: 0 0 0 5px rgba(52,211,153,0); } }
@keyframes tvSpinSlow  { from { transform: rotate(0); } to { transform: rotate(360deg); } }

.tv-fade-in    { animation: tvFadeIn .5s ease both; }
.tv-fade-in-up { animation: tvFadeInUp .55s ease .08s both; }
.tv-slide-down { animation: tvSlideDown .45s ease both; }

@media (prefers-reduced-motion: reduce) {
    .tv-scope *, .tv-scope *::before, .tv-scope *::after {
        animation-duration: .001ms !important;
        transition-duration: .001ms !important;
    }
}
</style>
@endsection