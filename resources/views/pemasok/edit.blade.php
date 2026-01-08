@extends('adminlte::page')
@section('content')
<div class="container">
    <h4>Edit Pemasok</h4>
    <form action="{{ route('pemasok.update', $pemasok->id) }}"
        method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="nama_pemasok" class="form-label">Nama Pemasok</label>
            <input type="text" name="nama_pemasok" class="form-control"
                value="{{ old('nama_pemasok', $pemasok->nama_pemasok) }}" required>
            @error('nama_pemasok')
            <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <input type="text" name="alamat" class="form-control"
                value="{{ old('alamat', $pemasok->alamat) }}">
            @error('alamat')
            <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="tlp" class="form-label">Telepon</label>
            <input type="text" name="tlp" class="form-control"
                value="{{ old('tlp', $pemasok->tlp) }}">
            @error('tlp')
            <div class="text-danger small">{{ $message }}</div>
            @enderror
        </div>
        <button class="btn btn-success">Update</button>
        <a href="{{ route('pemasok.index') }}"
            class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection