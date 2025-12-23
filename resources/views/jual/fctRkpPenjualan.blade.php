@extends('adminlte::page')

@section('title', 'Formulir Cetak Laporan')

@section('content_header')
<h1>Memasukkan Periode Pertanggal</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('laporan.cetak') }}" method="POST" target="_blank">
            @csrf

            <div class="form-group row">
                <label for="tgl1" class="col-sm-2 col-form-label">Dari Tanggal</label>
                <div class="col-sm-4">
                    <input type="date" class="form-control @error('tgl1') is-invalid @enderror"
                        id="tgl1" name="tgl1" value="{{ old('tgl1', date('Y-m-d', strtotime('-30 days'))) }}" required>
                    @error('tgl1')
                    <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <label for="tgl2" class="col-sm-2 col-form-label">Sampai Tanggal</label>
                <div class="col-sm-4">
                    <input type="date" class="form-control @error('tgl2') is-invalid @enderror"
                        id="tgl2" name="tgl2" value="{{ old('tgl2', date('Y-m-d')) }}" required>
                    @error('tgl2')
                    <span class="invalid-feedback">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group row">
                <div class="col-sm-6 offset-sm-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-print"></i> Cetak
                    </button>
                    <a href="{{ url('/') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('css')
<style>
    .form-group {
        margin-bottom: 1.5rem;
    }

    .card {
        margin-top: 20px;
    }
</style>
@endsection

@section('js')
<script>
    // Validasi: Sampai Tanggal tidak boleh lebih kecil dari Dari Tanggal
    document.getElementById('tgl1').addEventListener('change', function() {
        document.getElementById('tgl2').setAttribute('min', this.value);
    });

    // Set min value saat halaman load
    document.addEventListener('DOMContentLoaded', function() {
        var tgl1 = document.getElementById('tgl1').value;
        if (tgl1) {
            document.getElementById('tgl2').setAttribute('min', tgl1);
        }
    });
</script>
@endsection