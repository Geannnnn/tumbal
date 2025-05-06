<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class testController extends Controller
{
    public function test(Request $request){
        return redirect()->back()->with('success', 'Data berhasil disimpan sebagai draft!');
    }
    public function aju(Request $request){
        return redirect()->back()->with('success', 'Pengajuan Berhasil! Surat kamu telah dikirim dan sedang menunggu validasi dari pihak terkait.');
    }
}
