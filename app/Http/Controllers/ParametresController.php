<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ParametresController extends Controller
{
    public function index(): View
    {
        return view('parametres.index');
    }
}
