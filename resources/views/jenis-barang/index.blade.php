@extends('adminlte::page')
@section('content')
<div class="container">
    <h4>Data Jenis Barang</h4>
    <a href="{{ route('jenis-barang.create') }}"
        class="btn btn-primary mb-3">Tambah Jenis</a>
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-bordered">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="20%" class="text-center">Nama Jenis</th>
                <th width="20%" class="text-center">Dibuat</th>
                <th width="20%" class="text-center">Diupdate</th>
                <th width="13%" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($jenisBarang as $jb)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $jb->nama_jenis }}</td>
                <td>{{ $jb->created_at }}</td>
                <td>{{ $jb->updated_at }}</td>
                <td class="text-center">
                    <a href="{{ route('jenis-barang.edit', $jb->id) }}"
                        class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('jenis-barang.destroy', $jb->id) }}"
                        method="POST" class="d-inline"
                        onsubmit="return confirm('Hapus data ini?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>
@endsection