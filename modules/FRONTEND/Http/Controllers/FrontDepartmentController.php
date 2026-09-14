<?php

namespace Modules\FRONTEND\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class FrontDepartmentController extends Controller
{
    public function index()
    {
        
        return Inertia::render('FRONTEND::Department/Department');
    }

    public function single($slug)
    {
        return Inertia::render('FRONTEND::Department/Single', [
            'slug' => $slug,
        ]);
    }
}