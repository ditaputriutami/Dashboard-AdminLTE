<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\DetailJual;
use App\Models\Pelanggan;
use App\Models\Jual;
use App\Models\Barang;

class JualController extends Controller
{
    /*************************************
    masukan data pelanggan dan membuat
    nomor transaksi/nota
    disimpan ke tabel jual (id)
     ************************************/
    public function create()
    {
        // Ambil nomor transaksi berikutnya untuk ditampilkan (prediksi)
        $nextId = DB::table('jual')->max('id') + 1;
        $jual = (object)[
            'id' => $nextId,
            'tanggal' => date('Y-m-d')
        ];
        return view('jual.create', compact('jual'));
    }
    /*********************************************
     * pembacaan data pelanggan menggunakan ajax
     * mengembalikan data format json
     **********************************************/
    public function getPelanggan(Request $request)
    {
        $pelanggan = DB::table("pelanggan")
            ->select("nama_pelanggan", "alamat", "telp_hp")
            ->where("id", $request->pelanggan_id)
            ->first();
        return response()->json($pelanggan);
    }
    /***********************************
     * menyimpan data pelanggan
     * dilanjutkan ke detail_jual
     **********************************/
    public function store(Request $request)
    {
        $tgJam = date('Y-m-d h:i:s');
        // Membuat nomor transaksi baru saat pelanggan disimpan
        $id = DB::table('jual')
            ->insertGetId([
                'tanggal' => date('Y-m-d'),
                'pelanggan_id' => $request->pelanggan_id,
                'created_at' => $tgJam,
                'updated_at' => $tgJam,
                'user_id' => Auth::id()
            ]);
        // Return JSON response so AJAX can redirect
        return response()->json([
            'success' => true,
            'message' => 'Pelanggan berhasil disimpan',
            'id' => $id,
            'redirect_url' => url('/detailJual/' . $id)
        ]);
    }
    //dilanjukan ke detail jual
    public function detailJual($id)
    {
        // Verify the transaction exists
        $jual = DB::table('jual')->where('id', $id)->first();
        if (!$jual) {
            abort(404, 'Transaksi tidak ditemukan');
        }
        return view('jual.detail_jual', compact('id'));
    }
// Melanjutkan utk petemuan 13
    /****************************************
     * pembacaan data barang menggunakan ajax
     * mengembalikan data format json
     ****************************************/
    public function getBarang(Request $request)
    {
        // Log untuk debugging
        Log::info('getBarang dipanggil dengan ID: ' . $request->id);

        $barang = DB::table("barang")
            ->select("id", "nama_barang", "harga_jual as harga", "satuan", "stok")
            ->where("id", $request->id)
            ->first();

        Log::info('Hasil query barang: ' . json_encode($barang));

        if (!$barang) {
            return response()->json([
                'error' => 'Barang tidak ditemukan dengan ID: ' . $request->id,
                'nama_barang' => null,
                'harga' => null,
                'satuan' => null
            ]);
        }

        return response()->json($barang);
    }
    /**********************************************************
     * menyimpan rekaman
     * ubah jumlah_pembelian di tabel jual
     * menambah master detil tabel detail_jual
     * ubah tabel barang(mengurangi stok setiap barang yg dijual)
     ************************************************************/
    public function simpan(Request $request)
    {
        //menggunakan mode transaksi, ketika terjadi
        //salah satu kesalahan akan dibatalkan semua
        //******************************************
        DB::beginTransaction();
        try {
            // Validasi input
            if (!$request->has('dataBarang') || empty($request->dataBarang)) {
                return response()->json([
                    'berhasil' => false,
                    'message' => 'Tidak ada data barang yang dikirim'
                ], 400);
            }

            $total = 0;
            // looping $request->dataBarang
            foreach ($request->dataBarang as $key => $barang) {
                // Cek stok barang sebelum mengurangi
                $barangData = Barang::find($barang['barang_id']);
                if (!$barangData) {
                    throw new \Exception("Barang dengan ID {$barang['barang_id']} tidak ditemukan");
                }

                if ($barangData->stok < $barang['qty']) {
                    throw new \Exception("Stok barang {$barangData->nama_barang} tidak mencukupi. Stok tersedia: {$barangData->stok}, diminta: {$barang['qty']}");
                }

                //simpan ke transaksi jual
                $tgJam = date('Y-m-d h:i:s');
                DB::table('detail_jual')->insert(
                    [
                        'jual_id' => $request->idJual,
                        'barang_id' => $barang['barang_id'],
                        'qty' => $barang['qty'],
                        'harga_sekarang' => $barang['harga_sekarang'],
                        'created_at' => $tgJam,
                        'updated_at' => $tgJam
                    ]
                );

                //kurangi stok di tabel barang
                $affected = DB::table('barang')
                    ->where('id', $barang['barang_id'])
                    ->update(['stok' => DB::raw('stok - ' . $barang['qty'])]);

                Log::info("Stok barang {$barang['barang_id']} dikurangi {$barang['qty']}. Rows affected: {$affected}");

                //menjumlah pertransaksi
                $total += $barang['qty'] * $barang['harga_sekarang'];
            }

            // merekam jumlah pembelian pertransaksi
            Jual::whereId($request->idJual)->update([
                'jumlah_pembelian' => $total
            ]);

            DB::commit();

            Log::info("Transaksi {$request->idJual} berhasil disimpan. Total: Rp {$total}");

            //kembalikan response berupa json ke client
            //utuk mencetak strok pembayaran
            return response()->json([
                'berhasil' => true,
                'message' => 'Transaksi berhasil disimpan',
                'urlCetak' => url('/jual/cetak/' . $request->idJual)
            ]);
        } catch (\Throwable $e) {
            //jika terjadi kesalahan batalkan semua
            DB::rollback();
            Log::error("Error simpan transaksi: " . $e->getMessage());

            return response()->json([
                'berhasil' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function cetak($id)
    {
        $djual = DetailJual::where('jual_id', $id)->get();
        $jual = Jual::find($id);
        $tgl = $jual->tanggal;
        $pelanggan = Pelanggan::find($jual->pelanggan_id);
        return view('jual.cetak', compact('djual', 'pelanggan', 'id', 'tgl'));
    }

    /****************************************************
     * LAPORAN REKAP PENJUALAN
     ****************************************************/

    // Menampilkan form input tanggal
    public function laporanForm()
    {
        return view('jual.fctRkpPenjualan');
    }

    // Proses cetak laporan berdasarkan range tanggal
    public function laporanCetak(Request $request)
    {
        $request->validate([
            'tgl1' => 'required|date',
            'tgl2' => 'required|date|after_or_equal:tgl1'
        ], [
            'tgl2.after_or_equal' => 'Sampai Tanggal harus lebih besar atau sama dengan Dari Tanggal'
        ]);

        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;

        $rekap = DB::table('jual')
            ->join('pelanggan', 'jual.pelanggan_id', '=', 'pelanggan.id')
            ->select('jual.id', 'jual.tanggal', 'pelanggan.nama_pelanggan', 'pelanggan.alamat', 'pelanggan.telp_hp', 'jual.jumlah_pembelian')
            ->whereBetween('jual.tanggal', [$tgl1, $tgl2])
            ->orderBy('jual.tanggal', 'asc')
            ->orderBy('jual.id', 'asc')
            ->get();

        return view('jual.ctRkpPenjualan', compact('rekap', 'tgl1', 'tgl2'));
    }
} //end class