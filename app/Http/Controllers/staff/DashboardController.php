<?php

namespace App\Http\Controllers\staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\Disposisi;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the Staff TU dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        $totalSuratMasuk = SuratMasuk::count();
        $totalSuratKeluar = SuratKeluar::count();
        $suratMasukBelumDidisposisi = SuratMasuk::whereDoesntHave('disposisi')->count();
        $suratKeluarMenungguPersetujuan = SuratKeluar::whereHas('status', function($query) {
            $query->where('nama_status', 'Menunggu Persetujuan');
        })->count();

        // Disposisi yang diterima oleh Staff TU (jika ada alur internal disposisi ke TU)
        $disposisiDiterima = Disposisi::where('ke_user_id', $user->id)
                                    ->with('suratMasuk')
                                    ->latest()
                                    ->take(5)
                                    ->get();

        // Terbaru surat masuk yang belum ditindaklanjuti
        $latestSuratMasuk = SuratMasuk::latest()->take(5)->get();

        // Terbaru surat keluar yang perlu diverifikasi
        $latestSuratKeluarPengajuan = SuratKeluar::whereHas('status', function($query) {
                                            $query->where('nama_status', 'Menunggu Persetujuan');
                                        })
                                        ->latest()
                                        ->take(5)
                                        ->get();


        return view('staff.dashboard', compact(
            'totalSuratMasuk',
            'totalSuratKeluar',
            'suratMasukBelumDidisposisi',
            'suratKeluarMenungguPersetujuan',
            'disposisiDiterima',
            'latestSuratMasuk',
            'latestSuratKeluarPengajuan'
        ));
    }
}