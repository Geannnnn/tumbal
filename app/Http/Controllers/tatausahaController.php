<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class tatausahaController extends Controller
{
    public function index(){
        return view('staff.tata-usaha.index');
    }

        public function statistik(){
        return view('staff.tata-usaha.statistik');
    }

    public function terbitkan(){
        return view('staff.tata-usaha.terbitkan');
    }

    public function statussurat(){
        return view('staff.tata-usaha.statussurat');
    }

    public function jenissurat(){
        return view('staff.tata-usaha.jenissurat');
    }
}
