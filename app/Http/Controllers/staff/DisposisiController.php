<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Disposisi;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class DisposisiController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Disposisi::with(['surat_masuk', 'pengirim', 'penerima'])
                ->where('ke_user_id', Auth::id())
                ->select([
                    'id',
                    'surat_masuk_id',
                    'dari_user_id',
                    'ke_user_id',
                    'instruksi',
                    'status_disposisi',
                    'instruksi',
                ]);

            if ($request->status_disposisi) {
                $query->where('status_disposisi', $request->status_disposisi);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('surat_masuk.tanggal_surat', function ($disposisi) {
                    return $disposisi->surat_masuk->tanggal_surat->format('d-m-Y');
                })
                ->make(true);
        }

        return view('staff.disposisi');
    }

    public function forward(Request $request, Disposisi $disposisi)
    {
        if ($disposisi->status_disposisi !== 'Pending') {
            return redirect()->route('staff.disposisi.index')->with('error', 'Hanya disposisi dengan status Pending yang dapat diforward.');
        }

        $validator = Validator::make($request->all(), [
            'ke_user_id' => 'required|exists:users,id',
            'instruksi' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Disposisi::create([
            'surat_masuk_id' => $disposisi->surat_masuk_id,
            'dari_user_id' => Auth::id(),
            'ke_user_id' => $request->ke_user_id,
            'instruksi' => $request->instruksi,
            'status_disposisi' => 'Pending',
        ]);

        return redirect()->route('staff.disposisi.index')->with('success', 'Disposisi berhasil diforward.');
    }

    public function complete(Request $request, Disposisi $disposisi)
    {
        if ($disposisi->status_disposisi !== 'Pending') {
            return redirect()->route('staff.disposisi.index')->with('error', 'Hanya disposisi dengan status_disposisi Pending yang dapat ditandai selesai.');
        }

        $validator = Validator::make($request->all(), [
            'instruksi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $disposisi->update([
            'status_disposisi' => 'Selesai',
            'instruksi' => $request->instruksi,
        ]);

        return redirect()->route('staff.disposisi.index')->with('success', 'Disposisi berhasil ditandai selesai.');
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