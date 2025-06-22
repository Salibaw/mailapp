<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\SifatSurat;
use App\Models\StatusSurat;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class SuratMasukController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = SuratMasuk::with(['status', 'sifat', 'pengirim'])
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
                    $actions = '
                        <button onclick="openShowModal(\'' . addslashes($suratMasuk->nomor_agenda) . '\', \'' . addslashes($suratMasuk->nomor_surat) . '\', \'' . $suratMasuk->tanggal_surat->format('d-m-Y') . '\', \'' . $suratMasuk->tanggal_terima->format('d-m-Y') . '\', \'' . addslashes($suratMasuk->perihal) . '\', \'' . addslashes($suratMasuk->pengirim ? $suratMasuk->pengirim->nama : '-') . '\', \'' . addslashes($suratMasuk->sifat ? $suratMasuk->sifat->nama_sifat : '-') . '\', \'' . addslashes($suratMasuk->status ? $suratMasuk->status->nama_status : '-') . '\', \'' . addslashes($suratMasuk->isi_ringkas) . '\', \'' . ($suratMasuk->lampiran ? asset('storage/' . $suratMasuk->lampiran) : '') . '\')"
                            class="text-blue-600 hover:text-blue-800 mr-2">
                            <i class="fas fa-eye"></i> Lihat
                        </button>
                        <button onclick="openEditModal(' . $suratMasuk->id . ', \'' . addslashes($suratMasuk->nomor_agenda) . '\', \'' . addslashes($suratMasuk->nomor_surat) . '\', \'' . $suratMasuk->tanggal_surat->format('Y-m-d') . '\', \'' . $suratMasuk->tanggal_terima->format('Y-m-d') . '\', \'' . addslashes($suratMasuk->perihal) . '\', \'' . ($suratMasuk->pengirim ? addslashes($suratMasuk->pengirim->nama) : '') . '\', \'' . addslashes($suratMasuk->isi_ringkas) . '\', \'' . $suratMasuk->sifat_surat_id . '\', \'' . ($suratMasuk->lampiran ? asset('storage/' . $suratMasuk->lampiran) : '') . '\')"
                            class="text-green-600 hover:text-green-800 mr-2">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button onclick="openDisposisiModal(' . $suratMasuk->id . ')"
                            class="text-yellow-600 hover:text-yellow-800 mr-2">
                            <i class="fas fa-tasks"></i> Disposisi
                        </button>
                        <button onclick="archiveSurat(' . $suratMasuk->id . ')" class="text-red-600 hover:text-red-800">
                            <i class="fas fa-archive"></i> Arsip
                        </button>';
                    return $actions;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $statusSurat = StatusSurat::all();
        $sifatSurat = SifatSurat::all();
        return view('staff.suratmasuk', compact('statusSurat', 'sifatSurat'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nomor_agenda' => 'required|string|max:255|unique:surat_masuks',
            'nomor_surat' => 'required|string|max:255',
            'tanggal_surat' => 'required|date',
            'tanggal_terima' => 'required|date',
            'pengirim_id' => 'required|exists:users,id',
            'perihal' => 'required|string|max:255',
            'isi_ringkas' => 'nullable|string',
            'sifat_surat_id' => 'required|exists:sifat_surats,id',
            'lampiran' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'nomor_agenda',
            'nomor_surat',
            'tanggal_surat',
            'tanggal_terima',
            'pengirim_id',
            'perihal',
            'isi_ringkas',
            'sifat_surat_id',
        ]);

        $data['user_id'] = Auth::id();
        $data['status_id'] = StatusSurat::where('nama_status', 'Diterima')->first()->id;

        if ($request->hasFile('lampiran')) {
            $data['lampiran'] = $request->file('lampiran')->store('lampiran', 'public');
        }

        SuratMasuk::create($data);

        return redirect()->route('staff.surat-masuk.index')->with('success', 'Surat masuk berhasil dicatat.');
    }

    public function show(SuratMasuk $suratMasuk)
    {
        return view('staff.suratmasuk.show', compact('suratMasuk'));
    }

    public function edit(SuratMasuk $suratMasuk)
    {
        $statusSurat = StatusSurat::all();
        $sifatSurat = SifatSurat::all();
        return view('staff.suratmasuk.edit', compact('suratMasuk', 'statusSurat', 'sifatSurat'));
    }

    public function update(Request $request, SuratMasuk $suratMasuk)
    {
        $validator = Validator::make($request->all(), [
            'nomor_agenda' => 'required|string|max:255|unique:surat_masuks,nomor_agenda,' . $suratMasuk->id,
            'nomor_surat' => 'required|string|max:255',
            'tanggal_surat' => 'required|date',
            'tanggal_terima' => 'required|date',
            'pengirim_id' => 'required|exists:users,id',
            'perihal' => 'required|string|max:255',
            'isi_ringkas' => 'nullable|string',
            'sifat_surat_id' => 'required|exists:sifat_surats,id',
            'lampiran' => 'nullable|file|mimes:pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = $request->only([
            'nomor_agenda',
            'nomor_surat',
            'tanggal_surat',
            'tanggal_terima',
            'pengirim_id',
            'perihal',
            'isi_ringkas',
            'sifat_surat_id',
        ]);

        if ($request->hasFile('lampiran')) {
            if ($suratMasuk->lampiran) {
                Storage::disk('public')->delete($suratMasuk->lampiran);
            }
            $data['lampiran'] = $request->file('lampiran')->store('lampiran', 'public');
        }

        $suratMasuk->update($data);

        return redirect()->route('staff.surat-masuk.index')->with('success', 'Surat masuk berhasil diperbarui.');
    }

    public function destroy(SuratMasuk $suratMasuk)
    {
        if ($suratMasuk->lampiran) {
            Storage::disk('public')->delete($suratMasuk->lampiran);
        }
        $suratMasuk->delete();

        return redirect()->route('staff.surat-masuk.index')->with('success', 'Surat masuk berhasil diarsipkan.');
    }

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