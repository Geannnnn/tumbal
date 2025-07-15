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
use App\Models\KomentarSurat;
use App\Notifications\SuratDiterbitkan;
use App\Notifications\SuratDitolak;

class tatausahaController extends Controller
{
    public function index(){
        $columns = [
            'judul_surat' =>'Nama Surat', 
            'nomor_surat' => "Nomor Surat",
            'tanggal_surat_dibuat' => 'Tanggal', 
            'tanggal_pengajuan' => 'Dibuat Pada',
            'status' => 'Status', 
        ];

        // Jenis surat sesuai permintaan
        $jenisSuratIds = JenisSurat::whereIn('jenis_surat', ['Surat Permohonan', 'Surat Pengantar', 'Surat Cuti Akademik'])->pluck('id_jenis_surat');
        $roleMahasiswa = RolePengusul::where('role', 'Mahasiswa')->first();
        $statusMenunggu = StatusSurat::where('status_surat', 'Diajukan')->first();
        $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();

        // Surat diterima: status terakhir 'Menunggu Persetujuan'
        $suratDiterima = 0;
        if ($roleMahasiswa && $statusMenunggu) {
            $suratDiterima = Surat::where('is_draft', 1)
                ->whereIn('id_jenis_surat', $jenisSuratIds)
                ->whereHas('dibuatOleh', function($q) use ($roleMahasiswa) {
                    $q->where('id_role_pengusul', $roleMahasiswa->id_role_pengusul);
                })
                ->whereHas('statusTerakhir', function($q) use ($statusMenunggu) {
                    $q->where('id_status_surat', $statusMenunggu->id_status_surat);
                })
                ->count();
        }

        // Surat ditolak: status terakhir 'Ditolak' dan diubah_oleh_tipe = 'staff'
        $suratDitolak = 0;
        if ($roleMahasiswa && $statusDitolak) {
            $suratDitolak = Surat::where('is_draft', 1)
                ->whereIn('id_jenis_surat', $jenisSuratIds)
                ->whereHas('dibuatOleh', function($q) use ($roleMahasiswa) {
                    $q->where('id_role_pengusul', $roleMahasiswa->id_role_pengusul);
                })
                ->whereHas('statusTerakhir', function($q) use ($statusDitolak) {
                    $q->where('id_status_surat', $statusDitolak->id_status_surat)
                      ->where('diubah_oleh_tipe', 'staff');
                })
                ->count();
        }

        return view('staff.tata-usaha.index', compact('columns', 'suratDiterima', 'suratDitolak'));
    }

    public function statistik(Request $request)
    {
        // Get filter parameters
        $year = $request->input('year', date('Y'));
        $month = $request->input('month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Get jenis surat IDs for Tata Usaha letter types
        $jenisSuratIds = JenisSurat::whereIn('jenis_surat', ['Surat Pengantar', 'Surat Permohonan', 'Surat Cuti Akademik', 'Surat Izin Tidak Masuk'])->pluck('id_jenis_surat');
        
        // Get role mahasiswa ID
        $roleMahasiswa = RolePengusul::where('role', 'Mahasiswa')->first();
        
        // Base query for tata usaha letters (only specific letter types from mahasiswa)
        $baseQuery = Surat::where('is_draft', 1)
            ->whereIn('id_jenis_surat', $jenisSuratIds)
            ->whereHas('dibuatOleh', function($q) use ($roleMahasiswa) {
                $q->where('id_role_pengusul', $roleMahasiswa->id_role_pengusul);
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

        return view('staff.tata-usaha.statistik', compact(
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
        return view('staff.tata-usaha.terbitkan');
    }

    public function getTerbitkanData(Request $request)
    {
        $statusMenungguPenerbitan = StatusSurat::where('status_surat', 'Menunggu Penerbitan')->first();
        $jenisSuratIds = JenisSurat::whereIn('jenis_surat', ['Surat Pengantar', 'Surat Permohonan', 'Surat Cuti Akademik', 'Surat Izin Tidak Masuk'])->pluck('id_jenis_surat');
        $roleMahasiswa = RolePengusul::where('role', 'Mahasiswa')->first();

        if (!$statusMenungguPenerbitan) {
            return response()->json([
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'error' => 'Status Menunggu Penerbitan tidak ditemukan',
            ], 400);
        }

        if (!$roleMahasiswa) {
            return response()->json([
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'error' => 'Role Mahasiswa tidak ditemukan',
            ], 400);
        }

        $query = Surat::with(['jenisSurat', 'dibuatOleh', 'statusTerakhir.statusSurat'])
            ->whereIn('id_jenis_surat', $jenisSuratIds)
            ->whereHas('dibuatOleh', function($q) use ($roleMahasiswa) {
                $q->where('id_role_pengusul', $roleMahasiswa->id_role_pengusul);
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
            ->addColumn('mahasiswa', function($row) {
                return $row->dibuatOleh ? $row->dibuatOleh->nama : '-';
            })
            ->addColumn('tanggal_pengajuan', function($row) {
                return $row->tanggal_pengajuan ? Carbon::parse($row->tanggal_pengajuan)->format('d-m-Y') : '-';
            })
            ->addColumn('actions', function($row) {
                return '<a href="' . route('tatausaha.terbitkan.detail', $row->id_surat) . '" class="inline-block bg-green-700 text-white px-3 py-1 rounded-lg hover:bg-green-800 transition-transform duration-300 transform hover:scale-110">Terbitkan</a>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function statussurat()
    {
        // Only get Tata Usaha letter types for the dropdown
        $jenisSurat = JenisSurat::whereIn('jenis_surat', ['Surat Pengantar', 'Surat Permohonan', 'Surat Cuti Akademik', 'Surat Izin Tidak Masuk'])
            ->pluck('jenis_surat', 'id_jenis_surat')
            ->toArray();
        $StatusSurat = StatusSurat::pluck('status_surat', 'id_status_surat')->toArray();
        
        return view('staff.tata-usaha.statussurat', compact('jenisSurat', 'StatusSurat'));
    }

    public function jenissurat()
    {
        $jsdata = DB::select('CALL sp_GetAllJenisSurat()');
        return view('staff.tata-usaha.jenissurat', compact('jsdata'));
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
        return view('staff.tata-usaha.tinjausurat');
    }

    public function getSuratData(Request $request)
    {
        $statusDiajukan = StatusSurat::where('status_surat', 'Diajukan')->first();
        $jenisSuratIds = JenisSurat::whereIn('jenis_surat', ['Surat Pengantar', 'Surat Permohonan', 'Surat Cuti Akademik', 'Surat Izin Tidak Masuk'])->pluck('id_jenis_surat');
        $roleMahasiswa = RolePengusul::where('role', 'Mahasiswa')->first();

        if (!$statusDiajukan) {
            return response()->json([
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'error' => 'Status Diajukan tidak ditemukan',
            ], 400);
        }

        $query = Surat::with(['jenisSurat', 'dibuatOleh', 'statusTerakhir.statusSurat'])
            ->whereIn('id_jenis_surat', $jenisSuratIds)
            ->whereHas('dibuatOleh', function($q) use ($roleMahasiswa) {
                $q->where('id_role_pengusul', $roleMahasiswa->id_role_pengusul);
            })
            ->whereHas('statusTerakhir', function($q) use ($statusDiajukan) {
                $q->where('id_status_surat', $statusDiajukan->id_status_surat);
            });

        // Filter Rentang Tanggal
        if ($request->filled('periode_awal') && $request->filled('periode_akhir')) {
            $startDate = Carbon::parse($request->periode_awal)->startOfDay();
            $endDate = Carbon::parse($request->periode_akhir)->endOfDay();
            $query->whereBetween('tanggal_pengajuan', [$startDate, $endDate]);
        } elseif ($request->filled('periode_awal')) {
            $startDate = Carbon::parse($request->periode_awal)->startOfDay();
            $query->where('tanggal_pengajuan', '>=', $startDate);
        } elseif ($request->filled('periode_akhir')) {
            $endDate = Carbon::parse($request->periode_akhir)->endOfDay();
            $query->where('tanggal_pengajuan', '<=', $endDate);
        }

        // Filter search query
        if ($request->filled('search_query')) {
            $search = $request->search_query;
            $query->where(function($q) use ($search) {
                $q->where('judul_surat', 'like', "%$search%")
                  ->orWhereHas('dibuatOleh', function($q2) use ($search) {
                      $q2->where('nama', 'like', "%$search%");
                  });
            });
        }

        $query->orderBy("tanggal_pengajuan","asc");

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('judul_surat', function($row) {
                return $row->judul_surat;
            })
            ->addColumn('jenis_surat', function($row) {
                return $row->jenisSurat->jenis_surat;
            })
            ->addColumn('ketua', function($row) {
                return $row->dibuat_oleh ? $row->dibuatOleh->nama : '-';
            })
            ->addColumn('tanggal_pengajuan', function($row) {
                return Carbon::parse($row->tanggal_pengajuan)->format('d-m-Y');
            })
            ->addColumn('status', function($row) {
                return $row->statusTerakhir ? $row->statusTerakhir->statusSurat->status_surat : '-';
            })
            ->addColumn('actions', function($row) {
                return '<a href="' . route('tatausaha.tinjau.detail', $row->id_surat) . '" class="inline-block bg-blue-700 text-white px-3 py-1 rounded-lg hover:bg-blue-800 transition-transform duration-300 transform hover:scale-110">Tinjau</a>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function showDetailSurat($id)
    {
        $surat = Surat::with(['jenisSurat', 'dibuatOleh', 'statusTerakhir.statusSurat'])->findOrFail($id);
        return view('staff.tata-usaha.detail-surat', compact('surat'));
    }

    public function detail($id)
    {
        $surat = Surat::with(['jenisSurat', 'dibuatOleh', 'statusTerakhir.statusSurat'])->findOrFail($id);
        return view('staff.tata-usaha.terbitkan-detail', compact('surat'));
    }

    public function tolakSurat(Request $request, $id)
    {
        $request->validate([
            'komentar' => 'required|string|max:500'
        ]);

        $surat = Surat::findOrFail($id);

        // Cari id_status_surat untuk "Ditolak"
        $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();
        if (!$statusDitolak) {
            return back()->with('error', 'Status Ditolak tidak ditemukan!');
        }
        
        $riwayat = RiwayatStatusSurat::create([
            'id_surat' => $surat->id_surat,
            'id_status_surat' => $statusDitolak->id_status_surat,
            'tanggal_rilis' => now('Asia/Jakarta'),
            'keterangan' => 'Ditolak oleh Tata Usaha',
            'diubah_oleh' => auth('staff')->user()->id_staff,
            'diubah_oleh_tipe' => 'staff',
        ]);

        if ($request->filled('komentar')) {
            KomentarSurat::create([
                'id_riwayat_status_surat' => $riwayat->id,
                'id_surat' => $surat->id_surat,
                'id_user' => auth('staff')->id(),
                'komentar' => $request->komentar,
            ]);
        }

        $pembuat = $surat->dibuatOleh; 

        if ($pembuat) {
            $pembuat->notify(new SuratDitolak($surat));
        }

        return redirect()->route('tatausaha.tinjausurat')->with('success', 'Surat berhasil ditolak');
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

        // Jika status terakhir adalah Diajukan, lanjutkan ke Divalidasi dan Menunggu Persetujuan
        if ($lastRiwayat && $lastRiwayat->id_status_surat == $statusDiajukan->id_status_surat) {
            // Tambahkan riwayat status "Divalidasi"
            RiwayatStatusSurat::create([
                'id_surat' => $surat->id_surat,
                'id_status_surat' => $statusValidasi->id_status_surat,
                'tanggal_rilis' => now('Asia/Jakarta'),
                'diubah_oleh' => auth('staff')->user()->id_staff,
                'diubah_oleh_tipe' => 'staff',
            ]);
            // Tambahkan riwayat status "Menunggu Persetujuan" dengan waktu +1 detik
            RiwayatStatusSurat::create([
                'id_surat' => $surat->id_surat,
                'id_status_surat' => $statusMenunggu->id_status_surat,
                'tanggal_rilis' => now('Asia/Jakarta')->addSeconds(1),
                'diubah_oleh' => auth('staff')->user()->id_staff,
                'diubah_oleh_tipe' => 'staff',
            ]);
        } else {
            // Jika status terakhir bukan Diajukan, jangan lanjutkan atau sesuaikan dengan kebutuhan
            return back()->with('error', 'Status surat tidak valid untuk di-approve.');
        }

        return redirect()->route('tatausaha.tinjausurat')->with('success', 'Surat berhasil di-approve dan dikirim ke kepala sub.');
    }

    public function showStatusSurat($id)
    {
        $surat = Surat::with(['riwayatStatus' => function($q) {
            $q->with('statusSurat','komentarSurat')->orderBy('tanggal_rilis', 'asc');
        }, 'dibuatOleh'])->findOrFail($id);

        $riwayat = [];
        $prevStatus = null;
        foreach ($surat->riwayatStatus as $item) {
            $statusName = $item->statusSurat->status_surat ?? '-';
            
            // Logika untuk menentukan siapa yang mengubah status
            if ($item->diubah_oleh && $item->diubah_oleh_tipe) {
                // Jika ada data diubah_oleh, gunakan itu
                $oleh = PengusulHelper::getNamaUserByTipe($item->diubah_oleh, $item->diubah_oleh_tipe);
            } else {
                // Jika tidak ada data diubah_oleh, gunakan pembuat surat (untuk status awal)
                $oleh = PengusulHelper::getNamaPengusul($surat->dibuat_oleh);
            }
            
            $tanggal = Carbon::parse($item->tanggal_rilis)->translatedFormat('j-m-Y H:i');
            
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
                'komentar' => strtolower($statusName) === 'ditolak' 
                    ? optional($item->komentarSurat->first())->komentar 
                    : null,
            ];
            $prevStatus = $statusName;
        }

        return view('staff.tata-usaha.riwayatstatus', [
            'riwayat' => $riwayat
        ]);
    }

    public function getStatusSuratData(Request $request)
    {
        // Get jenis surat IDs for Tata Usaha letter types
        $jenisSuratIds = JenisSurat::whereIn('jenis_surat', ['Surat Pengantar', 'Surat Permohonan', 'Surat Cuti Akademik', 'Surat Izin Tidak Masuk'])->pluck('id_jenis_surat');
        
        // Get role mahasiswa ID
        $roleMahasiswa = RolePengusul::where('role', 'Mahasiswa')->first();
        
        if (!$roleMahasiswa) {
            return response()->json([
                'data' => [],
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'error' => 'Role Mahasiswa tidak ditemukan',
            ], 400);
        }

        $query = Surat::with(['jenisSurat', 'riwayatStatus' => function($q) {
            $q->with('statusSurat')->latest('tanggal_rilis');
        }, 'dibuatOleh'])
        ->where('is_draft', 1) // Only submitted letters
        ->whereIn('id_jenis_surat', $jenisSuratIds) // Only Tata Usaha letter types
        ->whereHas('dibuatOleh', function($q) use ($roleMahasiswa) {
            $q->where('id_role_pengusul', $roleMahasiswa->id_role_pengusul); // Only submitted by mahasiswa
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
        $searchValue = $request->input('search.value', null);
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('judul_surat', 'like', "%{$searchValue}%")
                  ->orWhere('nomor_surat', 'like', "%{$searchValue}%");
            });
        }
        // Search khusus judul_surat dari input custom
        $searchJudul = $request->input('search_judul_surat');
        if (!empty($searchJudul)) {
            $query->where('judul_surat', 'like', "%{$searchJudul}%");
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
                return $row->tanggal_pengajuan ? date('d-m-Y', strtotime($row->tanggal_pengajuan)) : '-';
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    public function search(Request $request)
    {
        $jenisSuratIds = JenisSurat::whereIn('jenis_surat', ['Surat Pengantar', 'Surat Permohonan', 'Surat Cuti Akademik', 'Surat Izin Tidak Masuk'])->pluck('id_jenis_surat');
        $roleMahasiswa = RolePengusul::where('role', 'Mahasiswa')->first();

        $query = Surat::with(['jenisSurat', 'dibuatOleh', 'statusTerakhir.statusSurat'])
            ->whereIn('id_jenis_surat', $jenisSuratIds)
            ->whereHas('dibuatOleh', function($q) use ($roleMahasiswa) {
                $q->where('id_role_pengusul', $roleMahasiswa->id_role_pengusul);
            });

        // Apply date filters
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('tanggal_pengajuan', [$startDate, $endDate]);
        } elseif ($request->filled('month')) {
            $query->whereYear('tanggal_pengajuan', $request->year ?? date('Y'))
                  ->whereMonth('tanggal_pengajuan', $request->month);
        } elseif ($request->filled('year')) {
            $query->whereYear('tanggal_pengajuan', $request->year);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('judul_surat', function($row) {
                return $row->judul_surat;
            })
            ->addColumn('nomor_surat', function($row) {
                return $row->nomor_surat ?? '-';
            })
            ->addColumn('tanggal_surat_dibuat', function($row) {
                return $row->tanggal_surat_dibuat ? Carbon::parse($row->tanggal_surat_dibuat)->format('d-m-Y') : '-';
            })
            ->addColumn('tanggal_pengajuan', function($row) {
                return Carbon::parse($row->tanggal_pengajuan)->format('d-m-Y');
            })
            ->addColumn('status', function($row) {
                return $row->statusTerakhir ? $row->statusTerakhir->statusSurat->status_surat : '-';
            })
            ->rawColumns([])
            ->make(true);
    }

    public function terbitkanSurat(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);
        $statusDiterbitkan = StatusSurat::where('status_surat', 'Diterbitkan')->first();
        if (!$statusDiterbitkan) {
            return back()->with('error', 'Status Diterbitkan tidak ditemukan!');
        }
        // Generate nomor surat otomatis
        $tahun = now('Asia/Jakarta')->year;
        $jenisSurat = $surat->jenisSurat;
        $lastSurat = Surat::where('id_jenis_surat', $surat->id_jenis_surat)
            ->whereYear('tanggal_surat_dibuat', $tahun)
            ->whereNotNull('nomor_surat')
            ->orderByDesc('tanggal_surat_dibuat')
            ->orderByDesc('id_surat')
            ->first();
        $lastNumber = 0;
        if ($lastSurat && preg_match('/^(\d{3})\//', $lastSurat->nomor_surat, $matches)) {
            $lastNumber = (int)$matches[1];
        }
        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        // Singkatan jenis surat: huruf depan tiap kata
        $singkatan = collect(explode(' ', $jenisSurat->jenis_surat))->map(function($word) {
            return strtoupper(substr($word, 0, 1));
        })->implode('');
        // Gabungkan tanggal, bulan, jam, menit, detik tanpa pemisah
        $tanggalJamDetik = now('Asia/Jakarta')->format('dmHis'); // ddmmHHMMSS
        $nomorSurat = "$newNumber/$singkatan/$tanggalJamDetik/$tahun";
        $surat->nomor_surat = $nomorSurat;
        $surat->tanggal_surat_dibuat = now('Asia/Jakarta')->toDateString();
        $surat->save();
        $lastRiwayat = RiwayatStatusSurat::where('id_surat', $surat->id_surat)
            ->orderBy('tanggal_rilis', 'desc')
            ->first();
        $baseTime = $lastRiwayat ? Carbon::parse($lastRiwayat->tanggal_rilis) : now();
        RiwayatStatusSurat::create([
            'id_surat' => $surat->id_surat,
            'id_status_surat' => $statusDiterbitkan->id_status_surat,
            'tanggal_rilis' => $baseTime->copy()->addSecond(1),
            'keterangan' => 'Diterbitkan oleh Tata Usaha',
            'diubah_oleh' => auth('staff')->user()->id_staff,
            'diubah_oleh_tipe' => 'staff',
        ]);
        // Trigger notifikasi ke semua pengusul dan pembuat surat
        foreach ($surat->pengusul as $pengusul) {
            $pengusul->notify(new SuratDiterbitkan($surat));
        }
        if ($surat->dibuatOleh && !$surat->pengusul->contains('id_pengusul', $surat->dibuat_oleh)) {
            $surat->dibuatOleh->notify(new SuratDiterbitkan($surat));
        }



        return redirect()->route('tatausaha.terbitkan')->with('success', 'Surat berhasil diterbitkan!');
    }

    // Contoh penggunaan function GetNamaPengusul
    public function getNamaPengusulExample($id_pengusul)
    {
        $nama = PengusulHelper::getNamaPengusul($id_pengusul);
        return response()->json(['nama' => $nama]);
    }
}
