@extends('adminlte::page')

@section('title', 'Detail Jual Daftar Belanja')

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
<input type="hidden" name="jual_id" id="jual_id" value="{{ $id }}">
<section class="content container-fluid">
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/jual/create">
                    Form Pelanggan</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                Masukan Daftar Belanja</li>
        </ol>
    </nav>
    <div class="container">
        <h1>Daftar Belanja</h1>
        <div class="row">
            <div class="col-sm-2">Kasa</div>
            <div class="col-sm-4">: {{auth()->user()->name}}</div>
        </div>
        <div class="row">
            <div class="col-sm-2">Tanggal Transaksi</div>
            <div class="col-sm-4">: {{ date('d-m-Y') }}</div>
        </div>
        <div class="row">
            <div class="col-sm-2">No Transaksi</div>
            <div class="col-sm-4">: <b>{{$id}}</b></div>
        </div>
        <table class="table table-bordered">
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
                <td><input size="30" id="nama_barang" type="text"
                        name="nama_barang" disabled style="width:100%;"></td>
                <td><input size="5" type="text" id="qty" name="qty"
                        title="Ketik qty, tekan Enter" style="width:100%;"></td>
                <td><input size="10" id="satuan" type="text" name="satuan" disabled
                        style="width:100%;"></td>
                <td><input size="10" id="harga" type="number" name="harga_sekarang"
                        style="text-align:right; width:100%;" disabled></td>
                <td><input size="10" id="total" type="number" name="total"
                        style="text-align:right; width:100%;"></td>
                <td><input type="button" class="add-row btn btn-sm btn-success" value="+ Tambah"></td>
            </tr>
        </table>
        <br>
        <h5>Daftar Belanja</h5>
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
                        <output id="jtotal" style="text-align:right"></output>
                    </td>
                </tr>
            </tfoot>
        </table>
        <button type="button" class="delete-row">Hapus</button>
        <button type="button" class="simpan">Simpan/Cetak</button>
    </div>
</section>
@endsection

@section('js')
<script>
    $(document).ready(function() {
        var CSRF_TOKEN = $('input[name="_token"]').val();
        var jTotal = 0;
        console.log("CSRF Token:", CSRF_TOKEN);

        // kode barang di tekan enter atau tab
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
                    url: '/bacaBarang',
                    type: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    data: {
                        id: barangId
                    },
                    dataType: 'JSON',
                    success: function(data) {
                        console.log("Response dari bacaBarang:", data);

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
        //jumlah barang - hitung otomatis total
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
        }); //akhir qty*harga
        //menambahkan ke kranjang belanja
        $(".add-row").click(function() {
            var barang_id = $(".barang_id").val();
            var qty = $("#qty").val();
            var nama_barang = $("#nama_barang").val();
            var satuan = $("#satuan").val();
            var harga = $("#harga").val();
            var total = $("#total").val();
            jTotal += parseInt(total);
            var html = "<tr><td style=\"text-align:center\">" +
                "<input type='checkbox' name='record'></td><td>" +
                barang_id + "</td><td>" +
                nama_barang + "</td><td style=\"text-align:right\">" +
                qty + "</td><td>" +
                satuan + "</td><td style=\"text-align:right\">" +
                harga + "</td><td style=\"text-align:right\">" +
                total + "</td></tr>";
            //"<tr><td>"+ jTotal +"</td></tr>";
            $("#table1").find('tbody').append(html);
            $("#jtotal").val(jTotal);
            //kosongkan isian
            $(".barang_id").val('');
            $(".barang_id").focus();
            $("#nama_barang").prop("disabled", true);
            $("#nama_barang").val('');
            $("#qty").val(0);
            $("#satuan").val('');
            $("#harga").val(0);
            $("#total").val(0);
        }); //akhir menambah kranjang belanja
        // Menghapus jika isian salah
        $(".delete-row").click(function() {
            var jtotal = $("#jtotal").val();
            $("table tbody").find('input[name="record"]').
            each(function() {
                if ($(this).is(":checked")) {
                    //kurangi total kalau dihapus
                    var currow = $(this).closest('tr');
                    var isicol6 = currow.find('td:eq(6)').text();
                    jtotal -= parseInt(isicol6);
                    $(this).parents("tr").remove();
                    $("#jtotal").val(jtotal);
                }
            });
        }); //akhir menghapus jika isian salah
        //kirim ke server, simpan rekamanan
        $(".simpan").click(function() {
            let dataBarang = [];

            // Ambil SEMUA baris dari tbody (tidak hanya yang tercentang)
            $("#table1 tbody tr").each(function() {
                var currow = $(this);
                // isikan ke array dataBarang
                dataBarang.push({
                    'barang_id': currow.find('td:eq(1)').text(),
                    'qty': currow.find('td:eq(3)').text(),
                    'harga_sekarang': currow.find('td:eq(5)').text(),
                    'jual_id': "{{$id}}",
                });
            });

            // Validasi: cek apakah ada barang yang ditambahkan
            if (dataBarang.length === 0) {
                alert('Belum ada barang yang ditambahkan ke daftar belanja!');
                return false;
            }

            // Disable tombol simpan agar tidak double klik
            $(this).prop('disabled', true).text('Menyimpan...');

            // kirim ke server untuk disimpan
            $.ajax({
                    url: '/jual/simpan',
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        idJual: "{{ $id }}",
                        dataBarang: dataBarang
                    }
                })
                .done(function(response) { // jika berhasil
                    console.log('Response:', response);
                    if (response.berhasil) {
                        alert('Transaksi berhasil disimpan!\nStok barang telah dikurangi.');
                        window.location.href = response.urlCetak;
                    } else {
                        alert('Gagal menyimpan transaksi!');
                        $(".simpan").prop('disabled', false).text('Simpan/Cetak');
                    }
                })
                .fail(function(error) { // jika gagal
                    console.error("Error simpan:", error);
                    alert('Terjadi kesalahan saat menyimpan transaksi!\n' +
                        (error.responseJSON?.message || 'Silakan coba lagi.'));
                    $(".simpan").prop('disabled', false).text('Simpan/Cetak');
                });
        }); //akhir kirim ke server
    });
</script>
@endsection