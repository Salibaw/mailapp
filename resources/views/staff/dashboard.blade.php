@extends('staff.layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard Staf TU')

@section('content')
<div class="bg-white p-6 rounded-lg shadow">
    <!-- Welcome Message -->
    <h2 class="text-xl font-semibold text-gray-800 mb-4">Selamat Datang, {{ auth()->user()->name }}</h2>
    <p class="text-gray-600 mb-6">Berikut adalah ringkasan aktivitas surat dan tugas Anda sebagai Staf TU.</p>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-indigo-100 p-4 rounded-lg shadow flex items-center">
            <i class="fas fa-inbox text-indigo-600 text-3xl mr-4"></i>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Surat Masuk</h3>
                <p class="text-2xl font-bold text-indigo-600">{{ $suratMasukCount }}</p>
                <p class="text-sm text-gray-600">Total surat masuk yang diterima.</p>
            </div>
        </div>
        <div class="bg-green-100 p-4 rounded-lg shadow flex items-center">
            <i class="fas fa-paper-plane text-green-600 text-3xl mr-4"></i>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Surat Keluar</h3>
                <p class="text-2xl font-bold text-green-600">{{ $suratKeluarCount }}</p>
                <p class="text-sm text-gray-600">Total surat keluar yang diproses.</p>
            </div>
        </div>
        <div class="bg-yellow-100 p-4 rounded-lg shadow flex items-center">
            <i class="fas fa-tasks text-yellow-600 text-3xl mr-4"></i>
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Disposisi Tertunda</h3>
                <p class="text-2xl font-bold text-yellow-600">{{ $disposisiPendingCount }}</p>
                <p class="text-sm text-gray-600">Disposisi yang perlu ditindaklanjuti.</p>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi Cepat</h3>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('staff.surat-masuk.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 flex items-center">
                <i class="fas fa-plus mr-2"></i> Catat Surat Masuk
            </a>
            <a href="{{ route('staff.surat-keluar.index') }}" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 flex items-center">
                <i class="fas fa-check-circle mr-2"></i> Validasi Surat Keluar
            </a>
            <a href="{{ route('staff.disposisi.index') }}" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700 flex items-center">
                <i class="fas fa-tasks mr-2"></i> Kelola Disposisi
            </a>
        </div>
    </div>

    <!-- Disposisi Notifications -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Notifikasi Disposisi Baru</h3>
        @if($disposisiNotifications->isEmpty())
            <div class="bg-gray-100 p-4 rounded-lg text-gray-600">
                Tidak ada disposisi baru.
            </div>
        @else
            <div class="bg-white border rounded-lg shadow overflow-hidden">
                <ul>
                    @foreach($disposisiNotifications as $disposisi)
                        <li class="p-4 border-b last:border-b-0 hover:bg-gray-50 flex justify-between items-center">
                            <div>
                                <p class="font-medium text-gray-800">Surat: {{ $disposisi->suratMasuk->nomor_surat }}</p>
                                <p class="text-sm text-gray-600">Perihal: {{ $disposisi->suratMasuk->perihal }}</p>
                                <p class="text-sm text-gray-500">Diterima: {{ $disposisi->created_at->format('d-m-Y H:i') }}</p>
                            </div>
                            <a href="{{ route('staff.disposisi.show', $disposisi->id) }}" class="text-indigo-600 hover:text-indigo-800">
                                <i class="fas fa-eye"></i> Lihat
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Surat Masuk Tertunda -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Surat Masuk Perlu Tindak Lanjut</h3>
        @if($suratMasukPending->isEmpty())
            <div class="bg-gray-100 p-4 rounded-lg text-gray-600">
                Tidak ada surat masuk yang perlu ditindaklanjuti.
            </div>
        @else
            <table class="min-w-full bg-white border rounded-lg shadow">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Nomor Agenda</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Perihal</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suratMasukPending as $surat)
                        <tr class="border-b last:border-b-0">
                            <td class="px-6 py-3 text-gray-700">{{ $surat->nomor_agenda }}</td>
                            <td class="px-6 py-3 text-gray-700">{{ $surat->perihal }}</td>
                            <td class="px-6 py-3 text-gray-700">{{ $surat->status->nama_status }}</td>
                            <td class="px-6 py-3">
                                <a href="{{ route('staff.surat-masuk.show', $surat->id) }}"
                                   class="text-indigo-600 hover:text-indigo-800">
                                    <i class="fas fa-eye"></i> Lihat
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Surat Keluar Tertunda -->
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Surat Keluar Perlu Validasi</h3>
        @if($suratKeluarPending->isEmpty())
            <div class="bg-gray-100 p-4 rounded-lg text-gray-600">
                Tidak ada surat keluar yang perlu divalidasi.
            </div>
        @else
            <table class="min-w-full bg-white border rounded-lg shadow">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Nomor Surat</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Perihal</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suratKeluarPending as $surat)
                        <tr class="border-b last:border-b-0">
                            <td class="px-6 py-3 text-gray-700">{{ $surat->nomor_surat }}</td>
                            <td class="px-6 py-3 text-gray-700">{{ $surat->perihal }}</td>
                            <td class="px-6 py-3 text-gray-700">{{ $surat->status->nama_status }}</td>
                            <td class="px-6 py-3">
                                <a href="{{ route('staff.surat-keluar.show', $surat->id) }}"
                                   class="text-indigo-600 hover:text-indigo-800">
                                    <i class="fas fa-eye"></i> Lihat
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<!-- Scripts -->
@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        // Auto-hide alerts
        const alerts = document.querySelectorAll('[id^="alert-"]');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.add('hidden');
            }, 3000);
        });
    });
</script>
@endpush
@endsection