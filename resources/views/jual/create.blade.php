@extends('adminlte::page')

@section('title', 'Tambah Transaksi Penjualan')

@section('content')
<h2>Tambah Transaksi Penjualan</h2>
<div class="card">
    <div class="card-body">

        {{-- METADATA CSRF (WAJIB untuk AJAX POST) --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <table>
            <tr>
                <td>Nomor Transaksi</td>
                <td>
                    <input type="text" class="id" name="id"
                        value="{{ $jual->id }}" size="50" disabled>
                </td>
            </tr>

            <tr>
                <td>Tanggal</td>
                <td>
                    <input type="text" class="tanggal" name="tanggal"
                        value="{{ $jual->tanggal }}" size="50">
                </td>
            </tr>

            <tr>
                <td>Kasir</td>
                <td>
                    <input type="text" class="username" name="username"
                        value="{{ auth()->user()->name ?? '' }}" size="50">
                </td>
            </tr>

            <tr>
                <td>Nomor ID Anggota [Enter]</td>
                <td>
                    <input type="text" class="pelanggan_id" name="pelanggan_id" size="50">
                </td>
            </tr>

            <tr>
                <td>Nama</td>
                <td>
                    <input type="text" id="nama_pelanggan" name="nama_pelanggan" size="50">
                </td>
            </tr>
        </table>

        <div class="form-group mt-3">
            <button type="button" class="proses btn btn-primary">Proses</button>
        </div>
    </div>
</div>
@endsection


@section('css')
<link rel="stylesheet" href="/js_css/bootstrap.min.css">
@endsection


@section('js')
<script>
    document.addEventListener("DOMContentLoaded", function() {

        let CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

        // ==============================
        // GET PELANGGAN BY ID
        // ==============================
        $(document).on('keypress', '.pelanggan_id', function(e) {
            if (e.which === 13) {

                $.ajax({
                    url: '/bacaPelanggan',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        pelanggan_id: $('.pelanggan_id').val()
                    },
                    success: function(data) {
                        $('#nama_pelanggan').val(data.nama_pelanggan);
                    },
                    error: function(xhr) {
                        alert('Gagal membaca pelanggan: ' + xhr.status);
                    }
                });

            }
        });


        // ==============================
        // SIMPAN TRANSAKSI
        // ==============================
        $(document).on('click', '.proses', function() {

            let pelanggan_id = $('.pelanggan_id').val();

            if (!pelanggan_id) {
                alert('ID Pelanggan harus diisi!');
                return;
            }

            $.ajax({
                url: '/jual/store',
                type: 'POST',
                data: {
                    _token: CSRF_TOKEN,
                    pelanggan_id: pelanggan_id,
                },
                success: function(response) {
                    // Redirect ke halaman detail dengan rute yang benar
                    window.location.href = "/detailJual/" + response.id;
                },

                error: function(xhr) {
                    alert("Simpan gagal: " + xhr.status + " - " + xhr.statusText);
                    console.log(xhr.responseText);
                }
            });

        });

    });
</script>
@endsection