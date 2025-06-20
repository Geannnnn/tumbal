<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\Pengusul;
use App\Models\StatusSurat;
use App\Models\Surat;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class dosenController extends Controller
{
    public function index () {

        $suratDiterima = Surat::where('is_draft',0)->whereHas('riwayatStatus',function($q){
            $q->where('id_status_surat',1);
        })->count();

        $suratDitolak = Surat::where('is_draft',0)->whereHas('riwayatStatus',function($q){
            $q->where('id_status_surat',2);
        })->count();

        $notifikasiSurat = collect(); // default empty
        
        $columns = [
            'nomor_surat' => "Nomor Surat",
            'judul_surat' =>'Nama Surat', 
            'tanggal_surat_dibuat' => 'Tanggal Terbit', 
            'lampiran' => 'Dokumen', 
            'tanggal_pengajuan' => 'Dibuat Pada'];


        return view('pengusul.dosen.index', compact('columns','suratDiterima','suratDitolak','notifikasiSurat'));
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
    // Ambil hanya draft milik user yang sedang login
    $userId = auth('pengusul')->id();
    $surats = Surat::where('is_draft', 0)
        ->where('dibuat_oleh', $userId)
        ->select(['id_surat','judul_surat'])
        ->get();

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
        $jenisSurat = JenisSurat::pluck('jenis_surat', 'id_jenis_surat')->toArray();
        $StatusSurat = StatusSurat::pluck('status_surat', 'id_status_surat')->toArray();
        
        return view('pengusul.dosen.status',compact('jenisSurat','StatusSurat'));
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
    
            $query = Surat::with(['jenisSurat', 'riwayatStatus' => function($q) {
                $q->with('statusSurat')->latest('tanggal_rilis');
            }])
            ->whereYear('tanggal_pengajuan', $request->input('year', date('Y')))
            ->where('is_draft', 1);
    
        if ($guard === 'pengusul') {
            $query->where('dibuat_oleh', $user->id_pengusul);
        }
    
            // Filter berdasarkan jenis surat
            if ($request->has('jenis_surat') && $request->jenis_surat) {
                $query->where('id_jenis_surat', $request->jenis_surat);
            }
    
            // Filter berdasarkan status
            if ($request->has('status_surat') && $request->status_surat) {
                $query->whereHas('riwayatStatus', function($q) use ($request) {
                    $q->where('id_status_surat', $request->status_surat);
                });
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
                ->addColumn('nomor_surat', function($row) {
                return $row->nomor_surat ? $row->nomor_surat : '-';
            })
            ->addColumn('status', function ($row) {
                    $latestStatus = $row->riwayatStatus->first();
                    return $latestStatus ? $latestStatus->statusSurat->status_surat : '-';
            })
                ->addColumn('tanggal_pengajuan', function($row) {
                    return $row->tanggal_pengajuan ? date('d/m/Y', strtotime($row->tanggal_pengajuan)) : '-';
            })
                ->rawColumns(['status'])
            ->make(true);
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

    public function showStatusSurat($id)
    {
        $surat = Surat::with(['riwayatStatus' => function($q) {
            $q->with('statusSurat')->orderBy('tanggal_rilis', 'asc');
        }, 'dibuatOleh'])->findOrFail($id);

        $riwayat = [];
        $prevStatus = null;
        foreach ($surat->riwayatStatus as $item) {
            $statusName = $item->statusSurat->status_surat ?? '-';
            $oleh = $surat->dibuatOleh->nim ?? $surat->dibuatOleh->nip ?? '-' . ' | ' . $surat->dibuatOleh->nama;
            $tanggal = \Carbon\Carbon::parse($item->tanggal_rilis)->translatedFormat('j F Y, H:i') . ' wib';
            $riwayat[] = [
                'tanggal' => $tanggal,
                'dari' => $prevStatus ? $prevStatus : 'Draft',
                'ke' => $statusName,
                'oleh' => $oleh,
                'warna' => 'bg-purple-500',
            ];
            $prevStatus = $statusName;
        }

        return view('pengusul.dosen.riwayatstatus', [
            'riwayat' => $riwayat
        ]);
    }
}
