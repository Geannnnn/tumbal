<?php

namespace App\Http\Controllers;

use App\Models\RiwayatStatusSurat;
use Illuminate\Http\Request;
use App\Models\Surat;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\StatusSurat;
use App\Models\JenisSurat;
use App\Models\RolePengusul;
use Illuminate\Support\Facades\DB;
use App\Helpers\PengusulHelper;

class staffumumController extends Controller
{
    public function index(){
        $columns = [
            'judul_surat' =>'Nama Surat', 
            'nomor_surat' => "Nomor Surat",
            'tanggal_surat_dibuat' => 'Tanggal', 
            'tanggal_pengajuan' => 'Dibuat Pada',
            'status' => 'Status', 
        ];

        // Get jenis surat IDs for Staff Umum letter types (Surat Pengantar, Surat Permohonan, Surat Cuti Akademik)
        $jenisSuratIds = JenisSurat::whereIn('jenis_surat', ['Surat Pengantar', 'Surat Permohonan', 'Surat Cuti Akademik'])->pluck('id_jenis_surat');
        
        // Get role mahasiswa ID
        $roleMahasiswa = RolePengusul::where('role', 'Dosen')->first();
        
        // Get status IDs
        $statusDiajukan = StatusSurat::where('status_surat', 'Diajukan')->first();
        $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();

        // Calculate surat diterima (letters from mahasiswa with specific types)
        $suratDiterima = 0;
        if ($roleMahasiswa && $statusDiajukan) {
            $suratDiterima = Surat::where('is_draft', 1)
                ->whereIn('id_jenis_surat', $jenisSuratIds)
                ->whereHas('dibuatOleh', function($q) use ($roleMahasiswa) {
                    $q->where('id_role_pengusul', $roleMahasiswa->id_role_pengusul);
                })
                ->whereHas('riwayatStatus', function($q) use ($statusDiajukan) {
                    $q->where('id_status_surat', $statusDiajukan->id_status_surat);
                })
                ->count();
        }

        // Calculate surat ditolak (all rejected letters)
        $suratDitolak = 0;
        if ($statusDitolak) {
            $suratDitolak = Surat::where('is_draft', 1)
                ->whereHas('riwayatStatus', function($q) use ($statusDitolak) {
                    $q->where('id_status_surat', $statusDitolak->id_status_surat);
                })
                ->count();
        }

        return view('staff.staff-umum.index', compact('columns', 'suratDiterima', 'suratDitolak'));
    }

    public function statistik(Request $request)
    {
        // Get filter parameters
        $year = $request->input('year', date('Y'));
        $month = $request->input('month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Get jenis surat IDs for Surat Tugas and Surat Undangan Kegiatan
        $jenisSuratIds = JenisSurat::whereIn('jenis_surat', ['Surat Tugas', 'Surat Undangan Kegiatan'])->pluck('id_jenis_surat');
        
        // Get role dosen ID
        $roleDosen = RolePengusul::where('role', 'Dosen')->first();
        
        // Base query for staff umum letters (only Surat Tugas and Surat Undangan Kegiatan from dosen)
        $baseQuery = Surat::where('is_draft', 1)
            ->whereIn('id_jenis_surat', $jenisSuratIds)
            ->whereHas('dibuatOleh', function($q) use ($roleDosen) {
                $q->where('id_role_pengusul', $roleDosen->id_role_pengusul);
            });

        // Apply date filters
        if ($startDate && $endDate) {
            $baseQuery->whereBetween('tanggal_pengajuan', [$startDate, $endDate]);
        } elseif ($month) {
            $baseQuery->whereYear('tanggal_pengajuan', $year)
                     ->whereMonth('tanggal_pengajuan', $month);
        } else {
            $baseQuery->whereYear('tanggal_pengajuan', $year);
        }

        // Get status IDs
        $statusDiajukan = StatusSurat::where('status_surat', 'Diajukan')->first();
        $statusDiterbitkan = StatusSurat::where('status_surat', 'Diterbitkan')->first();
        $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();

        // Calculate statistics
        $suratDiterima = 0;
        $suratDiterbitkan = 0;
        $suratDitolak = 0;

        if ($statusDiajukan) {
            $suratDiterima = (clone $baseQuery)->whereHas('riwayatStatus', function($q) use ($statusDiajukan) {
                $q->where('id_status_surat', $statusDiajukan->id_status_surat);
            })->count();
        }

        if ($statusDiterbitkan) {
            $suratDiterbitkan = (clone $baseQuery)->whereHas('riwayatStatus', function($q) use ($statusDiterbitkan) {
                $q->where('id_status_surat', $statusDiterbitkan->id_status_surat);
            })->count();
        }

        if ($statusDitolak) {
            $suratDitolak = (clone $baseQuery)->whereHas('riwayatStatus', function($q) use ($statusDitolak) {
                $q->where('id_status_surat', $statusDitolak->id_status_surat);
            })->count();
        }

        // Get monthly data for bar chart
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyQuery = (clone $baseQuery)->whereMonth('tanggal_pengajuan', $i);
            $monthlyData[] = $monthlyQuery->count();
        }

        // Get data by jenis surat
        $suratPerKategori = [];
        foreach ($jenisSuratIds as $jenisId) {
            $jenisSurat = JenisSurat::find($jenisId);
            $count = (clone $baseQuery)->where('id_jenis_surat', $jenisId)->count();
            $suratPerKategori[] = [
                'nama' => $jenisSurat->jenis_surat,
                'count' => $count
            ];
        }

        // Get data by status
        $statusSurat = [];
        $allStatuses = StatusSurat::all();
        foreach ($allStatuses as $status) {
            $count = (clone $baseQuery)->whereHas('riwayatStatus', function($q) use ($status) {
                $q->where('id_status_surat', $status->id_status_surat);
            })->count();
            if ($count > 0) {
                $statusSurat[] = [
                    'nama' => $status->status_surat,
                    'count' => $count
                ];
            }
        }

        // Prepare data for pie chart (Diajukan, Diproses, Disetujui/Diterbitkan, Ditolak)
        $pieChartData = [
            'Diajukan' => $suratDiterima,
            'Diproses' => 0, // You can calculate this based on your business logic
            'Disetujui' => $suratDiterbitkan,
            'Ditolak' => $suratDitolak
        ];

        return view('staff.staff-umum.statistik', compact(
            'suratDiterima',
            'suratDiterbitkan', 
            'suratDitolak',
            'monthlyData',
            'suratPerKategori',
            'statusSurat',
            'pieChartData',
            'year',
            'month',
            'startDate',
            'endDate'
        ));
    }

    public function terbitkan()
    {
        return view('staff.staff-umum.terbitkan');
    }

    public function statussurat()
    {
        // Only get Surat Tugas and Surat Undangan Kegiatan for the dropdown
        $jenisSurat = JenisSurat::whereIn('jenis_surat', ['Surat Tugas', 'Surat Undangan Kegiatan'])
            ->pluck('jenis_surat', 'id_jenis_surat')
            ->toArray();
        $StatusSurat = StatusSurat::pluck('status_surat', 'id_status_surat')->toArray();
        
        return view('staff.staff-umum.statussurat', compact('jenisSurat', 'StatusSurat'));
    }

    public function jenissurat()
    {
        $jsdata = DB::select('CALL sp_GetAllJenisSurat()');
        return view('staff.staff-umum.jenissurat', compact('jsdata'));
    }

    public function storeJenisSurat(Request $request)
    {
        $request->validate([
            'jenis_surat' => 'required|string|max:255',
        ]);

        JenisSurat::create([
            'jenis_surat' => $request->jenis_surat,
        ]);

        return redirect()->back()->with('success', 'Jenis surat berhasil ditambahkan.');
    }

    public function updateJenisSurat(Request $request, $id)
    {
        $request->validate([
            'jenis_surat' => 'required|string|max:255',
        ]);

        try {
            JenisSurat::where('id_jenis_surat', $id)->update([
                'jenis_surat' => $request->jenis_surat,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jenis surat berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui jenis surat: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyJenisSurat($id)
    {
        JenisSurat::destroy($id);
        return redirect()->back()->with('success', 'Jenis surat berhasil dihapus.');
    }

    public function tinjauSurat()
    {
        return view('staff.staff-umum.tinjausurat');
    }

    public function getSuratData(Request $request)
    {
        $statusDiajukan = StatusSurat::where('status_surat', 'Diajukan')->first();
        $jenisSuratIds = JenisSurat::whereIn('jenis_surat', ['Surat Tugas', 'Surat Undangan Kegiatan'])->pluck('id_jenis_surat');
        $roleDosen = RolePengusul::where('role', 'Dosen')->first();

        $query = Surat::with(['jenisSurat', 'dibuatOleh', 'statusTerakhir.statusSurat'])
            ->whereIn('id_jenis_surat', $jenisSuratIds)
            ->whereHas('dibuatOleh', function($q) use ($roleDosen) {
                $q->where('id_role_pengusul', $roleDosen->id_role_pengusul);
            })
            ->whereHas('statusTerakhir', function($q) use ($statusDiajukan) {
                $q->where('id_status_surat', $statusDiajukan->id_status_surat);
            });

        // Filter Rentang Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('tanggal_pengajuan', [$startDate, $endDate]);
        }

        // Filter search query
        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $query->where(function($q) use ($search) {
                $q->where('judul_surat', 'like', "%$search%")
                  ->orWhereHas('pengusul', function($q2) use ($search) {
                      $q2->where('nama', 'like', "%$search%");
                  });
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('jenis_surat', function ($surat) {
                return optional($surat->jenisSurat)->jenis_surat ?? '-';
            })
            ->addColumn('ketua', function ($surat) {
                $ketua = $surat->pengusul()->wherePivot('id_peran_keanggotaan', 1)->first(); 
                return optional($ketua) ? PengusulHelper::getNamaPengusul($ketua->id_pengusul) : '-';
            })
            ->addColumn('status', function($surat) {
                return optional($surat->statusTerakhir->statusSurat)->status_surat ?? 'Diajukan';
            })
            ->addColumn('tujuan_surat', function($surat) {
                return $surat->tujuan_surat ?? '-';
            })
            ->addColumn('tanggal_pengajuan', function($surat) {
                return Carbon::parse($surat->tanggal_pengajuan)->locale('id')->translatedFormat('d F Y');
            })
            ->addColumn('actions', function ($row) {
                return '<a href="' . route('staffumum.tinjau.detail', $row->id_surat) . '" class="inline-block bg-blue-700 text-white px-3 py-1 rounded-lg hover:bg-blue-800 transition-transform duration-300 transform hover:scale-110">Tinjau</a>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
    
    public function showDetailSurat($id)
    {
        $surat = Surat::with(['jenisSurat', 'dibuatOleh', 'pengusul', 'riwayatStatus.statusSurat'])
            ->findOrFail($id);
        
        return view('staff.staff-umum.detail-surat', compact('surat'));
    }

    public function getTerbitkanData(Request $request)
    {
        $statusMenungguPenerbitan = StatusSurat::where('status_surat', 'Menunggu Penerbitan')->first();
        $jenisSuratIds = JenisSurat::whereIn('jenis_surat', ['Surat Tugas', 'Surat Undangan Kegiatan', 'Surat Izin Tidak Masuk'])->pluck('id_jenis_surat');
        $roleDosen = RolePengusul::where('role', 'Dosen')->first();

        $query = Surat::with(['jenisSurat', 'dibuatOleh', 'statusTerakhir.statusSurat'])
            ->whereIn('id_jenis_surat', $jenisSuratIds)
            ->whereHas('dibuatOleh', function($q) use ($roleDosen) {
                $q->where('id_role_pengusul', $roleDosen->id_role_pengusul);
            })
            ->whereHas('statusTerakhir', function($q) use ($statusMenungguPenerbitan) {
                $q->where('id_status_surat', $statusMenungguPenerbitan->id_status_surat);
            });

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('judul_surat', function($row) {
                return $row->judul_surat ?? '-';
            })
            ->addColumn('jenis_surat', function($row) {
                return $row->jenisSurat ? $row->jenisSurat->jenis_surat : '-';
            })
            ->addColumn('dosen', function($row) {
                return $row->dibuatOleh ? $row->dibuatOleh->nama : '-';
            })
            ->addColumn('tanggal_pengajuan', function($row) {
                return $row->tanggal_pengajuan ? \Carbon\Carbon::parse($row->tanggal_pengajuan)->format('d/m/Y') : '-';
            })
            ->addColumn('actions', function($row) {
                return '<a href="' . route('staffumum.terbitkan.detail', $row->id_surat) . '" class="inline-block bg-green-700 text-white px-3 py-1 rounded-lg hover:bg-green-800 transition-transform duration-300 transform hover:scale-110">Terbitkan</a>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function terbitkanDetail($id)
    {
        $surat = Surat::with(['jenisSurat', 'dibuatOleh', 'statusTerakhir.statusSurat'])->findOrFail($id);
        return view('staff.staff-umum.terbitkan-detail', compact('surat'));
    }

    public function terbitkanSurat(Request $request, $id)
    {
        $request->validate([
            'nomor_surat' => 'required|string|max:255',
        ]);
        $surat = Surat::findOrFail($id);
        $statusDiterbitkan = StatusSurat::where('status_surat', 'Diterbitkan')->first();
        if (!$statusDiterbitkan) {
            return back()->with('error', 'Status Diterbitkan tidak ditemukan!');
        }
        $surat->nomor_surat = $request->nomor_surat;
        $surat->tanggal_surat_dibuat = now('Asia/Jakarta')->toDateString();
        $surat->save();
        $lastRiwayat = \App\Models\RiwayatStatusSurat::where('id_surat', $surat->id_surat)
            ->orderBy('tanggal_rilis', 'desc')
            ->first();
        $baseTime = $lastRiwayat ? \Carbon\Carbon::parse($lastRiwayat->tanggal_rilis) : now();
        \App\Models\RiwayatStatusSurat::create([
            'id_surat' => $surat->id_surat,
            'id_status_surat' => $statusDiterbitkan->id_status_surat,
            'tanggal_rilis' => $baseTime->copy()->addSecond(1),
            'keterangan' => 'Diterbitkan oleh Staff Umum'
        ]);
        return redirect()->route('staffumum.terbitkan')->with('success', 'Surat berhasil diterbitkan!');
    }

    public function tolakSurat(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);
        $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();
        if (!$statusDitolak) {
            return back()->with('error', 'Status Ditolak tidak ditemukan!');
        }
        $lastRiwayat = \App\Models\RiwayatStatusSurat::where('id_surat', $surat->id_surat)
            ->orderBy('tanggal_rilis', 'desc')
            ->first();
        $baseTime = $lastRiwayat ? \Carbon\Carbon::parse($lastRiwayat->tanggal_rilis) : now();
        $riwayat = \App\Models\RiwayatStatusSurat::create([
            'id_surat' => $surat->id_surat,
            'id_status_surat' => $statusDitolak->id_status_surat,
            'tanggal_rilis' => $baseTime->copy()->addSecond(1),
            'keterangan' => 'Ditolak oleh Staff Umum'
        ]);
        if ($request->filled('komentar')) {
            \App\Models\KomentarSurat::create([
                'id_riwayat_status_surat' => $riwayat->id,
                'id_surat' => $surat->id_surat,
                'id_user' => auth('staff')->id(),
                'komentar' => $request->komentar,
            ]);
        }
        return redirect()->route('staffumum.terbitkan')->with('success', 'Surat berhasil ditolak!');
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
            $tanggal = Carbon::parse($item->tanggal_rilis)->translatedFormat('j F Y H:i');
            
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

        return view('staff.staff-umum.riwayatstatus', [
            'riwayat' => $riwayat
        ]);
    }

    public function getStatusSuratData(Request $request)
    {
        // Get jenis surat IDs for Surat Tugas and Surat Undangan Kegiatan
        $jenisSuratIds = JenisSurat::whereIn('jenis_surat', ['Surat Tugas', 'Surat Undangan Kegiatan'])->pluck('id_jenis_surat');
        
        // Get role dosen ID
        $roleDosen = RolePengusul::where('role', 'Dosen')->first();
        
        if (!$roleDosen) {
            return response()->json([
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'error' => 'Role Dosen tidak ditemukan',
            ], 400);
        }

        $query = Surat::with(['jenisSurat', 'riwayatStatus' => function($q) {
            $q->with('statusSurat')->latest('tanggal_rilis');
        }, 'dibuatOleh'])
        ->where('is_draft', 1) // Only submitted letters
        ->whereIn('id_jenis_surat', $jenisSuratIds) // Only Surat Tugas and Surat Undangan Kegiatan
        ->whereHas('dibuatOleh', function($q) use ($roleDosen) {
            $q->where('id_role_pengusul', $roleDosen->id_role_pengusul); // Only submitted by dosen
        });

        // Filter by year
        if ($request->filled('year')) {
            $query->whereYear('tanggal_pengajuan', $request->year);
        } else {
            $query->whereYear('tanggal_pengajuan', date('Y'));
        }

        // Filter by jenis surat
        if ($request->filled('jenis_surat') && $request->jenis_surat) {
            $query->where('id_jenis_surat', $request->jenis_surat);
        }

        // Filter by status
        if ($request->filled('status_surat') && $request->status_surat) {
            $query->whereHas('riwayatStatus', function($q) use ($request) {
                $q->where('id_status_surat', $request->status_surat);
            });
        }

        // Search functionality
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
}
