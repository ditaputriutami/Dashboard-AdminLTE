<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Pembelian;
use App\Models\DetailPembelian;
use App\Models\Pemasok;
use App\Models\Barang;

class PembelianController extends Controller
{
    public function index()
    {
        $pembelian = Pembelian::with('pemasok')->orderBy('tanggal', 'desc')->get();
        return view('pembelian.index', compact('pembelian'));
    }

    public function create()
    {
        // Tampilkan prediksi nomor transaksi dan faktur (belum disimpan ke database)
        $nextId = DB::table('pembelian')->max('id') + 1;
        $noFaktur = 'PB' . date('Ymd') . sprintf('%04d', $nextId);

        $pembelian = (object)[
            'id' => $nextId,
            'no_faktur' => $noFaktur,
            'tanggal' => date('Y-m-d')
        ];

        return view('pembelian.create', compact('pembelian'));
    }

    public function getPemasok(Request $request)
    {
        $pemasok = DB::table("pemasok")
            ->select("nama_pemasok", "alamat", "tlp")
            ->where("id", $request->pemasok_id)
            ->first();
        return response()->json($pemasok);
    }

    public function store(Request $request)
    {
        $tgJam = date('Y-m-d H:i:s');

        // Generate nomor faktur saat data disimpan
        $id = DB::table('pembelian')
            ->insertGetId([
                'no_faktur' => 'PB' . date('Ymd') . sprintf('%04d', DB::table('pembelian')->max('id') + 1),
                'tanggal' => date('Y-m-d'),
                'pemasok_id' => $request->pemasok_id,
                'created_at' => $tgJam,
                'updated_at' => $tgJam,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Pemasok berhasil disimpan',
            'id' => $id,
            'redirect_url' => url('/detailPembelian/' . $id)
        ]);
    }

    public function detailPembelian($id)
    {
        $pembelian = DB::table('pembelian')
            ->join('pemasok', 'pembelian.pemasok_id', '=', 'pemasok.id')
            ->select('pembelian.*', 'pemasok.nama_pemasok')
            ->where('pembelian.id', $id)
            ->first();

        if (!$pembelian) {
            abort(404, 'Transaksi tidak ditemukan');
        }

        return view('pembelian.detail_pembelian', compact('id', 'pembelian'));
    }

    public function getBarang(Request $request)
    {
        try {
            // Log untuk debugging
            \Log::info('getBarang pembelian dipanggil dengan ID: ' . $request->id);

            if (!$request->id) {
                return response()->json([
                    'error' => 'ID barang tidak dikirim',
                    'nama_barang' => null,
                    'harga' => null,
                    'satuan' => null
                ]);
            }

            $barang = DB::table("barang")
                ->select("id", "nama_barang", "harga_pokok as harga", "satuan", "stok")
                ->where("id", $request->id)
                ->first();

            \Log::info('Hasil query barang pembelian: ' . json_encode($barang));

            if (!$barang) {
                return response()->json([
                    'error' => 'Barang tidak ditemukan dengan ID: ' . $request->id,
                    'nama_barang' => null,
                    'harga' => null,
                    'satuan' => null
                ]);
            }

            return response()->json($barang);
        } catch (\Exception $e) {
            \Log::error('Error pada getBarang pembelian: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'nama_barang' => null,
                'harga' => null,
                'satuan' => null
            ], 500);
        }
    }

    public function simpan(Request $request)
    {
        // Menggunakan mode transaksi untuk keamanan data
        DB::beginTransaction();
        try {
            // Validasi input
            if (!$request->has('details') || empty($request->details)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada data barang yang dikirim'
                ], 400);
            }

            $details = $request->details;
            $total = 0;

            foreach ($details as $detail) {
                $tgJam = date('Y-m-d H:i:s');

                DB::table('detail_pembelian')->insert([
                    'pembelian_id' => $request->id,
                    'barang_id' => $detail['barang_id'],
                    'harga' => $detail['harga'],
                    'quantity' => $detail['qty'],
                    'created_at' => $tgJam,
                    'updated_at' => $tgJam,
                ]);

                // Update stok barang (tambah karena pembelian)
                DB::table('barang')
                    ->where('id', $detail['barang_id'])
                    ->increment('stok', $detail['qty']);

                $total += ($detail['harga'] * $detail['qty']);
            }

            // Update jumlah pembelian
            DB::table('pembelian')
                ->where('id', $request->id)
                ->update([
                    'jumlah_pembelian' => $total,
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembelian berhasil disimpan',
                'redirect_url' => route('pembelian.index')
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pembelian: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $pembelian = Pembelian::with(['pemasok', 'detailPembelian.barang'])->findOrFail($id);
        return view('pembelian.show', compact('pembelian'));
    }

    public function cetak($id)
    {
        $pembelian = Pembelian::with(['pemasok', 'detailPembelian.barang'])->findOrFail($id);
        return view('pembelian.cetak', compact('pembelian'));
    }
}
