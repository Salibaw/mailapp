<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $templateSurat = TemplateSurat::select(['id', 'nama_template', 'konten', 'tipe']);
            return datatables()->of($templateSurat)
                ->addIndexColumn()
                ->addColumn('action', function ($templateSurat) {
                    return '
                        <button onclick="openEditModal(' . $templateSurat->id . ', \'' . addslashes($templateSurat->nama_template) . '\', \'' . addslashes($templateSurat->konten) . '\', \'' . addslashes($templateSurat->tipe) . '\')"
                                class="text-indigo-600 hover:text-indigo-800 mr-2">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button onclick="copyTemplate(' . $templateSurat->id . ', \'' . addslashes($templateSurat->nama_template) . '\', \'' . addslashes($templateSurat->konten) . '\', \'' . addslashes($templateSurat->tipe) . '\')"
                                class="text-green-600 hover:text-green-800 mr-2">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                        <form action="' . route('admin.template-surat.destroy', $templateSurat->id) . '" method="POST" class="delete-form inline" onsubmit="return false;">
                            ' . csrf_field() . '
                            ' . method_field('DELETE') . '
                            <button type="submit" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('admin.template');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_template' => 'required|string|max:255|unique:template_surat,nama_template',
            'konten' => 'required|string',
            'tipe' => 'required|in:Surat Masuk,Surat Keluar',
        ], [
            'nama_template.required' => 'Nama template wajib diisi.',
            'nama_template.max' => 'Nama template maksimum 255 karakter.',
            'nama_template.unique' => 'Nama template sudah digunakan.',
            'konten.required' => 'Konten template wajib diisi.',
            'tipe.required' => 'Tipe template wajib dipilih.',
            'tipe.in' => 'Tipe template tidak valid.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Gagal menambah template surat. Periksa input Anda.');
        }

        TemplateSurat::create([
            'nama_template' => $request->nama_template,
            'konten' => $request->konten,
            'tipe' => $request->tipe,
        ]);

        return redirect()->back()->with('success', 'Template surat berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TemplateSurat $templateSurat)
    {
        $validator = Validator::make($request->all(), [
            'nama_template' => 'required|string|max:255|unique:template_surat,nama_template,' . $templateSurat->id,
            'konten' => 'required|string',
            'tipe' => 'required|in:Surat Masuk,Surat Keluar',
        ], [
            'nama_template.required' => 'Nama template wajib diisi.',
            'nama_template.max' => 'Nama template maksimum 255 karakter.',
            'nama_template.unique' => 'Nama template sudah digunakan.',
            'konten.required' => 'Konten template wajib diisi.',
            'tipe.required' => 'Tipe template wajib dipilih.',
            'tipe.in' => 'Tipe template tidak valid.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Gagal memperbarui template surat. Periksa input Anda.');
        }

        $templateSurat->update([
            'nama_template' => $request->nama_template,
            'konten' => $request->konten,
            'tipe' => $request->tipe,
        ]);

        return redirect()->back()->with('success', 'Template surat berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TemplateSurat $templateSurat)
    {
        try {
            $templateSurat->delete();
            return redirect()->back()->with('success', 'Template surat berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus template surat: mungkin terkait dengan data lain.');
        }
    }
}