<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\SifatSurat;
use App\Models\StatusSurat;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\TemplateSurat;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class SuratKeluarController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = SuratKeluar::with(['status', 'sifat', 'penerima', 'user'])
                ->select([
                    'id',
                    'nomor_surat',
                    'tanggal_surat',
                    'perihal',
                    'penerima_id',
                    'status_id',
                    'sifat_surat_id',
                    'isi_surat',
                    'lampiran',
                    'template_surat_id',
                    'user_id',
                    'perihal'
                ])
                ->where('user_id', Auth::id());


            if ($request->status_id) {
                $query->where('status_id', $request->status_id);
            }

            if ($request->role) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('role', $request->role);
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('tanggal_surat', function ($suratKeluar) {
                    return $suratKeluar->tanggal_surat->format('d-m-Y');
                })
                ->addColumn('tanggal_surat_raw', function ($suratKeluar) {
                    return $suratKeluar->tanggal_surat->format('Y-m-d');
                })
                ->addColumn('penerima_id', function ($suratKeluar) {
                    return $suratKeluar->penerima_id;
                })
                ->addColumn('template_surat_id', function ($suratKeluar) {
                    return $suratKeluar->template_surat_id;
                })
                ->make(true);
        }

        $statusSurat = StatusSurat::all();
        $sifatSurat = SifatSurat::all();
        $templates = TemplateSurat::select('id', 'nama_template')->get();
        return view('staff.suratkeluar', compact('statusSurat', 'sifatSurat', 'templates'));
    }

    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'nomor_surat' => 'nullable|string|max:50',
            'template_id' => 'nullable|exists:template_surats,id',
            'penerima_id' => 'required|exists:users,id',
            'sifat_surat_id' => 'required|exists:sifat_surats,id',
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:255',
            'isi_surat' => 'required|string',
            'lampiran' => 'nullable|file|mimes:pdf|max:2048',
            'action' => 'required|in:draft,submit', // Added action validation
        ]);

        // Prepare data for SuratKeluar
        $data = $request->only([
            'nomor_surat',
            'template_id',
            'penerima_id',
            'sifat_surat_id',
            'tanggal_surat',
            'perihal',
            'isi_surat',
        ]);
        $data['user_id'] = Auth::id();

        // Set status_id based on action and sifat_surat
        if ($request->action === 'draft') {
            $statusDraf = StatusSurat::where('nama_status', 'Draft')->first();
            if (!$statusDraf) {
                return redirect()->back()->withErrors(['status' => 'Status "Draf" tidak ditemukan di database.'])->withInput();
            }
            $data['status_id'] = $statusDraf->id;
        } else {
            // For 'submit', set status based on sifat_surat
            $sifatSurat = SifatSurat::where('id', $request->sifat_surat_id)->first();
            if (!$sifatSurat) {
                return redirect()->back()->withErrors(['sifat_surat' => 'Sifat surat tidak ditemukan.'])->withInput();
            }

            if ($sifatSurat->nama_sifat === 'Biasa') {
                $statusDiterima = StatusSurat::where('nama_status', 'Diterima')->first();
                if (!$statusDiterima) {
                    return redirect()->back()->withErrors(['status' => 'Status "Diterima" tidak ditemukan di database.'])->withInput();
                }
                $data['status_id'] = $statusDiterima->id;
            } elseif ($sifatSurat->nama_sifat === 'Penting') {
                $statusMenunggu = StatusSurat::where('nama_status', 'Menunggu Persetujuan')->first();
                if (!$statusMenunggu) {
                    return redirect()->back()->withErrors(['status' => 'Status "Menunggu" tidak ditemukan di database.'])->withInput();
                }
                $data['status_id'] = $statusMenunggu->id;
            } else {
                return redirect()->back()->withErrors(['sifat_surat' => 'Sifat surat tidak valid (harus Biasa atau Penting).'])->withInput();
            }
        }

        // Handle lampiran file upload
        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('lampiran', 'public');
        }

        // Create SuratKeluar and SuratMasuk in a transaction
        return DB::transaction(function () use ($request, $data) {
            // Create SuratKeluar record
            $suratKeluar = SuratKeluar::create($data);

            // Create SuratMasuk record if submitted
            if ($request->action === 'submit') {
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
                    'status_id' => $data['status_id'], // Use same status as SuratKeluar
                    'sifat_surat_id' => $suratKeluar->sifat_surat_id,
                ]);
            }

            return redirect()->route('staff.surat-keluar.index')
                ->with('success', 'Surat keluar berhasil dibuat.');
        });
    }
    public function update(Request $request, SuratKeluar $suratKeluar)
    {
        if ($suratKeluar->status->nama_status !== 'Draf') {
            return redirect()->back()->with('error', 'Hanya surat dengan status Draf yang dapat diedit.')->withInput();
        }

        $validated = $request->validate([
            'template_id' => 'nullable|exists:template_surats,id',
            'penerima_id' => 'required|exists:users,id',
            'sifat_surat_id' => 'required|exists:sifat_surats,id',
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:255',
            'isi_surat' => 'required|string',
            'perihal' => 'nullable|string',
            'lampiran' => 'nullable|file|mimes:pdf|max:2048',
            'action' => 'required|in:draft,submit',
        ]);

        $data = $request->only([
            'template_id',
            'penerima_id',
            'sifat_surat_id',
            'tanggal_surat',
            'perihal',
            'isi_surat',
            'perihal',
        ]);
        $data['status_id'] = $request->action === 'draft'
            ? StatusSurat::where('nama_status', 'Draf')->first()->id ?? 1
            : StatusSurat::where('nama_status', 'Menunggu Validasi')->first()->id ?? 2;

        if ($request->hasFile('lampiran')) {
            if ($suratKeluar->lampiran) {
                Storage::disk('public')->delete($suratKeluar->lampiran);
            }
            $data['lampiran'] = $request->file('lampiran')->store('lampiran', 'public');
        }

        $suratKeluar->update($data);

        return redirect()->route('staff.surat-keluar.index')
            ->with('success', 'Surat keluar berhasil diperbarui.');
    }

    public function destroy(SuratKeluar $suratKeluar)
    {
        if ($suratKeluar->status->nama_status !== 'Draf') {
            return response()->json(['success' => false, 'message' => 'Hanya surat dengan status Draf yang dapat dihapus.'], 403);
        }

        if ($suratKeluar->lampiran) {
            Storage::disk('public')->delete($suratKeluar->lampiran);
        }
        $suratKeluar->delete();

        return response()->json(['success' => true, 'message' => 'Surat keluar berhasil dihapus.']);
    }

    public function validateSurat(Request $request, SuratKeluar $suratKeluar)
    {
        if ($suratKeluar->status->nama_status !== 'Menunggu Validasi') {
            return redirect()->back()->with('error', 'Hanya surat dengan status Menunggu Validasi yang dapat divalidasi.')->withInput();
        }

        $validated = $request->validate([
            'nomor_surat' => 'required|string|max:50',
            'status' => 'required|in:Disetujui,Ditolak',
            'perihal' => 'nullable|string',
        ]);

        $statusId = StatusSurat::where('nama_status', $request->status)->first()->id ?? ($request->status === 'Disetujui' ? 3 : 4);

        $suratKeluar->update([
            'nomor_surat' => $request->nomor_surat,
            'status_id' => $statusId,
            'perihal' => $request->perihal,
        ]);

        return redirect()->route('staff.surat-keluar.index')
            ->with('success', 'Surat keluar berhasil divalidasi.');
    }

    public function assignNumber(Request $request, SuratKeluar $suratKeluar)
    {
        if ($suratKeluar->status->nama_status !== 'Menunggu Validasi') {
            return redirect()->back()->with('error', 'Hanya surat dengan status Menunggu Validasi yang dapat diberi nomor.')->withInput();
        }

        $validated = $request->validate([
            'nomor_surat' => 'required|string|max:50',
        ]);

        $suratKeluar->update([
            'nomor_surat' => $request->nomor_surat,
        ]);

        return redirect()->route('staff.surat-keluar.index')
            ->with('success', 'Nomor surat berhasil diberikan.');
    }

    public function forwardForApproval(SuratKeluar $suratKeluar)
    {
        if ($suratKeluar->status->nama_status !== 'Draf') {
            return response()->json(['success' => false, 'message' => 'Hanya surat dengan status Draf yang dapat dikirim untuk persetujuan.'], 403);
        }

        $suratKeluar->update([
            'status_id' => StatusSurat::where('nama_status', 'Menunggu Validasi')->first()->id ?? 2,
        ]);

        // Optional: Notify pimpinan
        // \App\Models\User::where('role', 'pimpinan')->each(function ($pimpinan) {
        //     $pimpinan->notify(new \App\Notifications\SuratKeluarApproval($suratKeluar));
        // });

        return response()->json(['success' => true, 'message' => 'Surat keluar berhasil dikirim untuk validasi.']);
    }

    public function download(SuratKeluar $suratKeluar)
    {
        if ($suratKeluar->status->nama_status !== 'Disetujui') {
            return redirect()->route('staff.surat-keluar.index')->with('error', 'Hanya surat dengan status Disetujui yang dapat diunduh.');
        }

        $pdf = Pdf::loadView('staff.suratkeluar.pdf', compact('suratKeluar'));
        return $pdf->download('surat-keluar-' . ($suratKeluar->nomor_surat ?? 'document') . '.pdf');
    }

    public function searchUsers(Request $request)
    {
        $search = $request->query('search', '');
        $users = User::where('nama', 'like', '%' . $search . '%')
            ->orWhere('nip_nim', 'like', '%' . $search . '%')
            ->select('id', 'nama', 'email', 'nip_nim')
            ->limit(10)
            ->get();

        return response()->json($users);
    }
}
