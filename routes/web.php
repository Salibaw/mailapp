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
use App\Http\Controllers\staff\TemplateController as StaffTemplateController;

use App\Http\Controllers\pimpinan\DashboardController as PimpinanDashboardController;
use App\Http\Controllers\pimpinan\SuratKeluarController as PimpinanSuratKeluarController;
use App\Http\Controllers\pimpinan\SuratMasukController as PimpinanSuratMasukController;
use App\Http\Controllers\pimpinan\DisposisiController as PimpinanDisposisiController;
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

Route::prefix('admin')->middleware(['auth', 'role:admin'])->name('admin.')->group(function () {
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
        'index',
        'store',
        'update',
        'destroy'
    ]);
});

// cari data pengirim dan penerima surat
Route::get('search-users', [StaffSuratKeluarController::class, 'searchUsers'])->name('mahasiswa.search-users');

Route::prefix('mahasiswa')->middleware(['auth', 'role:mahasiswa'])->name('mahasiswa.')->group(function () {
    Route::get('/dashboard', [MahasiswaDashboardController::class, 'index'])->name('dashboard');
    // Tambahkan rute lain yang diperlukan untuk mahasiswa
    Route::resource('surat-masuk', MahasiswaSuratMasukController::class)->except(['show']);
    Route::resource('surat-keluar', MahasiswaSuratKeluarController::class)->except(['show']);
    Route::post('surat-keluar/draft', [MahasiswaSuratKeluarController::class, 'saveDraft'])->name('surat-keluar.draft');
    // Route::get('search-users', [MahasiswaSuratKeluarController::class, 'searchUsers'])->name('search-users');
});
Route::prefix('pimpinan')->middleware(['auth', 'role:pimpinan'])->name('pimpinan.')->group(function () {
    Route::get('/dashboard', [PimpinanDashboardController::class, 'index'])->name('dashboard');

    // Persetujuan Surat Keluar
    Route::get('surat-keluar', [PimpinanSuratKeluarController::class, 'index'])->name('surat-keluar.index');
    Route::get('surat-keluar/{surat_keluar}', [PimpinanSuratKeluarController::class, 'show'])->name('surat-keluar.show');
    Route::post('surat-keluar/{surat_keluar}/approve', [PimpinanSuratKeluarController::class, 'approve'])->name('surat-keluar.approve');
    Route::post('surat-keluar/{surat_keluar}/reject', [PimpinanSuratKeluarController::class, 'reject'])->name('surat-keluar.reject');
    Route::get('/surat-keluar/{suratKeluar}/download', [PimpinanSuratKeluarController::class, 'download'])->name('surat-keluar.download');
    // Melihat Surat Masuk (read-only)
    Route::get('surat-masuk', [PimpinanSuratMasukController::class, 'index'])->name('surat-masuk.index');
    Route::get('surat-masuk/{surat_masuk}', [PimpinanSuratMasukController::class, 'show'])->name('surat-masuk.show');

    // Melihat Disposisi (read-only, mungkin yang ditujukan kepadanya)
    Route::get('disposisi', [PimpinanDisposisiController::class, 'index'])->name('disposisi.index');
    Route::get('disposisi/{disposisi}', [PimpinanDisposisiController::class, 'show'])->name('disposisi.show');
});

Route::prefix('staff')->middleware(['auth', 'role:staff,dosen'])->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');

    Route::resource('surat-masuk', StaffSuratMasukController::class);
    Route::post('surat-masuk/{suratMasuk}/disposisi', [StaffSuratMasukController::class, 'storeDisposisi'])->name('surat-masuk.disposisi.store');
    Route::post('surat-masuk/{suratMasuk}/archive', [StaffSuratMasukController::class, 'archive'])->name('surat-masuk.archive');
    Route::get('surat-masuk/actionable', [StaffDashboardController::class, 'actionable'])->name('surat-masuk.actionable');

    Route::get('/surat-keluar', [StaffSuratKeluarController::class, 'index'])->name('surat-keluar.index');
    Route::post('/surat-keluar', [StaffSuratKeluarController::class, 'store'])->name('surat-keluar.store');
    Route::put('/surat-keluar/{suratKeluar}', [StaffSuratKeluarController::class, 'update'])->name('surat-keluar.update');
    Route::delete('/surat-keluar/{suratKeluar}', [StaffSuratKeluarController::class, 'destroy'])->name('surat-keluar.destroy');
    Route::post('/surat-keluar/{suratKeluar}/validate', [StaffSuratKeluarController::class, 'validateSurat'])->name('surat-keluar.validate');
    Route::post('/surat-keluar/{suratKeluar}/number', [StaffSuratKeluarController::class, 'assignNumber'])->name('surat-keluar.number');
    Route::post('/surat-keluar/{suratKeluar}/forward', [StaffSuratKeluarController::class, 'forwardForApproval'])->name('surat-keluar.forward');
    Route::get('/surat-keluar/{suratKeluar}/download', [StaffSuratKeluarController::class, 'download'])->name('surat-keluar.download');
    Route::get('/search-users', [StaffSuratKeluarController::class, 'searchUsers'])->name('search-users');
    Route::get('/template-surat/{id}', [StaffTemplateController::class, 'show'])->name('template-surat.show');

    Route::resource('templates', StaffTemplateController::class)->only([
        'index',
        'store',
        'update',
        'destroy',
    ]);

    Route::resource('disposisi', StaffDisposisiController::class)->only(['index']);
    Route::post('disposisi/{disposisi}/forward', [StaffDisposisiController::class, 'forward'])->name('disposisi.forward');
    Route::post('disposisi/{disposisi}/complete', [StaffDisposisiController::class, 'complete'])->name('disposisi.complete');

    Route::get('search-users', [StaffDisposisiController::class, 'searchUsers'])->name('search-users');
    Route::get('search-users', [StaffSuratMasukController::class, 'searchUsers'])->name('search-users');
});
require __DIR__ . '/auth.php';
