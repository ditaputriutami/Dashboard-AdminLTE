@extends('adminlte::page')

@section('title', 'Daftar Pembelian')

@section('content')
<div class="container">
    <h4>Daftar Pembelian</h4>
    <a href="{{ route('pembelian.create') }}" class="btn btn-primary mb-3">
        <i class="fas fa-plus"></i> Tambah Pembelian
    </a>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="15%" class="text-center">No Faktur</th>
                <th width="15%" class="text-center">Tanggal</th>
                <th width="25%" class="text-center">Pemasok</th>
                <th width="20%" class="text-center">Total Pembelian</th>
                <th width="20%" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pembelian as $p)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $p->no_faktur }}</td>
                <td>{{ $p->tanggal }}</td>
                <td>{{ $p->pemasok->nama_pemasok ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($p->jumlah_pembelian, 0, ',', '.') }}</td>
                <td class="text-center">
                    <a href="{{ route('pembelian.show', $p->id) }}" class="btn btn-sm btn-info">
                        <i class="fas fa-eye"></i> Detail
                    </a>
                    <a href="{{ url('/pembelian/cetak/' . $p->id) }}" class="btn btn-sm btn-secondary" target="_blank">
                        <i class="fas fa-print"></i> Cetak
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Belum ada data pembelian</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection