<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KepalaSubController extends Controller
{
    public function index () {
        return view('kepalasub.index');
    }
}
