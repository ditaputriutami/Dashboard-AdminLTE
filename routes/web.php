<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\KataBijakController;
use App\Http\Controllers\JenisBarangController;
use App\Http\Controllers\BarangController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\JualController;

/*Route::get('/', function () {
return view('welcome');
});*/

Route::get('/', function () {
    return 'Hello Laravel';
});

Route::get('/dashboard', function () {
    return view('dashboard');
});

Route::get('/user/{nama}', function ($nama) {
    return "Halo, $nama!";
});

Route::get('/produk/{id?}', function ($id = null) {
    return $id ? "Produk ID: $id" : "Tidak ada ID produk";
});

use App\Http\Controllers\HomeController;

Route::get('/home', [HomeController::class, 'index']);

Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return 'Halaman Dashboard Admin';
    });
    Route::get('/laporan', function () {
        return 'Halaman Laporan Admin';
    });
});

Route::get('kata-bijak/kata', [KataBijakController::class, 'kata']);
Route::get('kata-bijak/pepatah', [KataBijakController::class, 'pepatah']);

Route::get('segi-empat/input', [\App\Http\Controllers\SegiEmpatController::class, 'inputSegiEmpat'])->name('segi-empat.inputSegiEmpat');
Route::get('segi-empat/hasil', [\App\Http\Controllers\SegiEmpatController::class, 'hasil'])->name('segi-empat.hasil');

Route::get('segi-empat/input_blk', [\App\Http\Controllers\SegiEmpatController::class, 'inputBalok'])->name('segi-empat.inputSegiEmpat');
Route::get('segi-empat/hasilBalok', [\App\Http\Controllers\SegiEmpatController::class, 'hasilBalok'])->name('segi-empat.hasilBalok');

Route::resource('/jenis-barang', JenisBarangController::class);

Route::resource('barang', BarangController::class);
Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::resource('/jenis-barang', JenisBarangController::class);
    Route::resource('barang', BarangController::class);
    Route::get('/jual/create', [JualController::class, 'create']);
    //Route::get('/pelanggan/{id}', [JualController::class, 'show']);
    Route::post('/bacaPelanggan', [JualController::class, 'getPelanggan']);
    Route::post('/jual/store', [JualController::class, 'store']);
    Route::post('/jual/simpan', [JualController::class, 'simpan']);

    Route::post('/bacaBarang', [JualController::class, 'getBarang']);
    Route::get('/jual/cetak/{id}', [JualController::class, 'cetak']);
    Route::get('/detailJual/{id}', [JualController::class, 'detailJual']);
    Route::get('/jual/detail/{id}', [JualController::class, 'detailJual']); // Alternative route

    // Laporan Rekap Penjualan
    Route::get('/laporan/rekap-penjualan', [JualController::class, 'laporanForm'])->name('laporan.form');
    Route::post('/laporan/cetak-rekap', [JualController::class, 'laporanCetak'])->name('laporan.cetak');

    // Debug: Lihat semua barang
    Route::get('/debug/barang', function () {
        return DB::table('barang')->get();
    });

    // Debug: Test endpoint bacaBarang
    Route::get('/test/barang/{id}', function ($id) {
        $barang = DB::table("barang")
            ->select("id", "nama_barang", "harga_jual as harga", "satuan", "stok")
            ->where("id", $id)
            ->first();
        return response()->json($barang);
    });
});
