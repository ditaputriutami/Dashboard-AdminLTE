@extends('adminlte::page')
@section('content')
<div class="container">
    <h4>Detail Pemasok</h4>
    <div class="card">
        <div class="card-body">
            <table class="table table-borderless">
                <tr>
                    <th width="200">Nama Pemasok</th>
                    <td>{{ $pemasok->nama_pemasok }}</td>
                </tr>
                <tr>
                    <th>Alamat</th>
                    <td>{{ $pemasok->alamat }}</td>
                </tr>
                <tr>
                    <th>Telepon</th>
                    <td>{{ $pemasok->tlp }}</td>
                </tr>
                <tr>
                    <th>Dibuat</th>
                    <td>{{ $pemasok->created_at }}</td>
                </tr>
                <tr>
                    <th>Diupdate</th>
                    <td>{{ $pemasok->updated_at }}</td>
                </tr>
            </table>
        </div>
    </div>
    <a href="{{ route('pemasok.index') }}" class="btn btn-secondary mt-3">Kembali</a>
    <a href="{{ route('pemasok.edit', $pemasok->id) }}" class="btn btn-warning mt-3">Edit</a>
</div>
@endsection