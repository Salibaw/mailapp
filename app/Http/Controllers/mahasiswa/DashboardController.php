<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SuratKeluar;

class DashboardController extends Controller
{
    /**
     * Display the student dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Ambil surat keluar yang diajukan oleh mahasiswa ini
        $suratDiajukan = SuratKeluar::where('user_id', $user->id)
            ->with('status')
            ->latest()
            ->take(5) // Ambil 5 surat terbaru
            ->get();

        $totalSuratDiajukan = SuratKeluar::where('user_id', $user->id)->count();
        $suratMenungguPersetujuan = SuratKeluar::where('user_id', $user->id)
            ->whereHas('status', function ($query) {
                $query->where('nama_status', 'Menunggu Persetujuan');
            })
            ->count();
        $suratDisetujui = SuratKeluar::where('user_id', $user->id)
            ->whereHas('status', function ($query) {
                $query->where('nama_status', 'Disetujui');
            })
            ->count();

        return view('mahasiswa.dashboard', compact(
            'user',
            'suratDiajukan',
            'totalSuratDiajukan',
            'suratMenungguPersetujuan',
            'suratDisetujui'
        ));
    }
}
