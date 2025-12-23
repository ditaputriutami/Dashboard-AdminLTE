<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekap Penjualan</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 11px;
            margin-bottom: 3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table thead {
            background-color: #f0f0f0;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        table th {
            font-weight: bold;
            text-align: center;
        }

        table td.text-center {
            text-align: center;
        }

        table td.text-right {
            text-align: right;
        }

        table tfoot {
            background-color: #e8e8e8;
            font-weight: bold;
        }

        .no-print {
            margin-top: 20px;
            text-align: center;
        }

        .btn {
            padding: 10px 20px;
            margin: 5px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            font-size: 14px;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>TOKO SERBA ADA</h1>
        <h2>LAPORAN REKAPITULASI PENJUALAN</h2>
        <p>Periode Tanggal: {{ \Carbon\Carbon::parse($tgl1)->format('d-m-Y') }} Sampai dengan Tanggal: {{ \Carbon\Carbon::parse($tgl2)->format('d-m-Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 10%;">No Trans</th>
                <th style="width: 12%;">Tanggal</th>
                <th style="width: 20%;">Nama Pelanggan</th>
                <th style="width: 25%;">Alamat</th>
                <th style="width: 13%;">Telepon</th>
                <th style="width: 15%;">Jumlah Pembelian</th>
            </tr>
        </thead>
        <tbody>
            @php
            $total = 0;
            @endphp
            @forelse($rekap as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $item->id }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}</td>
                <td>{{ $item->nama_pelanggan }}</td>
                <td>{{ $item->alamat }}</td>
                <td>{{ $item->telp_hp }}</td>
                <td class="text-right">{{ number_format($item->jumlah_pembelian ?? 0, 0, ',', '.') }}</td>
            </tr>
            @php
            $total += $item->jumlah_pembelian ?? 0;
            @endphp
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data untuk periode tanggal yang dipilih</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="text-right"><strong>Total:</strong></td>
                <td class="text-right"><strong>{{ number_format($total, 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">
            🖨️ Cetak Laporan
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            Tutup
        </button>
    </div>
</body>

</html>