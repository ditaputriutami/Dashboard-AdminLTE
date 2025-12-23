<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\DB;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Hanya create user jika belum ada
        if (!User::where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Tambah sample jenis barang
        if (DB::table('jenis_barang')->count() == 0) {
            DB::table('jenis_barang')->insert([
                ['nama_jenis' => 'Elektronik'],
                ['nama_jenis' => 'Makanan'],
                ['nama_jenis' => 'Minuman'],
            ]);
        }

        // Tambah sample barang
        if (DB::table('barang')->count() == 0) {
            DB::table('barang')->insert([
                [
                    'id' => 1,
                    'jenis_barang_id' => 1,
                    'nama_barang' => 'Lampu LED 10W',
                    'satuan' => 'Pcs',
                    'harga_pokok' => 50000,
                    'harga_jual' => 75000,
                    'stok' => 50,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 2,
                    'jenis_barang_id' => 1,
                    'nama_barang' => 'Charger USB-C',
                    'satuan' => 'Pcs',
                    'harga_pokok' => 40000,
                    'harga_jual' => 60000,
                    'stok' => 30,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 3,
                    'jenis_barang_id' => 2,
                    'nama_barang' => 'Kacang Goreng 200g',
                    'satuan' => 'Box',
                    'harga_pokok' => 15000,
                    'harga_jual' => 25000,
                    'stok' => 100,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 4,
                    'jenis_barang_id' => 3,
                    'nama_barang' => 'Air Mineral 600ml',
                    'satuan' => 'Botol',
                    'harga_pokok' => 3000,
                    'harga_jual' => 5000,
                    'stok' => 200,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // Tambah sample pelanggan
        if (DB::table('pelanggan')->count() == 0) {
            DB::table('pelanggan')->insert([
                [
                    'id' => 2,
                    'nama_pelanggan' => 'Badiyanto',
                    'jenis_kelamin' => 'L',
                    'alamat' => 'Bantul',
                    'telp_hp' => '23344',
                    'email' => 'aaae@gm.com',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 3,
                    'nama_pelanggan' => 'Dewi Rachmawati',
                    'jenis_kelamin' => 'F',
                    'alamat' => 'Bantul',
                    'telp_hp' => '084555555',
                    'email' => 'dewi@gmail.com',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 4,
                    'nama_pelanggan' => 'Mustikawati',
                    'jenis_kelamin' => 'F',
                    'alamat' => 'Bantul',
                    'telp_hp' => '088333',
                    'email' => 'mustka@gmail.com',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // Tambah sample data transaksi jual
        if (DB::table('jual')->count() == 0) {
            DB::table('jual')->insert([
                [
                    'id' => 148,
                    'tanggal' => '2022-07-13',
                    'pelanggan_id' => 2,
                    'user_id' => 1,
                    'jumlah_pembelian' => 12000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 149,
                    'tanggal' => '2022-07-13',
                    'pelanggan_id' => 3,
                    'user_id' => 1,
                    'jumlah_pembelian' => 230000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 150,
                    'tanggal' => '2022-07-13',
                    'pelanggan_id' => 2,
                    'user_id' => 1,
                    'jumlah_pembelian' => 20000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 151,
                    'tanggal' => '2022-07-13',
                    'pelanggan_id' => 3,
                    'user_id' => 1,
                    'jumlah_pembelian' => 18000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 152,
                    'tanggal' => '2022-07-14',
                    'pelanggan_id' => 2,
                    'user_id' => 1,
                    'jumlah_pembelian' => 12000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 153,
                    'tanggal' => '2022-07-14',
                    'pelanggan_id' => 3,
                    'user_id' => 1,
                    'jumlah_pembelian' => 230000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => 154,
                    'tanggal' => '2022-07-14',
                    'pelanggan_id' => 2,
                    'user_id' => 1,
                    'jumlah_pembelian' => 18000,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
