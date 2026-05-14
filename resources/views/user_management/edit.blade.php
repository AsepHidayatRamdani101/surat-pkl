@extends('adminlte::page')

@section('title', 'Edit User')

@section('content_header')
    <h1>Edit User</h1>
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
                <h3 class="card-title">Form Edit User</h3>
            </div>
            <form action="{{ route('user-management.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body row">
                    <div class="form-group col-md-6">
                        <label for="name">Nama</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="password">Password baru (opsional)</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                    </div>
                    <div class="form-group col-md-3">
                        <label for="role">Role</label>
                        <select id="role" name="role" class="form-control" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}" {{ old('role', $user->role) === $role ? 'selected' : '' }}>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-3">
                        <label for="jurusan_id">Jurusan ID</label>
                        <input type="text" id="jurusan_id" name="jurusan_id" class="form-control" value="{{ old('jurusan_id', $user->jurusan_id) }}">
                    </div>
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('user-management.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
@stop
