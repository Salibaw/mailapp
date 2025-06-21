<?php

namespace App\Http\Controllers\staff;

use App\Http\Controllers\Controller;
use App\Models\SuratMasuk;
use App\Models\StatusSurat;
use App\Models\SifatSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class SuratMasukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suratMasuk = SuratMasuk::with(['user', 'status', 'sifat'])->latest()->paginate(10);
        return view('staff_tu.surat_masuk.index', compact('suratMasuk'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $statusSurat = StatusSurat::all();
        $sifatSurat = SifatSurat::all();
        return view('staff_tu.surat_masuk.create', compact('statusSurat', 'sifatSurat'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomor_surat' => 'required|string|max:255',
            'tanggal_surat' => 'required|date',
            'tanggal_terima' => 'required|date',
            'pengirim' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'isi_ringkas' => 'nullable|string',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            'status_id' => 'required|exists:status_surat,id',
            'sifat_surat_id' => 'required|exists:sifat_surat,id',
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $request->file('lampiran')->store('lampiran_surat_masuk', 'public');
        }

        // Generate nomor agenda otomatis (contoh: SM/Tahun/Bulan/NomorUrut)
        $year = date('Y');
        $month = date('m');
        $lastSuratMasuk = SuratMasuk::whereYear('created_at', $year)
                                    ->whereMonth('created_at', $month)
                                    ->orderBy('id', 'desc')
                                    ->first();
        $nextUrut = ($lastSuratMasuk) ? (int) substr($lastSuratMasuk->nomor_agenda, -4) + 1 : 1;
        $nomorAgenda = "SM/{$year}/{$month}/" . str_pad($nextUrut, 4, '0', STR_PAD_LEFT);

        SuratMasuk::create([
            'nomor_agenda' => $nomorAgenda,
            'nomor_surat' => $request->nomor_surat,
            'tanggal_surat' => $request->tanggal_surat,
            'tanggal_terima' => $request->tanggal_terima,
            'pengirim' => $request->pengirim,
            'perihal' => $request->perihal,
            'isi_ringkas' => $request->isi_ringkas,
            'lampiran' => $lampiranPath,
            'user_id' => Auth::id(), // Pencatat surat adalah Staf TU yang login
            'status_id' => $request->status_id,
            'sifat_surat_id' => $request->sifat_surat_id,
        ]);

        return redirect()->route('staff_tu.surat-masuk.index')->with('success', 'Surat Masuk berhasil dicatat.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SuratMasuk $suratMasuk)
    {
        $suratMasuk->load(['user', 'status', 'sifat', 'disposisi.dariUser', 'disposisi.keUser']); // Load relasi disposisi
        return view('staff_tu.surat_masuk.show', compact('suratMasuk'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SuratMasuk $suratMasuk)
    {
        $statusSurat = StatusSurat::all();
        $sifatSurat = SifatSurat::all();
        return view('staff_tu.surat_masuk.edit', compact('suratMasuk', 'statusSurat', 'sifatSurat'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SuratMasuk $suratMasuk)
    {
        $request->validate([
            // 'nomor_agenda' => ['required', 'string', 'max:255', Rule::unique('surat_masuk')->ignore($suratMasuk->id)], // Jangan diubah otomatis
            'nomor_surat' => 'required|string|max:255',
            'tanggal_surat' => 'required|date',
            'tanggal_terima' => 'required|date',
            'pengirim' => 'required|string|max:255',
            'perihal' => 'required|string|max:255',
            'isi_ringkas' => 'nullable|string',
            'lampiran' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
            'status_id' => 'required|exists:status_surat,id',
            'sifat_surat_id' => 'required|exists:sifat_surat,id',
        ]);

        $lampiranPath = $suratMasuk->lampiran;
        if ($request->hasFile('lampiran')) {
            if ($suratMasuk->lampiran && \Storage::disk('public')->exists($suratMasuk->lampiran)) {
                \Storage::disk('public')->delete($suratMasuk->lampiran);
            }
            $lampiranPath = $request->file('lampiran')->store('lampiran_surat_masuk', 'public');
        }

        $suratMasuk->update([
            'nomor_surat' => $request->nomor_surat,
            'tanggal_surat' => $request->tanggal_surat,
            'tanggal_terima' => $request->tanggal_terima,
            'pengirim' => $request->pengirim,
            'perihal' => $request->perihal,
            'isi_ringkas' => $request->isi_ringkas,
            'lampiran' => $lampiranPath,
            'status_id' => $request->status_id,
            'sifat_surat_id' => $request->sifat_surat_id,
        ]);

        return redirect()->route('staff_tu.surat-masuk.index')->with('success', 'Surat Masuk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SuratMasuk $suratMasuk)
    {
        if ($suratMasuk->lampiran && \Storage::disk('public')->exists($suratMasuk->lampiran)) {
            \Storage::disk('public')->delete($suratMasuk->lampiran);
        }
        $suratMasuk->delete();
        return redirect()->route('staff_tu.surat-masuk.index')->with('success', 'Surat Masuk berhasil dihapus.');
    }
}