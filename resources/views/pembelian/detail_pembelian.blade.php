@extends('adminlte::page')

@section('title', 'Detail Pembelian - Daftar Barang')

@section('css')
<style>
    table {
        border-collapse: collapse;
    }

    table,
    td,
    th {
        border: 1px solid black;
    }
</style>
@endsection

@section('content')
<input type="hidden" name="_token" value="{{ csrf_token() }}">
<input type="hidden" name="pembelian_id" id="pembelian_id" value="{{ $id }}">

<section class="content container-fluid">
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('pembelian.create') }}">Form Pemasok</a></li>
            <li class="breadcrumb-item active" aria-current="page">Masukan Daftar Barang</li>
        </ol>
    </nav>

    <div class="container">
        <h1>Daftar Barang Pembelian</h1>

        <div class="row">
            <div class="col-sm-2">User</div>
            <div class="col-sm-4">: {{ auth()->user()->name }}</div>
        </div>
        <div class="row">
            <div class="col-sm-2">Tanggal Transaksi</div>
            <div class="col-sm-4">: {{ date('d-m-Y', strtotime($pembelian->tanggal)) }}</div>
        </div>
        <div class="row">
            <div class="col-sm-2">No Transaksi</div>
            <div class="col-sm-4">: <b>{{ $id }}</b></div>
        </div>
        <div class="row">
            <div class="col-sm-2">No Faktur</div>
            <div class="col-sm-4">: <b>{{ $pembelian->no_faktur }}</b></div>
        </div>
        <div class="row">
            <div class="col-sm-2">Pemasok</div>
            <div class="col-sm-4">: {{ $pembelian->nama_pemasok }}</div>
        </div>

        <table class="table table-bordered mt-3">
            <tr style="background-color: #e8f4f8;">
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Qty</th>
                <th>Satuan</th>
                <th>Harga (Rp)</th>
                <th>Total (Rp)</th>
                <th>Aksi</th>
            </tr>
            <tr>
                <td><input type="text" class="barang_id" size="8" name="barang_id"
                        title="Ketik kode barang, tekan Enter" placeholder="Kode" style="width:100%;"></td>
                <td><input size="30" id="nama_barang" type="text" name="nama_barang" disabled style="width:100%;"></td>
                <td><input size="5" type="text" id="qty" name="qty" title="Ketik qty, tekan Enter" style="width:100%;"></td>
                <td><input size="10" id="satuan" type="text" name="satuan" disabled style="width:100%;"></td>
                <td><input size="10" id="harga" type="number" name="harga_sekarang" style="text-align:right; width:100%;" disabled></td>
                <td><input size="10" id="total" type="number" name="total" style="text-align:right; width:100%;" disabled></td>
                <td><input type="button" class="add-row btn btn-sm btn-success" value="+ Tambah"></td>
            </tr>
        </table>

        <br>
        <h5>Daftar Barang yang Dibeli</h5>
        <table id="table1">
            <thead>
                <tr style="background-color:#c7d1c7;">
                    <th width="3%">Pilih</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Qty</th>
                    <th>Satuan</th>
                    <th>Harga (Rp)</th>
                    <th>Total (Rp)</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6" style="text-align:right">Total :</th>
                    <td style="text-align:right">
                        <output id="jtotal" style="text-align:right">0</output>
                    </td>
                </tr>
            </tfoot>
        </table>

        <button type="button" class="delete-row btn btn-danger">Hapus</button>
        <button type="button" class="simpan btn btn-primary">Simpan</button>
        <a href="{{ route('pembelian.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</section>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        var CSRF_TOKEN = $('input[name="_token"]').val();
        var jTotal = 0;
        console.log("CSRF Token:", CSRF_TOKEN);

        // Kode barang di tekan enter atau tab - validasi otomatis seperti penjualan
        $(".barang_id").on('keydown', function(e) {
            var keycode = (e.keyCode ? e.keyCode : e.which);
            // Enter (13) atau Tab (9)
            if (keycode == '13' || keycode == '9') {
                e.preventDefault();

                var barangId = $(".barang_id").val();

                if (barangId == "" || barangId == null) {
                    alert("Kode barang tidak boleh kosong");
                    return false;
                }

                console.log("Mencari barang ID:", barangId);

                $.ajax({
                    url: '/bacaBarangPembelian',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    data: {
                        id: barangId
                    },
                    dataType: 'JSON',
                    success: function(data) {
                        console.log("Response dari bacaBarangPembelian:", data);

                        if (data.error) {
                            alert(data.error);
                            $(".barang_id").val('').focus();
                            return;
                        }

                        if (!data || !data.nama_barang) {
                            alert("Barang dengan kode " + barangId + " tidak ditemukan");
                            $(".barang_id").val('').focus();
                            return;
                        }

                        // Isi form dengan data barang
                        $("#nama_barang").val(data.nama_barang);
                        $("#harga").val(data.harga);
                        $("#satuan").val(data.satuan);
                        $("#qty").val(1);
                        $("#qty").focus();

                        console.log("Data berhasil diisi - Nama:", data.nama_barang, "Harga:", data.harga);
                    },
                    error: function(xhr, status, error) {
                        console.error("Error AJAX:", status, error);
                        console.error("Response Text:", xhr.responseText);
                        console.error("Status Code:", xhr.status);
                        alert("Error: " + error + "\nStatus: " + xhr.status);
                    }
                });
            }
        });

        // Hitung total otomatis
        $("#qty").on('keyup change', function(e) {
            var qty = $(this).val();
            var harga = $("#harga").val();

            if (qty && harga && qty > 0) {
                var total = parseInt(qty) * parseInt(harga);
                $("#total").val(total);
                console.log("Total dihitung: " + qty + " x " + harga + " = " + total);
            } else {
                $("#total").val(0);
            }
        });

        // Enter pada qty untuk fokus ke tombol tambah
        $("#qty").on('keydown', function(e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                $(".add-row").focus();
            }
        });

        // Menambahkan ke daftar pembelian
        $(".add-row").click(function() {
            var barang_id = $(".barang_id").val();
            var qty = $("#qty").val();
            var nama_barang = $("#nama_barang").val();
            var satuan = $("#satuan").val();
            var harga = $("#harga").val();
            var total = $("#total").val();

            if (!barang_id || !qty || !harga) {
                alert("Lengkapi data barang terlebih dahulu");
                return;
            }

            jTotal += parseInt(total);

            var html = "<tr><td style=\"text-align:center\">" +
                "<input type='checkbox' name='record'></td><td>" +
                barang_id + "</td><td>" + nama_barang + "</td><td>" +
                qty + "</td><td>" + satuan + "</td><td style=\"text-align:right\">" +
                harga + "</td><td style=\"text-align:right\">" + total + "</td></tr>";

            $("#table1 tbody").append(html);
            $("#jtotal").html(jTotal);

            // Reset form
            $(".barang_id").val('');
            $("#nama_barang").val('');
            $("#qty").val('');
            $("#satuan").val('');
            $("#harga").val('');
            $("#total").val('');
            $(".barang_id").focus();
        });

        // Hapus baris yang dipilih
        $(".delete-row").click(function() {
            $("#table1 tbody").find('input[name="record"]').each(function() {
                if ($(this).is(":checked")) {
                    var row = $(this).parents("tr");
                    var total = parseInt(row.find("td:eq(6)").text());
                    jTotal -= total;
                    $("#jtotal").html(jTotal);
                    $(this).parents("tr").remove();
                }
            });
        });

        // Simpan pembelian
        $(".simpan").click(function() {
            var details = [];
            $("#table1 tbody tr").each(function() {
                var row = $(this);
                details.push({
                    barang_id: row.find("td:eq(1)").text(),
                    qty: row.find("td:eq(3)").text(),
                    harga: row.find("td:eq(5)").text()
                });
            });

            if (details.length === 0) {
                alert("Belum ada barang yang ditambahkan");
                return;
            }

            $.ajax({
                url: '/pembelian/simpan',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                data: {
                    id: $("#pembelian_id").val(),
                    details: details
                },
                dataType: 'JSON',
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        window.location.href = response.redirect_url;
                    }
                },
                error: function(xhr, status, error) {
                    alert("Error: " + error);
                }
            });
        });
    });
</script>
@endsection