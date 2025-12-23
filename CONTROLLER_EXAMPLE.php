/**
* ========================================
* CONTROLLER METHOD: SIMPAN/CETAK TRANSAKSI
* ========================================
* File: app/Http/Controllers/JualController.php
*
* Fungsi ini menangani:
* 1. Menyimpan detail transaksi
* 2. MENGURANGI STOK BARANG OTOMATIS ⭐
* 3. Update total pembelian
* 4. Return URL untuk cetak struk
*/

public function simpan(Request $request)
{
// ============================================================
// MENGGUNAKAN DATABASE TRANSACTION UNTUK INTEGRITAS DATA
// ============================================================
DB::beginTransaction();

try {
// ============================================================
// VALIDASI INPUT - Pastikan ada data barang
// ============================================================
if (!$request->has('dataBarang') || empty($request->dataBarang)) {
return response()->json([
'berhasil' => false,
'message' => 'Tidak ada data barang yang dikirim'
], 400);
}

$total = 0;

// ============================================================
// LOOPING SETIAP BARANG YANG DIBELI
// ============================================================
foreach ($request->dataBarang as $key => $barang) {

// ========================================================
// CEK KETERSEDIAAN BARANG
// ========================================================
$barangData = Barang::find($barang['barang_id']);

if (!$barangData) {
throw new \Exception("Barang dengan ID {$barang['barang_id']} tidak ditemukan");
}

// ========================================================
// VALIDASI STOK - PENTING! ⚠️
// Mencegah stok menjadi negatif
// ========================================================
if ($barangData->stok < $barang['qty']) {
    throw new \Exception( "Stok barang '{$barangData->nama_barang}' tidak mencukupi.\n" . "Stok tersedia: {$barangData->stok}, diminta: {$barang['qty']}"
    );
    }

    //========================================================// SIMPAN KE TABEL DETAIL_JUAL
    //========================================================$tgJam=date('Y-m-d H:i:s');
    DB::table('detail_jual')->insert([
    'jual_id' => $request->idJual,
    'barang_id' => $barang['barang_id'],
    'qty' => $barang['qty'],
    'harga_sekarang' => $barang['harga_sekarang'],
    'created_at' => $tgJam,
    'updated_at' => $tgJam,
    'user_id' => Auth::id()
    ]);

    // ========================================================
    // ⭐ KURANGI STOK BARANG - INI BAGIAN TERPENTING! ⭐
    // ========================================================
    // Metode 1: Menggunakan DB::raw untuk operasi atomik
    $affected = DB::table('barang')
    ->where('id', $barang['barang_id'])
    ->update(['stok' => DB::raw('stok - ' . $barang['qty'])]);

    // Metode 2 (Alternatif): Menggunakan Eloquent decrement
    // $barangData->decrement('stok', $barang['qty']);

    // Log untuk tracking perubahan stok
    \Log::info("✅ Stok barang ID:{$barang['barang_id']} dikurangi {$barang['qty']}. Rows affected: {$affected}");

    // ========================================================
    // HITUNG SUBTOTAL UNTUK SETIAP ITEM
    // ========================================================
    $subtotal = $barang['qty'] * $barang['harga_sekarang'];
    $total += $subtotal;
    }

    // ============================================================
    // UPDATE TOTAL PEMBELIAN DI TABEL JUAL (MASTER)
    // ============================================================
    Jual::whereId($request->idJual)->update([
    'jumlah_pembelian' => $total
    ]);

    // ============================================================
    // COMMIT TRANSACTION - SIMPAN SEMUA PERUBAHAN
    // ============================================================
    DB::commit();

    \Log::info("✅ Transaksi #{$request->idJual} berhasil disimpan. Total: Rp " . number_format($total));

    // ============================================================
    // RETURN JSON RESPONSE DENGAN URL CETAK
    // ============================================================
    return response()->json([
    'berhasil' => true,
    'message' => 'Transaksi berhasil disimpan dan stok telah dikurangi',
    'urlCetak' => url('/jual/cetak/' . $request->idJual),
    'total' => $total
    ]);

    } catch (\Throwable $e) {
    // ============================================================
    // ROLLBACK - Batalkan semua perubahan jika error
    // ============================================================
    DB::rollback();
    \Log::error("❌ Error simpan transaksi: " . $e->getMessage());

    return response()->json([
    'berhasil' => false,
    'message' => $e->getMessage()
    ], 500);
    }
    }

    /**
    * ========================================
    * METHOD: CETAK STRUK
    * ========================================
    */
    public function cetak($id)
    {
    // Ambil data detail transaksi
    $djual = DetailJual::where('jual_id', $id)->get();

    // Ambil data transaksi
    $jual = Jual::find($id);
    $tgl = $jual->tanggal;

    // Ambil data pelanggan
    $pelanggan = Pelanggan::find($jual->pelanggan_id);

    // Tampilkan view cetak
    return view('jual.cetak', compact('djual', 'pelanggan', 'id', 'tgl'));
    }

    /**
    * ========================================
    * ALTERNATIVE METHOD (Opsional)
    * ========================================
    * Jika Anda ingin method terpisah dengan nama berbeda:
    */
    public function prosesSimpanCetak(Request $request, $id)
    {
    DB::beginTransaction();

    try {
    // Ambil semua detail transaksi dari request
    $items = $request->input('items', []);

    if (empty($items)) {
    throw new \Exception('Keranjang belanja kosong');
    }

    $grandTotal = 0;

    // Iterasi setiap item
    foreach ($items as $item) {
    // Cari barang
    $barang = Barang::lockForUpdate()->find($item['barang_id']);

    if (!$barang) {
    throw new \Exception("Barang tidak ditemukan");
    }

    // Validasi stok
    if ($barang->stok < $item['qty']) {
        throw new \Exception("Stok {$barang->nama_barang} tidak cukup");
        }

        // Simpan detail
        DetailJual::create([
        'jual_id' => $id,
        'barang_id' => $item['barang_id'],
        'qty' => $item['qty'],
        'harga_sekarang' => $barang->harga_jual,
        ]);

        // Kurangi stok menggunakan decrement
        $barang->decrement('stok', $item['qty']);

        $grandTotal += $item['qty'] * $barang->harga_jual;
        }

        // Update total di tabel jual
        Jual::where('id', $id)->update([
        'total_harga' => $grandTotal,
        'status' => 'selesai'
        ]);

        DB::commit();

        // Redirect ke halaman cetak
        return redirect()->route('jual.cetak', $id)
        ->with('success', 'Transaksi berhasil disimpan');

        } catch (\Exception $e) {
        DB::rollback();
        return back()->with('error', $e->getMessage());
        }
        }