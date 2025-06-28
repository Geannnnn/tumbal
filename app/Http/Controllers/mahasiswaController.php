<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\StatusSurat;
use App\Models\Surat;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class mahasiswaController extends Controller
{   

    public function index () {

        $statusDiterima = StatusSurat::where('status_surat', 'Diterbitkan')->first();

        if ($statusDiterima) {
            $suratDiterima = Surat::where('is_draft', 1)
                ->whereHas('riwayatStatus', function($q) use ($statusDiterima) {
                $q->where('id_status_surat', $statusDiterima->id_status_surat);
            })->count();
        } else {
            $suratDiterima = 0;
        }


        $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();

        if ($statusDitolak) {
            $suratDitolak = Surat::where('is_draft', 1)
                ->whereHas('riwayatStatus', function($q) use ($statusDitolak) {
                    $q->where('id_status_surat', $statusDitolak->id_status_surat);
                })->count();
        } else {
            $suratDitolak = 0;
        }

        $notifikasiSurat = collect(); // default empty
        
        $columns = [
            'no' => "No",
            'nomor_surat' => "Nomor Surat",
            'judul_surat' =>'Nama Surat', 
            'tanggal_surat_dibuat' => 'Tanggal Terbit', 
            'lampiran' => 'Dokumen', 
            'tanggal_pengajuan' => 'Dibuat Pada'];


        return view('pengusul.mahasiswa.index', compact('columns','suratDiterima','suratDitolak','notifikasiSurat'));
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
            ->where('is_draft',1) // 1 = diajukan, 0 = draft
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
            })
            ->whereNotNull('nomor_surat');
    
        $totalData = $query->count();
    
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('nomor_surat', 'like', "%$search%")
                  ->orWhere('judul_surat', 'like', "%$search%");
            });
        }
    
        $filterData = $query->count();
    
        $surats = $query->skip($start)->take($limit)->get();
        $data = $surats->map(function($item, $index) use ($start) {
            return [
                'no' => $start + $index + 1,
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

    public function draftData()
    {
        // Ambil hanya draft milik user yang sedang login
        $userId = auth('pengusul')->id();
        $surats = Surat::where('is_draft', 0) // 0 = draft, 1 = diajukan
            ->where('dibuat_oleh', $userId)
            ->select(['id_surat','judul_surat'])
            ->get();

        $surats->transform(function ($surat, $key){
            $surat->no = $key + 1;
            return $surat;
        });

        return DataTables::of($surats)
            ->addColumn('action', function ($surat) {
                return '
                    <a href="' . route('mahasiswa.surat.edit', $surat->id_surat) . '" class="inline-block py-2 px-4 rounded-[10px] bg-blue-700 text-white hover:cursor-pointer hover:scale-110 transition-all duration-300">Ubah</a>
                    <button onclick="hapusSurat(' . $surat->id_surat . ')" class="py-2 px-4 rounded-[10px] bg-red-700 text-white ml-2 hover:cursor-pointer hover:scale-110 transition-all duration-300">Hapus</button>
                    <form id="form-hapus-' . $surat->id_surat . '" action="' . route('mahasiswa.surat.destroy', $surat->id_surat) . '" method="POST" style="display: none; ">
                        ' . csrf_field() . method_field('DELETE') . '
                    </form>
                ';
            })
            ->rawColumns(['action'])
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

    public function showStatusSurat($id)
    {
        $surat = Surat::with(['riwayatStatus' => function($q) {
            $q->with('statusSurat')->orderBy('tanggal_rilis', 'asc');
        }, 'dibuatOleh'])->findOrFail($id);

        $riwayat = [];
        $prevStatus = null;
        foreach ($surat->riwayatStatus as $item) {
            $statusName = $item->statusSurat->status_surat ?? '-';
            $oleh = $surat->dibuatOleh->nama;
            $tanggal = \Carbon\Carbon::parse($item->tanggal_rilis)->translatedFormat('j F Y, H:i') . ' wib';
            
            // Tentukan warna berdasarkan status
            $warna = 'bg-purple-500'; // default
            switch (strtolower($statusName)) {
                case 'draft':
                    $warna = 'bg-purple-600';
                    break;
                case 'diajukan':
                    $warna = 'bg-orange-500';
                    break;
                case 'divalidasi':
                    $warna = 'bg-blue-500';
                    break;
                case 'menunggu persetujuan':
                    $warna = 'bg-yellow-500';
                    break;
                case 'menunggu penerbitan':
                    $warna = 'bg-lime-500';
                    break;
                case 'diterbitkan':
                    $warna = 'bg-green-600';
                    break;
                case 'ditolak':
                    $warna = 'bg-red-600';
                    break;
            }
            
            $riwayat[] = [
                'tanggal' => $tanggal,
                'dari' => $prevStatus ? $prevStatus : 'Draft',
                'ke' => $statusName,
                'oleh' => $oleh,
                'warna' => $warna,
            ];
            $prevStatus = $statusName;
        }

        return view('pengusul.mahasiswa.riwayatstatus', [
            'riwayat' => $riwayat
        ]);
    }


        public function setting()
        {
            return view('pengusul.mahasiswa.setting');
        }

    public function store(Request $request)
    {
        $isDraft = true; // Mahasiswa hanya boleh membuat draft pada awalnya

        $rules = [
            'judul_surat' => 'required|string|max:255',
            'lampiran' => 'nullable|file|mimes:pdf,jpeg,png,jpg,docx,xlsx|max:10240',
            'anggota' => 'array',
            'anggota.*' => 'exists:pengusul,id_pengusul',
        ];

        $rules['id_pengusul'] = 'nullable|exists:pengusul,id_pengusul';
        $rules['jenis_surat'] = 'nullable|exists:jenis_surat,id_jenis_surat';
        $rules['deskripsi'] = 'nullable|string|max:300';

        $request->validate($rules);

        DB::beginTransaction();
        try {
            $lampiranPath = null;
            if ($request->hasFile('lampiran')) {
                $lampiranPath = $request->file('lampiran')->store('lampiran', 'public');
            }

            $dataSurat = [
                'judul_surat' => $request->judul_surat,
                'tanggal_pengajuan' => now(),
                'dibuat_oleh' => auth('pengusul')->id(),
                'lampiran' => $lampiranPath,
                'is_draft' => 0, // 0 = draft, 1 = diajukan
                'id_jenis_surat' => $request->filled('jenis_surat') ? $request->jenis_surat : null,
                'deskripsi' => $request->filled('deskripsi') ? $request->deskripsi : null,
            ];

            $surat = Surat::create($dataSurat);

            // Simpan riwayat status awal Draft (id_status_surat = 3)
            \App\Models\RiwayatStatusSurat::create([
                'id_surat' => $surat->id_surat,
                'id_status_surat' => 3, // Draft
                'tanggal_rilis' => now(),
            ]);

            // Simpan data ketua jika diisi
            if ($request->filled('id_pengusul')) {
                \App\Models\PivotPengusulSurat::create([
                    'id_surat' => $surat->id_surat,
                    'id_pengusul' => $request->id_pengusul,
                    'id_peran_keanggotaan' => 1,
                ]);
            }

            // Simpan data anggota jika diisi
            if ($request->filled('anggota')) {
                foreach ($request->anggota as $anggotaId) {
                    \App\Models\PivotPengusulSurat::create([
                        'id_surat' => $surat->id_surat,
                        'id_pengusul' => $anggotaId,
                        'id_peran_keanggotaan' => 2,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('mahasiswa.pengajuansurat')->with('success', 'Surat berhasil disimpan sebagai draft.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan surat: ' . $e->getMessage());
        }
        }
}

