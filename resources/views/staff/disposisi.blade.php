@extends('staff.layouts.app')

@section('title', 'Disposisi')

@section('page-title', 'Disposisi')

@section('content')
<div class="bg-white p-6 rounded-lg shadow">
    <!-- Alerts -->
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

    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-800">Daftar Disposisi</h2>
    </div>

    <!-- Filter Status -->
    <div class="mb-4">
        <label for="status_filter" class="block text-sm font-medium text-gray-700">Filter Status</label>
        <select id="status_filter" class="mt-1 block w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Semua Status</option>
            <option value="Pending">Pending</option>
            <option value="Selesai">Selesai</option>
        </select>
    </div>

    <!-- DataTables -->
    <div class="overflow-x-auto">
        <table id="disposisiTable" class="min-w-full bg-white border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">No</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Nomor Surat Masuk</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Tanggal Surat</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Perihal</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Pengirim Disposisi</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Instruksi</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Status</th>
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
        <h3 class="text-lg font-semibold mb-4">Detail Disposisi</h3>
        <div class="mb-4">
            <p><strong>Nomor Surat Masuk:</strong> <span id="show_nomor_surat"></span></p>
            <p><strong>Tanggal Surat:</strong> <span id="show_tanggal_surat"></span></p>
            <p><strong>Perihal:</strong> <span id="show_perihal"></span></p>
            <p><strong>Pengirim Surat:</strong> <span id="show_pengirim_surat"></span></p>
            <p><strong>Pengirim Disposisi:</strong> <span id="show_pengirim_disposisi"></span></p>
            <p><strong>Instruksi:</strong> <span id="show_instruksi"></span></p>
            <p><strong>Status:</strong> <span id="show_status"></span></p>
            <p><strong>Catatan:</strong> <span id="show_catatan"></span></p>
            <p><strong>Lampiran Surat Masuk:</strong> <a id="show_lampiran" href="#" target="_blank" class="text-blue-600 hover:underline"></a></p>
        </div>
        <div class="flex justify-end">
            <button type="button" onclick="closeModal('showModal')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Tutup</button>
        </div>
    </div>
</div>

<!-- Forward Modal -->
<div id="forwardModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden" onclick="closeModalOnOutsideClick(event, 'forwardModal')">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">Forward Disposisi</h3>
        <form id="forwardForm" method="POST">
            @csrf
            <div class="mb-4">
                <label for="penerima_id" class="block text-sm font-medium text-gray-700">Penerima Disposisi</label>
                <select id="penerima_id" name="penerima_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="">Pilih Penerima</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="instruksi" class="block text-sm font-medium text-gray-700">Instruksi</label>
                <textarea id="instruksi" name="instruksi" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('forwardModal')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Kirim</button>
            </div>
        </form>
    </div>
</div>

<!-- Complete Modal -->
<div id="completeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden" onclick="closeModalOnOutsideClick(event, 'completeModal')">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">Tandai Disposisi Selesai</h3>
        <form id="completeForm" method="POST">
            @csrf
            <div class="mb-4">
                <label for="catatan" class="block text-sm font-medium text-gray-700">Catatan (opsional)</label>
                <textarea id="catatan" name="catatan" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('completeModal')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Selesai</button>
            </div>
        </form>
    </div>
</div>

<!-- JavaScript -->
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

    function openShowModal(nomor_surat, tanggal_surat, perihal, pengirim_surat, pengirim_disposisi, instruksi, status, catatan, lampiran) {
        document.getElementById('show_nomor_surat').textContent = nomor_surat;
        document.getElementById('show_tanggal_surat').textContent = tanggal_surat;
        document.getElementById('show_perihal').textContent = perihal;
        document.getElementById('show_pengirim_surat').textContent = pengirim_surat;
        document.getElementById('show_pengirim_disposisi').textContent = pengirim_disposisi;
        document.getElementById('show_instruksi').textContent = instruksi;
        document.getElementById('show_status').textContent = status;
        document.getElementById('show_catatan').textContent = catatan || '-';
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

    function openForwardModal(disposisiId, suratMasukId) {
        const form = document.getElementById('forwardForm');
        form.action = '{{ route("staff.disposisi.forward", ":id") }}'.replace(':id', disposisiId);
        document.getElementById('penerima_id').value = '';
        document.getElementById('instruksi').value = '';
        $('#penerima_id').trigger('change');
        openModal('forwardModal');
    }

    function openCompleteModal(disposisiId) {
        const form = document.getElementById('completeForm');
        form.action = '{{ route("staff.disposisi.complete", ":id") }}'.replace(':id', disposisiId);
        document.getElementById('catatan').value = '';
        openModal('completeModal');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const alerts = document.querySelectorAll('[id^="alert-"]');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.add('hidden');
            }, 3000);
        });

        $('#penerima_id').select2({
            ajax: {
                url: '{{ route("staff.search-users") }}',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return { search: params.term };
                },
                processResults: function (data) {
                    return {
                        results: data.map(user => ({
                            id: user.id,
                            text: `${user.nama} (${user.email})`
                        }))
                    };
                },
                cache: true
            },
            placeholder: 'Cari penerima...',
            minimumInputLength: 1
        });
    });
</script>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function () {
        const table = $('#disposisiTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '{{ route("staff.disposisi.index") }}',
                data: function (d) {
                    d.status = $('#status_filter').val();
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Gagal memuat data: ' + (xhr.responseText || 'Unknown error'),
                        icon: 'error'
                    });
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'surat_masuk.nomor_surat', name: 'surat_masuk.nomor_surat' },
                { data: 'surat_masuk.tanggal_surat', name: 'surat_masuk.tanggal_surat' },
                { data: 'surat_masuk.perihal', name: 'surat_masuk.perihal' },
                { data: 'dari_user', name: 'dariUser.nama' },
                { data: 'instruksi', name: 'instruksi' },
                { data: 'status_disposisi', name: 'status_disposisi' },
                {
                    data: null,
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        const escapedNomorSurat = data.surat_masuk.nomor_surat ? data.surat_masuk.nomor_surat.replace(/'/g, "\\'") : '-';
                        const escapedPerihal = data.surat_masuk.perihal.replace(/'/g, "\\'");
                        const escapedPengirimSurat = data.surat_masuk.pengirim ? data.surat_masuk.pengirim.replace(/'/g, "\\'") : '-';
                        const escapedPengirimDisposisi = data.pengirim ? data.pengirim.nama.replace(/'/g, "\\'") : '-';
                        const escapedInstruksi = data.instruksi.replace(/'/g, "\\'");
                        const escapedCatatan = data.catatan ? data.catatan.replace(/'/g, "\\'") : '';
                        const lampiranUrl = data.surat_masuk.lampiran ? '/storage/' + data.surat_masuk.lampiran : '';

                        let actions = `
                            <button onclick="openShowModal('${escapedNomorSurat}', '${data.surat_masuk.tanggal_surat}', '${escapedPerihal}', '${escapedPengirimSurat}', '${escapedPengirimDisposisi}', '${escapedInstruksi}', '${data.status}', '${escapedCatatan}', '${lampiranUrl}')"
                                class="text-blue-600 hover:text-blue-800 mr-2">
                                <i class="fas fa-eye"></i> Lihat
                            </button>
                        `;

                        if (data.status === 'Pending') {
                            actions += `
                                <button onclick="openForwardModal(${data.id}, ${data.surat_masuk_id})"
                                    class="text-yellow-600 hover:text-yellow-800 mr-2">
                                    <i class="fas fa-share"></i> Forward
                                </button>
                                <button onclick="openCompleteModal(${data.id})"
                                    class="text-green-600 hover:text-green-800 mr-2">
                                    <i class="fas fa-check-circle"></i> Selesai
                                </button>
                            `;
                        }

                        return actions;
                    }
                }
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
                emptyTable: 'Tidak ada data disposisi.',
                processing: 'Memproses...'
            }
        });

        $('#status_filter').on('change', function () {
            table.ajax.reload();
        });

        $('#disposisiTable_filter input').unbind().bind('keyup', function(e) {
            table.search(this.value).draw();
        });
    });
</script>
@endpush
@endsection