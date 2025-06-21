<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SifatSurat;
use Illuminate\Http\Request;

class SifatSuratController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sifatSurat = SifatSurat::latest()->paginate(10);
        return view('admin.sifat_surat.index', compact('sifatSurat'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.sifat_surat.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_sifat' => 'required|string|max:255|unique:sifat_surat',
        ]);

        SifatSurat::create($request->all());

        return redirect()->route('admin.sifat-surat.index')->with('success', 'Sifat Surat berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SifatSurat $sifatSurat)
    {
        return view('admin.sifat_surat.edit', compact('sifatSurat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SifatSurat $sifatSurat)
    {
        $request->validate([
            'nama_sifat' => 'required|string|max:255|unique:sifat_surat,nama_sifat,' . $sifatSurat->id,
        ]);

        $sifatSurat->update($request->all());

        return redirect()->route('admin.sifat-surat.index')->with('success', 'Sifat Surat berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SifatSurat $sifatSurat)
    {
        // Periksa apakah ada surat yang masih menggunakan sifat ini
        if ($sifatSurat->suratMasuk()->count() > 0 || $sifatSurat->suratKeluar()->count() > 0) {
            return redirect()->route('admin.sifat-surat.index')->with('error', 'Tidak bisa menghapus Sifat Surat ini karena masih digunakan oleh beberapa surat.');
        }

        $sifatSurat->delete();
        return redirect()->route('admin.sifat-surat.index')->with('success', 'Sifat Surat berhasil dihapus.');
    }
}