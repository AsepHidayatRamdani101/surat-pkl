{{-- Gunakan layout bawaan AdminLTE --}}
@extends('adminlte::page')

@push('css')
    @yield('css')
@endpush

@section('title', 'SIPKL')

@section('content_header')
    <h1>@yield('header', 'Dashboard')</h1>
@stop

@section('content')
    @yield('content-body')
@stop

@section('footer')
    <strong>Copyright &copy; 2014-2021 <a href="https://adminlte.io">AdminLTE.io</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
        <b>Version</b> 3.2.0
    </div>
@stop

@push('js')
    @yield('js')
@endpush
