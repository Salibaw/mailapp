<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\admin\UserController;
use App\Http\Controllers\admin\UserTypeController;
use App\Http\Controllers\admin\StatusSuratController;
use App\Http\Controllers\admin\SifatSuratController;
use App\Http\Controllers\admin\TemplateController;

use App\Http\Controllers\mahasiswa\SuratKeluarController as MahasiswaSuratKeluarController;
use App\Http\Controllers\mahasiswa\SuratMasukController as MahasiswaSuratMasukController;
use App\Http\Controllers\mahasiswa\DashboardController as MahasiswaDashboardController;

use App\Http\Controllers\staff\DashboardController as StaffDashboardController;
use App\Http\Controllers\staff\SuratMasukController as StaffSuratMasukController;
use App\Http\Controllers\staff\SuratKeluarController as StaffSuratKeluarController;
use App\Http\Controllers\staff\DisposisiController as StaffDisposisiController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::prefix('admin')->middleware(['auth','role:admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // CRUD untuk User
    Route::resource('users', UserController::class);
    Route::post('users/import', [\App\Http\Controllers\Admin\UserController::class, 'import'])->name('admin.users.import');
    // CRUD untuk UserType (Role)
    Route::resource('roles', UserTypeController::class)->except(['show']); // Tidak perlu show untuk detail role

    // CRUD untuk Status Surat
    Route::resource('status-surat', StatusSuratController::class)->except(['show']);

    // CRUD untuk Sifat Surat
    Route::resource('sifat-surat', SifatSuratController::class)->except(['show']);

    // CRUD untuk Template Surat    
    Route::resource('templates', TemplateController::class)->only([
        'index', 'store', 'update', 'destroy'
    ]);
});

Route::prefix('mahasiswa')->middleware(['auth', 'role:mahasiswa'])->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');
    // Tambahkan rute lain yang diperlukan untuk mahasiswa
    Route::resource('surat-masuk', MahasiswaSuratMasukController::class)->except(['show']);
    Route::resource('surat-keluar', MahasiswaSuratKeluarController::class)->except(['show']);
    Route::post('surat-keluar/draft', [MahasiswaSuratKeluarController::class, 'saveDraft'])->name('surat-keluar.draft');
    Route::get('search-users', [MahasiswaSuratKeluarController::class, 'searchUsers'])->name('search-users');
});

Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');

    // CRUD Surat Masuk
    Route::resource('surat-masuk', StaffSuratMasukController::class);

    // CRUD Surat Keluar (verifikasi, penomoran, persetujuan)
    Route::resource('surat-keluar', StaffSuratKeluarController::class);
    Route::get('surat-keluar/{surat_keluar}/setujui', [StaffSuratKeluarController::class, 'showSetujuiForm'])->name('surat-keluar.setujui.form');
    Route::post('surat-keluar/{surat_keluar}/setujui', [StaffSuratKeluarController::class, 'setujui'])->name('surat-keluar.setujui');
    Route::get('surat-keluar/{surat_keluar}/tolak', [StaffSuratKeluarController::class, 'showTolakForm'])->name('surat-keluar.tolak.form');
    Route::post('surat-keluar/{surat_keluar}/tolak', [StaffSuratKeluarController::class, 'tolak'])->name('surat-keluar.tolak');
    Route::get('surat-keluar/{surat_keluar}/generate-pdf', [StaffSuratKeluarController::class, 'generatePdf'])->name('surat-keluar.generate-pdf');


    // Disposisi Surat Masuk
    Route::get('disposisi/{surat_masuk}', [StaffDisposisiController::class, 'create'])->name('disposisi.create');
    Route::post('disposisi/{surat_masuk}', [StaffDisposisiController::class, 'store'])->name('disposisi.store');
    Route::get('disposisi-list', [StaffDisposisiController::class, 'index'])->name('disposisi.index'); // Melihat semua disposisi yang pernah dibuat
    Route::get('disposisi-masuk', [StaffDisposisiController::class, 'disposisiMasuk'])->name('disposisi.masuk'); // Disposisi yang diterima Staff TU

});
require __DIR__ . '/auth.php';
