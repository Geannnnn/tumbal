<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\StatusSurat;
use App\Models\Surat;
use Illuminate\Http\Request;
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

        $jenisSurat = JenisSurat::pluck('jenis_surat', 'id_jenis_surat')->toArray();
        $StatusSurat = StatusSurat::pluck('status_surat', 'id_status_surat')->toArray();
        
        return view('pengusul.mahasiswa.status',compact('jenisSurat','StatusSurat'));
    }

   

    public function getStatusSuratData(Request $request)
{
    
    $user = null;
    $guard = null;

    if (auth('pengusul')->check()) {
        $guard = 'pengusul';
        $user = auth('pengusul')->user();
    } elseif (auth('admin')->check()) {
        $guard = 'admin';
        $user = auth('admin')->user();
    } elseif (auth('kepala_sub')->check()) {
        $guard = 'kepala_sub';
        $user = auth('kepala_sub')->user();
    }

    if (!$user) {
        return response()->json([
            'data' => [],
            'recordsTotal' => 0,
            'recordsFiltered' => 0,
            'error' => 'Unauthorized',
        ], 401);
    }

    $query = Surat::with(['jenisSurat', 'riwayatStatus'])
        ->whereYear('tanggal_pengajuan', 2025)
        ->where('is_draft',1);

    if ($guard === 'pengusul') {
        $query->where('dibuat_oleh', $user->id_pengusul);
    }

    if ($request->has('search') && $request->search['value'] != '') {
        $searchValue = $request->search['value'];
        $query->where(function ($q) use ($searchValue) {
            $q->where('judul_surat', 'like', "%{$searchValue}%")
              ->orWhere('nomor_surat', 'like', "%{$searchValue}%");
        });
    }

    return DataTables::of($query)
        ->addColumn('jenis_surat', fn($row) => $row->jenisSurat->jenis_surat ?? '-')
        ->addColumn('nomor_surat',function($row){
            return $row->nomor_surat ? $row->nomor_surat : '-';
        })
        ->addColumn('status', function ($row) {
            $latestStatus = $row->riwayatStatus->sortByDesc('created_at')->first();
            $status = $latestStatus->statusSurat->nama_status ?? '-';
        

            $badge = match ($status) {
                'Disetujui' => 'text-green-600',
                'Ditolak' => 'text-red-600',
                'Menunggu' => 'text-yellow-500',
                'Diproses' => 'text-orange-500',
                default => 'text-gray-500',
            };

            return '<span class="font-semibold '.$badge.'">'.$status.'</span>';
        })
        ->addColumn('aksi', function ($row) use ($guard) {
            $route = match ($guard) {
                'pengusul' => route('mahasiswa.statussurat', $row->id_surat),
                'admin' => route('admin.statusSurat.show', $row->id_surat),
                'kepala_sub' => route('kepala_sub.statusSurat.show', $row->id_surat),
                default => '#',
            };

            return '<a href="'.$route.'" class="bg-blue-100 text-blue-800 px-4 py-1 rounded text-sm">Detail</a>';
        })
        ->rawColumns(['status', 'aksi'])
        ->make(true);
}




        public function setting()
        {
            return view('pengusul.mahasiswa.setting');
        }
}

