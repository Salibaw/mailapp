<?php

namespace App\Http\Controllers\staff;

use App\Http\Controllers\Controller;
use App\Models\SuratKeluar;
use App\Models\StatusSurat;
use App\Models\SifatSurat;
use App\Models\PersetujuanSuratKeluar; // Import
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PDF; // Pastikan Anda sudah menginstal dan mengkonfigurasi DomPDF/Snappy
use Illuminate\Support\Carbon; // Untuk tanggal
use Illuminate\Support\Str; // Untuk slugify nama surat

class SuratKeluarController extends Controller
{
    /**
     * Display a listing of the resource (Surat Keluar yang diajukan oleh pengguna lain).
     */
    public function index()
    {
        $suratKeluar = SuratKeluar::with(['user.userType', 'status', 'sifat'])
                                ->latest()
                                ->paginate(10);
        return view('staff_tu.surat_keluar.index', compact('suratKeluar'));
    }

    /**
     * Display the specified resource.
     */
    public function show(SuratKeluar $suratKeluar)
    {
        $suratKeluar->load(['user.userType', 'status', 'sifat', 'templateSurat', 'persetujuan.penyetuju']);
        return view('staff_tu.surat_keluar.show', compact('suratKeluar'));
    }

    /**
     * Show the form to set a letter for approval (optional: used by Staff TU).
     * This method might be integrated into the 'show' view or a dedicated form.
     */
    public function showSetujuiForm(SuratKeluar $suratKeluar)
    {
        // Pastikan hanya surat yang 'Menunggu Persetujuan' atau 'Ditolak' yang bisa diproses
        if (!in_array($suratKeluar->status->nama_status, ['Menunggu Persetujuan', 'Ditolak'])) {
            return redirect()->route('staff_tu.surat-keluar.show', $suratKeluar->id)->with('error', 'Surat ini tidak bisa langsung disetujui melalui form ini.');
        }

        // Contoh: Form untuk staf TU mengisi nomor surat, tanggal surat sebelum diteruskan ke pimpinan
        return view('staff_tu.surat_keluar.form_setujui', compact('suratKeluar'));
    }

    /**
     * Staff TU to process the letter for approval (add number, date).
     * This is the action where Staff TU "prepares" the letter.
     */
    public function setujui(Request $request, SuratKeluar $suratKeluar)
    {
        $request->validate([
            'nomor_surat' => 'required|string|unique:surat_keluar,nomor_surat,' . $suratKeluar->id,
            'tanggal_surat' => 'required|date',
        ]);

        // Dapatkan status 'Disetujui' atau 'Menunggu Persetujuan' jika belum ada
        $statusDisetujui = StatusSurat::where('nama_status', 'Disetujui')->firstOrFail();
        $statusMenungguPersetujuan = StatusSurat::where('nama_status', 'Menunggu Persetujuan')->firstOrFail();


        $suratKeluar->update([
            'nomor_surat' => $request->nomor_surat,
            'tanggal_surat' => $request->tanggal_surat,
            'status_id' => $statusMenungguPersetujuan->id, // Setelah diisi nomor, status tetap menunggu persetujuan Pimpinan
        ]);

        // Catat sebagai persetujuan oleh staf TU (atau ini bisa diabaikan jika hanya pimpinan yang menyetujui final)
        // PersetujuanSuratKeluar::create([
        //     'surat_keluar_id' => $suratKeluar->id,
        //     'user_id_penyetuju' => Auth::id(), // ID Staf TU
        //     'status_persetujuan' => 'Diteruskan ke Pimpinan',
        //     'catatan' => 'Surat telah diberi nomor dan siap untuk persetujuan pimpinan.',
        //     'tanggal_persetujuan' => Carbon::now(),
        // ]);

        return redirect()->route('staff_tu.surat-keluar.index')->with('success', 'Surat berhasil diberi nomor dan diteruskan untuk persetujuan pimpinan.');
    }

    /**
     * Show the form to reject a letter.
     */
    public function showTolakForm(SuratKeluar $suratKeluar)
    {
        if (!in_array($suratKeluar->status->nama_status, ['Menunggu Persetujuan', 'Disetujui'])) {
            return redirect()->route('staff_tu.surat-keluar.show', $suratKeluar->id)->with('error', 'Surat ini tidak bisa ditolak.');
        }
        return view('staff_tu.surat_keluar.form_tolak', compact('suratKeluar'));
    }

    /**
     * Staff TU to reject the letter.
     */
    public function tolak(Request $request, SuratKeluar $suratKeluar)
    {
        $request->validate([
            'catatan_penolakan' => 'required|string|min:10',
        ]);

        $statusDitolak = StatusSurat::where('nama_status', 'Ditolak')->firstOrFail();

        $suratKeluar->update([
            'status_id' => $statusDitolak->id,
            'nomor_surat' => null, // Reset nomor surat jika ditolak
            'tanggal_surat' => null, // Reset tanggal surat jika ditolak
        ]);

        // Catat penolakan
        PersetujuanSuratKeluar::create([
            'surat_keluar_id' => $suratKeluar->id,
            'user_id_penyetuju' => Auth::id(), // ID Staf TU yang menolak
            'status_persetujuan' => 'Ditolak',
            'catatan' => $request->catatan_penolakan,
            'tanggal_persetujuan' => Carbon::now(),
        ]);

        return redirect()->route('staff_tu.surat-keluar.index')->with('success', 'Surat berhasil ditolak.');
    }

    /**
     * Generate PDF for a surat keluar (after approval).
     */
    public function generatePdf(SuratKeluar $suratKeluar)
    {
        // Pastikan surat sudah disetujui dan memiliki nomor/tanggal
        if ($suratKeluar->status->nama_status !== 'Disetujui' || !$suratKeluar->nomor_surat) {
            return redirect()->back()->with('error', 'Surat belum disetujui atau belum memiliki nomor surat.');
        }

        $data = [
            'surat' => $suratKeluar,
            // Tambahkan data lain yang mungkin dibutuhkan di template PDF
            'kampus_nama' => 'Nama Kampus Anda', // Konfigurasi ini
            'kampus_alamat' => 'Alamat Kampus Anda', // Konfigurasi ini
        ];

        // Load view yang akan dijadikan PDF
        $pdf = PDF::loadView('pdf.surat_keluar', $data);

        // Unduh PDF
        return $pdf->download('surat_keluar_' . Str::slug($suratKeluar->perihal) . '.pdf');
    }

    // Tidak perlu implementasi create, store, edit, update, destroy di sini
    // karena pembuatan surat diajukan oleh mahasiswa/dosen, bukan staf TU.
    // Staf TU hanya memprosesnya.
    // Jika Staf TU juga bisa membuat surat dari awal, maka metode ini perlu diimplementasi.
    // Untuk saat ini, asumsikan Staf TU hanya memproses pengajuan.
}