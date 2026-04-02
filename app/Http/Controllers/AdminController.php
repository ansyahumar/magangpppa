<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HasilIndeks;
use App\Helpers\PenilaianHelper;
use Illuminate\Routing\Controller;

class AdminController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function dashboard()
    {
        return view('admin.dashboard');  
    }

}
