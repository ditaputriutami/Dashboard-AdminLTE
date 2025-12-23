<!DOCTYPE html>
<html>

<head>
    <title>Struk Pembayaran - Transaksi {{$id}}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            font-size: 24px;
            color: #2c3e50;
        }

        .header p {
            margin: 5px 0;
            color: #666;
        }

        .info-section {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }

        .info-row {
            display: flex;
            margin-bottom: 8px;
        }

        .info-label {
            width: 150px;
            font-weight: bold;
            color: #555;
        }

        .info-value {
            flex: 1;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th {
            background-color: #2c3e50;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
        }

        table td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
        }

        table tbody tr:hover {
            background-color: #f5f5f5;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .total-row {
            background-color: #ecf0f1;
            font-weight: bold;
            font-size: 16px;
        }

        .total-row td {
            padding: 15px 8px;
            border-top: 2px solid #2c3e50;
            border-bottom: 2px solid #2c3e50;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px dashed #999;
            text-align: center;
            color: #666;
        }

        .button-group {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .btn {
            padding: 10px 20px;
            margin: 0 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-print {
            background-color: #3498db;
            color: white;
        }

        .btn-print:hover {
            background-color: #2980b9;
        }

        .btn-back {
            background-color: #95a5a6;
            color: white;
        }

        .btn-back:hover {
            background-color: #7f8c8d;
        }

        @media print {
            .button-group {
                display: none;
            }

            body {
                padding: 0;
            }
        }

        .amount {
            font-weight: bold;
            color: #27ae60;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>TOKO SERBA ADA</h2>
        <p>Jl. Wonosari KM.7 Bantul, Yogyakarta</p>
        <p>Telp: (0274) 123456 | Email: info@tokoserbaada.com</p>
    </div>

    <div class="info-section">
        <div class="info-row">
            <div class="info-label">No Transaksi</div>
            <div class="info-value">: <strong>{{$id}}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Tanggal</div>
            <div class="info-value">: <strong>{{ date('d-m-Y', strtotime($tgl)) }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Kasir</div>
            <div class="info-value">: <strong>{{ auth()->user()->name }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Pelanggan</div>
            <div class="info-value">: <strong>{{ $pelanggan->nama_pelanggan }}</strong></div>
        </div>
        @if($pelanggan->alamat)
        <div class="info-row">
            <div class="info-label">Alamat</div>
            <div class="info-value">: {{ $pelanggan->alamat }}</div>
        </div>
        @endif
        @if($pelanggan->telp_hp)
        <div class="info-row">
            <div class="info-label">Telp/HP</div>
            <div class="info-value">: {{ $pelanggan->telp_hp }}</div>
        </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" width="5%">No</th>
                <th width="10%">Kode</th>
                <th width="30%">Nama Barang</th>
                <th class="text-center" width="10%">Qty</th>
                <th class="text-center" width="10%">Satuan</th>
                <th class="text-right" width="15%">Harga (Rp)</th>
                <th class="text-right" width="20%">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php
            $i = 1;
            $total = 0;
            @endphp
            @foreach($djual as $j)
            <tr>
                <td class="text-center">{{$i++}}</td>
                <td>{{$j->barang_id}}</td>
                <td>{{$j->barang->nama_barang}}</td>
                <td class="text-center">{{$j->qty}}</td>
                <td class="text-center">{{ $j->barang->satuan}}</td>
                <td class="text-right">{{number_format($j->harga_sekarang, 0, ',', '.')}}</td>
                <td class="text-right">{{number_format($j->qty * $j->harga_sekarang, 0, ',', '.')}}</td>
                @php
                $total += $j->qty * $j->harga_sekarang;
                @endphp
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="6" class="text-right"><strong>TOTAL PEMBAYARAN</strong></td>
                <td class="text-right amount">Rp {{number_format($total, 0, ',', '.')}}</td>
            </tr>
        </tfoot>
    </table>

    <div class="button-group">
        <button class="btn btn-print" onclick="window.print()">🖨️ Cetak Struk</button>
        <a href="/jual/create" class="btn btn-back">🏠 Kembali ke Transaksi Baru</a>
    </div>

    <div class="footer">
        <p>*** TERIMA KASIH ATAS KUNJUNGAN ANDA ***</p>
        <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
        <p style="font-size: 12px; margin-top: 10px;">Dicetak pada: {{ date('d-m-Y H:i:s') }}</p>
    </div>

    <script>
        // Auto focus untuk print saat halaman dimuat (opsional)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>

</html>