@extends('adminlte::page')

@section('title', 'Edit Role')

@section('content_header')
    <h1>Edit Role</h1>
@stop

@section('content')
    <div class="container-fluid">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">Form Edit Role</h3>
            </div>
            <form action="{{ route('role-management.update', $role) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body row">
                    <div class="form-group col-md-6">
                        <label for="name">Nama Role</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('role-management.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
@stop
