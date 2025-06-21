<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SuratMasukController extends Controller
{
    public function index()
    {
        return view('mahasiswa.suratmasuk');
    }

    public function create()
    {
        return view('mahasiswa.surat-masuk.create');
    }

    public function store(Request $request)
    {
        // Implement store logic
    }

    public function edit($id)
    {
        return view('mahasiswa.surat-masuk.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        // Implement update logic
    }

    public function destroy($id)
    {
        // Implement destroy logic
    }
}