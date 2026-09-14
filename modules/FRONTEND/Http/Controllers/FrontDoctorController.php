<?php

namespace Modules\FRONTEND\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class FrontDoctorController extends Controller
{
    public function index()
    {
        return Inertia::render('FRONTEND::Doctor/Find-doctor');
    }

    public function profile($slug)
    {
        return Inertia::render('FRONTEND::Doctor/Doctor-profile', [
            'slug' => $slug,
        ]);
    }
}