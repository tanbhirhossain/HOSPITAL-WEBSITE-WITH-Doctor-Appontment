<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['web', 'auth'])->group(function (): void {
    //
});

Route::get('/home', function () {
    return Inertia::render('FRONTEND::Index');
})->name('welcome');

Route::get('/find-doctor', function () {
    return Inertia::render('FRONTEND::Doctor/Find-doctor');
})->name('find-doctor');

Route::get('/doctors/profile', function () {
    return Inertia::render('FRONTEND::Doctor/Doctor-profile');
})->name('doctor-profile');


Route::get('/departments', function () {
    return Inertia::render('FRONTEND::Department/Department');
})->name('departments');

Route::get('/blog', function () {
    return Inertia::render('FRONTEND::Index');
})->name('blogs');

Route::get('/about', function () {
    return Inertia::render('FRONTEND::Index');
})->name('about');

Route::get('/about', function () {
    return Inertia::render('FRONTEND::Index');
})->name('appointments.guide');


