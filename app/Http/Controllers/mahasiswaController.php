<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class mahasiswaController extends Controller
{   

    public function index () {

        $columns = [
            'nomor_surat' => "Nomor Surat",
            'judul_surat' =>'Nama Surat', 
            'tanggal_surat_dibuat' => 'Tanggal Terbit', 
            'lampiran' => 'Dokumen', 
            'tanggal_pengajuan' => 'Dibuat Pada'];


        return view('pengusul.mahasiswa.index', compact('columns'));
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

    public function search(Request $request){
        $limit = $request->input('length');
        $start = $request->input('start');
        $user = auth('pengusul')->id();
    
        $query = Surat::with(['dibuatOleh'])
            ->whereHas('dibuatOleh.role', function ($q) {
                $q->whereIn('role', ['mahasiswa', 'dosen']);
            })
            ->whereHas('pengusul', function ($q) use ($user) {
                $q->where('pivot_pengusul_surat.id_pengusul', $user)
                  ->whereIn('pivot_pengusul_surat.id_peran_keanggotaan', [1, 2]);
            });
    
        $totalData = $query->count();
    
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_surat', 'like', "%$search%")
                  ->orWhere('judul_surat', 'like', "%$search%");
            });
        }
    
        $filterData = $query->count();
    
        $data = $query->skip($start)->take($limit)->get()->map(function($item){
            return [
                'id' => $item->id_surat,
                'nomor_surat' => $item->nomor_surat ?? '-',
                'judul_surat' => $item->judul_surat ?? '-',
                'tanggal_surat_dibuat' => $item->tanggal_surat_dibuat ?? '-',
                'lampiran' => $item->lampiran ?? null,
                'tanggal_pengajuan' => $item->tanggal_pengajuan ?? '-',
            ];
        });
    
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $filterData,
            'data' => $data,
        ]);
    }
    

        // public function pengajuandata(Request $request){
        //     $userId = auth('pengusul')->id();

        //     $query = Surat::with(['jenisSurat', 'dibuatOleh', 'pengusul'])
        //         ->whereHas('pengusul', function ($q) use ($userId) {
        //             $q->where('pivot_pengusul_surat.id_pengusul', $userId);
        //         });

        //     if ($search = $request->input('search.value')) {
        //         $query->where(function ($q) use ($search) {
        //             $q->where('judul_surat', 'like', "%$search%")
        //             ->orWhere('nomor_surat', 'like', "%$search%");
        //         });
        //     }

        //     $recordsTotal = $query->count();

        //     $suratList = $query->skip($request->start)
        //         ->take($request->length)
        //         ->get();

        //     $data = [];
        //     foreach ($suratList as $surat) {
        //         $anggota = $surat->pengusul->where('pivot.id_peran_keanggotaan', 2)->pluck('nama')->join(', ');
        //         $ketua = $surat->pengusul->firstWhere('pivot.id_peran_keanggotaan', 1)?->nama ?? '';
        //         $shortDescription = \Illuminate\Support\Str::limit(strip_tags($surat->deskripsi), 50, '...');

        //         $data[] = [
        //             'judul_surat' => $surat->judul_surat,
        //             'tanggal_pengajuan' => $surat->tanggal_pengajuan ?? '-',
        //             'jenis_surat' => $surat->jenisSurat->jenis_surat ?? '-',
        //             'dibuat_oleh' => $surat->dibuatOleh->nama ?? '-',
        //             'ketua' => $ketua,
        //             'anggota' => $anggota,
        //             'lampiran' => $surat->lampiran 
        //                 ? '<a href="' . asset('storage/' . $surat->lampiran) . '" target="_blank" class="text-blue-600 hover:underline"><i class="fa-solid fa-download mr-1"></i>Unduh</a>' 
        //                 : '-',
        //             'deskripsi' => $shortDescription,
        //             'id' => $surat->id_surat
        //         ];
        //     }

        //     return response()->json([
        //         'draw' => intval($request->draw),
        //         'recordsTotal' => $recordsTotal,
        //         'recordsFiltered' => $recordsTotal,
        //         'data' => $data
        //     ]);
        // }
        
    

    public function detail($id){

        $surat = Surat::with(['jenisSurat', 'dibuatOleh', 'pengusul'])->findOrFail($id);
        return view('pengusul.mahasiswa.detail', compact('surat'));
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
    
    public function draft()
    {
        return view('pengusul.mahasiswa.draft'); 
    }

    public function status() {
        return view('pengusul.mahasiswa.status');
    }
}

