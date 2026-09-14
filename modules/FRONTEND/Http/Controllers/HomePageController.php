<?php 

namespace Modules\FRONTEND\Http\Controllers;

use App\Http\Controllers\Controller;

class HomePageController extends Controller
{
    public function index()
    {
        
        return view('FRONTEND::index');
    }
}