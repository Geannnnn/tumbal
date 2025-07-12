<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Surat;
use App\Models\StatusSurat;
use App\Models\RiwayatStatusSurat;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\PengusulHelper;
use App\Models\KomentarSurat;
use App\Notifications\SuratDitolak;
use Illuminate\Support\Facades\Auth;

class DirekturController extends Controller
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
                ->whereHas('statusTerakhir.statusSurat', function($q){
                    $q->where('status_surat', 'Diterbitkan');
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

        return view('direktur.index', compact('columns', 'suratDiterima', 'suratDisetujui', 'suratDitolak'));
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
                'tanggal_pengajuan' => $item->tanggal_pengajuan ? date('d-m-Y', strtotime($item->tanggal_pengajuan)) : '-',
                'jenis_surat' => $item->jenisSurat ? $item->jenisSurat->jenis_surat : '-',
                'pengusul' => $item->dibuat_oleh ? PengusulHelper::getNamaPengusul($item->dibuat_oleh) : '-',
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

        return view('direktur.statistik', compact('suratMenungguPersetujuan', 'suratDisetujui', 'suratDitolak'));
    }

    public function persetujuansurat() {
        return view('direktur.persetujuansurat');
    }

    public function getSuratDiajukanData(Request $request) {
        $statusMenungguPersetujuan = StatusSurat::where('status_surat', 'Menunggu Persetujuan')->first();
        
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
        ->whereHas('riwayatStatus', function($q) use ($statusMenungguPersetujuan) {
            $q->where('id_status_surat', $statusMenungguPersetujuan->id_status_surat);
        });

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
            return [
                'no' => '', // This will be automatically numbered by DataTables
                'id' => $item->id_surat,
                'nomor_surat' => $item->nomor_surat ?? '-',
                'judul_surat' => $item->judul_surat ?? '-',
                'tanggal_pengajuan' => $item->tanggal_pengajuan ? date('d-m-Y', strtotime($item->tanggal_pengajuan)) : '-',
                'jenis_surat' => $item->jenisSurat ? $item->jenisSurat->jenis_surat : '-',
                'pengusul' => $item->dibuat_oleh ? PengusulHelper::getNamaPengusul($item->dibuat_oleh) : '-',
                'lampiran' => $item->lampiran ?? '-',
                'status' => 'Menunggu Persetujuan'
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $filterData,
            'data' => $data,
        ]);
    }

    public function tinjauSurat($id) {
        $surat = Surat::with([
            'jenisSurat',
            'dibuatOleh',
            'riwayatStatus' => function($q) {
                $q->orderBy('tanggal_rilis', 'asc');
            },
            'riwayatStatus.statusSurat'
        ])->findOrFail($id);
        
        return view('direktur.tinjau-surat', compact('surat'));
    }

    public function approveSurat(Request $request, $id) {
        try {
            $surat = Surat::findOrFail($id);
            $statusDisetujui = StatusSurat::where('status_surat', 'Disetujui')->first();
            if (!$statusDisetujui) {
                return response()->json(['success' => false, 'message' => 'Status Disetujui tidak ditemukan']);
            }
            
            // Get current logged in direktur
            $currentDirektur = Auth::guard('direktur')->user();
            
            // Ambil riwayat status terakhir
            $lastRiwayat = RiwayatStatusSurat::where('id_surat', $surat->id_surat)
                ->orderBy('tanggal_rilis', 'desc')
                ->first();
            $baseTime = $lastRiwayat ? Carbon::parse($lastRiwayat->tanggal_rilis) : now();
            
            // Tambahkan riwayat status baru
            $riwayat = RiwayatStatusSurat::create([
                'id_surat' => $surat->id_surat,
                'id_status_surat' => $statusDisetujui->id_status_surat,
                'tanggal_rilis' => $baseTime->addSecond(1),
                'keterangan' => $request->keterangan ?? 'Disetujui oleh Direktur',
                'diubah_oleh' => $currentDirektur->id_direktur,
                'diubah_oleh_tipe' => 'direktur'
            ]);

            return response()->json(['success' => true, 'message' => 'Surat berhasil disetujui']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function rejectSurat(Request $request, $id) {
        try {
            $surat = Surat::findOrFail($id);
            $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();
            if (!$statusDitolak) {
                return response()->json(['success' => false, 'message' => 'Status Ditolak tidak ditemukan']);
            }
            
            // Get current logged in direktur
            $currentDirektur = Auth::guard('direktur')->user();
            
            // Ambil riwayat status terakhir
            $lastRiwayat = RiwayatStatusSurat::where('id_surat', $surat->id_surat)
                ->orderBy('tanggal_rilis', 'desc')
                ->first();
            $baseTime = $lastRiwayat ? Carbon::parse($lastRiwayat->tanggal_rilis) : now();
            
            // Tambahkan riwayat status baru
            $riwayat = RiwayatStatusSurat::create([
                'id_surat' => $surat->id_surat,
                'id_status_surat' => $statusDitolak->id_status_surat,
                'tanggal_rilis' => $baseTime->addSecond(1),
                'keterangan' => $request->keterangan ?? 'Ditolak oleh Direktur',
                'diubah_oleh' => $currentDirektur->id_direktur,
                'diubah_oleh_tipe' => 'direktur'
            ]);

            return response()->json(['success' => true, 'message' => 'Surat berhasil ditolak']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    public function riwayatPersetujuan()
    {
        return view('direktur.riwayat-persetujuan');
    }

    public function riwayatPersetujuanData(Request $request)
    {
        $statusDisetujui = StatusSurat::where('status_surat', 'Disetujui')->first();
        $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();
        
        // Get current logged in direktur
        $currentDirektur = Auth::guard('direktur')->user();
        
        $query = Surat::with(['jenisSurat', 'dibuatOleh', 'riwayatStatus' => function($q) {
            $q->with('statusSurat')->latest('tanggal_rilis');
        }])
        ->where('is_draft', 1)
        ->where(function($q) use ($statusDisetujui, $statusDitolak) {
            if ($statusDisetujui) {
                $q->whereHas('riwayatStatus', function($subQ) use ($statusDisetujui) {
                    $subQ->where('id_status_surat', $statusDisetujui->id_status_surat);
                });
            }
            if ($statusDitolak) {
                $q->orWhereHas('riwayatStatus', function($subQ) use ($statusDitolak) {
                    $subQ->where('id_status_surat', $statusDitolak->id_status_surat);
                });
            }
        })
        // Only show surat that have been processed by current direktur
        ->whereHas('riwayatStatus', function($q) use ($currentDirektur) {
            $q->where('diubah_oleh', $currentDirektur->id_direktur)
              ->where('diubah_oleh_tipe', 'direktur');
        });

        // Filter by status
        $statusFilter = $request->input('status_filter');
        if ($statusFilter) {
            $statusId = null;
            if ($statusFilter === 'Disetujui' && $statusDisetujui) {
                $statusId = $statusDisetujui->id_status_surat;
            } elseif ($statusFilter === 'Ditolak' && $statusDitolak) {
                $statusId = $statusDitolak->id_status_surat;
            }
            
            if ($statusId) {
                $query->whereHas('riwayatStatus', function($q) use ($statusId, $currentDirektur) {
                    $q->where('id_status_surat', $statusId)
                      ->where('diubah_oleh', $currentDirektur->id_direktur)
                      ->where('diubah_oleh_tipe', 'direktur');
                });
            }
        }

        // Filter by year
        $yearFilter = $request->input('year_filter');
        if ($yearFilter) {
            $query->whereYear('tanggal_pengajuan', $yearFilter);
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
                'tanggal_pengajuan' => $item->tanggal_pengajuan ? date('d-m-Y', strtotime($item->tanggal_pengajuan)) : '-',
                'jenis_surat' => $item->jenisSurat ? $item->jenisSurat->jenis_surat : '-',
                'pengusul' => $item->dibuat_oleh ? PengusulHelper::getNamaPengusul($item->dibuat_oleh) : '-',
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
}
