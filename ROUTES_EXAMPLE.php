/**
* ========================================
* ROUTE DEFINITIONS
* ========================================
* File: routes/web.php
*/

use App\Http\Controllers\JualController;

Route::middleware(['auth'])->group(function () {

// ============================================
// ROUTE TRANSAKSI PENJUALAN
// ============================================

// Halaman form pelanggan
Route::get('/jual/create', [JualController::class, 'create'])
->name('jual.create');

// Simpan data pelanggan
Route::post('/jual/store', [JualController::class, 'store'])
->name('jual.store');

// Halaman detail jual (input barang)
Route::get('/detailJual/{id}', [JualController::class, 'detailJual'])
->name('jual.detail');

// ⭐ ROUTE UTAMA: SIMPAN & KURANGI STOK
Route::post('/jual/simpan', [JualController::class, 'simpan'])
->name('jual.simpan');

// ⭐ ROUTE CETAK STRUK
Route::get('/jual/cetak/{id}', [JualController::class, 'cetak'])
->name('jual.cetak');

// ============================================
// ROUTE AJAX UNTUK AUTOCOMPLETE
// ============================================

// Baca data pelanggan
Route::post('/bacaPelanggan', [JualController::class, 'getPelanggan']);

// Baca data barang
Route::post('/bacaBarang', [JualController::class, 'getBarang']);
});


/**
* ========================================
* ALTERNATIVE ROUTE (Opsional)
* ========================================
* Jika ingin menggunakan RESTful naming:
*/

// Cara 1: Menggunakan route resource
Route::resource('transaksi', JualController::class);

// Cara 2: Custom route dengan naming yang jelas
Route::post('/transaksi/simpan-cetak/{id}', [JualController::class, 'prosesSimpanCetak'])
->name('transaksi.simpan-cetak');

// Cara 3: Route group untuk organisasi lebih baik
Route::prefix('transaksi')->name('transaksi.')->group(function () {
Route::get('/create', [JualController::class, 'create'])->name('create');
Route::post('/store', [JualController::class, 'store'])->name('store');
Route::get('/detail/{id}', [JualController::class, 'detailJual'])->name('detail');
Route::post('/simpan', [JualController::class, 'simpan'])->name('simpan');
Route::get('/cetak/{id}', [JualController::class, 'cetak'])->name('cetak');
});