# ❓ FAQ - PERTANYAAN YANG SERING DITANYAKAN

## 📚 DAFTAR ISI

1. [Tentang Pengurangan Stok](#pengurangan-stok)
2. [Database Transaction](#database-transaction)
3. [Error Handling](#error-handling)
4. [Performance](#performance)
5. [Keamanan](#keamanan)
6. [Troubleshooting](#troubleshooting)

---

## 🔻 PENGURANGAN STOK

### Q1: Kenapa harus mengurangi stok di backend, tidak di frontend saja?

**A:**

-   ❌ Frontend bisa dimanipulasi user
-   ✅ Backend lebih aman dan terpercaya
-   ✅ Database adalah single source of truth
-   ✅ Mencegah race condition

### Q2: Apa perbedaan antara 2 metode ini?

```php
// Metode 1: DB::raw
DB::table('barang')
    ->where('id', $id)
    ->update(['stok' => DB::raw('stok - ' . $qty)]);

// Metode 2: Eloquent decrement
$barang->decrement('stok', $qty);
```

**A:**

-   **Metode 1** (DB::raw):

    -   ✅ Operasi atomik di database
    -   ✅ Lebih cepat (1 query)
    -   ✅ Tidak perlu fetch model dulu
    -   ❌ Tidak trigger Eloquent events

-   **Metode 2** (decrement):
    -   ✅ Lebih Laravel-style
    -   ✅ Trigger events (updating, updated)
    -   ❌ Butuh 2 query (SELECT + UPDATE)
    -   ❌ Sedikit lebih lambat

**Rekomendasi:** Gunakan Metode 1 untuk performa, Metode 2 jika butuh events.

### Q3: Bagaimana mencegah stok menjadi negatif?

**A:**

```php
// Cara 1: Validasi di aplikasi
if ($barangData->stok < $barang['qty']) {
    throw new \Exception("Stok tidak cukup");
}

// Cara 2: Database constraint
ALTER TABLE barang ADD CONSTRAINT check_stok_positive
CHECK (stok >= 0);

// Cara 3: Kombinasi keduanya (TERBAIK)
```

### Q4: Apakah stok langsung berkurang saat tombol diklik?

**A:**

-   ❌ **TIDAK** langsung saat diklik
-   ✅ Berkurang setelah `DB::commit()` dijalankan
-   ✅ Jika ada error, akan di-rollback
-   ✅ Ini menjaga integritas data

---

## 🔄 DATABASE TRANSACTION

### Q5: Apa itu Database Transaction?

**A:**
Transaction adalah sekumpulan operasi database yang diperlakukan sebagai **satu unit**:

-   ✅ Semua berhasil = COMMIT (disimpan semua)
-   ❌ Ada yang gagal = ROLLBACK (batalkan semua)

**Analogi:** Seperti transfer bank:

-   Uang keluar dari rekening A
-   Uang masuk ke rekening B
-   Jika salah satu gagal, KEDUANYA dibatalkan

### Q6: Kenapa harus pakai Transaction?

**A:**
Tanpa transaction:

```php
// Simpan detail ✅
DB::table('detail_jual')->insert(...);

// Kurangi stok ✅
DB::table('barang')->update(...);

// Update total ❌ ERROR!
Jual::update(...);
// MASALAH: Detail sudah tersimpan, stok sudah berkurang,
// tapi total tidak terupdate! Data jadi tidak konsisten!
```

Dengan transaction:

```php
DB::beginTransaction();
try {
    // Semua operasi...
    DB::commit(); // Semua berhasil
} catch (\Exception $e) {
    DB::rollback(); // Batalkan SEMUA jika ada error
}
```

### Q7: Apakah bisa nested transaction?

**A:**
Laravel **tidak support** native nested transaction, tapi bisa pakai **savepoint**:

```php
DB::transaction(function() {
    // Operasi 1

    DB::transaction(function() {
        // Operasi 2 (nested)
    });
});

// Atau gunakan manual savepoint
DB::beginTransaction();
// ... operasi ...
DB::statement('SAVEPOINT point1');
// ... operasi lagi ...
DB::statement('ROLLBACK TO SAVEPOINT point1'); // Rollback sebagian
DB::commit();
```

---

## ⚠️ ERROR HANDLING

### Q8: Apa yang terjadi jika koneksi database putus saat transaksi?

**A:**

-   ❌ Transaksi otomatis di-rollback oleh database
-   ✅ Tidak ada data yang tersimpan sebagian
-   ✅ Stok tidak akan berkurang
-   ⚠️ User akan melihat error

**Solusi:**

```php
try {
    DB::beginTransaction();
    // ... operasi ...
    DB::commit();
} catch (\Illuminate\Database\QueryException $e) {
    DB::rollback();
    \Log::error("Database error: " . $e->getMessage());
    return response()->json([
        'berhasil' => false,
        'message' => 'Koneksi database bermasalah, silakan coba lagi'
    ], 500);
}
```

### Q9: Bagaimana handle error yang spesifik?

**A:**

```php
catch (\Illuminate\Database\QueryException $e) {
    // Error database (syntax, constraint, dll)

} catch (\Illuminate\Validation\ValidationException $e) {
    // Error validasi input

} catch (\Exception $e) {
    // Error umum lainnya

} catch (\Throwable $e) {
    // Catch ALL (termasuk fatal error)
}
```

---

## 🚀 PERFORMANCE

### Q10: Bagaimana optimize untuk banyak item?

**A:**

```php
// ❌ LAMBAT: Insert satu-satu
foreach ($items as $item) {
    DB::table('detail_jual')->insert([...]);
}

// ✅ CEPAT: Bulk insert
$data = [];
foreach ($items as $item) {
    $data[] = [
        'jual_id' => $jualId,
        'barang_id' => $item['barang_id'],
        'qty' => $item['qty'],
        // ...
    ];
}
DB::table('detail_jual')->insert($data);
```

### Q11: Apakah perlu index database?

**A:** **YA!** Sangat penting:

```sql
-- Index untuk foreign key
ALTER TABLE detail_jual ADD INDEX idx_jual_id (jual_id);
ALTER TABLE detail_jual ADD INDEX idx_barang_id (barang_id);

-- Index untuk pencarian
ALTER TABLE barang ADD INDEX idx_nama (nama_barang);

-- Composite index
ALTER TABLE jual ADD INDEX idx_tanggal_pelanggan (tanggal, pelanggan_id);
```

### Q12: Berapa lama timeout untuk transaksi?

**A:**
Default MySQL: **50 detik**

Jika butuh lebih lama:

```php
// Set timeout 120 detik
DB::statement('SET SESSION wait_timeout = 120');
DB::statement('SET SESSION interactive_timeout = 120');
```

---

## 🔐 KEAMANAN

### Q13: Apakah kode ini aman dari SQL Injection?

**A:**

```php
// ❌ TIDAK AMAN (SQL Injection!)
$sql = "UPDATE barang SET stok = stok - " . $_POST['qty'] .
       " WHERE id = " . $_POST['id'];
DB::statement($sql);

// ✅ AMAN (Parameter Binding)
DB::table('barang')
    ->where('id', $request->barang_id)
    ->update(['stok' => DB::raw('stok - ?')], [$request->qty]);

// ✅ LEBIH AMAN (Validation + Binding)
$validated = $request->validate([
    'qty' => 'required|integer|min:1'
]);
```

### Q14: Bagaimana mencegah user manipulasi qty atau harga?

**A:**

```php
// ❌ JANGAN percaya input user untuk harga!
$harga = $request->harga; // User bisa ubah jadi Rp 1!

// ✅ Selalu ambil harga dari database
$barang = Barang::find($request->barang_id);
$harga = $barang->harga_jual; // Dari database, bukan dari user

// ✅ Validasi qty
$qty = max(1, min(1000, (int)$request->qty)); // Min 1, Max 1000
```

### Q15: Apakah perlu CSRF protection?

**A:** **WAJIB!**

```html
<!-- Di Blade -->
<form method="POST">
    @csrf
    <!-- form fields -->
</form>

<!-- Di AJAX -->
<script>
    $.ajax({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        // ...
    });
</script>
```

---

## 🔧 TROUBLESHOOTING

### Q16: Error "Class 'DB' not found"

**A:**

```php
// Tambahkan di top file
use Illuminate\Support\Facades\DB;
```

### Q17: Transaction tidak rollback saat error

**A:**
Cek apakah menggunakan `try-catch`:

```php
// ❌ Tanpa try-catch, error langsung ke user
DB::beginTransaction();
// ... code ...
DB::commit();

// ✅ Dengan try-catch, bisa rollback
DB::beginTransaction();
try {
    // ... code ...
    DB::commit();
} catch (\Exception $e) {
    DB::rollback(); // PENTING!
    throw $e;
}
```

### Q18: Stok berkurang 2x saat klik 1x

**A:**
Penyebab: Double submit

Solusi:

```javascript
// Disable tombol setelah klik
$(".simpan").click(function () {
    $(this).prop("disabled", true); // ⭐ PENTING
    // ... ajax ...
});

// Atau gunakan flag
let isSubmitting = false;
$(".simpan").click(function () {
    if (isSubmitting) return;
    isSubmitting = true;
    // ... ajax ...
});
```

### Q19: Error "SQLSTATE[HY000]: General error: 1205 Lock wait timeout"

**A:**
Penyebab: Transaction terlalu lama / deadlock

Solusi:

```php
// 1. Perkecil transaction scope
DB::beginTransaction();
try {
    // Hanya operasi critical
    DB::commit();
} catch...

// 2. Gunakan lockForUpdate hati-hati
$barang = Barang::lockForUpdate()->find($id); // Lock baris

// 3. Tingkatkan timeout (temporary)
DB::statement('SET SESSION innodb_lock_wait_timeout = 120');
```

### Q20: Data tidak tersimpan tapi tidak ada error

**A:**
Kemungkinan penyebab:

1. Transaction tidak di-commit
2. Silent failure (error di-catch tapi tidak di-throw)
3. Middleware yang mengubah response

Debug:

```php
// Tambahkan logging di setiap step
\Log::info('Before insert');
DB::table('detail_jual')->insert(...);
\Log::info('After insert');

DB::commit();
\Log::info('After commit');
```

---

## 💡 TIPS & BEST PRACTICES

### Tip 1: Gunakan Eloquent Events untuk Audit

```php
// Di Model Barang
protected static function booted()
{
    static::updating(function ($barang) {
        if ($barang->isDirty('stok')) {
            \Log::info("Stok {$barang->nama_barang} berubah dari {$barang->getOriginal('stok')} ke {$barang->stok}");
        }
    });
}
```

### Tip 2: Buat Backup Sebelum Update Stok

```php
// Simpan snapshot stok sebelum transaksi
DB::table('stok_history')->insert([
    'barang_id' => $barangId,
    'stok_sebelum' => $barang->stok,
    'stok_sesudah' => $barang->stok - $qty,
    'transaksi_id' => $jualId,
    'created_at' => now()
]);
```

### Tip 3: Gunakan Queue untuk Transaksi Besar

```php
// Jika ada lebih dari 100 item
if (count($items) > 100) {
    ProcessTransactionJob::dispatch($jualId, $items);
    return response()->json(['message' => 'Transaksi sedang diproses...']);
}
```

---

## 📚 RESOURCES

-   [Laravel Database Transactions](https://laravel.com/docs/database#database-transactions)
-   [Eloquent ORM](https://laravel.com/docs/eloquent)
-   [Query Builder](https://laravel.com/docs/queries)
-   [Validation](https://laravel.com/docs/validation)

---

**Masih ada pertanyaan? Silakan hubungi tim development! 🚀**
