<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $templates = TemplateSurat::with('user')->latest()->paginate(10);
        return view('admin.template_surat.index', compact('templates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.template_surat.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_template' => 'required|string|max:255|unique:template_surat',
            'jenis_surat' => 'required|string|max:255',
            'isi_template' => 'required|string',
        ]);

        TemplateSurat::create([
            'nama_template' => $request->nama_template,
            'jenis_surat' => $request->jenis_surat,
            'isi_template' => $request->isi_template,
            'user_id' => Auth::id(), // Otomatis mengisi ID user yang membuat template
        ]);

        return redirect()->route('admin.template-surat.index')->with('success', 'Template Surat berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TemplateSurat $templateSurat)
    {
        return view('admin.template_surat.show', compact('templateSurat'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TemplateSurat $templateSurat)
    {
        return view('admin.template_surat.edit', compact('templateSurat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TemplateSurat $templateSurat)
    {
        $request->validate([
            'nama_template' => ['required', 'string', 'max:255', Rule::unique('template_surat')->ignore($templateSurat->id)],
            'jenis_surat' => 'required|string|max:255',
            'isi_template' => 'required|string',
        ]);

        $templateSurat->update($request->all());

        return redirect()->route('admin.template-surat.index')->with('success', 'Template Surat berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TemplateSurat $templateSurat)
    {
        // Periksa apakah ada surat keluar yang masih menggunakan template ini
        if ($templateSurat->suratKeluar()->count() > 0) {
            return redirect()->route('admin.template-surat.index')->with('error', 'Tidak bisa menghapus Template Surat ini karena masih digunakan oleh beberapa surat keluar.');
        }

        $templateSurat->delete();
        return redirect()->route('admin.template-surat.index')->with('success', 'Template Surat berhasil dihapus.');
    }
}