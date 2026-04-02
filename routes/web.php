<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\PenilaianKriteriaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DataMasterController;
use App\Http\Controllers\Verifikator\VerifikasiController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\P1Controller;
use App\Http\Controllers\IndikatorController;


Route::get('/dashboard-redirect', function () {
    $role = auth::user()->role;
    return match($role) {
        'admin'      => redirect()->route('admin.dashboard'),
        'verifikator' => redirect()->route('verifikator.verifikasi'),
        'pemimpin'          => redirect()->route('p1.chart'),
        'p2'          => redirect()->route('p2.target'),
        'user'        => redirect()->route('penilaian.form'),
        default       => abort(403),
    };
})->middleware(['auth'])->name('dashboard.redirect');

Route::get('/', function () {
    return view('welcome');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', 'verified', 'role:user'])->group(function () {
   
    Route::get('/dashboard', function () {
        return redirect()->route('penilaian.form');
    })->name('dashboard');
    
   });
Route::middleware(['auth', 'verified', 'role:user'])->group(function () {
        Route::post('/form-penilaian', [PenilaianController::class, 'process'])->name('penilaian.process');
        Route::get('/form-penilaian', [PenilaianController::class, 'form'])->name('penilaian.form');

});

Route::middleware(['auth', 'role:verifikator'])->group(function () {
    Route::get('/verifikator/dashboard', [VerifikasiController::class, 'index'])->name('verifikator.verifikasi');
    Route::get('/verifikator/penilaian', [VerifikasiController::class, 'index'])->name('verifikator.penilaian');
    Route::get('/verifikator/penilaian/{tahun}', [VerifikasiController::class, 'listPenilaian'])->name('verifikator.list');
    Route::post('/verifikasi/store', [VerifikasiController::class, 'storeVerifikasi'])->name('verifikator.storeVerifikasi');
});


Route::middleware(['auth', 'role:p1'])->prefix('p1')->group(function () {
    Route::get('nilai', [P1Controller::class, 'lihatNilai'])->name('p1.nilai');
    Route::get('chart', [P1Controller::class, 'lihatChart'])->name('p1.chart');
    Route::get('hasil', [P1Controller::class, 'lihatNilai'])->name('p1.hasil');
});

Route::middleware(['auth', 'role:p2'])->group(function () {
    Route::get('/p2/dashboard', [PenilaianController::class, 'dashboardP2'])->name('p2.dashboard');
    Route::get('/p2/target', [PenilaianController::class, 'targetP2'])->name('p2.target');
});


Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/hasil', [PenilaianController::class, 'hasilPenilaian'])->name('hasil');
    Route::get('/monitoring', [PenilaianController::class, 'monitorAdmin'])->name('monitoring');
    Route::post('/finalisasi-eksternal', [PenilaianController::class, 'finalisasiEksternal'])->name('finalisasi_eksternal');
    

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');


    Route::get('/profile', function () {
        return view('admin.profile', ['user' => Auth::user()]);
    })->name('profileadmin');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/master', [DataMasterController::class, 'index'])->name('master.index');

    Route::post('/admin/domain/store', [DataMasterController::class, 'storeDomain'])->name('admin.master.domain.store');
    Route::put('/admin/domain/{id}/update', [DataMasterController::class, 'updateDomain'])->name('admin.master.domain.update');
    Route::delete('/admin/domain/{id}/delete', [DataMasterController::class, 'deleteDomain'])->name('admin.master.domain.delete');
    Route::put('/admin/domain/{id}/bobot', [DataMasterController::class, 'updateBobotDomain'])->name('domain.bobot.update');

    Route::post('/admin/aspek/store', [DataMasterController::class, 'storeAspek'])->name('admin.master.aspek.store');
    Route::put('/admin/aspek/{id}/update', [DataMasterController::class, 'updateAspek'])->name('aspek.update');
    Route::delete('/admin/aspek/{id}/delete', [DataMasterController::class, 'deleteAspek'])->name('aspek.delete');
    Route::put('/admin/aspek/{id}/bobot', [DataMasterController::class, 'updateBobotAspek'])->name('aspek.bobot.update');

    Route::post('/admin/indikator/store', [DataMasterController::class, 'storeIndikator'])->name('admin.master.indikator.store');
    Route::put('/admin/indikator/{id}/update', [DataMasterController::class, 'updateIndikator'])->name('admin.master.indikator.update');
    Route::delete('/admin/indikator/{id}/delete', [DataMasterController::class, 'deleteIndikator'])->name('admin.master.indikator.delete');

    Route::post('/admin/kriteria/store', [DataMasterController::class, 'storeKriteria'])->name('kriteria.store');
    Route::put('/admin/kriteria/{id}/update', [DataMasterController::class, 'updateKriteria'])->name('kriteria.update');
    Route::delete('/admin/kriteria/{id}/delete', [DataMasterController::class, 'deleteKriteria'])->name('kriteria.delete');
    Route::get('/admin/master-data', [DataMasterController::class, 'index'])->name('admin.master');

    Route::post('/penjelasan/store', [DataMasterController::class, 'storePenjelasan'])->name('penjelasan.store');
    Route::put('/penjelasan/{id}/update', [DataMasterController::class, 'updatePenjelasan'])->name('penjelasan.update');
    Route::delete('/penjelasan/{id}/delete', [DataMasterController::class, 'deletePenjelasan'])->name('penjelasan.delete');


Route::post('/admin/master-data/copy', [DataMasterController::class, 'copyStructure'])->name('admin.master.copy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/indikator/{id}/detail', [PenilaianKriteriaController::class, 'show'])->name('penilaian.detail');
    Route::get('/indikator/{id}/modal', [PenilaianKriteriaController::class, 'modalIndikator']);
    Route::post('/penilaian-kriteria/store', [PenilaianKriteriaController::class, 'store'])->name('penilaian-kriteria.store');
    Route::get('/second-landing', function () { return view('secondLanding'); })->name('secondLandingPage');
Route::middleware(['auth', 'role:p2'])->group(function () {
    Route::post('/p2/processTarget', [PenilaianController::class, 'finalisasiTarget'])->name('target.finalisasi');
});
Route::post('/penilaian/finalisasi-verifikator', [PenilaianController::class, 'finalisasiVerifikator'])
    ->name('penilaian.finalisasi_verifikator');
});

Route::get('/panduan/{id_indikator}/{tipe}', [IndikatorController::class, 'showPanduan'])
    ->name('panduan.show');
   
    Route::post('/admin/master/aspek/move', [DataMasterController::class, 'moveAspek']);
Route::post('/admin/master/indikator/move', [DataMasterController::class, 'moveIndikator']);
Route::post('/admin/master/domain/move', [DataMasterController::class, 'moveDomain']);
Route::get('/view-bukti/{filename}', function ($filename) {
    $path = storage_path('app/public/bukti_spbe/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Content-Type' => 'application/pdf',
    ]);
})->name('view.bukti');
Route::get('/admin/kriteria/master/{id_indikator}', [PenilaianKriteriaController::class, 'getKriteriaMaster'])->name('admin.kriteria.master');

Route::get('/admin/monitoring', [DataMasterController::class, 'monitoring'])->name('admin.monitoring');
Route::get('/view-bukti/{file}', function ($file) {
    $path = storage_path('app/bukti/' . $file);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
});

Route::get('/dashboard-redirect', function () {
    $role = strtolower(auth::user()->role);
    return match($role) {
        'admin'       => redirect()->route('admin.dashboard'),
        'verifikator' => redirect()->route('verifikator.verifikasi'),
        'p2'   => redirect()->route('p2.dashboard'),
        'p1'    => redirect()->route('p1.nilai'),
        default       => redirect()->route('dashboard'),
    };
})->name('dashboard.redirect');

require __DIR__.'/auth.php';