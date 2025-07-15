<?php

namespace App\Http\Controllers;

use App\Models\RiwayatStatusSurat;
use App\Notifications\SuratDitolak;
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

        // Jenis surat sesuai permintaan
        $jenisSuratIds = JenisSurat::whereIn('jenis_surat', ['Surat Tugas', 'Surat Undangan Kegiatan', 'Surat Izin Tidak Masuk'])->pluck('id_jenis_surat');
        $roleDosen = RolePengusul::where('role', 'Dosen')->first();
        $statusMenunggu = StatusSurat::where('status_surat', 'Diajukan')->first();
        $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();

        // Surat diterima: status terakhir 'Menunggu Persetujuan'
        $suratDiterima = 0;
        if ($roleDosen && $statusMenunggu) {
            $suratDiterima = Surat::where('is_draft', 1)
                ->whereIn('id_jenis_surat', $jenisSuratIds)
                ->whereHas('dibuatOleh', function($q) use ($roleDosen) {
                    $q->where('id_role_pengusul', $roleDosen->id_role_pengusul);
                })
                ->whereHas('statusTerakhir', function($q) use ($statusMenunggu) {
                    $q->where('id_status_surat', $statusMenunggu->id_status_surat);
                })
                ->count();
        }

        // Surat ditolak: status terakhir 'Ditolak' dan diubah_oleh_tipe = 'staff'
        $suratDitolak = 0;
        if ($roleDosen && $statusDitolak) {
            $suratDitolak = Surat::where('is_draft', 1)
                ->whereIn('id_jenis_surat', $jenisSuratIds)
                ->whereHas('dibuatOleh', function($q) use ($roleDosen) {
                    $q->where('id_role_pengusul', $roleDosen->id_role_pengusul);
                })
                ->whereHas('statusTerakhir', function($q) use ($statusDitolak) {
                    $q->where('id_status_surat', $statusDitolak->id_status_surat)
                      ->where('diubah_oleh_tipe', 'staff');
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

        // Cek duplikasi
        if (JenisSurat::whereRaw('LOWER(jenis_surat) = ?', [strtolower($request->jenis_surat)])->exists()) {
            return redirect()->back()->with('error', 'Jenis surat sudah ada!');
        }

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

        // Cek duplikasi, kecuali untuk dirinya sendiri
        if (JenisSurat::whereRaw('LOWER(jenis_surat) = ?', [strtolower($request->jenis_surat)])
            ->where('id_jenis_surat', '!=', $id)
            ->exists()) {
            return redirect()->back()->with('error', 'Jenis surat sudah ada!');
        }

        try {
            JenisSurat::where('id_jenis_surat', $id)->update([
                'jenis_surat' => $request->jenis_surat,
            ]);

            return redirect()->route('staffumum.jenissurat')->with('success', 'Jenis surat berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memperbarui jenis surat: ' . $e->getMessage());
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
        
        // Get Surat Izin Tidak Masuk ID separately
        $suratIzinTidakMasuk = JenisSurat::where('jenis_surat', 'Surat Izin Tidak Masuk')->first();
        
        $roleDosen = RolePengusul::where('role', 'Dosen')->first();

        $query = Surat::with(['jenisSurat', 'dibuatOleh', 'statusTerakhir.statusSurat'])
            ->where(function($q) use ($jenisSuratIds, $suratIzinTidakMasuk) {
                // Surat yang memerlukan ketua dan anggota
                if (!empty($jenisSuratIds)) {
                    $q->whereIn('id_jenis_surat', $jenisSuratIds);
                }
                
                // ATAU Surat Izin Tidak Masuk (hanya dibuat oleh pengusul)
                if ($suratIzinTidakMasuk) {
                    $q->orWhere('id_jenis_surat', $suratIzinTidakMasuk->id_jenis_surat);
                }
            })
            ->whereHas('dibuatOleh', function($q) use ($roleDosen) {
                $q->where('id_role_pengusul', $roleDosen->id_role_pengusul);
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
                  ->orWhereHas('pengusul', function($q2) use ($search) {
                      $q2->where('nama', 'like', "%$search%");
                  });
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('judul_surat', function ($surat) {
                return $surat->judul_surat ?? '-';
            })
            ->addColumn('jenis_surat', function ($surat) {
                return optional($surat->jenisSurat)->jenis_surat ?? '-';
            })
            ->addColumn('pengaju', function ($surat) {
                return $surat->dibuatOleh ? $surat->dibuatOleh->nama : '-';
            })
            ->addColumn('ketua', function ($surat) {
                // Untuk Surat Izin Tidak Masuk, tidak ada ketua
                if ($surat->jenisSurat && $surat->jenisSurat->jenis_surat === 'Surat Izin Tidak Masuk') {
                    return '-';
                }
                
                $ketua = $surat->pengusul()->wherePivot('id_peran_keanggotaan', 1)->first(); 
                return optional($ketua) ? PengusulHelper::getNamaPengusul($ketua->id_pengusul) : '-';
            })
            ->addColumn('tanggal_pengajuan', function($surat) {
                return Carbon::parse($surat->tanggal_pengajuan)->locale('id')->translatedFormat('d-m-Y');
            })
            ->addColumn('status', function($surat) {
                return optional($surat->statusTerakhir->statusSurat)->status_surat ?? 'Diajukan';
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
                return $row->tanggal_pengajuan ? Carbon::parse($row->tanggal_pengajuan)->format('d-m-Y') : '-';
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
            'keterangan' => 'Diterbitkan oleh Staff Umum',
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
        return redirect()->route('staffumum.terbitkan')->with('success', 'Surat berhasil diterbitkan!');
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
                'keterangan' => $request->komentar ?? 'Divalidasi oleh Staff Umum',
                'diubah_oleh' => auth('staff')->user()->id_staff,
                'diubah_oleh_tipe' => 'staff',
            ]);
            // Tambahkan riwayat status "Menunggu Persetujuan" dengan waktu +1 detik
            RiwayatStatusSurat::create([
                'id_surat' => $surat->id_surat,
                'id_status_surat' => $statusMenunggu->id_status_surat,
                'tanggal_rilis' => $now->copy()->addSecond(),
                'keterangan' => 'Dikirim ke Kepala Sub untuk persetujuan',
                'diubah_oleh' => auth('staff')->user()->id_staff,
                'diubah_oleh_tipe' => 'staff',
            ]);
        } else {
            // Jika status terakhir bukan Diajukan, jangan lanjutkan atau sesuaikan dengan kebutuhan
            return back()->with('error', 'Status surat tidak valid untuk di-approve.');
        }

        return redirect()->route('staffumum.tinjausurat')->with('success', 'Surat berhasil di-approve dan dikirim ke kepala sub.');
    }

    public function tolakSurat(Request $request, $id)
    {
        $request->validate([
            'komentar' => 'required|string|max:500'
        ]);

        $surat = Surat::findOrFail($id);
        $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();
        if (!$statusDitolak) {
            return back()->with('error', 'Status Ditolak tidak ditemukan!');
        }
        $lastRiwayat = RiwayatStatusSurat::where('id_surat', $surat->id_surat)
            ->orderBy('tanggal_rilis', 'desc')
            ->first();
        $baseTime = $lastRiwayat ? Carbon::parse($lastRiwayat->tanggal_rilis) : now();
            $riwayat = RiwayatStatusSurat::create([
                'id_surat' => $surat->id_surat,
                'id_status_surat' => $statusDitolak->id_status_surat,
                'tanggal_rilis' => $baseTime->copy()->addSecond(1),
                'keterangan' => 'Ditolak oleh Staff Umum',
                'diubah_oleh' => auth('staff')->id(),
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
        return redirect()->route('staffumum.tinjausurat')->with('success', 'Surat berhasil ditolak!');
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
        $jenisSuratIds = JenisSurat::whereIn('jenis_surat', ['Surat Tugas', 'Surat Undangan Kegiatan','Surat Izin Tidak Masuk'])->pluck('id_jenis_surat');
        
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
        if ($request->filled('search_judul_surat')) {
            $search = $request->search_judul_surat;
            $query->where(function ($q) use ($search) {
                $q->where('judul_surat', 'like', "%{$search}%")
                  ->orWhere('nomor_surat', 'like', "%{$search}%");
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
                return $row->tanggal_pengajuan ? date('d-m-Y', strtotime($row->tanggal_pengajuan)) : '-';
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    public function search(Request $request)
    {
        // Get status IDs
        $statusDiajukan = StatusSurat::where('status_surat', 'Diajukan')->first();
        $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();
        $statusDivalidasi = StatusSurat::where('status_surat', 'Divalidasi')->first();
        
        // Get jenis surat IDs for specific types
        $jenisSuratIds = JenisSurat::whereIn('jenis_surat', [
            'Surat Pengantar', 
            'Surat Permohonan', 
            'Surat Cuti Akademik'
        ])->pluck('id_jenis_surat');
        
        // Get Surat Izin Tidak Masuk ID separately
        $suratIzinTidakMasuk = JenisSurat::where('jenis_surat', 'Surat Izin Tidak Masuk')->first();
        
        // Get role mahasiswa ID (for letters from mahasiswa)
        $roleMahasiswa = RolePengusul::where('role', 'Dosen')->first();
        
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
        ->where(function($q) use ($jenisSuratIds, $suratIzinTidakMasuk) {
            // Surat yang memerlukan ketua dan anggota
            if (!empty($jenisSuratIds)) {
                $q->whereIn('id_jenis_surat', $jenisSuratIds);
            }
            
            // ATAU Surat Izin Tidak Masuk (hanya dibuat oleh pengusul)
            if ($suratIzinTidakMasuk) {
                $q->orWhere('id_jenis_surat', $suratIzinTidakMasuk->id_jenis_surat);
            }
        })
        ->whereHas('dibuatOleh', function($q) use ($roleMahasiswa) {
            $q->where('id_role_pengusul', $roleMahasiswa->id_role_pengusul); // Only submitted by mahasiswa
        })
        ->where(function($q) use ($statusDiajukan, $statusDitolak, $statusDivalidasi) {
            // Surat dengan status Diajukan
            if ($statusDiajukan) {
                $q->whereHas('riwayatStatus', function($subQ) use ($statusDiajukan) {
                    $subQ->where('id_status_surat', $statusDiajukan->id_status_surat);
                });
            }
            
            // ATAU surat yang pernah ditolak oleh staff umum
            if ($statusDitolak) {
                $q->orWhereHas('riwayatStatus', function($subQ) use ($statusDitolak) {
                    $subQ->where('id_status_surat', $statusDitolak->id_status_surat)
                          ->where('keterangan', 'like', '%Staff Umum%');
                });
            }
            
            // ATAU surat yang pernah divalidasi oleh staff umum
            if ($statusDivalidasi) {
                $q->orWhereHas('riwayatStatus', function($subQ) use ($statusDivalidasi) {
                    $subQ->where('id_status_surat', $statusDivalidasi->id_status_surat)
                          ->where('keterangan', 'like', '%Staff Umum%');
                });
            }
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
                  ->orWhere('nomor_surat', 'like', "%{$searchValue}%")
                  ->orWhereHas('dibuatOleh', function($subQ) use ($searchValue) {
                      $subQ->where('nama', 'like', "%{$searchValue}%");
                  });
            });
        }

        return DataTables::of($query)
            ->addColumn('jenis_surat', fn($row) => $row->jenisSurat->jenis_surat ?? '-')
            ->addColumn('nomor_surat', function($row) {
                return $row->nomor_surat ? $row->nomor_surat : '-';
            })
            ->addColumn('pengusul', function($row) {
                return $row->dibuatOleh ? $row->dibuatOleh->nama : '-';
            })
            ->addColumn('status', function ($row) {
                $latestStatus = $row->riwayatStatus->first();
                return $latestStatus ? $latestStatus->statusSurat->status_surat : '-';
            })
            ->addColumn('tanggal_pengajuan', function($row) {
                return $row->tanggal_pengajuan ? date('d-m-Y', strtotime($row->tanggal_pengajuan)) : '-';
            })
            ->addColumn('actions', function ($row) {
                $id = $row->id_surat;
                return '<button class="bg-blue-100 text-black rounded-xl px-4 py-1 font-semibold text-sm hover:bg-blue-200 transition hover:scale-110 cursor-pointer btn-detail-surat" data-id="' . $id . '" data-url="/staff-umum/statussurat/' . $id . '">Detail</button>';
            })
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }
}
