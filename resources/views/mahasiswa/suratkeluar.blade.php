@extends('mahasiswa.layouts.app')

@section('title', 'Surat Keluar')

@section('page-title', 'Surat Keluar')

@section('content')
<div class="bg-white p-6 rounded-lg shadow">
    <!-- Alert Notifikasi -->
    @if (session('success'))
        <div id="alert-success" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded relative flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span>{{ session('success') }}</span>
            <button onclick="closeAlert('alert-success')" class="absolute right-4 text-green-700 hover:text-green-900">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif
    @if (session('error'))
        <div id="alert-error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded relative flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span>{{ session('error') }}</span>
            <button onclick="closeAlert('alert-error')" class="absolute right-4 text-red-700 hover:text-red-900">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-800">Daftar Surat Keluar</h2>
        <button onclick="openModal('createModal')" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
            <i class="fas fa-plus mr-2"></i> Buat Surat Keluar
        </button>
    </div>

    <!-- Tabel Surat Keluar dengan DataTables -->
    <div class="overflow-x-auto">
        <table id="suratKeluarTable" class="min-w-full bg-white border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">No</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Nomor Surat</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Tanggal</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Perihal</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Penerima</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Sifat</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-lg p-6 max-w-xl">
        <h3 class="text-lg font-semibold mb-4">Buat Surat Keluar</h3>
        <form id="createForm" action="{{ route('mahasiswa.surat-keluar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="nomor_surat" class="block text-sm font-medium text-gray-700">Nomor Surat</label>
                    <input type="text" name="nomor_surat" id="nomor_surat" value="{{ old('nomor_surat') }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                           autocomplete="off" required>
                    @error('nomor_surat')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="tanggal_surat" class="block text-sm font-medium text-gray-700">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" id="tanggal_surat" value="{{ old('tanggal_surat', now()->format('Y-m-d')) }}"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                           autocomplete="off" required>
                    @error('tanggal_surat')
                        <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <div class="mb-4">
                <label for="perihal" class="block text-sm font-medium text-gray-700">Perihal</label>
                <input type="text" name="perihal" id="perihal" value="{{ old('perihal') }}"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                       autocomplete="off" required>
                @error('perihal')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-4">
                <label for="penerima" class="block text-sm font-medium text-gray-700">Penerima</label>
                <select name="penerima_id" id="penerima"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        autocomplete="off" required>
                    <option value="">Pilih Penerima</option>
                </select>
                @error('penerima')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-4">
                <label for="sifat_surat_id" class="block text-sm font-medium text-gray-700">Sifat Surat</label>
                <select name="sifat_surat_id" id="sifat_surat_id"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        autocomplete="off" required>
                    <option value="">Pilih Sifat Surat</option>
                    @foreach ($sifatSurat as $sifat)
                        <option value="{{ $sifat->id }}" {{ old('sifat_surat_id') == $sifat->id ? 'selected' : '' }}>{{ $sifat->nama_sifat }}</option>
                    @endforeach
                </select>
                @error('sifat_surat_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-4">
                <label for="template_surat_id" class="block text-sm font-medium text-gray-700">Template Surat</label>
                <select name="template_surat_id" id="template_surat_id"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                        autocomplete="off">
                    <option value="">Pilih Template (Opsional)</option>
                    @foreach ($templateSurat as $template)
                        <option value="{{ $template->id }}" {{ old('template_surat_id') == $template->id ? 'selected' : '' }}>{{ $template->nama_template }}</option>
                    @endforeach
                </select>
                @error('template_surat_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-4">
                <label for="isi_surat" class="block text-sm font-medium text-gray-700">Isi Surat</label>
                <textarea name="isi_surat" id="isi_surat" rows="6"
                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                          autocomplete="off" required>{{ old('isi_surat') }}</textarea>
                @error('isi_surat')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-4">
                <label for="lampiran" class="block text-sm font-medium text-gray-700">Lampiran (PDF, max 5MB)</label>
                <input type="file" name="lampiran" id="lampiran" accept=".pdf"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('lampiran')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('createModal')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Submit</button>
            </div>
        </form>
    </div>
</div>


<!-- JavaScript untuk Modal, Alert, dan Template -->
<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function closeAlert(alertId) {
        document.getElementById(alertId).classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const alerts = document.querySelectorAll('[id^="alert-"]');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.add('hidden');
            }, 3000);
        });

        // Load template content
        // const templates = @json($templateSurat->pluck('konten', 'id'));
        // document.getElementById('template_surat_id').addEventListener('change', function () {
        //     const templateId = this.value;
        //     if (templateId) {
        //         document.getElementById('isi_surat').value = templates[templateId];
        //     }
        // });
    });
</script>

@endsection

@push('scripts')
<!-- jQuery CDN -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 CSS dan JS CDN -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- DataTables CSS dan JS CDN -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<!-- DataTables Buttons -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        var table = $('#suratKeluarTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '{{ route("mahasiswa.surat-keluar.index") }}',
                error: function(xhr, error, thrown) {
                    console.log('DataTables AJAX Error:', xhr.responseText);
                    Swal.fire({
                        title: 'Error',
                        text: 'Gagal memuat data: ' + (xhr.responseText || 'Unknown error'),
                        icon: 'error'
                    });
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'nomor_surat', name: 'nomor_surat' },
                { data: 'tanggal_surat', name: 'tanggal_surat' },
                { data: 'perihal', name: 'perihal' },
                { data: 'penerima.nama', name: 'penerima.nama' },
                { data: 'status.nama_status', name: 'status.nama_status' },
                { data: 'sifat.nama_sifat', name: 'sifat.nama_sifat' },
            ],
            language: {
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ entri',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri',
                paginate: {
                    first: 'Pertama',
                    last: 'Terakhir',
                    next: 'Selanjutnya',
                    previous: 'Sebelumnya'
                },
                emptyTable: 'Tidak ada data surat keluar.',
                processing: 'Memproses...'
            }
        });
        // Initialize Select2 for penerima
        $('#penerima').select2({
            placeholder: 'Pilih Penerima',
            allowClear: true,
            ajax: {
                url: '{{ route("mahasiswa.search-users") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function (data) {
                    return {
                        results: data.map(function (user) {
                            return {
                                id: user.id,
                                text: user.nama + ' (' + user.email + ')'
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 1
        });

        // Pencarian langsung tanpa Enter
        $('#suratKeluarTable_filter input').unbind().bind('keyup', function(e) {
            table.search(this.value).draw();
        });
    });
</script>
@endpush