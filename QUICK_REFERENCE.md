# 🚀 QUICK REFERENCE - SIMPAN/CETAK TRANSAKSI

## 📦 FILE YANG TERLIBAT

```
app/Http/Controllers/JualController.php  ← Logic utama
routes/web.php                            ← Route definition
resources/views/jual/detail_jual.blade.php ← Form input + JavaScript
resources/views/jual/cetak.blade.php      ← Tampilan struk
```

---

## ⚡ KODE PENTING (COPY-PASTE READY)

### 1️⃣ ROUTE (routes/web.php)

```php
Route::post('/jual/simpan', [JualController::class, 'simpan']);
Route::get('/jual/cetak/{id}', [JualController::class, 'cetak']);
```

### 2️⃣ CONTROLLER METHOD (JualController.php)

```php
public function simpan(Request $request)
{
    DB::beginTransaction();
    try {
        if (empty($request->dataBarang)) {
            return response()->json(['berhasil' => false, 'message' => 'Data kosong'], 400);
        }

        $total = 0;
        foreach ($request->dataBarang as $barang) {
            $barangData = Barang::find($barang['barang_id']);

            if ($barangData->stok < $barang['qty']) {
                throw new \Exception("Stok {$barangData->nama_barang} tidak cukup");
            }

            DB::table('detail_jual')->insert([
                'jual_id' => $request->idJual,
                'barang_id' => $barang['barang_id'],
                'qty' => $barang['qty'],
                'harga_sekarang' => $barang['harga_sekarang'],
                'created_at' => now(),
                'user_id' => Auth::id()
            ]);

            // ⭐ PENGURANGAN STOK
            DB::table('barang')
                ->where('id', $barang['barang_id'])
                ->update(['stok' => DB::raw('stok - ' . $barang['qty'])]);

            $total += $barang['qty'] * $barang['harga_sekarang'];
        }

        Jual::whereId($request->idJual)->update(['jumlah_pembelian' => $total]);
        DB::commit();

        return response()->json([
            'berhasil' => true,
            'urlCetak' => url('/jual/cetak/' . $request->idJual)
        ]);
    } catch (\Throwable $e) {
        DB::rollback();
        return response()->json(['berhasil' => false, 'message' => $e->getMessage()], 500);
    }
}
```

### 3️⃣ JAVASCRIPT (detail_jual.blade.php)

```javascript
$(".simpan").click(function () {
    let dataBarang = [];

    $("#table1 tbody tr").each(function () {
        var row = $(this);
        dataBarang.push({
            barang_id: row.find("td:eq(1)").text().trim(),
            qty: row.find("td:eq(3)").text().trim(),
            harga_sekarang: row.find("td:eq(5)").text().trim(),
        });
    });

    if (dataBarang.length === 0) {
        alert("Keranjang kosong!");
        return;
    }

    if (!confirm("Simpan transaksi?\nStok akan dikurangi otomatis.")) {
        return;
    }

    $(this).prop("disabled", true).text("Menyimpan...");

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
            if (response.berhasil) {
                alert("✅ Berhasil!");
                window.location.href = response.urlCetak;
            }
        })
        .fail(function (xhr) {
            alert("❌ " + (xhr.responseJSON?.message || "Error!"));
            $(".simpan").prop("disabled", false).text("Simpan/Cetak");
        });
});
```

---

## 🎯 ALUR SINGKAT

```
User klik "Simpan/Cetak"
    ↓
Ambil data dari tabel (#table1)
    ↓
Validasi (keranjang tidak kosong)
    ↓
Konfirmasi user
    ↓
AJAX POST ke /jual/simpan
    ↓
Controller: DB::beginTransaction()
    ↓
Loop setiap barang:
  - Validasi stok ✓
  - Insert detail_jual ✓
  - UPDATE stok = stok - qty ⭐
  - Hitung total ✓
    ↓
Update jumlah_pembelian
    ↓
DB::commit()
    ↓
Return JSON: {berhasil: true, urlCetak: ...}
    ↓
JavaScript redirect ke halaman cetak
    ↓
Tampilkan struk ✅
```

---

## 🔥 COMMAND PENTING

```bash
# Jalankan server
php artisan serve

# Cek route
php artisan route:list | grep jual

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Lihat log realtime
tail -f storage/logs/laravel.log

# Database
php artisan migrate
php artisan db:seed
```

---

## 🗄️ STRUKTUR DATABASE

```sql
-- Tabel barang
CREATE TABLE barang (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_barang VARCHAR(255),
    harga_jual DECIMAL(10,2),
    satuan VARCHAR(50),
    stok INT DEFAULT 0  -- ⭐ Field yang dikurangi
);

-- Tabel jual
CREATE TABLE jual (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tanggal DATE,
    pelanggan_id INT,
    user_id INT,
    jumlah_pembelian DECIMAL(12,2)  -- ⭐ Total yang di-update
);

-- Tabel detail_jual
CREATE TABLE detail_jual (
    id INT PRIMARY KEY AUTO_INCREMENT,
    jual_id INT,  -- FK ke tabel jual
    barang_id INT,  -- FK ke tabel barang
    qty INT,  -- ⭐ Qty yang mengurangi stok
    harga_sekarang DECIMAL(10,2),
    created_at TIMESTAMP
);
```

---

## ✅ CHECKLIST IMPLEMENTASI

-   [ ] Route `/jual/simpan` sudah terdaftar
-   [ ] Route `/jual/cetak/{id}` sudah terdaftar
-   [ ] Method `simpan()` di Controller sudah ada
-   [ ] Method `cetak()` di Controller sudah ada
-   [ ] JavaScript tombol simpan sudah connect
-   [ ] Validasi stok sudah aktif
-   [ ] DB::transaction sudah dipakai
-   [ ] Error handling sudah lengkap
-   [ ] View cetak.blade.php sudah ada
-   [ ] Testing sudah dilakukan

---

## 🐛 TROUBLESHOOTING CEPAT

| Problem              | Solution                                      |
| -------------------- | --------------------------------------------- |
| Tombol tidak klik    | Cek jQuery loaded, cek selector `.simpan`     |
| 404 Not Found        | Cek route: `php artisan route:list`           |
| 500 Error            | Cek log: `storage/logs/laravel.log`           |
| CSRF Token mismatch  | Pastikan ada `@csrf` atau header X-CSRF-TOKEN |
| Stok tidak berkurang | Cek query UPDATE, cek `DB::commit()`          |
| Data tidak tersimpan | Cek transaction rollback, cek error di log    |

---

## 📊 QUERY CEK HASIL

```sql
-- Cek stok barang
SELECT id, nama_barang, stok FROM barang;

-- Cek transaksi
SELECT * FROM jual WHERE id = 160;

-- Cek detail transaksi
SELECT dj.*, b.nama_barang
FROM detail_jual dj
JOIN barang b ON dj.barang_id = b.id
WHERE dj.jual_id = 160;

-- Cek total
SELECT
    j.id,
    j.jumlah_pembelian,
    SUM(dj.qty * dj.harga_sekarang) as total_calculated
FROM jual j
LEFT JOIN detail_jual dj ON j.id = dj.jual_id
WHERE j.id = 160
GROUP BY j.id;
```

---

## 🎨 UI/UX TIPS

```javascript
// Loading indicator
Swal.fire({
    title: "Menyimpan...",
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
});

// Success message
Swal.fire({
    icon: "success",
    title: "Berhasil!",
    text: "Transaksi tersimpan",
    timer: 1500,
});

// Error message
Swal.fire({
    icon: "error",
    title: "Gagal!",
    text: "Stok tidak cukup",
});
```

---

## 🔐 SECURITY CHECKLIST

-   [ ] CSRF Protection aktif
-   [ ] Input validation (qty, harga)
-   [ ] SQL Injection prevention (parameter binding)
-   [ ] Authorization (user login check)
-   [ ] Harga dari database, bukan dari input user
-   [ ] Validasi stok sebelum transaksi
-   [ ] Transaction rollback jika error

---

## 📈 OPTIMIZATION

```php
// Bulk insert (untuk banyak item)
$detailData = [];
foreach ($items as $item) {
    $detailData[] = [
        'jual_id' => $jualId,
        'barang_id' => $item['id'],
        'qty' => $item['qty'],
        'harga_sekarang' => $item['harga']
    ];
}
DB::table('detail_jual')->insert($detailData);

// Eager loading (untuk view cetak)
$djual = DetailJual::with('barang')->where('jual_id', $id)->get();

// Index database
ALTER TABLE detail_jual ADD INDEX idx_jual_id (jual_id);
```

---

## 📞 SUPPORT

-   **Documentation:** `DOKUMENTASI_SIMPAN_CETAK.md`
-   **Examples:** `CONTROLLER_EXAMPLE.php`, `JAVASCRIPT_EXAMPLE.js`
-   **Testing:** `TESTING_GUIDE.md`
-   **FAQ:** `FAQ.md`

---

**System Ready! 🚀**
