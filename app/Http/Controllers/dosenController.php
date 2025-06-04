<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\Pengusul;
use App\Models\Surat;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class dosenController extends Controller
{
    public function index () {

        $columns = [
            'nomor_surat' => "Nomor Surat",
            'judul_surat' =>'Nama Surat', 
            'tanggal_surat_dibuat' => 'Tanggal Terbit', 
            'lampiran' => 'Dokumen', 
            'tanggal_pengajuan' => 'Dibuat Pada'];


        return view('pengusul.dosen.index', compact('columns'));
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
        return view('pengusul.dosen.pengajuansurat', compact('jenisSurat','columns','data'));
    }

    public function draft() {
        return view('pengusul.dosen.draft');
    }

    public function draftData()
{
    // Ambil surat dengan is_draft = 0
    $surats = Surat::where('is_draft', 0)->select(['id_surat','judul_surat'])->get();

    return DataTables::of($surats)
        ->addColumn('action', function ($surat) {
            
            return '
                <a href="' . route('surat.edit', $surat->id_surat) . '" class="py-2 px-4 rounded-[10px] bg-blue-700 text-white">Edit</a>
                <button onclick="hapusSurat(' . $surat->id_surat . ')" class="py-2 px-4 rounded-[10px] bg-red-700 text-white ml-2 hover:cursor-pointer">Hapus</button>
                <form id="form-hapus-' . $surat->id_surat . '" action="' . route('surat.destroy', $surat->id_surat) . '" method="POST" style="display: none; ">
                    ' . csrf_field() . method_field('DELETE') . '
                </form>
            ';
        })
        ->rawColumns(['action']) // supaya tombol tidak di-escape
        ->make(true);
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

            public function setting()
        {
            return view('pengusul.dosen.setting');
        }
}
