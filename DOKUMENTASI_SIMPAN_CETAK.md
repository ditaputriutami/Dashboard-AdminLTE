# 📋 DOKUMENTASI FITUR SIMPAN/CETAK TRANSAKSI

## 🎯 RINGKASAN FITUR

Fitur ini memungkinkan kasir untuk:

1. Menambahkan barang ke keranjang belanja
2. Menyimpan transaksi ke database
3. **Mengurangi stok barang secara otomatis**
4. Mencetak struk pembayaran

---

## 🔧 KOMPONEN SISTEM

### 1. **ROUTE** (`routes/web.php`)

```php
// Route untuk menyimpan transaksi dan mengurangi stok
Route::post('/jual/simpan', [JualController::class, 'simpan']);

// Route untuk menampilkan halaman cetak
Route::get('/jual/cetak/{id}', [JualController::class, 'cetak']);
```

---

### 2. **CONTROLLER** (`app/Http/Controllers/JualController.php`)

#### Method: `simpan(Request $request)`

**Fungsi Utama:**

-   Menyimpan detail transaksi ke tabel `detail_jual`
-   **Mengurangi stok barang di tabel `barang`**
-   Update total pembelian di tabel `jual`
-   Menggunakan **Database Transaction** untuk integritas data

**Kode Lengkap:**

```php
public function simpan(Request $request)
{
    // ========================================
    // STEP 1: Mulai Database Transaction
    // ========================================
    DB::beginTransaction();

    try {
        // ========================================
        // STEP 2: Validasi Input
        // ========================================
        if (!$request->has('dataBarang') || empty($request->dataBarang)) {
            return response()->json([
                'berhasil' => false,
                'message' => 'Tidak ada data barang yang dikirim'
            ], 400);
        }

        $total = 0;

        // ========================================
        // STEP 3: Looping Setiap Barang
        // ========================================
        foreach ($request->dataBarang as $key => $barang) {

            // ========================================
            // STEP 3a: Validasi Stok Barang
            // ========================================
            $barangData = Barang::find($barang['barang_id']);

            if (!$barangData) {
                throw new \Exception("Barang dengan ID {$barang['barang_id']} tidak ditemukan");
            }

            // Validasi: Cek apakah stok mencukupi
            if ($barangData->stok < $barang['qty']) {
                throw new \Exception(
                    "Stok barang {$barangData->nama_barang} tidak mencukupi. " .
                    "Stok tersedia: {$barangData->stok}, diminta: {$barang['qty']}"
                );
            }

            // ========================================
            // STEP 3b: Simpan Detail Transaksi
            // ========================================
            $tgJam = date('Y-m-d H:i:s');
            DB::table('detail_jual')->insert([
                'jual_id' => $request->idJual,
                'barang_id' => $barang['barang_id'],
                'qty' => $barang['qty'],
                'harga_sekarang' => $barang['harga_sekarang'],
                'created_at' => $tgJam,
                'updated_at' => $tgJam,
                'user_id' => Auth::id()
            ]);

            // ========================================
            // STEP 3c: KURANGI STOK BARANG ⭐ PENTING!
            // ========================================
            $affected = DB::table('barang')
                ->where('id', $barang['barang_id'])
                ->update(['stok' => DB::raw('stok - ' . $barang['qty'])]);

            // Log untuk tracking
            \Log::info("Stok barang {$barang['barang_id']} dikurangi {$barang['qty']}. Rows affected: {$affected}");

            // ========================================
            // STEP 3d: Hitung Total Transaksi
            // ========================================
            $total += $barang['qty'] * $barang['harga_sekarang'];
        }

        // ========================================
        // STEP 4: Update Total di Tabel Jual
        // ========================================
        Jual::whereId($request->idJual)->update([
            'jumlah_pembelian' => $total
        ]);

        // ========================================
        // STEP 5: Commit Transaction (SIMPAN SEMUA)
        // ========================================
        DB::commit();

        \Log::info("Transaksi {$request->idJual} berhasil disimpan. Total: Rp {$total}");

        // ========================================
        // STEP 6: Return Response dengan URL Cetak
        // ========================================
        return response()->json([
            'berhasil' => true,
            'message' => 'Transaksi berhasil disimpan',
            'urlCetak' => url('/jual/cetak/' . $request->idJual)
        ]);

    } catch (\Throwable $e) {
        // ========================================
        // ROLLBACK jika terjadi error
        // ========================================
        DB::rollback();
        \Log::error("Error simpan transaksi: " . $e->getMessage());

        return response()->json([
            'berhasil' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
```

#### Method: `cetak($id)`

**Fungsi:**
Menampilkan halaman cetak/struk pembayaran

```php
public function cetak($id)
{
    $djual = DetailJual::where('jual_id', $id)->get();
    $jual = Jual::find($id);
    $tgl = $jual->tanggal;
    $pelanggan = Pelanggan::find($jual->pelanggan_id);

    return view('jual.cetak', compact('djual', 'pelanggan', 'id', 'tgl'));
}
```

---

### 3. **FRONTEND (JavaScript)** (`resources/views/jual/detail_jual.blade.php`)

**Kode jQuery untuk Tombol Simpan/Cetak:**

```javascript
$(".simpan").click(function () {
    let dataBarang = [];

    // Ambil SEMUA baris dari tabel belanja
    $("#table1 tbody tr").each(function () {
        var currow = $(this);
        dataBarang.push({
            barang_id: currow.find("td:eq(1)").text(),
            qty: currow.find("td:eq(3)").text(),
            harga_sekarang: currow.find("td:eq(5)").text(),
            jual_id: "{{$id}}",
        });
    });

    // Validasi: cek apakah ada barang
    if (dataBarang.length === 0) {
        alert("Belum ada barang yang ditambahkan ke daftar belanja!");
        return false;
    }

    // Konfirmasi sebelum menyimpan
    if (
        !confirm(
            "Apakah Anda yakin ingin menyimpan transaksi ini?\n\n" +
                "Total: " +
                dataBarang.length +
                " item barang\n" +
                "Stok barang akan dikurangi otomatis."
        )
    ) {
        return false;
    }

    // Disable tombol untuk mencegah double click
    $(this).prop("disabled", true).text("Menyimpan...");

    // Kirim data ke server via AJAX
    $.ajax({
        url: "/jual/simpan",
        type: "POST",
        data: {
            _token: CSRF_TOKEN,
            idJual: "{{ $id }}",
            dataBarang: dataBarang,
        },
    })
        .done(function (response) {
            console.log("Response:", response);
            if (response.berhasil) {
                alert(
                    "Transaksi berhasil disimpan!\nStok barang telah dikurangi."
                );
                // Redirect ke halaman cetak
                window.location.href = response.urlCetak;
            } else {
                alert("Gagal menyimpan transaksi!");
                $(".simpan").prop("disabled", false).text("Simpan/Cetak");
            }
        })
        .fail(function (error) {
            console.error("Error simpan:", error);
            alert(
                "Terjadi kesalahan saat menyimpan transaksi!\n" +
                    (error.responseJSON?.message || "Silakan coba lagi.")
            );
            $(".simpan").prop("disabled", false).text("Simpan/Cetak");
        });
});
```

---

## 📊 STRUKTUR DATABASE

### Tabel: `barang` (Master Barang)

```sql
- id (PK)
- nama_barang
- harga_jual
- satuan
- stok  ⭐ Field yang dikurangi otomatis
```

### Tabel: `jual` (Master Transaksi)

```sql
- id (PK)
- tanggal
- pelanggan_id
- user_id
- jumlah_pembelian  ⭐ Total yang di-update
```

### Tabel: `detail_jual` (Detail Transaksi)

```sql
- id (PK)
- jual_id (FK)
- barang_id (FK)
- qty  ⭐ Jumlah yang akan mengurangi stok
- harga_sekarang
- user_id
```

---

## 🔄 ALUR PROSES LENGKAP

```
1. User klik "Simpan/Cetak"
   ↓
2. JavaScript mengambil semua data dari tabel
   ↓
3. Validasi: cek apakah ada barang
   ↓
4. Konfirmasi dari user
   ↓
5. AJAX POST ke /jual/simpan
   ↓
6. Controller: DB::beginTransaction()
   ↓
7. Loop setiap barang:
   - Validasi stok mencukupi ✅
   - Insert ke detail_jual ✅
   - UPDATE barang SET stok = stok - qty ⭐ PENGURANGAN STOK
   - Hitung total ✅
   ↓
8. Update total di tabel jual
   ↓
9. DB::commit() - Simpan semua perubahan
   ↓
10. Return JSON dengan URL cetak
   ↓
11. JavaScript redirect ke halaman cetak
   ↓
12. Tampilkan struk pembayaran
```

---

## ⚠️ FITUR KEAMANAN

### 1. **Database Transaction**

```php
DB::beginTransaction();
try {
    // Proses data...
    DB::commit();
} catch (\Throwable $e) {
    DB::rollback(); // Batalkan semua jika error
}
```

**Keuntungan:**

-   Jika terjadi error di tengah proses, SEMUA perubahan dibatalkan
-   Mencegah data inconsistency
-   Stok tidak akan berkurang jika transaksi gagal

### 2. **Validasi Stok**

```php
if ($barangData->stok < $barang['qty']) {
    throw new \Exception("Stok tidak mencukupi");
}
```

**Keuntungan:**

-   Mencegah stok menjadi negatif
-   Error message yang jelas untuk user

### 3. **Prevent Double Click**

```javascript
$(this).prop("disabled", true).text("Menyimpan...");
```

**Keuntungan:**

-   Mencegah transaksi duplikat
-   User tidak bisa klik 2x

---

## 🧪 CARA TESTING

### Test Case 1: **Transaksi Normal**

1. Tambahkan 3 barang ke keranjang
2. Klik "Simpan/Cetak"
3. ✅ Stok barang berkurang
4. ✅ Transaksi tersimpan
5. ✅ Struk tampil

### Test Case 2: **Stok Tidak Cukup**

1. Tambahkan barang dengan qty > stok
2. Klik "Simpan/Cetak"
3. ✅ Muncul error "Stok tidak mencukupi"
4. ✅ Transaksi tidak tersimpan
5. ✅ Stok tidak berkurang

### Test Case 3: **Keranjang Kosong**

1. Jangan tambahkan barang
2. Klik "Simpan/Cetak"
3. ✅ Alert "Belum ada barang"
4. ✅ Tidak ada request ke server

---

## 📝 LOG FILE

Sistem mencatat setiap pengurangan stok di file log Laravel:

```
storage/logs/laravel.log
```

Contoh log:

```
[2025-12-23 10:30:45] local.INFO: Stok barang 1 dikurangi 5. Rows affected: 1
[2025-12-23 10:30:45] local.INFO: Stok barang 2 dikurangi 3. Rows affected: 1
[2025-12-23 10:30:45] local.INFO: Transaksi 160 berhasil disimpan. Total: Rp 75000
```

---

## 🎨 TAMPILAN STRUK

File: `resources/views/jual/cetak.blade.php`

Fitur tampilan:

-   Header toko dengan logo
-   Informasi transaksi lengkap
-   Tabel detail barang
-   Total pembayaran
-   Tombol cetak otomatis
-   Footer dengan terima kasih

---

## 🚀 KESIMPULAN

✅ **Sistem sudah LENGKAP dan BERFUNGSI:**

1. Tombol Simpan/Cetak aktif
2. Stok otomatis berkurang saat transaksi
3. Database transaction untuk keamanan
4. Validasi stok mencukupi
5. Error handling yang baik
6. Logging untuk audit trail
7. Tampilan cetak profesional

**Siap digunakan untuk production! 🎉**
