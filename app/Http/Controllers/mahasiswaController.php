<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\PivotPengusulSurat;
use App\Models\StatusSurat;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Pengusul;
use App\Models\RiwayatStatusSurat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Helpers\PengusulHelper;
use Barryvdh\DomPDF\Facade\Pdf;

class mahasiswaController extends Controller
{   

    public function index () {
        $user = auth('pengusul')->id();
        $statusDiterima = StatusSurat::where('status_surat', 'Diterbitkan')->first();

        if ($statusDiterima) {
            $suratDiterima = Surat::where('is_draft', 1)
                ->whereNotNull('nomor_surat')
                ->whereYear('tanggal_surat_dibuat', date('Y')) // Default tahun saat ini
                ->whereHas('riwayatStatus', function($q) use ($statusDiterima) {
                    $q->where('id_status_surat', $statusDiterima->id_status_surat);
                })
                ->whereHas('pengusul', function ($q) use ($user) {
                    $q->where('pivot_pengusul_surat.id_pengusul', $user)
                      ->whereIn('pivot_pengusul_surat.id_peran_keanggotaan', [1, 2]);
                })
                ->count();
        } else {
            $suratDiterima = 0;
        }

        $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();

        if ($statusDitolak) {
            $suratDitolak = Surat::where('is_draft', 1)
                ->whereNotNull('nomor_surat')
                ->whereYear('tanggal_surat_dibuat', date('Y')) // Default tahun saat ini
                ->whereHas('riwayatStatus', function($q) use ($statusDitolak) {
                    $q->where('id_status_surat', $statusDitolak->id_status_surat);
                })
                ->whereHas('pengusul', function ($q) use ($user) {
                    $q->where('pivot_pengusul_surat.id_pengusul', $user)
                      ->whereIn('pivot_pengusul_surat.id_peran_keanggotaan', [1, 2]);
                })
                ->count();
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
            ->where('is_draft',1)
            ->whereHas('dibuatOleh.role',function($q){
                $q->whereIn('role',['mahasiswa','dosen']);
            })
            ->orderBy('tanggal_pengajuan','desc')
            ->get();
        $jenisSurat = JenisSurat::whereIn('jenis_surat', ['Surat Cuti Akademik', 'Surat Pengantar', 'Surat Permohonan'])
            ->pluck('jenis_surat', 'id_jenis_surat')
            ->toArray();
        $namaPengaju = auth('pengusul')->user() ? auth('pengusul')->user()->nama : '';
        return view('pengusul.mahasiswa.pengajuansurat', compact('jenisSurat','columns','data','namaPengaju'));
    }

    public function search(Request $request) {
        try {
            $user = auth('pengusul')->id();
            $statusDiterbitkan = StatusSurat::where('status_surat', 'Diterbitkan')->first();
            if (!$statusDiterbitkan) {
                return response()->json([
                    'error' => 'Status Diterbitkan tidak ditemukan'
                ], 404);
            }
            $query = Surat::with([
                'dibuatOleh', 
                'jenisSurat', 
                'pengusul', 
                'statusTerakhir.statusSurat'
            ])
                ->whereNotNull('nomor_surat')
                ->where('is_draft', 1)
                ->whereHas('pengusul', function ($q) use ($user) {
                    $q->where('pivot_pengusul_surat.id_pengusul', $user)
                      ->whereIn('pivot_pengusul_surat.id_peran_keanggotaan', [1, 2]);
                })
                ->whereHas('statusTerakhir', function($q) use ($statusDiterbitkan) {
                    $q->where('id_status_surat', $statusDiterbitkan->id_status_surat);
                });
            $surat = $query->get();
            $data = $surat->map(function ($item) {
                return [
                    'no' => $item->no,
                    'nomor_surat' => $item->nomor_surat,
                    'judul_surat' => $item->judul_surat,
                    'tanggal_surat_dibuat' => $item->tanggal_surat_dibuat ? date('d/m/Y', strtotime($item->tanggal_surat_dibuat)) : '-',
                    'jenis_surat' => $item->jenisSurat ? $item->jenisSurat->jenis_surat : '-',
                    'lampiran' => $item->lampiran,
                    'tanggal_pengajuan' => $item->tanggal_pengajuan ? date('d/m/Y', strtotime($item->tanggal_pengajuan)) : '-',
                    'aksi' => '<a href="' . route('mahasiswa.surat.downloadPdf', $item->id_surat) . '" class="btn btn-success" target="_blank">Unduh PDF</a>'
                ];
            });
            return response()->json([
                'draw' => $request->get('draw'),
                'recordsTotal' => $surat->count(),
                'recordsFiltered' => $surat->count(),
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in mahasiswa search: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'draw' => $request->get('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ], 500);
        }
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

        $jenisSurat = collect(DB::select('CALL sp_GetJenisSuratForSelect()'))->pluck('jenis_surat', 'id_jenis_surat')->toArray();
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
            $oleh = PengusulHelper::getNamaPengusul($surat->dibuat_oleh);
            $tanggal = \Carbon\Carbon::parse($item->tanggal_rilis)->translatedFormat('j F Y H:i');
            
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
            RiwayatStatusSurat::create([
                'id_surat' => $surat->id_surat,
                'id_status_surat' => 3, // Draft
                'tanggal_rilis' => now(),
            ]);

            // Simpan data ketua jika diisi
            if ($request->filled('id_pengusul')) {
                PivotPengusulSurat::create([
                    'id_surat' => $surat->id_surat,
                    'id_pengusul' => $request->id_pengusul,
                    'id_peran_keanggotaan' => 1,
                ]);
            }

            // Simpan data anggota jika diisi
            if ($request->filled('anggota')) {
                foreach ($request->anggota as $anggotaId) {
                    PivotPengusulSurat::create([
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

    public function getFilteredStatistics(Request $request) {
        try {
            $user = auth('pengusul')->id();
            
            // Get status Diterbitkan and Ditolak
            $statusDiterbitkan = StatusSurat::where('status_surat', 'Diterbitkan')->first();
            $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();
            
            if (!$statusDiterbitkan || !$statusDitolak) {
                return response()->json([
                    'error' => 'Status tidak ditemukan'
                ], 404);
            }

            $baseQuery = Surat::with([
                'dibuatOleh', 
                'jenisSurat', 
                'pengusul', 
                'statusTerakhir.statusSurat'
            ])
                ->whereNotNull('nomor_surat')
                ->where('is_draft', 1)
                ->whereHas('pengusul', function ($q) use ($user) {
                    $q->where('pivot_pengusul_surat.id_pengusul', $user)
                      ->whereIn('pivot_pengusul_surat.id_peran_keanggotaan', [1, 2]);
                });

            // Apply filters
            $filterType = $request->get('filter_type', 'tahun');
            
            if ($filterType === 'tahun') {
                $year = $request->get('year', date('Y')); // Default to current year
                if ($year) {
                    $baseQuery->whereYear('tanggal_surat_dibuat', $year);
                }
            } elseif ($filterType === 'bulan') {
                $month = $request->get('month');
                if ($month) {
                    $baseQuery->whereMonth('tanggal_surat_dibuat', $month);
                }
            } elseif ($filterType === 'jarak') {
                $startDate = $request->get('start_date');
                $endDate = $request->get('end_date');
                if ($startDate && $endDate) {
                    $baseQuery->whereBetween('tanggal_surat_dibuat', [$startDate, $endDate]);
                }
            }

            // Count surat diterima
            $suratDiterima = (clone $baseQuery)
                ->whereHas('statusTerakhir', function($q) use ($statusDiterbitkan) {
                    $q->where('id_status_surat', $statusDiterbitkan->id_status_surat);
                })
                ->count();

            // Count surat ditolak
            $suratDitolak = (clone $baseQuery)
                ->whereHas('statusTerakhir', function($q) use ($statusDitolak) {
                    $q->where('id_status_surat', $statusDitolak->id_status_surat);
                })
                ->count();

            return response()->json([
                'suratDiterima' => $suratDiterima,
                'suratDitolak' => $suratDitolak
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getFilteredStatistics: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'suratDiterima' => 0,
                'suratDitolak' => 0
            ], 500);
        }
    }

    public function testData() {
        try {
            $user = auth('pengusul')->id();
            
            // Test 1: Cek user yang login
            $userData = auth('pengusul')->user();
            
            // Test 2: Cek status Diterbitkan
            $statusDiterbitkan = StatusSurat::where('status_surat', 'Diterbitkan')->first();
            
            // Test 3: Cek semua surat yang dimiliki user
            $allSurat = Surat::with(['dibuatOleh', 'riwayatStatus.statusSurat', 'pengusul'])
                ->whereHas('pengusul', function ($q) use ($user) {
                    $q->where('pivot_pengusul_surat.id_pengusul', $user)
                      ->whereIn('pivot_pengusul_surat.id_peran_keanggotaan', [1, 2]);
                })
                ->get();
            
            // Test 4: Cek surat dengan nomor_surat
            $suratWithNomor = Surat::with(['dibuatOleh', 'riwayatStatus.statusSurat', 'pengusul'])
                ->whereNotNull('nomor_surat')
                ->whereHas('pengusul', function ($q) use ($user) {
                    $q->where('pivot_pengusul_surat.id_pengusul', $user)
                      ->whereIn('pivot_pengusul_surat.id_peran_keanggotaan', [1, 2]);
                })
                ->get();
            
            // Test 5: Cek surat dengan status Diterbitkan
            $suratDiterbitkan = Surat::with(['dibuatOleh', 'riwayatStatus.statusSurat', 'pengusul'])
                ->whereNotNull('nomor_surat')
                ->where('is_draft', 1)
                ->whereHas('riwayatStatus', function($q) use ($statusDiterbitkan) {
                    $q->where('id_status_surat', $statusDiterbitkan->id_status_surat);
                })
                ->whereHas('pengusul', function ($q) use ($user) {
                    $q->where('pivot_pengusul_surat.id_pengusul', $user)
                      ->whereIn('pivot_pengusul_surat.id_peran_keanggotaan', [1, 2]);
                })
                ->get();
            
            // Test 6: Cek surat tahun 2025
            $surat2025 = Surat::with(['dibuatOleh', 'riwayatStatus.statusSurat', 'pengusul'])
                ->whereNotNull('nomor_surat')
                ->where('is_draft', 1)
                ->whereYear('tanggal_surat_dibuat', 2025)
                ->whereHas('riwayatStatus', function($q) use ($statusDiterbitkan) {
                    $q->where('id_status_surat', $statusDiterbitkan->id_status_surat);
                })
                ->whereHas('pengusul', function ($q) use ($user) {
                    $q->where('pivot_pengusul_surat.id_pengusul', $user)
                      ->whereIn('pivot_pengusul_surat.id_peran_keanggotaan', [1, 2]);
                })
                ->get();
            
            return response()->json([
                'user_id' => $user,
                'user_data' => $userData,
                'status_diterbitkan' => $statusDiterbitkan,
                'all_surat_count' => $allSurat->count(),
                'all_surat' => $allSurat->map(function($item) {
                    return [
                        'id_surat' => $item->id_surat,
                        'judul_surat' => $item->judul_surat,
                        'nomor_surat' => $item->nomor_surat,
                        'is_draft' => $item->is_draft,
                        'tanggal_surat_dibuat' => $item->tanggal_surat_dibuat,
                        'dibuat_oleh' => $item->dibuat_oleh,
                        'riwayat_status' => $item->riwayatStatus->map(function($rs) {
                            return [
                                'id_status' => $rs->id_status_surat,
                                'status_name' => $rs->statusSurat->status_surat ?? 'N/A',
                                'tanggal_rilis' => $rs->tanggal_rilis
                            ];
                        }),
                        'pengusul' => $item->pengusul->map(function($p) {
                            return [
                                'id_pengusul' => $p->id_pengusul,
                                'nama' => $p->nama,
                                'peran' => $p->pivot->id_peran_keanggotaan
                            ];
                        })
                    ];
                }),
                'surat_with_nomor_count' => $suratWithNomor->count(),
                'surat_diterbitkan_count' => $suratDiterbitkan->count(),
                'surat_2025_count' => $surat2025->count(),
                'surat_2025' => $surat2025->map(function($item) {
                    return [
                        'id_surat' => $item->id_surat,
                        'judul_surat' => $item->judul_surat,
                        'nomor_surat' => $item->nomor_surat,
                        'is_draft' => $item->is_draft,
                        'tanggal_surat_dibuat' => $item->tanggal_surat_dibuat,
                        'dibuat_oleh' => $item->dibuat_oleh,
                        'riwayat_status' => $item->riwayatStatus->map(function($rs) {
                            return [
                                'id_status' => $rs->id_status_surat,
                                'status_name' => $rs->statusSurat->status_surat ?? 'N/A',
                                'tanggal_rilis' => $rs->tanggal_rilis
                            ];
                        }),
                        'pengusul' => $item->pengusul->map(function($p) {
                            return [
                                'id_pengusul' => $p->id_pengusul,
                                'nama' => $p->nama,
                                'peran' => $p->pivot->id_peran_keanggotaan
                            ];
                        })
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function downloadPdf($id)
    {
        $surat = Surat::with(['dibuatOleh', 'jenisSurat', 'statusTerakhir.statusSurat'])->findOrFail($id);
        $tanggalSurat = $surat->tanggal_surat_dibuat ? Carbon::parse($surat->tanggal_surat_dibuat)->translatedFormat('d F Y') : '-';
        $tanggalPengajuan = $surat->tanggal_pengajuan ? Carbon::parse($surat->tanggal_pengajuan)->translatedFormat('d F Y') : '-';
        $today = Carbon::now()->translatedFormat('d F Y');
        $pdf = Pdf::loadView('pdf.surat', [
            'surat' => $surat,
            'tanggalSurat' => $tanggalSurat,
            'tanggalPengajuan' => $tanggalPengajuan,
            'today' => $today
        ]);
        $filename = $surat->nomor_surat ? ($surat->nomor_surat.'-'.$surat->id_surat.'.pdf') : ('surat-'.$id.'.pdf');
        return $pdf->stream($filename);
    }

}


