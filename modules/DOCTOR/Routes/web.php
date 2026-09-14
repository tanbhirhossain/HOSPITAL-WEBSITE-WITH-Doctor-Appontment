<?php

use Illuminate\Support\Facades\Route;
use Modules\DOCTOR\Http\Controllers\DepartmentCategoryController;
use Modules\DOCTOR\Http\Controllers\DepartmentController;
use Modules\DOCTOR\Http\Controllers\DoctorController;
use Modules\DOCTOR\Http\Controllers\DoctorExpertiseController;
use Modules\DOCTOR\Http\Controllers\DoctorScheduleController;
use Modules\DOCTOR\Http\Controllers\PageSectionController;
use Modules\DOCTOR\Http\Controllers\SymptomController;

/*
|--------------------------------------------------------------------------
| DOCTOR module — administration
|--------------------------------------------------------------------------
|
| Prefix: /admin/doctors   Name: doctor.*
| Read routes need `auth`; every write is additionally gated by a Spatie
| permission so the ACL screens actually control access.
|
*/

Route::prefix('admin/doctors')
    ->name('doctor.')
    ->middleware(['web', 'auth', 'verified'])
    ->group(function (): void {

        /* --------------------------- Departments -------------------------- */
        Route::get('departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::get('departments/create', [DepartmentController::class, 'create'])
            ->middleware('permission:department.create')
            ->name('departments.create');
        Route::get('departments/{department}/edit', [DepartmentController::class, 'edit'])
            ->middleware('permission:department.view')
            ->name('departments.edit');
        Route::post('departments', [DepartmentController::class, 'store'])
            ->middleware('permission:department.create')
            ->name('departments.store');
        Route::put('departments/{department}', [DepartmentController::class, 'update'])
            ->middleware('permission:department.update')
            ->name('departments.update');
        Route::delete('departments/{department}', [DepartmentController::class, 'destroy'])
            ->middleware('permission:department.delete')
            ->name('departments.destroy');
        Route::patch('departments/{department}/featured', [DepartmentController::class, 'toggleFeatured'])
            ->middleware('permission:department.update')
            ->name('departments.featured');

        /* ----------------------- Department categories -------------------- */
        Route::get('categories', [DepartmentCategoryController::class, 'index'])->name('categories.index');
        Route::post('categories', [DepartmentCategoryController::class, 'store'])
            ->middleware('permission:department.create')
            ->name('categories.store');
        Route::put('categories/{category}', [DepartmentCategoryController::class, 'update'])
            ->middleware('permission:department.update')
            ->name('categories.update');
        Route::delete('categories/{category}', [DepartmentCategoryController::class, 'destroy'])
            ->middleware('permission:department.delete')
            ->name('categories.destroy');

        /* ------------------------------ Doctors --------------------------- */
        Route::get('/', [DoctorController::class, 'index'])->name('index');
        Route::get('create', [DoctorController::class, 'create'])
            ->middleware('permission:doctor.create')
            ->name('create');
        Route::get('{doctor}/edit', [DoctorController::class, 'edit'])
            ->middleware('permission:doctor.view')
            ->name('edit');
        Route::post('/', [DoctorController::class, 'store'])
            ->middleware('permission:doctor.create')
            ->name('store');
        Route::put('{doctor}', [DoctorController::class, 'update'])
            ->middleware('permission:doctor.update')
            ->name('update');
        Route::delete('{doctor}', [DoctorController::class, 'destroy'])
            ->middleware('permission:doctor.delete')
            ->name('destroy');
        Route::patch('{doctor}/featured', [DoctorController::class, 'toggleFeatured'])
            ->middleware('permission:doctor.update')
            ->name('featured');
        Route::patch('{doctor}/status', [DoctorController::class, 'toggleStatus'])
            ->middleware('permission:doctor.update')
            ->name('status');

        /* ---------------------------- Expertises -------------------------- */
        Route::get('expertises', [DoctorExpertiseController::class, 'index'])->name('expertises.index');
        Route::post('expertises', [DoctorExpertiseController::class, 'store'])
            ->middleware('permission:doctor.update')
            ->name('expertises.store');
        Route::put('expertises/{expertise}', [DoctorExpertiseController::class, 'update'])
            ->middleware('permission:doctor.update')
            ->name('expertises.update');
        Route::delete('expertises/{expertise}', [DoctorExpertiseController::class, 'destroy'])
            ->middleware('permission:doctor.update')
            ->name('expertises.destroy');

        /* ---------------------------- Schedules --------------------------- */
        Route::get('schedules', [DoctorScheduleController::class, 'index'])->name('schedules.index');
        Route::post('schedules', [DoctorScheduleController::class, 'store'])
            ->middleware('permission:schedule.create')
            ->name('schedules.store');
        Route::put('schedules/{schedule}', [DoctorScheduleController::class, 'update'])
            ->middleware('permission:schedule.update')
            ->name('schedules.update');
        Route::delete('schedules/{schedule}', [DoctorScheduleController::class, 'destroy'])
            ->middleware('permission:schedule.delete')
            ->name('schedules.destroy');

        /* ----------------------------- Symptoms --------------------------- */
        Route::get('symptoms', [SymptomController::class, 'index'])->name('symptoms.index');
        Route::post('symptoms', [SymptomController::class, 'store'])
            ->middleware('permission:symptom.create')
            ->name('symptoms.store');
        Route::put('symptoms/{symptom}', [SymptomController::class, 'update'])
            ->middleware('permission:symptom.update')
            ->name('symptoms.update');
        Route::delete('symptoms/{symptom}', [SymptomController::class, 'destroy'])
            ->middleware('permission:symptom.delete')
            ->name('symptoms.destroy');

        /* --------------------------- Page sections ------------------------ */
        Route::get('page-sections', [PageSectionController::class, 'index'])->name('sections.index');
        Route::post('page-sections', [PageSectionController::class, 'store'])
            ->middleware('permission:page-section.create')
            ->name('sections.store');
        Route::put('page-sections/{section}', [PageSectionController::class, 'update'])
            ->middleware('permission:page-section.update')
            ->name('sections.update');
        Route::delete('page-sections/{section}', [PageSectionController::class, 'destroy'])
            ->middleware('permission:page-section.delete')
            ->name('sections.destroy');
    });
