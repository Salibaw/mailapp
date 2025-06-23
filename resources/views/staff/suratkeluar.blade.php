@extends('staff.layouts.app')

@section('title', 'Surat Keluar')

@section('page-title', 'Surat Keluar')

@section('content')
<div class="bg-white p-6 rounded-lg shadow">
    <!-- Alerts -->
    @if (session('success'))
    <div id="alert-success" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded relative flex items-center" role="alert">
        <i class="fas fa-check-circle mr-2"></i>
        <span>{{ session('success') }}</span>
        <button onclick="closeAlert('alert-success')" class="absolute right-4 text-green-700 hover:text-green-900">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif
    @if (session('error'))
    <div id="alert-error" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded relative flex items-center" role="alert">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <span>{{ session('error') }}</span>
        <button onclick="closeAlert('alert-error')" class="absolute right-4 text-red-700 hover:text-red-900">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    <!-- Header and Create Button -->
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-semibold text-gray-800">Daftar Surat Keluar</h2>
        <a href="#" onclick="openModal('createModal')" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 flex items-center">
            <i class="fas fa-plus mr-2"></i> Buat Surat Keluar
        </a>
    </div>

    <!-- Filters -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div>
            <label for="status_filter" class="block text-sm font-medium text-gray-700">Filter Status</label>
            <select id="status_filter" class="mt-1 block w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Semua Status</option>
                @foreach ($statusSurat as $status)
                <option value="{{ $status->id }}">{{ $status->nama_status }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="role_filter" class="block text-sm font-medium text-gray-700">Filter Pengirim</label>
            <select id="role_filter" class="mt-1 block w-full max-w-xs border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                <option value="">Semua Pengirim</option>
                <option value="staff">Staff</option>
                <option value="mahasiswa">Mahasiswa</option>
                <option value="dosen">Dosen</option>
            </select>
        </div>
    </div>

    <!-- DataTables -->
    <div class="overflow-x-auto">
        <table id="suratKeluarTable" class="min-w-full bg-white border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">No</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Nomor Surat</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Tanggal Surat</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Perihal</th>
                    <th class="px-6 py-3 text-left text-sm font-medium text-gray-600">Penerima</th>
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
        <h3 class="text-lg font-semibold mb-4">Buat Surat Keluar</h3>
        <form id="createForm" action="{{ route('staff.surat-keluar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
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
                    <label for="nomor_surat" class="block text-sm font-medium text-gray-700">Nomor Surat (Opsional)</label>
                    <input type="text" id="nomor_surat" name="nomor_surat" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="Nomor Surat (Opsional)">
                </div>
                <div>
                    <label for="template_id" class="block text-sm font-medium text-gray-700">Template Surat</label>
                    <select id="template_id" name="template_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Pilih Template (Opsional)</option>
                        @foreach ($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->nama_template }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="penerima_id" class="block text-sm font-medium text-gray-700">Penerima</label>
                    <select id="penerima_id" name="penerima_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="">Pilih Penerima</option>
                    </select>
                </div>
                <div>
                    <label for="tanggal_surat" class="block text-sm font-medium text-gray-700">Tanggal Surat</label>
                    <input type="date" id="tanggal_surat" name="tanggal_surat" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div>
                    <label for="sifat_surat_id" class="block text-sm font-medium text-gray-700">Sifat Surat</label>
                    <select id="sifat_surat_id" name="sifat_surat_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="">Pilih Sifat</option>
                        @foreach ($sifatSurat as $sifat)
                        <option value="{{ $sifat->id }}">{{ $sifat->nama_sifat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label for="perihal" class="block text-sm font-medium text-gray-700">Perihal</label>
                <input type="text" id="perihal" name="perihal" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>
            <div class="mb-4">
                <label for="isi_surat" class="block text-sm font-medium text-gray-700">Isi Surat</label>
                <textarea id="isi_surat" name="isi_surat" rows="6" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></textarea>
            </div>
            <div class="mb-4">
                <label for="catatan_surat" class="block text-sm font-medium text-gray-700">Catatan (Opsional)</label>
                <textarea id="catatan_surat" name="catatan_surat" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
            </div>
            <div class="mb-4">
                <label for="lampiran" class="block text-sm font-medium text-gray-700">Lampiran</label>
                <input type="file" id="lampiran" name="lampiran" accept=".pdf" class="mt-1 block w-full">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('createModal')" class="bg-gray-300 text-gray-800 px-4 py-2 rounded hover:bg-gray-400">Batal</button>
                <button type="submit" name="action" value="draft" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">Simpan Draft</button>
                <button type="submit" name="action" value="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">Kirim</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden" onclick="closeModalOnOutsideClick(event, 'editModal')">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">Edit Surat Keluar</h3>
        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
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
                    <label for="edit_template_id" class="block text-sm font-medium text-gray-700">Template Surat</label>
                    <select id="edit_template_id" name="template_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Pilih Template (Opsional)</option>
                        @foreach ($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->nama_template }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="edit_penerima_id" class="block text-sm font-medium text-gray-700">Penerima</label>
                    <select id="edit_penerima_id" name="penerima_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="">Pilih Penerima</option>
                    </select>
                </div>
                <div>
                    <label for="edit_tanggal_surat" class="block text-sm font-medium text-gray-700">Tanggal Surat</label>
                    <input type="date" id="edit_tanggal_surat" name="tanggal_surat" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                </div>
                <div>
                    <label for="edit_sifat_surat_id" class="block text-sm font-medium text-gray-700">Sifat Surat</label>
                    <select id="edit_sifat_surat_id" name="sifat_surat_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                        <option value="">Pilih Sifat</option>
                        @foreach ($sifatSurat as $sifat)
                        <option value="{{ $sifat->id }}">{{ $sifat->nama_sifat }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label for="edit_perihal" class="block text-sm font-medium text-gray-700">Perihal</label>
                <input type="text" id="edit_perihal" name="perihal" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>
            <div class="mb-4">
                <label for="edit_isi_surat" class="block text-sm font-medium text-gray-700">Isi Surat</label>
                <textarea id="edit_isi_surat" name="isi_surat" rows="6" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></textarea>
            </div>
            <div class="mb-4">
                <label for="edit_catatan_surat" class="block text-sm font-medium text-gray-700">Catatan (Opsional)</label>
                <textarea id="edit_catatan_surat" name="catatan_surat" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
            </div>
            <div class="mb-4">
                <label for="edit_lampiran" class="block text-sm font-medium text-gray-700">Lampiran Baru (opsional)</label>
                <input type="file" id="edit_lampiran" name="lampiran" accept=".pdf" class="mt-1 block w-full">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('editModal')" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</button>
                <button type="submit" name="action" value="draft" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">Simpan Draft</button>
                <button type="submit" name="action" value="submit" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">Kirim</button>
            </div>
        </form>
    </div>
</div>

<!-- Show Modal -->
<div id="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden" onclick="closeModalOnOutsideClick(event, 'showModal')">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">Detail Surat Keluar</h3>
        <div class="mb-4">
            <p><strong>Nomor Surat:</strong> <span id="show_nomor_surat"></span></p>
            <p><strong>Tanggal Surat:</strong> <span id="show_tanggal_surat"></span></p>
            <p><strong>Perihal:</strong> <span id="show_perihal"></span></p>
            <p><strong>Pengirim:</strong> <span id="show_pengirim"></span></p>
            <p><strong>Penerima:</strong> <span id="show_penerima"></span></p>
            <p><strong>Sifat Surat:</strong> <span id="show_sifat_surat"></span></p>
            <p><strong>Status:</strong> <span id="show_status"></span></p>
            <p><strong>Catatan:</strong> <span id="show_catatan_surat"></span></p>
            <p><strong>Isi Surat:</strong></p>
            <div id="show_isi_surat" class="border p-2 rounded"></div>
            <p><strong>Lampiran:</strong> <a id="show_lampiran" href="#" target="_blank" class="text-blue-600 hover:underline"></a></p>
        </div>
        <div class="flex justify-end">
            <button type="button" onclick="closeModal('showModal')" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Tutup</button>
        </div>
    </div>
</div>

<!-- Validate Modal -->
<div id="validateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden" onclick="closeModalOnOutsideClick(event, 'validateModal')">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">Validasi Surat Keluar</h3>
        <form id="validateForm" method="POST">
            @csrf
            <div class="mb-4">
                <label for="nomor_surat" class="block text-sm font-medium text-gray-700">Nomor Surat</label>
                <input type="text" id="nomor_surat" name="nomor_surat" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>
            <div class="mb-4">
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
                    <option value="Disetujui">Disetujui</option>
                    <option value="Ditolak">Ditolak</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="catatan_surat" class="block text-sm font-medium text-gray-700">Catatan (opsional)</label>
                <textarea id="catatan_surat" name="catatan_surat" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('validateModal')" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</button>
                <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Number Modal -->
<div id="assignNumberModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden" onclick="closeModalOnOutsideClick(event, 'assignNumberModal')">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full" onclick="event.stopPropagation()">
        <h3 class="text-lg font-semibold mb-4">Berikan Nomor Surat</h3>
        <form id="assignNumberForm" method="POST">
            @csrf
            <div class="mb-4">
                <label for="assign_nomor_surat" class="block text-sm font-medium text-gray-700">Nomor Surat</label>
                <input type="text" id="assign_nomor_surat" name="nomor_surat" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal('assignNumberModal')" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</button>
                <button type="submit" class="bg-indigo-500 text-white px-4 py-2 rounded hover:bg-indigo-600">Simpan</button>
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
        if (modalId === 'createModal' || modalId === 'editModal') {
            document.getElementById(modalId === 'createModal' ? 'createForm' : 'editForm').reset();
            $('#penerima_id, #edit_penerima_id').val(null).trigger('change');
        }
    }

    function closeModalOnOutsideClick(event, modalId) {
        if (event.target.id === modalId) {
            closeModal(modalId);
        }
    }

    function closeAlert(alertId) {
        document.getElementById(alertId).classList.add('hidden');
    }

    function openEditModal(id, tanggal_surat, perihal, penerima_id, penerima_nama, isi_surat, sifat_surat_id, template_id, lampiran, catatan_surat) {
        const form = document.getElementById('editForm');
        form.action = '{{ route("staff.surat-keluar.update", ":id") }}'.replace(':id', id);
        document.getElementById('edit_tanggal_surat').value = tanggal_surat || '';
        document.getElementById('edit_perihal').value = perihal || '';
        document.getElementById('edit_isi_surat').value = isi_surat || '';
        document.getElementById('edit_sifat_surat_id').value = sifat_surat_id || '';
        document.getElementById('edit_template_id').value = template_id || '';
        document.getElementById('edit_catatan_surat').value = catatan_surat || '';

        const penerimaSelect = $('#edit_penerima_id');
        penerimaSelect.empty().append(new Option(penerima_nama || 'Pilih Penerima', penerima_id || '', true, !!penerima_id)).trigger('change');

        openModal('editModal');
    }

    function openShowModal(nomor_surat, tanggal_surat, perihal, pengirim, penerima, sifat_surat, status, catatan_surat, isi_surat, lampiran) {
        document.getElementById('show_nomor_surat').textContent = nomor_surat || '-';
        document.getElementById('show_tanggal_surat').textContent = tanggal_surat || '-';
        document.getElementById('show_perihal').textContent = perihal || '-';
        document.getElementById('show_pengirim').textContent = pengirim || '-';
        document.getElementById('show_penerima').textContent = penerima || '-';
        document.getElementById('show_sifat_surat').textContent = sifat_surat || '-';
        document.getElementById('show_status').textContent = status || '-';
        document.getElementById('show_catatan_surat').textContent = catatan_surat || '-';
        document.getElementById('show_isi_surat').textContent = isi_surat || '-';
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

    function openValidateModal(suratKeluarId, nomor_surat) {
        const form = document.getElementById('validateForm');
        form.action = '{{ route("staff.surat-keluar.validate", ":id") }}'.replace(':id', suratKeluarId);
        document.getElementById('nomor_surat').value = nomor_surat || '';
        document.getElementById('status').value = '';
        document.getElementById('catatan_surat').value = '';
        openModal('validateModal');
    }

    function openAssignNumberModal(suratKeluarId, nomor_surat) {
        const form = document.getElementById('assignNumberForm');
        form.action = '{{ route("staff.surat-keluar.number", ":id") }}'.replace(':id', suratKeluarId);
        document.getElementById('assign_nomor_surat').value = nomor_surat || '';
        openModal('assignNumberModal');
    }

    function forwardForApproval(id) {
        Swal.fire({
            title: 'Kirim untuk Persetujuan?',
            text: 'Surat akan dikirim untuk validasi.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Kirim',
            cancelButtonText: 'Batal',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return fetch(`{{ route("staff.surat-keluar.forward", ":id") }}`.replace(':id', id), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (!data.success) throw new Error(data.message || 'Unknown error');
                    return data;
                })
                .catch(error => {
                    Swal.showValidationMessage(`Gagal: ${error.message}`);
                });
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Berhasil!', result.value.message, 'success').then(() => {
                    $('#suratKeluarTable').DataTable().ajax.reload();
                });
            }
        });
    }

    function deleteSurat(id) {
        Swal.fire({
            title: 'Hapus Surat?',
            text: 'Surat ini akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`{{ route("staff.surat-keluar.destroy", ":id") }}`.replace(':id', id), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Berhasil!', data.message, 'success').then(() => {
                            $('#suratKeluarTable').DataTable().ajax.reload();
                        });
                    } else {
                        Swal.fire('Gagal!', data.message, 'error');
                    }
                })
                .catch(error => {
                    Swal.fire('Error!', 'Gagal menghapus surat: ' + error, 'error');
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        const alerts = document.querySelectorAll('[id^="alert-"]');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.classList.add('hidden');
            }, 5000); // Alert akan hilang setelah 5 detik
        });

        $('#penerima_id, #edit_penerima_id').select2({
            ajax: {
                url: '{{ route("staff.search-users") }}',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term
                    };
                },
                processResults: function(data) {
                    console.log('Data received:', data); // Debug
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
            minimumInputLength: 1,
            allowClear: true
        }).on('select2:open', function() {
            console.log('Select2 opened'); // Debug
        });

        $('#template_id, #edit_template_id').on('change', function() {
            const templateId = $(this).val();
            const textareaId = $(this).attr('id').includes('edit') ? 'edit_isi_surat' : 'isi_surat';
            if (templateId) {
                fetch(`{{ route("staff.template-surat.show", ":id") }}`.replace(':id', templateId))
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.isi_template) {
                            document.getElementById(textareaId).value = data.isi_template;
                        } else {
                            console.log('No isi_template in response:', data);
                            document.getElementById(textareaId).value = '';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching template:', error);
                        Swal.fire('Error!', 'Gagal memuat template: ' + error.message, 'error');
                    });
            } else {
                document.getElementById(textareaId).value = '';
            }
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
    $(document).ready(function() {
        const table = $('#suratKeluarTable').DataTable({
            serverSide: true,
            processing: true,
            ajax: {
                url: '{{ route("staff.surat-keluar.index") }}',
                data: function(d) {
                    d.status_id = $('#status_filter').val();
                    d.role = $('#role_filter').val();
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
                { data: 'nomor_surat', name: 'nomor_surat' },
                { data: 'tanggal_surat', name: 'tanggal_surat' },
                { data: 'perihal', name: 'perihal' },
                { data: 'penerima.nama', name: 'penerima.nama' },
                { data: 'user.nama', name: 'user.nama' },
                { data: 'status.nama_status', name: 'status.nama_status' },
                { data: 'sifat.nama_sifat', name: 'sifat.nama_sifat' },
                {
                    data: null,
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data) {
                        let escapedNomorSurat = (data.nomor_surat || '').replace(/"/g, '"').replace(/\n/g, '\\n');
                        let escapedPerihal = (data.perihal || '').replace(/"/g, '"').replace(/\n/g, '\\n');
                        let escapedIsiSurat = (data.isi_surat || '').replace(/"/g, '"').replace(/\n/g, '\\n');
                        let escapedPengirim = (data.user ? data.user.nama : '-').replace(/"/g, '"').replace(/\n/g, '\\n');
                        let escapedPenerima = (data.penerima ? data.penerima.nama : '-').replace(/"/g, '"').replace(/\n/g, '\\n');
                        let escapedSifat = (data.sifat ? data.sifat.nama_sifat : '-').replace(/"/g, '"').replace(/\n/g, '\\n');
                        let escapedStatus = (data.status ? data.status.nama_status : '-').replace(/"/g, '"').replace(/\n/g, '\\n');
                        let escapedCatatan = (data.catatan_surat || '-').replace(/"/g, '"').replace(/\n/g, '\\n');
                        let lampiranUrl = data.lampiran ? '/storage/' + data.lampiran : '';
                        let tanggalSuratRaw = data.tanggal_surat_raw || '';

                        let actions = `
                            <button onclick="openShowModal('${escapedNomorSurat}', '${data.tanggal_surat}', '${escapedPerihal}', '${escapedPengirim}', '${escapedPenerima}', '${escapedSifat}', '${escapedStatus}', '${escapedCatatan}', '${escapedIsiSurat}', '${lampiranUrl}')"
                                class="text-blue-600 hover:text-blue-800 mr-2">
                                <i class="fas fa-eye"></i> Lihat
                            </button>
                        `;

                        if (data.status && data.status.nama_status === 'Draf') {
                            actions += `
                                <button onclick="openEditModal(${data.id}, '${tanggalSuratRaw}', '${escapedPerihal}', '${data.penerima_id || ''}', '${escapedPenerima}', '${escapedIsiSurat}', '${data.sifat_surat_id || ''}', '${data.template_surat_id || ''}', '${lampiranUrl}', '${escapedCatatan}')"
                                    class="text-green-600 hover:text-green-800 mr-2">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button onclick="forwardForApproval(${data.id})"
                                    class="text-yellow-600 hover:text-yellow-800 mr-2">
                                    <i class="fas fa-paper-plane"></i> Kirim
                                </button>
                                <button onclick="deleteSurat(${data.id})"
                                    class="text-red-600 hover:text-red-800 mr-2">
                                    <i class="fas fa-trash"></i> Hapus
                                </button>
                            `;
                        } else if (data.status && data.status.nama_status === 'Menunggu Validasi') {
                            actions += `
                                <button onclick="openAssignNumberModal(${data.id}, '${escapedNomorSurat}')"
                                    class="text-orange-600 hover:text-orange-800 mr-2">
                                    <i class="fas fa-tag"></i> Beri Nomor
                                </button>
                                <button onclick="openValidateModal(${data.id}, '${escapedNomorSurat}')"
                                    class="text-purple-600 hover:text-purple-800 mr-2">
                                    <i class="fas fa-check-circle"></i> Validasi
                                </button>
                            `;
                        } else if (data.status && data.status.nama_status === 'Disetujui') {
                            actions += `
                                <a href="{{ route('staff.surat-keluar.download', ':id') }}".replace(':id', ${data.id})
                                    class="text-green-600 hover:text-green-800 mr-2">
                                    <i class="fas fa-download"></i> Unduh PDF
                                </a>
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
                emptyTable: 'Tidak ada data surat keluar.',
                processing: 'Memproses...'
            }
        });

        $('#status_filter, #role_filter').on('change', function() {
            table.ajax.reload();
        });

        $('#suratKeluarTable_filter input').unbind().bind('keyup', function(e) {
            table.search(this.value).draw();
        });
    });
</script>
@endpush
@endsection