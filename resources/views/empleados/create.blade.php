@extends('layouts.app')

@section('header_title', 'Nuevo empleado')

@section('content')

<style>[x-cloak] { display: none !important; }</style>

@include('empleados.partials.form', ['empleado' => null])

@endsection
