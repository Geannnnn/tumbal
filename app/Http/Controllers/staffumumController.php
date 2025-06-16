<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class staffumumController extends Controller
{
    public function index(){
        $columns = [
            'judul_surat' =>'Nama Surat', 
            'nomor_surat' => "Nomor Surat",
            'tanggal_surat_dibuat' => 'Tanggal', 
            'tanggal_pengajuan' => 'Dibuat Pada',
            'status' => 'Status', 
        ];

        return view('staff.staff-umum.index',compact('columns'));
    }

    public function statistik()
    {
        return view('staff.staff-umum.statistik');
    }

    public function terbitkan()
    {
        return view('staff.staff-umum.terbitkan');
    }

    public function statussurat()
    {
        return view('staff.staff-umum.statussurat');
    }

    public function jenissurat()
    {
        return view('staff.staff-umum.jenissurat');
    }
}
