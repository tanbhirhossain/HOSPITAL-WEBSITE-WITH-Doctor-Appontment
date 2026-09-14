<?php 

namespace Modules\FRONTEND\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class HomePageController extends Controller
{
    public function index()
    {
        
        return Inertia::render('FRONTEND::Index');
    }
}