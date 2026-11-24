<?php

use App\Http\Controllers\Alumni\CancelAlumniVideoUploadController;
use App\Http\Controllers\Alumni\ConfirmAlumniRegistrationController;
use App\Http\Controllers\Alumni\PresignAlumniVideoController;
use App\Http\Controllers\AlumniExportController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::alumni.landing')->name('home');
Route::livewire('/alumni/daftar', 'pages::alumni.register')->middleware('throttle:alumni-registration')->name('alumni.register');
Route::prefix('alumni/daftar')->name('alumni.registration.')->group(function (): void {
    Route::post('presign', PresignAlumniVideoController::class)->middleware('throttle:alumni-video-presign')->name('presign');
    Route::post('confirm', ConfirmAlumniRegistrationController::class)->middleware('throttle:alumni-video-confirm')->name('confirm');
    Route::delete('upload', CancelAlumniVideoUploadController::class)->middleware('throttle:alumni-video-confirm')->name('cancel');
});
Route::livewire('/alumni/sukses/{kode}', 'pages::alumni.success')->name('alumni.success');
Route::livewire('/alumni/revisi/{kode}/{token}', 'pages::alumni.revision')->name('alumni.revision');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', 'alumni/dashboard')->name('dashboard');
    Route::prefix('alumni')->name('alumni.')->group(function (): void {
        Route::livewire('dashboard', 'pages::alumni.dashboard')->middleware('can:alumni.view')->name('dashboard');
        Route::livewire('peserta', 'pages::alumni.participants')->middleware('can:alumni.view')->name('participants');
        Route::livewire('peserta/{peserta}', 'pages::alumni.participant-detail')->middleware('can:alumni.view')->name('participants.show');
        Route::livewire('kurasi', 'pages::alumni.curation')->middleware('can:alumni.curate')->name('curation');
        Route::livewire('publikasi', 'pages::alumni.publications')->middleware('can:alumni.publish')->name('publications');
        Route::livewire('penilaian', 'pages::alumni.scoring')->middleware('can:alumni.score')->name('scoring');
        Route::livewire('pengaturan', 'pages::alumni.settings')->middleware('can:alumni.manage')->name('settings');
        Route::get('ekspor/peserta', AlumniExportController::class)->middleware('can:alumni.export')->name('export.participants');
    });
});

require __DIR__.'/settings.php';
