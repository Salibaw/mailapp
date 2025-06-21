@extends('staff.layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Dashboard Staf Tata Usaha</h2>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Total Surat Masuk</h5>
                    <p class="card-text fs-3">{{ $totalSuratMasuk }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Surat Keluar</h5>
                    <p class="card-text fs-3">{{ $totalSuratKeluar }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">SM Belum Didisposisi</h5>
                    <p class="card-text fs-3">{{ $suratMasukBelumDidisposisi }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">SK Menunggu Persetujuan</h5>
                    <p class="card-text fs-3">{{ $suratKeluarMenungguPersetujuan }}</p>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-4 mb-3">5 Surat Masuk Terbaru</h3>
    {{-- Tabel untuk latestSuratMasuk (mirip dengan index surat masuk) --}}

    <h3 class="mt-4 mb-3">5 Pengajuan Surat Keluar Terbaru (Perlu Verifikasi)</h3>
    {{-- Tabel untuk latestSuratKeluarPengajuan (mirip dengan index surat keluar) --}}

    <h3 class="mt-4 mb-3">5 Disposisi Masuk Terbaru untuk Anda</h3>
    {{-- Tabel untuk disposisiDiterima (mirip dengan index disposisi masuk) --}}

    <div class="mt-4">
        <a href="{{ route('staff.surat-masuk.create') }}" class="btn btn-success">Catat Surat Masuk Baru</a>
        <a href="{{ route('staff.surat-keluar.index') }}" class="btn btn-info">Lihat Semua Pengajuan SK</a>
    </div>
</div>
@endsection