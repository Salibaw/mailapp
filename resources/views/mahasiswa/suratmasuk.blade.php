@extends('mahasiswa.layouts.app')

@section('title', 'Surat Masuk')

@section('page-title', 'Surat Masuk')

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
        <h2 class="text-xl font-semibold text-gray-800">Daftar Surat Masuk</h2>
    </div>

    <!-- Filter Status -->
    <div class="mb-4">
        <label for="status_filter" class="block text-sm font-medium text-gray-700">Filter Status</label>
        <select id="status_filter" class="mt-1 block w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Semua Status</option>
            @foreach ($statusSurat as $status)
                <option value="{{ $status->id }}">{{ $status->nama_status }}</option>
            @endforeach
        </select>
    </div>

    <!-- Tabel Surat Masuk dengan DataTables -->
    <div class="overflow-x-auto">
        <table id="suratMasukTable" class="min-w-full bg-white border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">No</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Nomor Agenda</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Nomor Surat</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Tanggal Surat</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Perihal</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Pengirim</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Status</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Sifat</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<!-- Show Modal -->
<div id="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden" onclick="closeModalOnOutsideClick(event, 'showModal')">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">Detail Surat Masuk</h3>
        <div class="mb-4">
            <p><strong>Nomor Agenda:</strong> <span id="show_nomor_agenda"></span></p>
            <p><strong>Nomor Surat:</strong> <span id="show_nomor_surat"></span></p>
            <p><strong>Tanggal Surat:</strong> <span id="show_tanggal_surat"></span></p>
            <p><strong>Tanggal Terima:</strong> <span id="show_tanggal_terima"></span></p>
            <p><strong>Perihal:</strong> <span id="show_perihal"></span></p>
            <p><strong>Pengirim:</strong> <span id="show_pengirim"></span></p>
            <p><strong>Sifat Surat:</strong> <span id="show_sifat_surat"></span></p>
            <p><strong>Status:</strong> <span id="show_status"></span></p>
            <p><strong>Isi Ringkas:</strong></p>
            <div id="show_isi_ringkas" class="border p-2 rounded"></div>
            <p><strong>Lampiran:</strong> <a id="show_lampiran" href="#" target="_blank" class="text-blue-600 hover:underline"></a></p>
        </div>
        <div class="flex justify-end">
            <button type="button" onclick="closeModal('showModal')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Tutup</button>
        </div>
    </div>
</div>

<!-- JavaScript untuk Modal dan Alert -->
<script>
    function openModal(modalId) {
        document.getElementById(modalId).classList.remove('hidden');
    }

    function closeModal(modalId) {
        document.getElementById(modalId).classList.add('hidden');
    }

    function closeModalOnOutsideClick(event, modalId) {
        if (event.target.id === modalId) {
            closeModal(modalId);
        }
    }

    function closeAlert(alertId) {
        document.getElementById(alertId).classList.add('hidden');
    }

    function openShowModal(nomor_agenda, nomor_surat, tanggal_surat, tanggal_terima, perihal, pengirim, sifat_surat, status, isi_ringkas, lampiran) {
        document.getElementById('show_nomor_agenda').textContent = nomor_agenda;
        document.getElementById('show_nomor_surat').textContent = nomor_surat;
        document.getElementById('show_tanggal_surat').textContent = tanggal_surat;
        document.getElementById('show_tanggal_terima').textContent = tanggal_terima;
        document.getElementById('show_perihal').textContent = perihal;
        document.getElementById('show_pengirim').textContent = pengirim;
        document.getElementById('show_sifat_surat').textContent = sifat_surat;
        document.getElementById('show_status').textContent = status;
        document.getElementById('show_isi_ringkas').textContent = isi_ringkas;
        const lampiranLink = document.getElementById('show_lampiran');
        if (lampiran) {
            lampiranLink.href = lampiran;
            lampiranLink.textContent = 'Lihat Lampiran';
        } else {
            lampiranLink.href = '#';
            lampiranLink.textContent = 'Tidak ada lampiran';
        }
        openModal('showModal');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const alerts = document.querySelectorAll('[id^="alert-"]');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.add('hidden');
            }, 3000);
        });
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
        const table = $('#suratMasukTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '{{ route("mahasiswa.surat-masuk.index") }}',
                data: function (d) {
                    d.status_id = $('#status_filter').val();
                },
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
                { data: 'nomor_agenda', name: 'nomor_agenda' },
                { data: 'nomor_surat', name: 'nomor_surat' },
                { data: 'tanggal_surat', name: 'tanggal_surat' },
                { data: 'perihal', name: 'perihal' },
                { data: 'pengirim.nama', name: 'pengirim.nama' },
                { data: 'status.nama_status', name: 'status.nama_status' },
                { data: 'sifat.nama_sifat', name: 'sifat.nama_sifat' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
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
                emptyTable: 'Tidak ada data surat masuk.',
                processing: 'Memproses...'
            }
        });

        // Filter by status
        $('#status_filter').on('change', function () {
            table.ajax.reload();
        });

        // Pencarian langsung tanpa Enter
        $('#suratMasukTable_filter input').unbind().bind('keyup', function(e) {
            table.search(this.value).draw();
        });
    });
</script>
@endpush