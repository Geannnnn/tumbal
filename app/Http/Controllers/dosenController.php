<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\Pengusul;
use App\Models\Surat;
use Illuminate\Http\Request;

class dosenController extends Controller
{
    private function generateTableData($suratList){

        $data = [];
        $userId = auth('pengusul')->id();

        foreach ($suratList as $surat) {

            $isUserInvolved = 
            $surat->pengusul->contains('id_pengusul',$userId) ||
            $surat->dibuat_oleh == $userId;

            if (!$isUserInvolved) continue;

            $anggota = $surat->pengusul->where('pivot.id_peran_keanggotaan', 2)->pluck('nama')->join(', ');

            $ketua = $surat->pengusul
                ->firstWhere('pivot.id_peran_keanggotaan',1)?->nama ?? '';

            $shortDescription = \Illuminate\Support\Str::limit(strip_tags($surat->deskripsi), 50, '...');   

            $data[] = [
                'id' => $surat->id_surat,
                $surat->judul_surat,
                $surat->tanggal_pengajuan ?? '-',
                $surat->jenisSurat->jenis_surat ?? '-',
                $surat->dibuatOleh->nama ?? '-',
                $ketua,
                $anggota,
                $surat->lampiran 
                    ? '<a href="' . asset('storage/' . $surat->lampiran) . '" target="_blank" class="text-blue-600 hover:underline"><i class="fa-solid fa-download mr-1"></i>Unduh</a>' 
                    : 'Tidak ada lampiran',
                $shortDescription
            ];
        }

        $columns = ['Judul', 'Tanggal Pengajuan', 'Jenis Surat', 'Diajukan Oleh', 'Diketuai Oleh', 'Anggota', 'Dokumen', 'Deskripsi'];

        return [$columns, $data];
    }

    public function index () {
        return view('pengusul.dosen.index');
    }

    // public function pengajuanshow(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $data = Surat::where('role', 1)  
    //                     ->paginate(10);

    //         return view('partials.surat_table', compact('data'));
    //     }

    //     return view('pengajuan.surat_dosen'); 
    // }

    public function pengajuan() {
        $jenisSurat = JenisSurat::pluck('jenis_surat', 'id_jenis_surat')->toArray();

        $userId = auth('pengusul')->id();

        $suratList = Surat::with(['jenisSurat', 'dibuatOleh','pengusul'])
            ->where(function($query) use ($userId){
                $query->wherehas('pengusul',function($q) use ($userId){
                    $q->where('pengusul.id_pengusul',$userId);
                }) 
                ->orWhere('dibuat_oleh',$userId);
            })
            ->paginate(10);
            // ->whereHas('dibuatOleh.role', function($q) {
            //     $q->where('role', 'mahasiswa');
            // })->get();

        [$columns, $data] = $this->generateTableData($suratList);

        return view('pengusul.mahasiswa.pengajuansurat', compact('jenisSurat', 'columns', 'data','suratList'));
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
