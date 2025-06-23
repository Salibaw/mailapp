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
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

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
                    'catatan_surat'
                ])
                ->where('user_id', Auth::id());

            if ($request->status_id) {
                $query->where('status_id', $request->status_id);
            }


            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('tanggal_surat', function ($suratKeluar) {
                    return $suratKeluar->tanggal_surat ? $suratKeluar->tanggal_surat->format('d-m-Y') : '-';
                })
                ->addColumn('tanggal_surat_raw', function ($suratKeluar) {
                    return $suratKeluar->tanggal_surat ? $suratKeluar->tanggal_surat->format('Y-m-d') : '';
                })
                ->addColumn('penerima_id', function ($suratKeluar) {
                    return optional($suratKeluar->penerima)->nama ?? '';
                })
                ->addColumn('user.nama', function ($suratKeluar) {
                    return optional($suratKeluar->user)->nama ?? '';
                })
                ->addColumn('status.nama_status', function ($suratKeluar) {
                    return optional($suratKeluar->status)->nama_status ?? '';
                })
                ->addColumn('sifat.nama_sifat', function ($suratKeluar) {
                    return optional($suratKeluar->sifat)->nama_sifat ?? '';
                })
                ->addColumn('action', function ($suratKeluar) {
                    return ''; // Aksi akan diatur di frontend
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $statusSurat = StatusSurat::all();
        $sifatSurat = SifatSurat::all();
        $templates = TemplateSurat::select('id', 'nama_template')->get();
        return view('staff.suratkeluar', compact('statusSurat', 'sifatSurat', 'templates'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomor_surat' => 'nullable|string|max:255|unique:surat_keluars,nomor_surat',
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:255',
            'penerima_id' => 'required|exists:users,id',
            'isi_surat' => 'required|string',
            'sifat_surat_id' => 'required|exists:sifat_surats,id',
            'template_surat_id' => 'nullable|exists:template_surats,id',
            'lampiran' => 'nullable|file|mimes:pdf|max:5120',
            'catatan_surat' => 'nullable|string',
        ], [
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
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Gagal menambah surat keluar.');
        }

        $drafStatus = StatusSurat::where('nama_status', 'Draft')->first();
        $diterimaStatus = StatusSurat::where('nama_status', 'Diterima')->first();
        $menungguValidasiStatus = StatusSurat::where('nama_status', 'Menunggu Persetujuan')->first();

        if (!$drafStatus || !$diterimaStatus || !$menungguValidasiStatus) {
            Log::error('Status surat tidak lengkap: Draf, Diterima, atau Menunggu Validasi tidak ditemukan.');
            return redirect()->back()->with('error', 'Konfigurasi status surat tidak lengkap. Hubungi administrator.')->withInput();
        }

        $data = $request->only([
            'nomor_surat',
            'tanggal_surat',
            'perihal',
            'penerima_id',
            'isi_surat',
            'sifat_surat_id',
            'template_surat_id',
            'catatan_surat',
        ]);

        $data['user_id'] = Auth::id();
        $sifatBiasa = SifatSurat::where('nama_sifat', 'Biasa')->first();

        $data['status_id'] = $request->action === 'draft'
            ? $drafStatus->id
            : ($sifatBiasa && $request->sifat_surat_id == $sifatBiasa->id ? $diterimaStatus->id : $menungguValidasiStatus->id);

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('lampiran', 'public');
        }

        DB::beginTransaction();
        try {
            $suratKeluar = SuratKeluar::create($data);

            if ($request->action !== 'draft') {
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
                    'catatan_surat' => $data['catatan_surat'],
                ]);

                Log::info('Surat keluar ID: ' . $suratKeluar->id . ' dibuat dan surat masuk dibuat dengan nomor agenda: ' . $nomorAgenda);
            }

            DB::commit();
            return redirect()->route('staff.surat-keluar.index')->with('success', 'Surat keluar berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal menyimpan surat keluar: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan surat keluar.')->withInput();
        }
    }

    public function update(Request $request, SuratKeluar $suratKeluar)
    {
        if (!$suratKeluar->status || $suratKeluar->status->nama_status !== 'Draf') {
            return redirect()->back()->with('error', 'Hanya surat dengan status Draf yang dapat diedit.')->withInput();
        }

        $validator = Validator::make($request->all(), [
            'template_id' => 'nullable|exists:template_surats,id',
            'penerima_id' => 'required|exists:users,id',
            'sifat_surat_id' => 'required|exists:sifat_surats,id',
            'tanggal_surat' => 'required|date',
            'perihal' => 'required|string|max:255',
            'isi_surat' => 'required|string',
            'lampiran' => 'nullable|file|mimes:pdf|max:2048',
            'catatan_surat' => 'nullable|string',
            'action' => 'required|in:draft,submit',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Gagal memperbarui surat keluar.');
        }

        $drafStatus = StatusSurat::where('nama_status', 'Draf')->first();
        $menungguValidasiStatus = StatusSurat::where('nama_status', 'Menunggu Validasi')->first();

        if (!$drafStatus || !$menungguValidasiStatus) {
            Log::error('Status surat tidak lengkap: Draf atau Menunggu Validasi tidak ditemukan.');
            return redirect()->back()->with('error', 'Konfigurasi status surat tidak lengkap. Hubungi administrator.')->withInput();
        }

        $data = $request->only([
            'template_id',
            'penerima_id',
            'sifat_surat_id',
            'tanggal_surat',
            'perihal',
            'isi_surat',
            'catatan_surat',
        ]);

        $data['status_id'] = $request->action === 'draft' ? $drafStatus->id : $menungguValidasiStatus->id;

        if ($request->hasFile('lampiran')) {
            if ($suratKeluar->lampiran) {
                Storage::disk('public')->delete($suratKeluar->lampiran);
            }
            $data['lampiran'] = $request->file('lampiran')->store('lampiran', 'public');
        }

        DB::beginTransaction();
        try {
            $suratKeluar->update($data);
            Log::info('Surat keluar ID: ' . $suratKeluar->id . ' diperbarui dengan status: ' . $data['status_id']);

            DB::commit();
            return redirect()->route('staff.surat-keluar.index')->with('success', 'Surat keluar berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal memperbarui surat keluar: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui surat keluar.')->withInput();
        }
    }

    public function destroy(SuratKeluar $suratKeluar)
    {
        if (!$suratKeluar->status || $suratKeluar->status->nama_status !== 'Draf') {
            return response()->json(['success' => false, 'message' => 'Hanya surat dengan status Draf yang dapat dihapus.'], 403);
        }

        DB::beginTransaction();
        try {
            if ($suratKeluar->lampiran) {
                Storage::disk('public')->delete($suratKeluar->lampiran);
            }
            $suratKeluar->delete();
            Log::info('Surat keluar ID: ' . $suratKeluar->id . ' dihapus.');
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Surat keluar berhasil dihapus.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal menghapus surat keluar: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus surat keluar.'], 500);
        }
    }

    public function validateSurat(Request $request, SuratKeluar $suratKeluar)
    {
        if (!$suratKeluar->status || $suratKeluar->status->nama_status !== 'Menunggu Validasi') {
            return redirect()->back()->with('error', 'Hanya surat dengan status Menunggu Validasi yang dapat divalidasi.')->withInput();
        }

        $validator = Validator::make($request->all(), [
            'nomor_surat' => 'required|string|max:50|unique:surat_keluars,nomor_surat,' . $suratKeluar->id,
            'status' => 'required|in:Disetujui,Ditolak',
            'catatan_surat' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Gagal memvalidasi surat keluar.');
        }

        $status = StatusSurat::where('nama_status', $request->status)->first();
        if (!$status) {
            Log::error('Status surat tidak ditemukan: ' . $request->status);
            return redirect()->back()->with('error', 'Status surat tidak valid. Hubungi administrator.')->withInput();
        }

        DB::beginTransaction();
        try {
            $suratKeluar->update([
                'nomor_surat' => $request->nomor_surat,
                'status_id' => $status->id,
                'catatan_surat' => $request->catatan_surat,
            ]);

            Log::info('Surat keluar ID: ' . $suratKeluar->id . ' divalidasi dengan status: ' . $request->status);

            DB::commit();
            return redirect()->route('staff.surat-keluar.index')->with('success', 'Surat keluar berhasil divalidasi.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal memvalidasi surat keluar: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memvalidasi surat keluar.')->withInput();
        }
    }

    public function assignNumber(Request $request, SuratKeluar $suratKeluar)
    {
        if (!$suratKeluar->status || $suratKeluar->status->nama_status !== 'Menunggu Validasi') {
            return redirect()->back()->with('error', 'Hanya surat dengan status Menunggu Validasi yang dapat diberi nomor.')->withInput();
        }

        $validator = Validator::make($request->all(), [
            'nomor_surat' => 'required|string|max:50|unique:surat_keluars,nomor_surat,' . $suratKeluar->id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('error', 'Gagal memberikan nomor surat.');
        }

        $nomorSurat = $request->nomor_surat ?: 'SURAT/' . now()->format('Y') . '/' . str_pad(SuratKeluar::count() + 1, 3, '0', STR_PAD_LEFT);

        DB::beginTransaction();
        try {
            $suratKeluar->update(['nomor_surat' => $nomorSurat]);
            Log::info('Nomor surat ' . $nomorSurat . ' diberikan untuk surat keluar ID: ' . $suratKeluar->id);

            DB::commit();
            return redirect()->route('staff.surat-keluar.index')->with('success', 'Nomor surat berhasil diberikan.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal memberikan nomor surat: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memberikan nomor surat.')->withInput();
        }
    }

    public function forwardForApproval(SuratKeluar $suratKeluar)
    {
        if (!$suratKeluar->status || $suratKeluar->status->nama_status !== 'Draf') {
            return response()->json(['success' => false, 'message' => 'Hanya surat dengan status Draf yang dapat dikirim untuk persetujuan.'], 403);
        }

        $menungguValidasiStatus = StatusSurat::where('nama_status', 'Menunggu Validasi')->first();
        if (!$menungguValidasiStatus) {
            return response()->json(['success' => false, 'message' => 'Status Menunggu Validasi tidak ditemukan.'], 500);
        }

        DB::beginTransaction();
        try {
            $suratKeluar->update(['status_id' => $menungguValidasiStatus->id]);
            Log::info('Surat keluar ID: ' . $suratKeluar->id . ' dikirim untuk persetujuan.');
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Surat keluar berhasil dikirim untuk validasi.']);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal mengirim surat untuk persetujuan: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal mengirim surat untuk persetujuan.'], 500);
        }
    }

    public function download(SuratKeluar $suratKeluar)
    {
        if (!$suratKeluar->status || $suratKeluar->status->nama_status !== 'Disetujui') {
            return redirect()->route('staff.surat-keluar.index')->with('error', 'Hanya surat dengan status Disetujui yang dapat diunduh.');
        }

        try {
            $pdf = Pdf::loadView('staff.surat_keluar.pdf', [
                'suratKeluar' => $suratKeluar,
                'watermark' => 'Dokumen Resmi - ' . now()->format('Y-m-d'),
            ]);
            $pdf->setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);
            return $pdf->download('surat-keluar-' . ($suratKeluar->nomor_surat ?? 'document') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Gagal mendownload PDF: ' . $e->getMessage());
            return redirect()->route('staff.surat-keluar.index')->with('error', 'Gagal mengunduh PDF.');
        }
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
