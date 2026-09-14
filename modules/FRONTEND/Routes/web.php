<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Modules\FRONTEND\Http\Controllers\FrontDepartmentController;
use Modules\FRONTEND\Http\Controllers\FrontDoctorController;
use Modules\FRONTEND\Http\Controllers\HomePageController;

Route::middleware(['web', 'auth'])->group(function (): void {
    //
});

Route::get('/', [HomePageController::class, 'index'])->name('welcome');
Route::get('/find-doctor', [FrontDoctorController::class, 'index'])->name('find-doctor');
Route::get('/doctors/profile/{slug}', [FrontDoctorController::class, 'profile'])->name('doctor-profile');
Route::get('/departments', [FrontDepartmentController::class, 'index'])->name('departments');
Route::get('/departments/{slug}', [FrontDepartmentController::class, 'single'])->name('departments.single');

Route::get('/blog', function () {
    return Inertia::render('FRONTEND::Index');
})->name('blogs');

Route::get('/about', function () {
    return Inertia::render('FRONTEND::Index');
})->name('about');

Route::get('/about', function () {
    return Inertia::render('FRONTEND::Index');
})->name('appointments.guide');


