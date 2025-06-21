<?php

namespace App\Http\Controllers\staff;

use App\Http\Controllers\Controller;
use App\Models\Disposisi;
use App\Models\SuratMasuk;
use App\Models\User;
use App\Models\StatusSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DisposisiController extends Controller
{
    /**
     * Display a listing of dispositions created by this Staff TU.
     */
    public function index()
    {
        $userId = Auth::id();
        $disposisiDibuat = Disposisi::where('dari_user_id', $userId)
                                    ->with(['suratMasuk', 'keUser'])
                                    ->latest()
                                    ->paginate(10);
        return view('staff_tu.disposisi.index', compact('disposisiDibuat'));
    }

    /**
     * Display a listing of dispositions received by this Staff TU.
     */
    public function disposisiMasuk()
    {
        $userId = Auth::id();
        $disposisiDiterima = Disposisi::where('ke_user_id', $userId)
                                     ->with(['suratMasuk', 'dariUser'])
                                     ->latest()
                                     ->paginate(10);
        return view('staff_tu.disposisi.masuk', compact('disposisiDiterima'));
    }


    /**
     * Show the form for creating a new disposition for a specific incoming letter.
     */
    public function create(SuratMasuk $suratMasuk)
    {
        // Pastikan surat masuk belum pernah didisposisi atau statusnya memungkinkan
        if ($suratMasuk->status->nama_status === 'Selesai') {
             return redirect()->route('staff_tu.surat-masuk.show', $suratMasuk->id)->with('error', 'Surat ini sudah selesai dan tidak dapat didisposisi lagi.');
        }

        $users = User::whereHas('userType', function($query) {
                        $query->whereIn('nama_tipe', ['Pimpinan', 'Dosen', 'Staf TU']); // Bisa didisposisi ke Pimpinan, Dosen, atau Staff TU lain
                    })->where('id', '!=', Auth::id()) // Jangan disposisi ke diri sendiri
                    ->get();
        return view('staff_tu.disposisi.create', compact('suratMasuk', 'users'));
    }

    /**
     * Store a newly created disposition in storage.
     */
    public function store(Request $request, SuratMasuk $suratMasuk)
    {
        $request->validate([
            'ke_user_id' => 'required|exists:users,id',
            'instruksi' => 'nullable|string',
            'status_disposisi' => 'required|string|in:Diteruskan,Diterima,Selesai', // Atau sesuaikan opsi
        ]);

        Disposisi::create([
            'surat_masuk_id' => $suratMasuk->id,
            'dari_user_id' => Auth::id(), // Staff TU yang membuat disposisi
            'ke_user_id' => $request->ke_user_id,
            'instruksi' => $request->instruksi,
            'tanggal_disposisi' => Carbon::now(),
            'status_disposisi' => $request->status_disposisi,
        ]);

        // Update status surat masuk menjadi 'Didisposisi'
        $statusDidisposisi = StatusSurat::where('nama_status', 'Didisposisi')->firstOrFail();
        $suratMasuk->update(['status_id' => $statusDidisposisi->id]);


        return redirect()->route('staff_tu.surat-masuk.show', $suratMasuk->id)->with('success', 'Disposisi berhasil dibuat.');
    }
}