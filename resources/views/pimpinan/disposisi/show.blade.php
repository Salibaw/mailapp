@extends('pimpinan.layouts.app')

@section('content')
<div class="container-fluid">
    <h2>Detail Disposisi</h2>

    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Disposisi Surat: {{ $disposisi->suratMasuk->nomor_agenda ?? 'N/A' }}</h5>
            <p class="card-text"><strong>Perihal Surat:</strong> {{ $disposisi->suratMasuk->perihal ?? 'N/A' }}</p>
            <p class="card-text"><strong>Dari (Pemberi Disposisi):</strong> {{ $disposisi->dariUser->nama ?? 'N/A' }} ({{ $disposisi->dariUser->userType->nama_tipe ?? 'N/A' }})</p>
            <p class="card-text"><strong>Kepada:</strong> {{ $disposisi->keUser->nama ?? 'N/A' }} ({{ $disposisi->keUser->userType->nama_tipe ?? 'N/A' }})</p>
            <p class="card-text"><strong>Tanggal Disposisi:</strong> {{ $disposisi->tanggal_disposisi->translatedFormat('d F Y H:i') }}</p>
            <p class="card-text"><strong>Status Disposisi:</strong> <span class="badge bg-primary">{{ $disposisi->status_disposisi }}</span></p>

            <hr>
            <h5>Instruksi:</h5>
            <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; white-space: pre-wrap;">
                {!! nl2br(e($disposisi->instruksi)) !!}
            </div>

            @if($disposisi->suratMasuk->lampiran)
                <h5 class="mt-3">Lampiran Surat Masuk:</h5>
                <p><a href="{{ asset('storage/' . $disposisi->suratMasuk->lampiran) }}" target="_blank" class="btn btn-outline-primary">Lihat / Unduh Lampiran Surat</a></p>
            @endif

            <div class="mt-4">
                <a href="{{ route('pimpinan.disposisi.index') }}" class="btn btn-secondary">Kembali</a>
                <a href="{{ route('pimpinan.surat-masuk.show', $disposisi->suratMasuk->id) }}" class="btn btn-info">Lihat Detail Surat Masuk</a>
            </div>
        </div>
    </div>
</div>
@endsection