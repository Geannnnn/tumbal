<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class tatausahaController extends Controller
{
    public function index(){
        return view('staff.tata-usaha.index');
    }
}
