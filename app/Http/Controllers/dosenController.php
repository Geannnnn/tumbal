<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\Pengusul;
use App\Models\Surat;
use Illuminate\Http\Request;

class dosenController extends Controller
{
    public function index () {
        return view('pengusul.dosen.index');
    }


    public function pengajuan() {

        $columns = [
            'judul_surat' => 'Judul Surat',
            'tanggal_pengajuan' => 'Tanggal Pengajuan',
            'jenis_surat' => 'Jenis Surat',
            'dibuat_oleh' => 'Diajukan Oleh',
            'ketua' => 'Ketua',
            'anggota' => 'Anggota',
            'lampiran' => 'Dokumen',
            'deskripsi' => 'Deskripsi',
        ];
        
        $data = Surat::with(['dibuatOleh'])
            ->where('is_draft',1)
            ->whereHas('dibuatOleh.role',function($q){
                $q->whereIn('role',['mahasiswa','dosen']);
            })
            ->orderBy('tanggal_pengajuan','desc')
            ->get();

        $jenisSurat = JenisSurat::pluck('jenis_surat', 'id_jenis_surat')->toArray();
        return view('pengusul.mahasiswa.pengajuansurat', compact('jenisSurat','columns','data'));
    }

    public function draft() {
        return view('pengusul.dosen.draft');
    }

    public function status() {
        return view('pengusul.dosen.status');
    }

    public function searchDosen(Request $request) {
        $query = $request->input('query'); 

        $dosens = Pengusul::where('id_role_pengusul', 1) 
                        ->where(function($queryBuilder) use ($query) {
                            $queryBuilder->where('nip', 'like', "%$query%")
                                         ->orWhere('nama', 'like', "%$query%");
                        })
                        ->limit(4) 
                        ->get(['id_pengusul', 'nip', 'nama']); 

        return response()->json($dosens);
    }
}
