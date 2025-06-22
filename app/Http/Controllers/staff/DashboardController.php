<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Disposisi;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Count total Surat Masuk for the authenticated staff (as recipient)
        $suratMasukCount = SuratMasuk::where('user_id', Auth::id())->count();

        // Count total Surat Keluar processed by the staff (assuming staff can process all)
        $suratKeluarCount = SuratKeluar::where('status_id', '!=', \App\Models\StatusSurat::where('nama_status', 'Draf')->first()->id ?? 0)->count();

        // Count pending Disposisi assigned to the staff
        $disposisiPendingCount = Disposisi::where('ke_user_id', Auth::id())
            ->where('status_disposisi', 'pending')
            ->count();

        // Fetch new Disposisi notifications (e.g., last 5 recent disposisi)
        $disposisiNotifications = Disposisi::with('suratMasuk')
            ->where('ke_user_id', Auth::id())
            ->where('status_disposisi', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Fetch Surat Masuk pending follow-up (e.g., status "Menunggu Persetujuan")
        $suratMasukPending = SuratMasuk::with('status')
            ->where('user_id', Auth::id())
            ->where('status_id', \App\Models\StatusSurat::where('nama_status', 'Menunggu Persetujuan')->first()->id ?? 0)
            ->orderBy('tanggal_terima', 'desc')
            ->take(5)
            ->get();

        // Fetch Surat Keluar pending validation (e.g., status "Menunggu Persetujuan")
        $suratKeluarPending = SuratKeluar::with('status')
            ->where('status_id', \App\Models\StatusSurat::where('nama_status', 'Menunggu Persetujuan')->first()->id ?? 0)
            ->orderBy('tanggal_surat', 'desc')
            ->take(5)
            ->get();

        return view('staff.dashboard', compact(
            'suratMasukCount',
            'suratKeluarCount',
            'disposisiPendingCount',
            'disposisiNotifications',
            'suratMasukPending',
            'suratKeluarPending'
        ));
    }
}