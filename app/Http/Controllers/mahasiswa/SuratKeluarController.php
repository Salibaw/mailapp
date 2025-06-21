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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SuratKeluarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $suratKeluar = SuratKeluar::with(['status', 'sifat','penerima'])
                ->where('user_id', auth()->id())
                ->select(['id', 'nomor_surat', 'tanggal_surat', 'perihal', 'penerima_id', 'status_id', 'sifat_surat_id']);
            return datatables()->of($suratKeluar)
                ->addIndexColumn()
                ->editColumn('tanggal_surat', function ($suratKeluar) {
                    return $suratKeluar->tanggal_surat->format('d-m-Y');
                })
                ->addColumn('action', function ($suratKeluar) {
                    $draftStatus = StatusSurat::where('nama_status', 'Draf')->first();
                    if ($draftStatus && $suratKeluar->status_id === $draftStatus->id) {
                        return '
            <button onclick="openEditModal(' . $suratKeluar->id . ', \'' . addslashes($suratKeluar->nomor_surat) . '\', \'' . $suratKeluar->tanggal_surat->format('Y-m-d') . '\', \'' . addslashes($suratKeluar->perihal) . '\', \'' . addslashes($suratKeluar->penerima_id) . '\', \'' . addslashes($suratKeluar->isi_surat) . '\', ' . $suratKeluar->sifat_surat_id . ', ' . ($suratKeluar->template_surat_id ?? 'null') . ')"
                    class="text-indigo-600 hover:text-indigo-800 mr-2">
                <i class="fas fa-edit"></i> Edit
            </button>
            <form action="' . route('mahasiswa.surat-keluar.destroy', $suratKeluar->id) . '" method="POST" class="delete-form inline" onsubmit="return false;">
                ' . csrf_field() . '
                ' . method_field('DELETE') . '
                <button type="submit" class="text-red-600 hover:text-red-800">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>';
                    }
                    return '-';
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        $sifatSurat = SifatSurat::all();
        $templateSurat = TemplateSurat::all();
        return view('mahasiswa.suratkeluar', compact('sifatSurat', 'templateSurat'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sifatSurat = SifatSurat::all();
        $templateSurat = TemplateSurat::all();
        return view('mahasiswa.surat-keluar.create', compact('sifatSurat', 'templateSurat'));
    }

    /**
     * Store a newly created resource in storage.
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


        $data['user_id'] = auth()->id();
        $data['status_id'] = StatusSurat::where('nama_status', 'Menunggu Persetujuan')->first()->id;

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('lampiran', 'public');
        }

        // Create SuratKeluar
        $suratKeluar = SuratKeluar::create($data);


        $nomorAgenda = 'AGENDA/' . now()->format('Y') . '/' . str_pad(SuratMasuk::count() + 1, 3, '0', STR_PAD_LEFT);


        SuratMasuk::create([
            'nomor_agenda' => $nomorAgenda,
            'nomor_surat' => $suratKeluar->nomor_surat,
            'tanggal_surat' => $suratKeluar->tanggal_surat,
            'tanggal_terima' => now()->toDateString(),
            'pengirim_id' => auth()->user()->id,
            'perihal' => $suratKeluar->perihal,
            'isi_ringkas' => substr($suratKeluar->isi_surat, 0, 255),
            'lampiran' => $suratKeluar->lampiran,
            'user_id' => $suratKeluar->penerima_id,
            'status_id' => StatusSurat::where('nama_status', 'Diterima')->first()->id,
            'sifat_surat_id' => $suratKeluar->sifat_surat_id,
        ]);

        return redirect()->route('mahasiswa.surat-keluar.index')->with('success', 'Surat keluar berhasil disubmit dan surat masuk dibuat.');
    }
    public function searchUsers(Request $request)
    {
        $search = $request->input('search', '');

        $users = User::where('nama', 'LIKE', '%' . $search . '%')
            ->orWhere('email', 'LIKE', '%' . $search . '%')
            ->select('id', 'nama', 'email', 'nip_nim', 'telepon', 'alamat')
            ->take(10)
            ->get();

        return response()->json($users);
    }
}
