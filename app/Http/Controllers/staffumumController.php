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

        return view('staff.staff-umum.index',compact('columns'));
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
        return view('staff.staff-umum.jenissurat');
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
                return optional($ketua)->nama ?? '-';
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

    public function tolakSurat(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);

        // Cari id_status_surat untuk "Ditolak"
        $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();
        if (!$statusDitolak) {
            return back()->with('error', 'Status Ditolak tidak ditemukan!');
        }

        $riwayat = RiwayatStatusSurat::create([
            'id_surat' => $surat->id_surat,
            'id_status_surat' => $statusDitolak->id_status_surat,
            'tanggal_rilis' => now(),
        ]);

        if ($request->filled('catatan')) {
            \App\Models\KomentarSurat::create([
                'id_riwayat_status_surat' => $riwayat->id,
                'id_surat' => $surat->id_surat,
                'id_user' => auth('staff')->id(),
                'komentar' => $request->catatan,
            ]);
        }

        return redirect()->route('staffumum.tinjausurat')->with('success', 'Surat berhasil ditolak');
    }

    public function approveSurat(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);

        $statusDiajukan = StatusSurat::where('status_surat', 'Diajukan')->first();
        $statusValidasi = StatusSurat::where('status_surat', 'Divalidasi')->first();
        $statusMenunggu = StatusSurat::where('status_surat', 'Menunggu Persetujuan')->first();

        if (!$statusDiajukan || !$statusValidasi || !$statusMenunggu) {
            return back()->with('error', 'Status diajukan/validasi/menunggu tidak ditemukan!');
        }

        // Ambil status terakhir surat
        $lastRiwayat = $surat->riwayatStatus()->latest('tanggal_rilis')->first();
        $now = $lastRiwayat ? Carbon::parse($lastRiwayat->tanggal_rilis)->addSecond() : now();

        // Jika status terakhir adalah Diajukan, lanjutkan ke Divalidasi dan Menunggu Persetujuan
        if ($lastRiwayat && $lastRiwayat->id_status_surat == $statusDiajukan->id_status_surat) {
            // Tambahkan riwayat status "Divalidasi"
            RiwayatStatusSurat::create([
                'id_surat' => $surat->id_surat,
                'id_status_surat' => $statusValidasi->id_status_surat,
                'tanggal_rilis' => $now,
            ]);
            // Tambahkan riwayat status "Menunggu Persetujuan" dengan waktu +1 detik
            RiwayatStatusSurat::create([
                'id_surat' => $surat->id_surat,
                'id_status_surat' => $statusMenunggu->id_status_surat,
                'tanggal_rilis' => $now->copy()->addSecond(),
            ]);
        } else {
            // Jika status terakhir bukan Diajukan, jangan lanjutkan atau sesuaikan dengan kebutuhan
            return back()->with('error', 'Status surat tidak valid untuk di-approve.');
        }

        return redirect()->route('staffumum.tinjausurat')->with('success', 'Surat berhasil di-approve dan dikirim ke kepala sub.');
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
            $tanggal = Carbon::parse($item->tanggal_rilis)->translatedFormat('j F Y, H:i') . ' wib';
            
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
