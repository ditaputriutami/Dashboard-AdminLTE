<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use App\Models\JenisBarang;

class BarangController extends Controller
{
    public function index()
    {
        $barang = Barang::paginate(10);
        return view('barang.index', compact('barang'));
    }
    public function create()
    {
        $jenisBarang = JenisBarang::all(); // ambil untuk dropdown
        return view('barang.create', compact('jenisBarang'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|unique:barang,id',
            'jenis_barang_id' => 'required|integer|exists:jenis_barang,id',
            'nama_barang' => 'required|string|max:100',
            'satuan' => 'required|string|max:75',
            'harga_pokok' => 'required|integer|min:0',
            'harga_jual' => 'required|integer|min:0',
            'stok' => 'required|integer|min:0',
        ]);
        try {
            Barang::create($request->all());
            return redirect()->route('barang.index')
                ->with('success', 'Barang berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menyimpan barang: ' . $e->getMessage());
        }
    }
    public function show(Barang $barang)
    {
        $barang->load('jenis'); // agar relasi jenis_barang ikut dimuat
        return view('barang.show', compact('barang'));
    }
    public function edit(string $id)
    {
        $jenisBarang = JenisBarang::all();
        $barang = Barang::find($id);
        return view('barang.edit', compact('barang', 'jenisBarang'));
    }
    public function update(Request $request, Barang $barang)
    {
        // $request->validate([
        //     'id' => 'required|integer',
        //     'jenis_barang_id' => 'required|integer',
        //     'nama_barang' => 'required|string|max:100',
        //     'satuan' => 'required|string|max:75',
        //     'harga_pokok' => 'required|integer|min:0',
        //     'harga_jual' => 'required|integer|min:0',
        //     'stok' => 'required|integer|min:0',
        // ]);
        $barang->update($request->all());
        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil diperbarui!');
    }
    public function destroy(string $id)
    {
        // $barang->delete();
        Barang::find($id)->delete();
        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil dihapus!');
    }
    public function trans()
    {
        return "Masih dalam pengembangan";
    }
}
