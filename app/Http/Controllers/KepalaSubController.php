<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Surat;
use App\Models\StatusSurat;
use App\Models\RiwayatStatusSurat;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KepalaSubController extends Controller
{
    public function index () {
        // Get status IDs
        $statusMenungguPersetujuan = StatusSurat::where('status_surat', 'Menunggu Persetujuan')->first();
        $statusDisetujui = StatusSurat::where('status_surat', 'Disetujui')->first();
        $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();

        // Calculate statistics for current year
        $currentYear = date('Y');
        
        $suratDiterima = 0;
        $suratDisetujui = 0;
        $suratDitolak = 0;

        if ($statusMenungguPersetujuan) {
            $suratDiterima = Surat::where('is_draft', 1)
                ->whereYear('tanggal_pengajuan', $currentYear)
                ->whereHas('riwayatStatus', function($q) use ($statusMenungguPersetujuan) {
                    $q->where('id_status_surat', $statusMenungguPersetujuan->id_status_surat);
                })->count();
        }

        if ($statusDisetujui) {
            $suratDisetujui = Surat::where('is_draft', 1)
                ->whereYear('tanggal_pengajuan', $currentYear)
                ->whereHas('riwayatStatus', function($q) use ($statusDisetujui) {
                    $q->where('id_status_surat', $statusDisetujui->id_status_surat);
                })->count();
        }

        if ($statusDitolak) {
            $suratDitolak = Surat::where('is_draft', 1)
                ->whereYear('tanggal_pengajuan', $currentYear)
                ->whereHas('riwayatStatus', function($q) use ($statusDitolak) {
                    $q->where('id_status_surat', $statusDitolak->id_status_surat);
                })->count();
        }

        $columns = [
            'no' => "No",
            'nomor_surat' => "Nomor Surat",
            'judul_surat' =>'Nama Surat', 
            'tanggal_pengajuan' => 'Tanggal Pengajuan', 
            'jenis_surat' => 'Jenis Surat',
            'pengusul' => 'Pengusul',
            'lampiran' => 'Lampiran',
            'status' => 'Status'
        ];

        return view('kepalasub.index', compact('columns', 'suratDiterima', 'suratDisetujui', 'suratDitolak'));
    }

    public function getDashboardData(Request $request) {
        // Get status IDs
        $statusMenungguPersetujuan = StatusSurat::where('status_surat', 'Menunggu Persetujuan')->first();
        $statusDisetujui = StatusSurat::where('status_surat', 'Disetujui')->first();
        $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();
        
        if (!$statusMenungguPersetujuan) {
            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }

        $query = Surat::with(['jenisSurat', 'dibuatOleh', 'riwayatStatus' => function($q) {
            $q->with('statusSurat')->latest('tanggal_rilis');
        }])
        ->where('is_draft', 1)
        ->where(function($q) use ($statusMenungguPersetujuan, $statusDisetujui, $statusDitolak) {
            // Show letters with current status "Menunggu Persetujuan"
            $q->whereHas('riwayatStatus', function($subQ) use ($statusMenungguPersetujuan) {
                $subQ->where('id_status_surat', $statusMenungguPersetujuan->id_status_surat);
            })
            // Or show letters that have been approved or rejected (as history)
            ->orWhere(function($subQ) use ($statusDisetujui, $statusDitolak) {
                if ($statusDisetujui) {
                    $subQ->whereHas('riwayatStatus', function($subSubQ) use ($statusDisetujui) {
                        $subSubQ->where('id_status_surat', $statusDisetujui->id_status_surat);
                    });
                }
                if ($statusDitolak) {
                    $subQ->orWhereHas('riwayatStatus', function($subSubQ) use ($statusDitolak) {
                        $subSubQ->where('id_status_surat', $statusDitolak->id_status_surat);
                    });
                }
            });
        });

        // Apply date filters
        $filterType = $request->input('filter_type', 'tahun');
        $year = $request->input('year', date('Y'));
        $month = $request->input('month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($filterType === 'jarak' && $startDate && $endDate) {
            $query->whereBetween('tanggal_pengajuan', [$startDate, $endDate]);
        } elseif ($filterType === 'bulan' && $month) {
            $query->whereYear('tanggal_pengajuan', $year)
                  ->whereMonth('tanggal_pengajuan', $month);
        } else {
            // Default to year filter
            $query->whereYear('tanggal_pengajuan', $year);
        }

        $totalData = $query->count();

        // Search functionality
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('judul_surat', 'like', "%$search%")
                  ->orWhere('nomor_surat', 'like', "%$search%")
                  ->orWhereHas('dibuatOleh', function($subQ) use ($search) {
                      $subQ->where('nama', 'like', "%$search%");
                  });
            });
        }

        $filterData = $query->count();

        $surats = $query->skip($request->input('start'))->take($request->input('length'))->get();
        
        $data = $surats->map(function($item, $index) use ($request) {
            $currentStatus = $item->riwayatStatus->first();

            return [
                'no' => '', // This will be automatically numbered by DataTables
                'id' => $item->id_surat,
                'nomor_surat' => $item->nomor_surat ?? '-',
                'judul_surat' => $item->judul_surat ?? '-',
                'tanggal_pengajuan' => $item->tanggal_pengajuan ? date('d/m/Y', strtotime($item->tanggal_pengajuan)) : '-',
                'jenis_surat' => $item->jenisSurat ? $item->jenisSurat->jenis_surat : '-',
                'pengusul' => $item->dibuatOleh ? $item->dibuatOleh->nama : '-',
                'lampiran' => $item->lampiran ?? '-',
                'status' => $currentStatus ? $currentStatus->statusSurat->status_surat : '-'
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $filterData,
            'data' => $data,
        ]);
    }

    public function statistik(Request $request) {
        // Get status IDs
        $statusMenungguPersetujuan = StatusSurat::where('status_surat', 'Menunggu Persetujuan')->first();
        $statusDisetujui = StatusSurat::where('status_surat', 'Disetujui')->first();
        $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();

        // Get filter parameters
        $year = $request->input('year', date('Y'));
        $month = $request->input('month');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Base query for all letters
        $baseQuery = Surat::where('is_draft', 1);

        // Apply date filters
        if ($startDate && $endDate) {
            $baseQuery->whereBetween('tanggal_pengajuan', [$startDate, $endDate]);
        } elseif ($month) {
            $baseQuery->whereYear('tanggal_pengajuan', $year)
                      ->whereMonth('tanggal_pengajuan', $month);
        } else {
            $baseQuery->whereYear('tanggal_pengajuan', $year);
        }

        // Calculate statistics for the three statuses
        $suratMenungguPersetujuan = 0;
        $suratDisetujui = 0;
        $suratDitolak = 0;

        if ($statusMenungguPersetujuan) {
            $suratMenungguPersetujuan = (clone $baseQuery)
                ->whereHas('riwayatStatus', function($q) use ($statusMenungguPersetujuan) {
                    $q->where('id_status_surat', $statusMenungguPersetujuan->id_status_surat);
                })->count();
        }

        if ($statusDisetujui) {
            $suratDisetujui = (clone $baseQuery)
                ->whereHas('riwayatStatus', function($q) use ($statusDisetujui) {
                    $q->where('id_status_surat', $statusDisetujui->id_status_surat);
                })->count();
        }

        if ($statusDitolak) {
            $suratDitolak = (clone $baseQuery)
                ->whereHas('riwayatStatus', function($q) use ($statusDitolak) {
                    $q->where('id_status_surat', $statusDitolak->id_status_surat);
                })->count();
        }

        // Monthly data for bar chart
        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthlyQuery = (clone $baseQuery)->whereMonth('tanggal_pengajuan', $i);
            $monthlyData[] = $monthlyQuery->count();
        }

        // Pie chart data
        $pieChartData = [
            'Menunggu Persetujuan' => $suratMenungguPersetujuan,
            'Disetujui' => $suratDisetujui,
            'Ditolak' => $suratDitolak
        ];

        // Letters per category (all types)
        $suratPerKategori = Surat::join('jenis_surat', 'surat.id_jenis_surat', '=', 'jenis_surat.id_jenis_surat')
            ->where('surat.is_draft', 1)
            ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                return $q->whereBetween('surat.tanggal_pengajuan', [$startDate, $endDate]);
            })
            ->when($month, function($q) use ($year, $month) {
                return $q->whereYear('surat.tanggal_pengajuan', $year)
                         ->whereMonth('surat.tanggal_pengajuan', $month);
            }, function($q) use ($year) {
                return $q->whereYear('surat.tanggal_pengajuan', $year);
            })
            ->select('jenis_surat.jenis_surat as nama', DB::raw('count(*) as count'))
            ->groupBy('jenis_surat.id_jenis_surat', 'jenis_surat.jenis_surat')
            ->get()
            ->map(function($item) {
                return [
                    'nama' => $item->nama,
                    'count' => $item->count
                ];
            })
            ->toArray();

        // Status letters
        $statusSurat = [];
        if ($statusMenungguPersetujuan) {
            $statusSurat[] = [
                'nama' => 'Menunggu Persetujuan',
                'count' => $suratMenungguPersetujuan
            ];
        }
        if ($statusDisetujui) {
            $statusSurat[] = [
                'nama' => 'Disetujui',
                'count' => $suratDisetujui
            ];
        }
        if ($statusDitolak) {
            $statusSurat[] = [
                'nama' => 'Ditolak',
                'count' => $suratDitolak
            ];
        }

        return view('kepalasub.statistik', compact(
            'suratMenungguPersetujuan',
            'suratDisetujui', 
            'suratDitolak',
            'monthlyData',
            'pieChartData',
            'suratPerKategori',
            'statusSurat',
            'year',
            'month',
            'startDate',
            'endDate'
        ));
    }

    public function persetujuansurat() {
        return view('kepalasub.persetujuansurat');
    }

    public function getSuratDiajukanData() {
        // Ambil ID status "Menunggu Persetujuan"
        $statusMenungguPersetujuan = StatusSurat::where('status_surat', 'Menunggu Persetujuan')->first();
        
        if (!$statusMenungguPersetujuan) {
            return response()->json(['data' => []]);
        }

        // Ambil surat dengan status terakhir "Menunggu Persetujuan"
        $surat = Surat::with(['jenisSurat', 'dibuatOleh', 'statusTerakhir.statusSurat'])
            ->whereHas('riwayatStatus', function($query) use ($statusMenungguPersetujuan) {
                $query->where('id_status_surat', $statusMenungguPersetujuan->id_status_surat);
            })
            ->whereDoesntHave('riwayatStatus', function($query) use ($statusMenungguPersetujuan) {
                $query->where('id_status_surat', '!=', $statusMenungguPersetujuan->id_status_surat)
                      ->where('tanggal_rilis', '>', function($subQuery) use ($statusMenungguPersetujuan) {
                          $subQuery->select('tanggal_rilis')
                                   ->from('riwayat_status_surat')
                                   ->where('id_surat', DB::raw('surat.id_surat'))
                                   ->where('id_status_surat', $statusMenungguPersetujuan->id_status_surat)
                                   ->orderBy('tanggal_rilis', 'desc')
                                   ->limit(1);
                      });
            });

        return DataTables::of($surat)
            ->addIndexColumn()
            ->addColumn('jenis_surat', function($surat) {
                return $surat->jenisSurat ? $surat->jenisSurat->jenis_surat : '-';
            })
            ->addColumn('pengusul', function($surat) {
                return $surat->dibuatOleh ? $surat->dibuatOleh->nama : '-';
            })
            ->addColumn('tanggal_pengajuan', function($surat) {
                return date('d/m/Y', strtotime($surat->tanggal_pengajuan));
            })
            ->addColumn('status', function($surat) {
                return $surat->statusTerakhir && $surat->statusTerakhir->statusSurat 
                    ? $surat->statusTerakhir->statusSurat->status_surat 
                    : '-';
            })
            ->addColumn('actions', function($surat) {
                return '<a href="/kepala-sub/surat/' . $surat->id_surat . '/tinjau" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">Tinjau</a>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function tinjauSurat($id) {
        $surat = Surat::with(['jenisSurat', 'dibuatOleh', 'pengusul', 'riwayatStatus.statusSurat'])
            ->findOrFail($id);
        
        return view('kepalasub.tinjau-surat', compact('surat'));
    }

    public function approveSurat(Request $request, $id) {
        try {
            $surat = Surat::findOrFail($id);
            
            // Get status IDs
            $statusDisetujui = StatusSurat::where('status_surat', 'Disetujui')->first();
            
            if (!$statusDisetujui) {
                return response()->json(['success' => false, 'message' => 'Status Disetujui tidak ditemukan']);
            }

            // Add new status history
            RiwayatStatusSurat::create([
                'id_surat' => $surat->id_surat,
                'id_status_surat' => $statusDisetujui->id_status_surat,
                'tanggal_rilis' => now(),
                'keterangan' => 'Disetujui oleh Kepala Sub'
            ]);

            return response()->json(['success' => true, 'message' => 'Surat berhasil disetujui']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function rejectSurat(Request $request, $id) {
        try {
            $surat = Surat::findOrFail($id);
            
            // Get status IDs
            $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();
            
            if (!$statusDitolak) {
                return response()->json(['success' => false, 'message' => 'Status Ditolak tidak ditemukan']);
            }

            // Add new status history
            RiwayatStatusSurat::create([
                'id_surat' => $surat->id_surat,
                'id_status_surat' => $statusDitolak->id_status_surat,
                'tanggal_rilis' => now(),
                'keterangan' => 'Ditolak oleh Kepala Sub'
            ]);

            return response()->json(['success' => true, 'message' => 'Surat berhasil ditolak']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }
}
