# 🧪 PANDUAN TESTING FITUR SIMPAN/CETAK

## 📋 CHECKLIST TESTING

### ✅ PRE-REQUISITES

-   [ ] Database sudah ter-migrate
-   [ ] Tabel `barang` memiliki data dengan stok > 0
-   [ ] Tabel `pelanggan` memiliki data
-   [ ] User sudah login
-   [ ] Server Laravel sudah running (`php artisan serve`)

---

## 🎯 TEST CASE 1: TRANSAKSI NORMAL (HAPPY PATH)

### Langkah-langkah:

1. **Akses halaman transaksi**

    ```
    http://127.0.0.1:8000/jual/create
    ```

2. **Pilih pelanggan**

    - Masukkan ID pelanggan
    - Klik "Lanjutkan"

3. **Tambahkan barang ke keranjang**

    - Masukkan kode barang (misal: 1)
    - Tekan ENTER
    - Masukkan qty (misal: 5)
    - Klik "Tambah"
    - Ulangi untuk 2-3 barang lainnya

4. **Cek tampilan tabel**

    - [ ] Tabel "Daftar Belanja" terisi
    - [ ] Total harga terhitung

5. **Klik tombol "Simpan/Cetak"**

    - [ ] Muncul konfirmasi
    - [ ] Klik "OK"

6. **Cek hasil:**

    - [ ] Alert "Transaksi berhasil"
    - [ ] Redirect ke halaman cetak
    - [ ] Struk tampil dengan benar

7. **Verifikasi database:**

    ```sql
    -- Cek stok berkurang
    SELECT id, nama_barang, stok FROM barang WHERE id = 1;

    -- Cek detail transaksi tersimpan
    SELECT * FROM detail_jual WHERE jual_id = 160;

    -- Cek total di master
    SELECT id, jumlah_pembelian FROM jual WHERE id = 160;
    ```

### Expected Result:

✅ Stok barang berkurang sesuai qty
✅ Data tersimpan di database
✅ Struk tampil dengan benar

---

## ⚠️ TEST CASE 2: STOK TIDAK CUKUP

### Setup:

1. Pastikan ada barang dengan stok < 10 (misal: Barang ID=5, stok=3)

### Langkah-langkah:

1. Buat transaksi baru
2. Tambahkan Barang ID=5 dengan qty=10 (lebih dari stok)
3. Klik "Simpan/Cetak"

### Expected Result:

❌ Muncul error: "Stok barang [Nama Barang] tidak mencukupi. Stok tersedia: 3, diminta: 10"
✅ Transaksi tidak tersimpan
✅ Stok tidak berkurang

### Verifikasi:

```sql
-- Stok tidak berubah
SELECT stok FROM barang WHERE id = 5;
```

---

## 🚫 TEST CASE 3: KERANJANG KOSONG

### Langkah-langkah:

1. Buat transaksi baru
2. **Jangan tambahkan barang** ke keranjang
3. Langsung klik "Simpan/Cetak"

### Expected Result:

⚠️ Muncul alert: "Belum ada barang yang ditambahkan ke daftar belanja!"
✅ Tidak ada request ke server
✅ Tidak ada data tersimpan

---

## 🔄 TEST CASE 4: DOUBLE CLICK PREVENTION

### Langkah-langkah:

1. Buat transaksi normal
2. Tambahkan beberapa barang
3. Klik tombol "Simpan/Cetak" **2x dengan cepat**

### Expected Result:

✅ Tombol langsung disabled setelah klik pertama
✅ Hanya 1 transaksi yang tersimpan
✅ Tidak ada duplikat data

### Verifikasi:

```sql
-- Cek hanya ada 1 set detail
SELECT COUNT(*) FROM detail_jual WHERE jual_id = 160;
```

---

## 💾 TEST CASE 5: DATABASE TRANSACTION (ROLLBACK)

### Setup:

1. Buka file `JualController.php`
2. Tambahkan `throw new \Exception('Test rollback');` di tengah loop

### Langkah-langkah:

1. Tambahkan 3 barang ke keranjang
2. Klik "Simpan/Cetak"

### Expected Result:

❌ Muncul error
✅ **SEMUA** perubahan di-rollback
✅ Stok tidak berkurang sama sekali
✅ Detail transaksi tidak tersimpan

### Verifikasi:

```sql
-- Tidak ada data detail
SELECT COUNT(*) FROM detail_jual WHERE jual_id = 160;
-- Result: 0

-- Stok tidak berubah
SELECT stok FROM barang WHERE id IN (1, 2, 3);
```

---

## 🔍 TEST CASE 6: VALIDASI BARANG TIDAK DITEMUKAN

### Setup:

1. Edit JavaScript, ubah barang_id menjadi ID yang tidak ada
2. Atau hapus barang dari database setelah ditambahkan ke keranjang

### Expected Result:

❌ Error: "Barang dengan ID [XX] tidak ditemukan"
✅ Transaction rollback
✅ Data tidak tersimpan

---

## 🖨️ TEST CASE 7: HALAMAN CETAK

### Langkah-langkah:

1. Setelah transaksi berhasil, cek halaman cetak

### Checklist:

-   [ ] Header toko tampil
-   [ ] Nomor transaksi benar
-   [ ] Tanggal tampil
-   [ ] Nama pelanggan tampil
-   [ ] Daftar barang lengkap
-   [ ] Qty dan harga benar
-   [ ] Total benar
-   [ ] Tombol "Cetak Struk" berfungsi
-   [ ] Tombol "Kembali" berfungsi

### Test Print:

-   [ ] Klik tombol "Cetak Struk"
-   [ ] Print preview muncul
-   [ ] Tombol tidak tampil di print preview
-   [ ] Layout rapi

---

## 📊 TEST CASE 8: LOGGING

### Langkah-langkah:

1. Buat transaksi normal
2. Cek file log

### Lokasi log:

```
storage/logs/laravel.log
```

### Expected Content:

```
[2025-12-23 10:30:45] local.INFO: ✅ Stok barang ID:1 dikurangi 5. Rows affected: 1
[2025-12-23 10:30:45] local.INFO: ✅ Stok barang ID:2 dikurangi 3. Rows affected: 1
[2025-12-23 10:30:45] local.INFO: ✅ Transaksi #160 berhasil disimpan. Total: Rp 75000
```

---

## 🔐 TEST CASE 9: AUTHORIZATION

### Langkah-langkah:

1. Logout dari aplikasi
2. Akses URL langsung:
    ```
    http://127.0.0.1:8000/jual/create
    ```

### Expected Result:

↪️ Redirect ke halaman login
✅ Tidak bisa akses tanpa login

---

## 📈 TEST CASE 10: CONCURRENT TRANSACTIONS

### Setup:

1. Buka 2 browser berbeda
2. Login sebagai user berbeda di masing-masing browser

### Langkah-langkah:

1. **Browser 1**: Buat transaksi, tambahkan Barang ID=1, qty=5
2. **Browser 2**: Buat transaksi, tambahkan Barang ID=1, qty=8
3. **Browser 1**: Klik "Simpan/Cetak" ✅
4. **Browser 2**: Klik "Simpan/Cetak" (seharusnya gagal jika stok tidak cukup)

### Expected Result:

✅ Transaksi pertama berhasil
⚠️ Transaksi kedua cek stok terbaru
✅ Stok konsisten

---

## 🛠️ DEBUGGING TOOLS

### 1. Browser Console

```javascript
// Cek data yang dikirim
console.log("Data barang:", dataBarang);
```

### 2. Network Tab (DevTools)

-   Cek request payload
-   Cek response
-   Cek status code (200 = OK, 500 = Error)

### 3. Laravel Log

```bash
# Realtime log monitoring
tail -f storage/logs/laravel.log
```

### 4. Database Query Log

Tambahkan di `AppServiceProvider.php`:

```php
\DB::listen(function($query) {
    \Log::info($query->sql, $query->bindings);
});
```

### 5. Laravel Debugbar

```bash
composer require barryvdh/laravel-debugbar --dev
```

---

## 📝 CHECKLIST SEBELUM PRODUCTION

-   [ ] Semua test case di atas PASS
-   [ ] Error handling lengkap
-   [ ] Validasi input aktif
-   [ ] Logging aktif
-   [ ] Database transaction berfungsi
-   [ ] UI/UX user-friendly
-   [ ] Performance test (1000+ transaksi)
-   [ ] Backup database sebelum deploy
-   [ ] Documentation lengkap
-   [ ] Code review selesai

---

## 🎓 TIPS TESTING

1. **Test dengan data real**: Jangan pakai data dummy semua
2. **Test edge cases**: Qty = 0, qty negatif, qty sangat besar
3. **Test berbagai browser**: Chrome, Firefox, Safari, Edge
4. **Test mobile**: Responsive design
5. **Load testing**: Banyak transaksi bersamaan
6. **Security testing**: SQL Injection, XSS
7. **Backup data**: Sebelum testing yang destructive

---

## 📞 TROUBLESHOOTING

### Problem: Tombol tidak merespon

**Solution:**

-   Cek console browser untuk error JavaScript
-   Pastikan jQuery loaded
-   Cek selector CSS `.simpan` sudah benar

### Problem: Data tidak terkirim

**Solution:**

-   Cek CSRF token
-   Cek network tab untuk request
-   Cek route terdaftar: `php artisan route:list | grep simpan`

### Problem: Stok tidak berkurang

**Solution:**

-   Cek query di controller
-   Cek log file
-   Cek transaction di-commit
-   Cek field name `stok` di database

### Problem: Error 500

**Solution:**

-   Cek `storage/logs/laravel.log`
-   Enable debug mode di `.env`: `APP_DEBUG=true`
-   Cek database connection

---

## ✅ FINAL CHECKLIST

Setelah semua test pass:

-   [ ] Commit changes
-   [ ] Update documentation
-   [ ] Demo ke stakeholder
-   [ ] Deploy ke staging
-   [ ] User acceptance testing
-   [ ] Deploy ke production
-   [ ] Monitor 24 jam pertama

**SISTEM READY! 🚀**
