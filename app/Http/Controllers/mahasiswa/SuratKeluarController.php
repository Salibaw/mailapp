<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\SifatSurat;
use App\Models\StatusSurat;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\TemplateSurat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class SuratKeluarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = SuratKeluar::with(['status', 'sifat', 'penerima'])
                ->where('user_id', Auth::id())
                ->select(['id', 'nomor_surat', 'tanggal_surat', 'perihal', 'penerima_id', 'status_id', 'sifat_surat_id', 'isi_surat', 'lampiran', 'template_surat_id']);

            if ($request->status_id) {
                $query->where('status_id', $request->status_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('tanggal_surat', function ($suratKeluar) {
                    return $suratKeluar->tanggal_surat->format('d-m-Y');
                })
                ->addColumn('action', function ($suratKeluar) {
                    $draftStatus = StatusSurat::where('nama_status', 'Draf')->first();
                    $actions = '
                        <button onclick="openShowModal(\'' . addslashes($suratKeluar->nomor_surat) . '\', \'' . $suratKeluar->tanggal_surat->format('d-m-Y') . '\', \'' . addslashes($suratKeluar->perihal) . '\', \'' . addslashes($suratKeluar->penerima ? $suratKeluar->penerima->nama : '-') . '\', \'' . addslashes($suratKeluar->sifat ? $suratKeluar->sifat->nama_sifat : '-') . '\', \'' . addslashes($suratKeluar->status ? $suratKeluar->status->nama_status : '-') . '\', \'' . addslashes($suratKeluar->isi_surat) . '\', \'' . ($suratKeluar->lampiran ? asset('storage/' . $suratKeluar->lampiran) : '') . '\')"
                            class="text-blue-600 hover:text-blue-800 mr-2">
                            <i class="fas fa-eye"></i> Lihat
                        </button>';

                    if ($draftStatus && $suratKeluar->status_id === $draftStatus->id) {
                        $actions .= '
                            <button onclick="openEditModal(' . $suratKeluar->id . ', \'' . addslashes($suratKeluar->nomor_surat) . '\', \'' . $suratKeluar->tanggal_surat->format('Y-m-d') . '\', \'' . addslashes($suratKeluar->perihal) . '\', \'' . ($suratKeluar->penerima ? $suratKeluar->penerima->nama . ' (' . $suratKeluar->penerima->email . ')' : '') . '\', \'' . addslashes($suratKeluar->isi_surat) . '\', ' . $suratKeluar->sifat_surat_id . ', ' . ($suratKeluar->template_surat_id ?? 'null') . ')"
                                class="text-indigo-600 hover:text-indigo-800 mr-2">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <form action="' . route('mahasiswa.surat-keluar.destroy', $suratKeluar->id) . '" method="POST" class="delete-form inline" onsubmit="return false;">
                                ' . csrf_field() . '
                                ' . method_field('DELETE') . '
                                <button type="submit" class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            </form>';
                    }

                    return $actions;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $sifatSurat = SifatSurat::all();
        $templateSurat = TemplateSurat::all();
        $statusSurat = StatusSurat::all();
        return view('mahasiswa.suratkeluar', compact('sifatSurat', 'templateSurat', 'statusSurat'));
    }

    /**
     * Store a newly created resource in storage or save as draft.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomor_surat' => 'required|string|max:255|unique:surat_keluars,nomor_surat',
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:255',
            'penerima_id' => 'required|exists:users,id',
            'isi_surat' => 'required|string',
            'sifat_surat_id' => 'required|exists:sifat_surats,id',
            'template_surat_id' => 'nullable|exists:template_surats,id',
            'lampiran' => 'nullable|file|mimes:pdf|max:5120', // 5MB max
        ], [
            'nomor_surat.required' => 'Nomor surat wajib diisi.',
            'nomor_surat.unique' => 'Nomor surat sudah digunakan.',
            'tanggal_surat.required' => 'Tanggal surat wajib diisi.',
            'perihal.required' => 'Perihal wajib diisi.',
            'penerima_id.required' => 'Penerima wajib diisi.',
            'isi_surat.required' => 'Isi surat wajib diisi.',
            'sifat_surat_id.required' => 'Sifat surat wajib dipilih.',
            'lampiran.mimes' => 'Lampiran harus berformat PDF.',
            'lampiran.max' => 'Lampiran maksimum 5MB.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Gagal menambah surat keluar. Periksa input Anda.');
        }

        $data = $request->only([
            'nomor_surat',
            'tanggal_surat',
            'perihal',
            'penerima_id',
            'isi_surat',
            'sifat_surat_id',
            'template_surat_id',
        ]);

        $data['user_id'] = Auth::id();
        $sifatBiasa = SifatSurat::where('nama_sifat', 'Biasa')->first();
        $data['status_id'] = $sifatBiasa && $request->sifat_surat_id == $sifatBiasa->id
            ? StatusSurat::where('nama_status', 'Diterima')->first()->id
            : StatusSurat::where('nama_status', 'Menunggu Persetujuan')->first()->id;

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('lampiran', 'public');
        }

        // Create SuratKeluar
        $suratKeluar = SuratKeluar::create($data);

        // Create corresponding SuratMasuk only if not a draft
        if (!$request->is_draft) {
            $nomorAgenda = 'AGENDA/' . now()->format('Y') . '/' . str_pad(SuratMasuk::count() + 1, 3, '0', STR_PAD_LEFT);

            SuratMasuk::create([
                'nomor_agenda' => $nomorAgenda,
                'nomor_surat' => $suratKeluar->nomor_surat,
                'tanggal_surat' => $suratKeluar->tanggal_surat,
                'tanggal_terima' => now()->toDateString(),
                'pengirim_id' => Auth::id(),
                'perihal' => $suratKeluar->perihal,
                'isi_ringkas' => substr($suratKeluar->isi_surat, 0, 255),
                'lampiran' => $suratKeluar->lampiran,
                'user_id' => $suratKeluar->penerima_id,
                'status_id' => $data['status_id'],
                'sifat_surat_id' => $suratKeluar->sifat_surat_id,
            ]);
        }

        return redirect()->route('mahasiswa.surat-keluar.index')->with('success', 'Surat keluar berhasil disubmit dan surat masuk dibuat.');
    }

    /**
     * Save or update a draft.
     */
    public function saveDraft(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomor_surat' => 'nullable|string|max:255|unique:surat_keluars,nomor_surat',
            'tanggal_surat' => 'nullable|date',
            'perihal' => 'nullable|string|max:255',
            'penerima_id' => 'nullable|exists:users,id',
            'isi_surat' => 'nullable|string',
            'sifat_surat_id' => 'nullable|exists:sifat_surats,id',
            'template_surat_id' => 'nullable|exists:template_surats,id',
            'lampiran' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan draft. Periksa input Anda.'], 422);
        }

        $data = $request->only([
            'nomor_surat',
            'tanggal_surat',
            'perihal',
            'penerima_id',
            'isi_surat',
            'sifat_surat_id',
            'template_surat_id',
        ]);

        $data['user_id'] = Auth::id();
        $data['status_id'] = StatusSurat::where('nama_status', 'Draf')->first()->id;

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('lampiran', 'public');
        }

        SuratKeluar::create($data);

        return response()->json(['success' => true, 'message' => 'Draft berhasil disimpan.']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SuratKeluar $suratKeluar)
    {
        if ($suratKeluar->status->nama_status !== 'Draf') {
            return redirect()->route('mahasiswa.surat-keluar.index')->with('error', 'Hanya surat dengan status Draf yang dapat diedit.');
        }

        $validator = Validator::make($request->all(), [
            'nomor_surat' => 'required|string|max:255|unique:surat_keluars,nomor_surat,' . $suratKeluar->id,
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:255',
            'penerima_id' => 'required|exists:users,id',
            'isi_surat' => 'required|string',
            'sifat_surat_id' => 'required|exists:sifat_surats,id',
            'template_surat_id' => 'nullable|exists:template_surats,id',
            'lampiran' => 'nullable|file|mimes:pdf|max:5120',
        ], [
            'nomor_surat.required' => 'Nomor surat wajib diisi.',
            'nomor_surat.unique' => 'Nomor surat sudah digunakan.',
            'tanggal_surat.required' => 'Tanggal surat wajib diisi.',
            'perihal.required' => 'Perihal wajib diisi.',
            'penerima_id.required' => 'Penerima wajib diisi.',
            'isi_surat.required' => 'Isi surat wajib diisi.',
            'sifat_surat_id.required' => 'Sifat surat wajib dipilih.',
            'lampiran.mimes' => 'Lampiran harus berformat PDF.',
            'lampiran.max' => 'Lampiran maksimum 5MB.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Gagal memperbarui surat keluar. Periksa input Anda.');
        }

        $data = $request->only([
            'nomor_surat',
            'tanggal_surat',
            'perihal',
            'penerima_id',
            'isi_surat',
            'sifat_surat_id',
            'template_surat_id',
        ]);

        $sifatBiasa = SifatSurat::where('nama_sifat', 'Biasa')->first();
        $data['status_id'] = $sifatBiasa && $request->sifat_surat_id == $sifatBiasa->id
            ? StatusSurat::where('nama_status', 'Diterima')->first()->id
            : StatusSurat::where('nama_status', 'Menunggu Persetujuan')->first()->id;

        if ($request->hasFile('lampiran')) {
            if ($suratKeluar->lampiran) {
                Storage::disk('public')->delete($suratKeluar->lampiran);
            }
            $data['lampiran'] = $request->file('lampiran')->store('lampiran', 'public');
        }

        $suratKeluar->update($data);

        // Update or create corresponding SuratMasuk
        if ($data['status_id'] !== StatusSurat::where('nama_status', 'Draf')->first()->id) {
            $suratMasuk = SuratMasuk::where('nomor_surat', $suratKeluar->nomor_surat)->first();
            if (!$suratMasuk) {
                $nomorAgenda = 'AGENDA/' . now()->format('Y') . '/' . str_pad(SuratMasuk::count() + 1, 3, '0', STR_PAD_LEFT);
                SuratMasuk::create([
                    'nomor_agenda' => $nomorAgenda,
                    'nomor_surat' => $suratKeluar->nomor_surat,
                    'tanggal_surat' => $suratKeluar->tanggal_surat,
                    'tanggal_terima' => now()->toDateString(),
                    'pengirim_id' => Auth::id(),
                    'perihal' => $suratKeluar->perihal,
                    'isi_ringkas' => substr($suratKeluar->isi_surat, 0, 255),
                    'lampiran' => $suratKeluar->lampiran,
                    'user_id' => $suratKeluar->penerima_id,
                    'status_id' => $data['status_id'],
                    'sifat_surat_id' => $suratKeluar->sifat_surat_id,
                ]);
            } else {
                $suratMasuk->update([
                    'tanggal_surat' => $suratKeluar->tanggal_surat,
                    'perihal' => $suratKeluar->perihal,
                    'isi_ringkas' => substr($suratKeluar->isi_surat, 0, 255),
                    'lampiran' => $suratKeluar->lampiran,
                    'user_id' => $suratKeluar->penerima_id,
                    'status_id' => $data['status_id'],
                    'sifat_surat_id' => $suratKeluar->sifat_surat_id,
                ]);
            }
        }

        return redirect()->route('mahasiswa.surat-keluar.index')->with('success', 'Surat keluar berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SuratKeluar $suratKeluar)
    {
        if ($suratKeluar->status->nama_status !== 'Draf') {
            return redirect()->route('mahasiswa.surat-keluar.index')->with('error', 'Hanya surat dengan status Draf yang dapat dihapus.');
        }

        if ($suratKeluar->lampiran) {
            Storage::disk('public')->delete($suratKeluar->lampiran);
        }

        $suratKeluar->delete();

        return redirect()->route('mahasiswa.surat-keluar.index')->with('success', 'Surat keluar berhasil dihapus.');
    }

    /**
     * Search users for Select2.
     */
    public function searchUsers(Request $request)
    {
        $search = $request->input('search', '');

        $users = User::where('nama', 'LIKE', '%' . $search . '%')
            ->orWhere('email', 'LIKE', '%' . $search . '%')
            ->select('id', 'nama', 'email', 'nip_nim')
            ->take(10)
            ->get();

        return response()->json($users);
    }
}