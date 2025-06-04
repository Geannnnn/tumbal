<?php

namespace App\Http\Controllers;

use App\Models\Pengusul;
use App\Models\Surat;
use Illuminate\Http\Request;

class PengusulController extends Controller
{
    public function searchAnggota(Request $request)
    {
        $query = $request->input('query');

        $pengusuls = Pengusul::where(function ($q) use ($query) {
            $q->where('nama', 'like', "%$query%")
            ->orWhere('nip', 'like', "%$query%")
            ->orWhere('nim', 'like', "%$query%");
        })->limit(6)->get(['id_pengusul', 'nama', 'nip', 'nim']);

        return response()->json($pengusuls);
    }

   public function pengaturan(){
        return view('pengaturan');
   }     

   


}
