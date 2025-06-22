<?php

namespace App\Http\Controllers;

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

    public function statistik()
    {
        return view('staff.staff-umum.statistik');
    }

    public function terbitkan()
    {
        return view('staff.staff-umum.terbitkan');
    }

    public function statussurat()
    {
        return view('staff.staff-umum.statussurat');
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
        // Ambil ID status "Diajukan"
        $statusDiajukan = StatusSurat::where('status_surat', 'Diajukan')->first();
        
        // Ambil ID jenis surat yang diinginkan
        $jenisSuratIds = JenisSurat::whereIn('jenis_surat', ['Surat Tugas', 'Surat Undangan Kegiatan'])->pluck('id_jenis_surat');
        
        // Ambil ID role dosen
        $roleDosen = RolePengusul::where('role', 'Dosen')->first();

        $query = Surat::with(['jenisSurat', 'dibuatOleh', 'statusTerakhir.statusSurat'])
            ->where('is_draft', 1) // 1 = diajukan, 0 = draft
            ->whereIn('id_jenis_surat', $jenisSuratIds) // Hanya jenis surat tertentu
            ->whereHas('dibuatOleh', function($q) use ($roleDosen) {
                $q->where('id_role_pengusul', $roleDosen->id_role_pengusul); // Hanya role dosen
            });

        // Filter status terakhir "Diajukan"
        if ($statusDiajukan) {
            $query->whereHas('riwayatStatus', function($q) use ($statusDiajukan) {
                $q->where('id_status_surat', $statusDiajukan->id_status_surat);
            })->whereDoesntHave('riwayatStatus', function($q) use ($statusDiajukan) {
                $q->where('id_status_surat', '!=', $statusDiajukan->id_status_surat)
                  ->whereRaw('tanggal_rilis > (
                      SELECT MAX(tanggal_rilis) 
                      FROM riwayat_status_surat rss2 
                      WHERE rss2.id_surat = riwayat_status_surat.id_surat 
                      AND rss2.id_status_surat = ?
                  )', [$statusDiajukan->id_status_surat]);
            });
        }

        // Filter Rentang Tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('tanggal_pengajuan', [$startDate, $endDate]);
        }

        // Filter Pencarian Umum (Judul, Ketua, Anggota)
        if ($request->filled('search_query')) {
            $searchQuery = $request->search_query;
            $query->where(function ($q) use ($searchQuery) {
                $q->where('judul_surat', 'like', "%{$searchQuery}%")
                    ->orWhereHas('pengusul', function ($pengusulQuery) use ($searchQuery) {
                        $pengusulQuery->where('nama', 'like', "%{$searchQuery}%");
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
            ->addColumn('tanggal_pengajuan', function($surat) {
                return Carbon::parse($surat->tanggal_pengajuan)->translatedFormat('d F Y');
            })
            ->addColumn('actions', function ($row) {
                return '<a href="' . route('staffumum.tinjau.detail', $row->id_surat) . '" class="bg-blue-700 text-white px-3 py-1 rounded-lg hover:bg-blue-800 transition">Tinjau</a>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
    
    public function showDetailSurat($id)
    {
        $surat = Surat::with(['jenisSurat', 'dibuatOleh', 'pengusul', 'riwayatStatus.statusSurat'])
            ->findOrFail($id);
        
        // Menggunakan view yang sama dengan kepala sub, karena desainnya generik
        return view('kepalasub.tinjau-surat', compact('surat'));
    }
}
