<!DOCTYPE html>
<html>

<head>
    <title>Cetak Pembelian - {{ $pembelian->no_faktur }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .info {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 5px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>BUKTI PEMBELIAN</h2>
        <p>No: {{ $pembelian->no_faktur }}</p>
    </div>

    <div class="info">
        <table style="border: none;">
            <tr style="border: none;">
                <td style="border: none;"><strong>Tanggal</strong></td>
                <td style="border: none;">: {{ $pembelian->tanggal->format('d-m-Y') }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>Pemasok</strong></td>
                <td style="border: none;">: {{ $pembelian->pemasok->nama_pemasok }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>Alamat</strong></td>
                <td style="border: none;">: {{ $pembelian->pemasok->alamat }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"><strong>Telepon</strong></td>
                <td style="border: none;">: {{ $pembelian->pemasok->tlp }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="15%">Kode Barang</th>
                <th width="30%">Nama Barang</th>
                <th width="10%" class="text-center">Qty</th>
                <th width="10%">Satuan</th>
                <th width="15%" class="text-right">Harga</th>
                <th width="15%" class="text-right">Subtotal</th>
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
                <th colspan="6" class="text-right">TOTAL</th>
                <th class="text-right">Rp {{ number_format($pembelian->jumlah_pembelian, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 50px;">
        <table style="border: none; width: 100%;">
            <tr style="border: none;">
                <td style="border: none; width: 50%; text-align: center;">
                    <p>Pemasok,</p>
                    <br><br><br>
                    <p>___________________</p>
                </td>
                <td style="border: none; width: 50%; text-align: center;">
                    <p>Penerima,</p>
                    <br><br><br>
                    <p>___________________</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()">Cetak</button>
        <button onclick="window.close()">Tutup</button>
    </div>

    <script>
        window.onload = function() {
            // Auto print when page loads
            // window.print();
        }
    </script>
</body>

</html>