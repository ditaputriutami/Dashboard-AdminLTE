@extends('adminlte::page')

@section('title', 'Data Pemasok')

@section('content_header')
<h1>Data Pemasok</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Daftar Pemasok</h3>
        <div class="card-tools">
            <a href="{{ route('pemasok.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Tambah Pemasok
            </a>
        </div>
    </div>
    <div class="card-body">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="bg-light">
                    <tr>
                        <th width="5%" class="text-center">No</th>
                        <th width="20%">Nama Pemasok</th>
                        <th width="25%">Alamat</th>
                        <th width="15%" class="text-center">Telepon</th>
                        <th width="15%" class="text-center">Dibuat</th>
                        <th width="20%" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pemasok as $p)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $p->nama_pemasok }}</td>
                        <td>{{ $p->alamat }}</td>
                        <td class="text-center">{{ $p->tlp }}</td>
                        <td class="text-center">{{ date('d-m-Y H:i', strtotime($p->created_at)) }}</td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <a href="{{ route('pemasok.show', $p->id) }}"
                                    class="btn btn-sm btn-info" title="Detail">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <a href="{{ route('pemasok.edit', $p->id) }}"
                                    class="btn btn-sm btn-warning" title="Edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <form action="{{ route('pemasok.destroy', $p->id) }}"
                                    method="POST" class="d-inline"
                                    onsubmit="return confirm('Yakin ingin menghapus data pemasok {{ $p->nama_pemasok }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" title="Hapus">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data pemasok</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection