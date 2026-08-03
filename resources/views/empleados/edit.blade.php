@extends('layouts.app')

@section('content')

{{--
  Usa el mismo sistema de diseño que empleados/index.blade.php y
  empleados/create.blade.php (fuentes Space Grotesk / Inter / JetBrains Mono
  — ver nota en el index). Si vas a usar esto en varias vistas, considera
  mover el bloque <style> a un partial (@include('partials.tv-styles'))
  para no repetirlo.
--}}

<div class="tv-scope">
    <div class="container mx-auto px-4 py-6 max-w-5xl tv-fade-in">

        <div class="mb-6 tv-slide-down">
            <a href="{{ route('empleados.index') }}" class="tv-back-link">
                <svg class="tv-back-arrow w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Volver a Empleados
            </a>
            <h1 class="tv-title mt-2">Editar Empleado: {{ $empleado->name }}</h1>
        </div>

        <form action="{{ route('empleados.update', $empleado->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Datos Personales y Acceso --}}
            <div class="tv-form-card tv-fade-in-up" style="animation-delay:.05s">
                <h2 class="tv-form-card-title">Información Básica</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="tv-field">
                        <label class="tv-label">Nombre Completo</label>
                        <input type="text" name="name" value="{{ old('name', $empleado->name) }}" required class="tv-input">
                        @error('name') <span class="tv-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="tv-field">
                        <label class="tv-label">Correo Electrónico</label>
                        <input type="email" name="email" value="{{ old('email', $empleado->email) }}" required class="tv-input">
                        @error('email') <span class="tv-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="tv-field">
                        <label class="tv-label">Nueva Contraseña <span class="tv-hint">(dejar en blanco para mantener la actual)</span></label>
                        <input type="password" name="password" class="tv-input">
                        @error('password') <span class="tv-error">{{ $message }}</span> @enderror
                    </div>

                    <div class="tv-field">
                        <label class="tv-label">Sucursal Asignada</label>
                        <div class="tv-select-wrap">
                            <select name="sucursal_id" required class="tv-input tv-select">
                                @foreach($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}" {{ old('sucursal_id', $empleado->sucursal_id) == $sucursal->id ? 'selected' : '' }}>
                                        {{ $sucursal->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <svg class="tv-select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                        </div>
                    </div>

                    <div class="tv-field">
                        <label class="tv-label">Rol</label>
                        <div class="tv-select-wrap">
                            <select name="rol_id" required class="tv-input tv-select">
                                @foreach($roles as $rol)
                                    <option value="{{ $rol->id }}" {{ old('rol_id', $empleado->rol_id) == $rol->id ? 'selected' : '' }}>
                                        {{ $rol->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <svg class="tv-select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6"/></svg>
                        </div>
                    </div>

                    <div class="flex items-center pt-5">
                        <label class="tv-switch-wrap">
                            <input type="checkbox" name="activo" value="1" {{ $empleado->activo ? 'checked' : '' }} class="tv-switch-input">
                            <span class="tv-switch-track"><span class="tv-switch-thumb"></span></span>
                            <span class="tv-switch-label">Usuario Activo</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Matriz de Permisos --}}
            <div class="tv-form-card tv-fade-in-up" style="animation-delay:.12s">
                <h2 class="tv-form-card-title">Permisos Específicos por Módulo</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($permisosGrouped as $modulo => $permisos)
                        <div class="tv-module-card" style="animation-delay: {{ $loop->index * 60 }}ms">
                            <h3 class="tv-module-title">{{ $modulo }}</h3>
                            <div class="space-y-1">
                                @foreach($permisos as $permiso)
                                    <label class="tv-perm-item">
                                        <input type="checkbox" name="permisos[]" value="{{ $permiso->id }}"
                                               {{ in_array($permiso->id, $userPermisos) ? 'checked' : '' }}
                                               class="tv-check">
                                        {{ $permiso->nombre }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="tv-actions">
                <a href="{{ route('empleados.index') }}" class="tv-btn-secondary">Cancelar</a>
                <button type="submit" class="tv-btn-primary">
                    <span class="tv-btn-sweep"></span>
                    <span>Actualizar Empleado</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* ══════════════════════════════════════════════════════════════
   TOKENS — mismo sistema que empleados/index.blade.php
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
    --tv-green-bg:  #E7F6EC;
    --tv-green-fg:  #1D8A4C;
    --tv-red-bg:    #FCE9E9;
    --tv-red-fg:    #C21E2E;
    --tv-blue-bg:   #EAF1FE;
    --tv-blue-fg:   #3159C4;
    --tv-shadow:    0 1px 2px rgba(20,20,20,.04), 0 8px 24px -8px rgba(20,20,20,.10);
    color-scheme: light;
    font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
    background: var(--tv-bg);
    color: var(--tv-text);
    min-height: 100%;
    transition: background-color .35s ease, color .35s ease;
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
    --tv-green-bg:  rgba(52,211,153,.12);
    --tv-green-fg:  #45D68A;
    --tv-red-bg:    rgba(234,46,60,.12);
    --tv-red-fg:    #F0616C;
    --tv-blue-bg:   rgba(99,140,255,.12);
    --tv-blue-fg:   #7C9CFF;
    --tv-shadow:    0 1px 2px rgba(0,0,0,.3), 0 12px 32px -12px rgba(0,0,0,.6);
    color-scheme: dark;
}

.tv-title { font-family: 'Space Grotesk', ui-sans-serif, sans-serif; font-size: 1.6rem; font-weight: 700; letter-spacing: -0.01em; }

/* ── Volver ── */
.tv-back-link { display:inline-flex; align-items:center; gap:.35rem; font-size:.8rem; font-weight:500; color:var(--tv-muted); transition: color .2s ease, gap .2s ease; }
.tv-back-link:hover { color: var(--tv-red-fg); gap:.55rem; }
.tv-back-arrow { transition: transform .2s ease; }
.tv-back-link:hover .tv-back-arrow { transform: translateX(-3px); }

/* ── Tarjetas de formulario ── */
.tv-form-card {
    background: var(--tv-surface);
    border: 1px solid var(--tv-border);
    border-radius: 1.1rem;
    padding: 1.5rem;
    box-shadow: var(--tv-shadow);
    transition: background-color .35s ease, border-color .35s ease;
}
.tv-form-card-title {
    font-family: 'Space Grotesk', sans-serif;
    font-weight: 700;
    font-size: 1.05rem;
    border-bottom: 1px solid var(--tv-border);
    padding-bottom: .65rem;
    margin-bottom: 1.2rem;
}

/* ── Campos ── */
.tv-label {
    display: block;
    font-size: .78rem;
    font-weight: 600;
    color: var(--tv-muted);
    margin-bottom: .4rem;
    transition: color .2s ease;
}
.tv-field:focus-within .tv-label { color: var(--tv-red-fg); }
.tv-input {
    width: 100%;
    background: var(--tv-surface-2);
    border: 1px solid var(--tv-border);
    border-radius: .65rem;
    padding: .65rem .85rem;
    font-size: .875rem;
    color: var(--tv-text);
    transition: border-color .2s ease, box-shadow .2s ease, background-color .3s ease;
}
.tv-input:focus {
    outline: none;
    border-color: var(--tv-red);
    box-shadow: 0 0 0 3px rgba(220,31,46,.15);
    background: var(--tv-surface);
}
.tv-error { display:block; font-size: .72rem; color: var(--tv-red-fg); margin-top: .3rem; }
.tv-hint { font-weight: 400; color: var(--tv-muted); text-transform: none; letter-spacing: normal; }

.tv-select-wrap { position: relative; }
.tv-select { appearance: none; -webkit-appearance: none; padding-right: 2.4rem; cursor: pointer; }
.tv-select-arrow {
    position: absolute; right: .85rem; top: 50%; transform: translateY(-50%);
    width: 1rem; height: 1rem; color: var(--tv-muted); pointer-events: none;
    transition: transform .2s ease;
}
.tv-select-wrap:focus-within .tv-select-arrow { color: var(--tv-red-fg); transform: translateY(-50%) rotate(180deg); }

/* ── Switch "Usuario Activo" ── */
.tv-switch-wrap { display: inline-flex; align-items: center; gap: .65rem; cursor: pointer; }
.tv-switch-input { display: none; }
.tv-switch-track {
    width: 2.5rem; height: 1.4rem; border-radius: 999px;
    background: var(--tv-border); position: relative; flex-shrink: 0;
    transition: background .25s ease;
}
.tv-switch-thumb {
    position: absolute; top: 2px; left: 2px;
    width: 1.1rem; height: 1.1rem; border-radius: 50%;
    background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.35);
    transition: transform .25s cubic-bezier(.4,0,.2,1);
}
.tv-switch-input:checked + .tv-switch-track { background: linear-gradient(135deg, var(--tv-red), var(--tv-red-dark)); }
.tv-switch-input:checked + .tv-switch-track .tv-switch-thumb { transform: translateX(1.1rem); }
.tv-switch-label { font-size: .85rem; font-weight: 500; color: var(--tv-text); }

/* ── Checkbox de permisos con check animado ── */
.tv-check {
    appearance: none; -webkit-appearance: none;
    width: 1.05rem; height: 1.05rem; flex-shrink: 0;
    border: 1.5px solid var(--tv-border); border-radius: .35rem;
    background: var(--tv-surface); display: inline-grid; place-content: center;
    cursor: pointer; transition: background-color .2s ease, border-color .2s ease;
}
.tv-check::before {
    content: ''; width: .62rem; height: .62rem; transform: scale(0);
    transition: transform .18s cubic-bezier(.4,0,.2,1);
    box-shadow: inset 1em 1em white;
    clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%);
}
.tv-check:checked { background: linear-gradient(135deg, var(--tv-red), var(--tv-red-dark)); border-color: var(--tv-red); }
.tv-check:checked::before { transform: scale(1); }
.tv-check:hover { border-color: var(--tv-red); }
.tv-check:focus-visible { outline: 2px solid var(--tv-red); outline-offset: 2px; }

/* ── Módulos de permisos ── */
.tv-module-card {
    border: 1px solid var(--tv-border); border-radius: .9rem; padding: 1rem;
    background: var(--tv-surface-2);
    animation: tvFadeInUp .4s ease both;
    transition: transform .25s ease, border-color .25s ease, box-shadow .25s ease;
}
.tv-module-card:hover { transform: translateY(-3px); border-color: var(--tv-red); box-shadow: 0 12px 26px -14px rgba(220,31,46,.35); }
.tv-module-title {
    font-family: 'JetBrains Mono', ui-monospace, monospace;
    font-size: .7rem; text-transform: uppercase; letter-spacing: .07em; font-weight: 700;
    color: var(--tv-muted); border-bottom: 1px solid var(--tv-border);
    padding-bottom: .55rem; margin-bottom: .75rem;
}
.tv-perm-item {
    display: flex; align-items: center; gap: .55rem;
    font-size: .78rem; color: var(--tv-text);
    padding: .35rem .4rem; border-radius: .45rem; cursor: pointer;
    transition: background-color .2s ease;
}
.tv-perm-item:hover { background: var(--tv-border); }

/* ── Botones ── */
.tv-actions { display: flex; justify-content: flex-end; gap: .75rem; }
.tv-btn-primary {
    position: relative; overflow: hidden;
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .7rem 1.3rem; border-radius: .7rem;
    font-weight: 600; font-size: .875rem; color: #fff;
    background: linear-gradient(135deg, var(--tv-red), var(--tv-red-dark));
    box-shadow: 0 8px 20px -6px rgba(220,31,46,.45);
    transition: transform .25s ease, box-shadow .25s ease;
}
.tv-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 12px 26px -6px rgba(220,31,46,.6); }
.tv-btn-primary:active { transform: translateY(0) scale(.97); }
.tv-btn-sweep {
    position: absolute; top: 0; left: -60%; width: 40%; height: 100%;
    background: linear-gradient(120deg, transparent, rgba(255,255,255,.35), transparent);
    transform: skewX(-20deg); transition: left .6s ease;
}
.tv-btn-primary:hover .tv-btn-sweep { left: 130%; }
.tv-btn-secondary {
    display: inline-flex; align-items: center;
    padding: .7rem 1.3rem; border-radius: .7rem;
    background: var(--tv-surface-2); color: var(--tv-text);
    border: 1px solid var(--tv-border); font-weight: 600; font-size: .875rem;
    transition: background-color .2s ease, transform .2s ease;
}
.tv-btn-secondary:hover { background: var(--tv-border); transform: translateY(-1px); }

/* ══════════════════════════════════════════════════════════════
   ANIMACIONES
   ══════════════════════════════════════════════════════════════ */
@keyframes tvFadeIn    { from { opacity: 0; } to { opacity: 1; } }
@keyframes tvFadeInUp  { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }
@keyframes tvSlideDown { from { opacity: 0; transform: translateY(-14px); } to { opacity: 1; transform: translateY(0); } }
.tv-fade-in    { animation: tvFadeIn .5s ease both; }
.tv-fade-in-up { animation: tvFadeInUp .55s ease both; }
.tv-slide-down { animation: tvSlideDown .45s ease both; }

@media (prefers-reduced-motion: reduce) {
    .tv-scope *, .tv-scope *::before, .tv-scope *::after {
        animation-duration: .001ms !important;
        transition-duration: .001ms !important;
    }
}
</style>
@endsection