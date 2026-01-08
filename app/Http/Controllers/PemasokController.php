<?php

namespace App\Http\Controllers;

use App\Models\Pemasok;
use Illuminate\Http\Request;

class PemasokController extends Controller
{
    public function index()
    {
        $pemasok = Pemasok::all();
        return view('pemasok.index', compact('pemasok'));
    }

    public function create()
    {
        return view('pemasok.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pemasok' => 'required|max:100',
            'alamat' => 'nullable|max:100',
            'tlp' => 'nullable|max:50',
        ]);

        Pemasok::create($request->only(['nama_pemasok', 'alamat', 'tlp']));

        return redirect()->route('pemasok.index')
            ->with('success', 'Pemasok berhasil ditambahkan');
    }

    public function show(Pemasok $pemasok)
    {
        return view('pemasok.show', compact('pemasok'));
    }

    public function edit(Pemasok $pemasok)
    {
        return view('pemasok.edit', compact('pemasok'));
    }

    public function update(Request $request, Pemasok $pemasok)
    {
        $request->validate([
            'nama_pemasok' => 'required|max:100',
            'alamat' => 'nullable|max:100',
            'tlp' => 'nullable|max:50',
        ]);

        $pemasok->update($request->only(['nama_pemasok', 'alamat', 'tlp']));

        return redirect()->route('pemasok.index')
            ->with('success', 'Pemasok berhasil diperbarui');
    }

    public function destroy(Pemasok $pemasok)
    {
        $pemasok->delete();

        return redirect()->route('pemasok.index')
            ->with('success', 'Pemasok berhasil dihapus');
    }
}
