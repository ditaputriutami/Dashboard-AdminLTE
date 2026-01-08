@extends('adminlte::page')

@section('title', 'Detail Pembelian')

@section('content')
<div class="container">
    <h4>Detail Pembelian</h4>

    <div class="card">
        <div class="card-header">
            <h5>Informasi Pembelian</h5>
        </div>
        <div class="card-body">
            <table class="table table-borderless">
                <tr>
                    <th width="200">No Faktur</th>
                    <td>{{ $pembelian->no_faktur }}</td>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <td>{{ $pembelian->tanggal->format('d-m-Y') }}</td>
                </tr>
                <tr>
                    <th>Pemasok</th>
                    <td>{{ $pembelian->pemasok->nama_pemasok }}</td>
                </tr>
                <tr>
                    <th>Alamat Pemasok</th>
                    <td>{{ $pembelian->pemasok->alamat }}</td>
                </tr>
                <tr>
                    <th>Telepon</th>
                    <td>{{ $pembelian->pemasok->tlp }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header">
            <h5>Detail Barang</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="15%">Kode Barang</th>
                        <th width="30%">Nama Barang</th>
                        <th width="10%">Qty</th>
                        <th width="10%">Satuan</th>
                        <th width="15%">Harga</th>
                        <th width="15%">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pembelian->detailPembelian as $detail)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $detail->barang->id }}</td>
                        <td>{{ $detail->barang->nama_barang }}</td>
                        <td class="text-center">{{ $detail->quantity }}</td>
                        <td>{{ $detail->barang->satuan }}</td>
                        <td class="text-right">Rp {{ number_format($detail->harga, 0, ',', '.') }}</td>
                        <td class="text-right">Rp {{ number_format($detail->harga * $detail->quantity, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-right">Total</th>
                        <th class="text-right">Rp {{ number_format($pembelian->jumlah_pembelian, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">Kembali</a>
        <a href="{{ url('/pembelian/cetak/' . $pembelian->id) }}" class="btn btn-primary" target="_blank">
            <i class="fas fa-print"></i> Cetak
        </a>
    </div>
</div>
@endsection