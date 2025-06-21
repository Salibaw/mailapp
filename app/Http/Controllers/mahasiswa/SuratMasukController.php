<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\StatusSurat;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class SuratMasukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = SuratMasuk::with(['status', 'sifat', 'pengirim'])
                ->where('user_id', Auth::id())
                ->select([
                    'id',
                    'nomor_agenda',
                    'nomor_surat',
                    'tanggal_surat',
                    'tanggal_terima',
                    'perihal',
                    'pengirim_id',
                    'status_id',
                    'sifat_surat_id',
                    'isi_ringkas',
                    'lampiran'
                ]);

            if ($request->status_id) {
                $query->where('status_id', $request->status_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('tanggal_surat', function ($suratMasuk) {
                    return $suratMasuk->tanggal_surat->format('d-m-Y');
                })
                ->editColumn('tanggal_terima', function ($suratMasuk) {
                    return $suratMasuk->tanggal_terima->format('d-m-Y');
                })
                ->addColumn('action', function ($suratMasuk) {
                    return '
                        <button onclick="openShowModal(\'' . addslashes($suratMasuk->nomor_agenda) . '\', \'' . addslashes($suratMasuk->nomor_surat) . '\', \'' . $suratMasuk->tanggal_surat->format('d-m-Y') . '\', \'' . $suratMasuk->tanggal_terima->format('d-m-Y') . '\', \'' . addslashes($suratMasuk->perihal) . '\', \'' . addslashes($suratMasuk->pengirim ? $suratMasuk->pengirim->nama : '-') . '\', \'' . addslashes($suratMasuk->sifat ? $suratMasuk->sifat->nama_sifat : '-') . '\', \'' . addslashes($suratMasuk->status ? $suratMasuk->status->nama_status : '-') . '\', \'' . addslashes($suratMasuk->isi_ringkas) . '\', \'' . ($suratMasuk->lampiran ? asset('storage/' . $suratMasuk->lampiran) : '') . '\')"
                            class="text-blue-600 hover:text-blue-800 mr-2">
                            <i class="fas fa-eye"></i> Lihat
                        </button>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $statusSurat = StatusSurat::all();
        return view('mahasiswa.suratmasuk', compact('statusSurat'));
    }

    /**
     * Search users for Select2 (for potential future use).
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