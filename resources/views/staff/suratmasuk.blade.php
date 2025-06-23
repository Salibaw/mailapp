@extends('staff.layouts.app')

@section('title', 'Surat Masuk')

@section('page-title', 'Surat Masuk')

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

    <!-- Header and Create Button -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-800">Daftar Surat Masuk</h2>
        <a href="#" onclick="openModal('createModal')" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 flex items-center">
            <i class="fas fa-plus mr-2"></i> Catat Surat Masuk
        </a>
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

    <!-- DataTables -->
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

<!-- Create Modal -->
<div id="createModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden" onclick="closeModalOnOutsideClick(event, 'createModal')">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">Catat Surat Masuk</h3>
        <form id="createForm" action="{{ route('staff.surat-masuk.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="nomor_agenda" class="block text-sm font-medium text-gray-700">Nomor Agenda</label>
                    <input type="text" id="nomor_agenda" name="nomor_agenda" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="nomor_surat" class="block text-sm font-medium text-gray-700">Nomor Surat</label>
                    <input type="text" id="nomor_surat" name="nomor_surat" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="tanggal_surat" class="block text-sm font-medium text-gray-700">Tanggal Surat</label>
                    <input type="date" id="tanggal_surat" name="tanggal_surat" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="tanggal_terima" class="block text-sm font-medium text-gray-700">Tanggal Terima</label>
                    <input type="date" id="tanggal_terima" name="tanggal_terima" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                </div>
                <div>
                    <label for="pengirim_id" class="block text-sm font-medium text-gray-700">pengirim</label>
                    <select name="pengirim_id" id="pengirim_id" class="select2-pengirim mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="">Pilih Penerima</option>
                    </select>
                    @error('penerima_id')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="sifat_surat_id" class="block text-sm font-medium text-gray-700">Sifat Surat</label>
                    <select id="sifat_surat_id" name="sifat_surat_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Pilih Sifat</option>
                        @foreach ($sifatSurat as $sifat)
                        <option value="{{ $sifat->id }}">{{ $sifat->nama_sifat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label for="perihal" class="block text-sm font-medium text-gray-700">Perihal</label>
                <input type="text" id="perihal" name="perihal" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div class="mb-4">
                <label for="isi_ringkas" class="block text-sm font-medium text-gray-700">Isi Ringkas</label>
                <textarea id="isi_ringkas" name="isi_ringkas" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
            </div>
            <div class="mb-4">
                <label for="lampiran" class="block text-sm font-medium text-gray-700">Lampiran</label>
                <input type="file" id="lampiran" name="lampiran" accept=".pdf" class="mt-1 block w-full">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('createModal')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden" onclick="closeModalOnOutsideClick(event, 'editModal')">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">Edit Surat Masuk</h3>
        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <!-- Error Messages -->
            @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="edit_nomor_agenda" class="block text-sm font-medium text-gray-700">Nomor Agenda</label>
                    <input type="text" id="edit_nomor_agenda" name="nomor_agenda" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('nomor_agenda') border-red-500 @enderror">
                    @error('nomor_agenda')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="edit_nomor_surat" class="block text-sm font-medium text-gray-700">Nomor Surat</label>
                    <input type="text" id="edit_nomor_surat" name="nomor_surat" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('nomor_surat') border-red-500 @enderror">
                    @error('nomor_surat')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <!-- Add similar error handling for other fields -->
                <div>
                    <label for="edit_tanggal_surat" class="block text-sm font-medium text-gray-700">Tanggal Surat</label>
                    <input type="date" id="edit_tanggal_surat" name="tanggal_surat" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('tanggal_surat') border-red-500 @enderror">
                    @error('tanggal_surat')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="edit_tanggal_terima" class="block text-sm font-medium text-gray-700">Tanggal Terima</label>
                    <input type="date" id="edit_tanggal_terima" name="tanggal_terima" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('tanggal_terima') border-red-500 @enderror">
                    @error('tanggal_terima')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="edit_pengirim_id" class="block text-sm font-medium text-gray-700">Pengirim</label>
                    <select id="edit_pengirim_id" name="pengirim_id" class="select2-pengirim mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('pengirim_id') border-red-500 @enderror">
                        <option value="">Pilih Pengirim</option>
                    </select>
                    @error('pengirim_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="edit_sifat_surat_id" class="block text-sm font-medium text-gray-700">Sifat Surat</label>
                    <select id="edit_sifat_surat_id" name="sifat_surat_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('sifat_surat_id') border-red-500 @enderror">
                        <option value="">Pilih Sifat</option>
                        @foreach ($sifatSurat as $sifat)
                        <option value="{{ $sifat->id }}">{{ $sifat->nama_sifat }}</option>
                        @endforeach
                    </select>
                    @error('sifat_surat_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div class="mb-4">
                <label for="edit_perihal" class="block text-sm font-medium text-gray-700">Perihal</label>
                <input type="text" id="edit_perihal" name="perihal" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('perihal') border-red-500 @enderror">
                @error('perihal')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="edit_isi_ringkas" class="block text-sm font-medium text-gray-700">Isi Ringkas</label>
                <textarea id="edit_isi_ringkas" name="isi_ringkas" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm @error('isi_ringkas') border-red-500 @enderror"></textarea>
                @error('isi_ringkas')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="mb-4">
                <label for="edit_lampiran" class="block text-sm font-medium text-gray-700">Lampiran Baru (opsional)</label>
                <input type="file" id="edit_lampiran" name="lampiran" accept=".pdf" class="mt-1 block w-full">
                @error('lampiran')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('editModal')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
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

<!-- Disposisi Modal -->
<div id="disposisiModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden" onclick="closeModalOnOutsideClick(event, 'disposisiModal')">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">Tambah Disposisi</h3>

        <!-- FORM -->
        <form id="disposisiForm" method="POST" >
            @csrf
            <input type="hidden" id="disposisi_surat_masuk_id" name="surat_masuk_id">
            <!-- PENERIMA -->
            <div class="mb-4">
                <label for="disposisi_penerima_id" class="block text-sm font-medium text-gray-700">Penerima Disposisi</label>
                <select id="disposisi_penerima_id" name="ke_user_id" class="select2-pengirim mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Pilih Penerima</option>
                </select>
            </div>

            <!-- CATATAN -->
            <div class="mb-4">
                <label for="catatan" class="block text-sm font-medium text-gray-700">Catatan</label>
                <textarea id="catatan" name="instruksi" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
            </div>

            <!-- BUTTON -->
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('disposisiModal')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Kirim Disposisi</button>
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

    function openEditModal(id, nomor_agenda, nomor_surat, tanggal_surat, tanggal_terima, perihal, pengirim_id, pengirim_nama, isi_ringkas, sifat_surat_id, lampiran) {
        const form = document.getElementById('editForm');
        form.action = `/staff/surat-masuk/${id}`; // Set the correct action URL
        document.getElementById('edit_nomor_agenda').value = nomor_agenda;
        document.getElementById('edit_nomor_surat').value = nomor_surat;
        document.getElementById('edit_tanggal_surat').value = tanggal_surat;
        document.getElementById('edit_tanggal_terima').value = tanggal_terima;
        document.getElementById('edit_perihal').value = perihal;
        document.getElementById('edit_isi_ringkas').value = isi_ringkas;
        document.getElementById('edit_sifat_surat_id').value = sifat_surat_id;

        const pengirimSelect = $('#edit_pengirim_id');
        pengirimSelect.empty();
        pengirimSelect.append(new Option(pengirim_nama, pengirim_id, true, true));
        pengirimSelect.trigger('change');

        openModal('editModal');
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

    function openDisposisiModal(suratMasukId) {
        document.getElementById('disposisi_surat_masuk_id').value = suratMasukId;
        document.getElementById('disposisiForm').action = `/staff/surat-masuk/${suratMasukId}/disposisi`;

        // Reset the Select2 dropdown
        const penerimaSelect = $('#disposisi_penerima_id');
        penerimaSelect.val(null).trigger('change');

        openModal('disposisiModal');
    }


    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('[id^="alert-"]');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.class('hidden');
            }, 3000);
        });

       
    });
</script>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        const table = $('#suratMasukTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '{{ route("staff.surat-masuk.index") }}',
                data: function(d) {
                    d.status_id = $('#status_filter').val();
                },
                error: function(xhr) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Gagal memuat data: ' + (xhr.responseText || 'Unknown error'),
                        icon: 'error'
                    });
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'nomor_agenda',
                    name: 'nomor_agenda'
                },
                {
                    data: 'nomor_surat',
                    name: 'nomor_surat'
                },
                {
                    data: 'tanggal_surat',
                    name: 'tanggal_surat'
                },
                {
                    data: 'perihal',
                    name: 'perihal'
                },
                {
                    data: 'pengirim.nama',
                    name: 'pengirim.nama'
                },
                {
                    data: 'status.nama_status',
                    name: 'status.nama_status'
                },
                {
                    data: 'sifat.nama_sifat',
                    name: 'sifat.nama_sifat'
                },
                {
                    data: null,
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        return `
                            <button onclick="openShowModal('${data.nomor_agenda}', '${data.nomor_surat}', '${data.tanggal_surat}', '${data.tanggal_terima}', '${data.perihal}', '${data.pengirim ? data.pengirim.nama : '-'}', '${data.sifat ? data.sifat.nama_sifat : '-'}', '${data.status ? data.status.nama_status : '-'}', '${data.isi_ringkas}', '${data.lampiran ? '/storage/' + data.lampiran : ''}')"
                                class="text-blue-600 hover:text-blue-800 mr-2">
                                <i class="fas fa-eye"></i> Lihat
                            </button>
                            <button onclick="openEditModal(${data.id}, '${data.nomor_agenda}', '${data.nomor_surat}', '${data.tanggal_surat_raw}', '${data.tanggal_terima_raw}', '${data.perihal}', '${data.pengirim_id}', '${data.pengirim ? data.pengirim.nama : ''}', '${data.isi_ringkas}', '${data.sifat_surat_id}', '${data.lampiran ? '/storage/' + data.lampiran : ''}')"
                                class="text-green-600 hover:text-green-800 mr-2">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button onclick="openDisposisiModal(${data.id})"
                                class="text-yellow-600 hover:text-yellow-800 mr-2">
                                <i class="fas fa-tasks"></i> Disposisi
                            </button>
                            <form action="${'{{ route("staff.surat-masuk.destroy", ":id") }}'.replace(':id', data.id)}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Arsipkan surat ini?')"
                                    class="text-red-600 hover:text-red-800">
                                    <i class="fas fa-archive"></i> Arsip
                                </button>
                            </form>
                        `;
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
                emptyTable: 'Tidak ada data surat masuk.',
                processing: 'Memproses...'
            }
        });
        
    // Handle Edit Form Submission with AJAX
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const formData = new FormData(this);

        $.ajax({
            url: form.attr('action'),
            method: 'POST', // Laravel expects POST for updates with _method=PUT
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                closeModal('editModal');
                $('#suratMasukTable').DataTable().ajax.reload();
                Swal.fire({
                    title: 'Berhasil',
                    text: response.message || 'Surat masuk berhasil diperbarui.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors || {};
                let errorMessage = 'Terjadi kesalahan saat memperbarui surat.';
                if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (Object.keys(errors).length) {
                    errorMessage = Object.values(errors).flat().join('<br>');
                }
                Swal.fire({
                    title: 'Error',
                    html: errorMessage,
                    icon: 'error'
                });
            }
        });
    });
         // Initialize Select2 for penerima
        $('.select2-pengirim').select2({
            placeholder: 'Pilih penerima',
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
                                text: `${user.nama} (${user.email} ${user.nip_nim ? ', ' + user.nip_nim : ''})`
                            };
                        })
                    };
                },
                cache: true
            },
            minimumInputLength: 1,
            templateResult: function (data) {
                if (!data.id) {
                    return data.text;
                }
                return $(`
                    <div class="flex flex-col">
                        <span class="font-medium">${data.text.split(' (')[0]}</span>
                        <span class="text-sm text-gray-500">${data.text.split(' (')[1].replace(')', '')}</span>
                    </div>
                `);
            },
            templateSelection: function (data) {
                return data.text.split(' (')[0] || data.text;
            }
        });
        
    // Handle Disposisi Form Submission with AJAX
    $('#disposisiForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                if (response.success) {
                    closeModal('disposisiModal');
                    $('#suratMasukTable').DataTable().ajax.reload();
                    Swal.fire({
                        title: 'Berhasil',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors || {};
                let errorMessage = 'Terjadi kesalahan saat mengirim disposisi.';
                if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (Object.keys(errors).length) {
                    errorMessage = Object.values(errors).flat().join('<br>');
                }
                Swal.fire({
                    title: 'Error',
                    html: errorMessage,
                    icon: 'error'
                });
            }
        });
    });

        $('#status_filter').on('change', function() {
            table.ajax.reload();
        });

        $('#suratMasukTable_filter input').unbind().bind('keyup', function(e) {
            table.search(this.value).draw();
        });
    });
</script>
@endpush
@endsection