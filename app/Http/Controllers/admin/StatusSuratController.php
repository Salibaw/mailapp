<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StatusSurat;
use Illuminate\Http\Request;

class StatusSuratController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $statusSurat = StatusSurat::latest()->paginate(10);
        return view('admin.status_surat.index', compact('statusSurat'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.status_surat.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_status' => 'required|string|max:255|unique:status_surat',
        ]);

        StatusSurat::create($request->all());

        return redirect()->route('admin.status-surat.index')->with('success', 'Status Surat berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StatusSurat $statusSurat)
    {
        return view('admin.status_surat.edit', compact('statusSurat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, StatusSurat $statusSurat)
    {
        $request->validate([
            'nama_status' => 'required|string|max:255|unique:status_surat,nama_status,' . $statusSurat->id,
        ]);

        $statusSurat->update($request->all());

        return redirect()->route('admin.status-surat.index')->with('success', 'Status Surat berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(StatusSurat $statusSurat)
    {
        // Periksa apakah ada surat yang masih menggunakan status ini
        if ($statusSurat->suratMasuk()->count() > 0 || $statusSurat->suratKeluar()->count() > 0) {
            return redirect()->route('admin.status-surat.index')->with('error', 'Tidak bisa menghapus Status Surat ini karena masih digunakan oleh beberapa surat.');
        }

        $statusSurat->delete();
        return redirect()->route('admin.status-surat.index')->with('success', 'Status Surat berhasil dihapus.');
    }
}