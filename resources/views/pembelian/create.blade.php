@extends('adminlte::page')

@section('title', 'Tambah Transaksi Pembelian')

@section('content_header')
<h1>Tambah Transaksi Pembelian</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Form Pemasok</h3>
    </div>
    <div class="card-body">

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <div class="row">
            <div class="col-md-8">
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Nomor Transaksi</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="id_prediksi"
                            value="{{ $pembelian->id }}" disabled>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Nomor Faktur</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" name="no_faktur_prediksi"
                            value="{{ $pembelian->no_faktur }}" disabled>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Tanggal</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control tanggal" name="tanggal"
                            value="{{ $pembelian->tanggal }}" disabled>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">ID Pemasok <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control pemasok_id" name="pemasok_id"
                            placeholder="Ketik ID Pemasok lalu tekan Enter" autofocus>
                        <small class="form-text text-muted">Tekan Enter untuk validasi</small>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Nama Pemasok</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="nama_pemasok" name="nama_pemasok" readonly>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Alamat</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="alamat" name="alamat" readonly>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Telepon</label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control" id="tlp" name="tlp" readonly>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group mt-3">
            <button type="button" class="proses btn btn-primary btn-lg">
                <i class="fas fa-check"></i> Proses
            </button>
            <a href="{{ route('pembelian.index') }}" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
    .form-control:disabled {
        background-color: #e9ecef;
    }
</style>
@endsection

@section('js')
<script>
    document.addEventListener("DOMContentLoaded", function() {

        let CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

        // GET PEMASOK BY ID
        $(document).on('keypress', '.pemasok_id', function(e) {
            if (e.which === 13) {
                e.preventDefault();

                var pemasokId = $('.pemasok_id').val();

                if (!pemasokId) {
                    alert('Silakan masukkan ID Pemasok');
                    return;
                }

                $.ajax({
                    url: '/bacaPemasok',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        pemasok_id: pemasokId
                    },
                    success: function(data) {
                        if (data) {
                            $('#nama_pemasok').val(data.nama_pemasok);
                            $('#alamat').val(data.alamat);
                            $('#tlp').val(data.tlp);
                        } else {
                            alert('Pemasok dengan ID ' + pemasokId + ' tidak ditemukan');
                            $('.pemasok_id').val('').focus();
                        }
                    },
                    error: function(xhr) {
                        alert('Gagal membaca pemasok: ' + xhr.status + '\\nSilakan coba lagi');
                        $('.pemasok_id').val('').focus();
                    }
                });
            }
        });

        // SIMPAN TRANSAKSI
        $(document).on('click', '.proses', function() {
            if (!$('.pemasok_id').val()) {
                alert('Silakan pilih pemasok terlebih dahulu');
                $('.pemasok_id').focus();
                return;
            }

            if (!$('#nama_pemasok').val()) {
                alert('Silakan validasi pemasok terlebih dahulu (tekan Enter setelah input ID)');
                $('.pemasok_id').focus();
                return;
            }

            // Disable button untuk prevent double click
            var btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Memproses...');

            $.ajax({
                url: '/pembelian/store',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    pemasok_id: $('.pemasok_id').val()
                },
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.redirect_url;
                    }
                },
                error: function(xhr) {
                    alert('Gagal menyimpan transaksi: ' + xhr.status);
                    btn.prop('disabled', false).html('<i class="fas fa-check"></i> Proses');
                }
            });
        });

    });
</script>
@endsection