<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Surat;
use App\Models\StatusSurat;
use App\Models\RiwayatStatusSurat;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class KepalaSubController extends Controller
{
    public function index () {
        return view('kepalasub.index');
    }

    public function statistik() {
        return view('kepalasub.statistik');
    }

    public function persetujuansurat() {
        return view('kepalasub.persetujuansurat');
    }

    public function getSuratDiajukanData() {
        // Ambil ID status "Diajukan"
        $statusDiajukan = StatusSurat::where('status_surat', 'Diajukan')->first();
        
        if (!$statusDiajukan) {
            return response()->json(['data' => []]);
        }

        // Ambil surat dengan status terakhir "Diajukan"
        $surat = Surat::with(['jenisSurat', 'dibuatOleh', 'statusTerakhir.statusSurat'])
            ->whereHas('riwayatStatus', function($query) use ($statusDiajukan) {
                $query->where('id_status_surat', $statusDiajukan->id_status_surat);
            })
            ->whereDoesntHave('riwayatStatus', function($query) use ($statusDiajukan) {
                $query->where('id_status_surat', '!=', $statusDiajukan->id_status_surat)
                      ->where('tanggal_rilis', '>', function($subQuery) use ($statusDiajukan) {
                          $subQuery->select('tanggal_rilis')
                                   ->from('riwayat_status_surat')
                                   ->where('id_surat', DB::raw('surat.id_surat'))
                                   ->where('id_status_surat', $statusDiajukan->id_status_surat)
                                   ->orderBy('tanggal_rilis', 'desc')
                                   ->limit(1);
                      });
            });

        return DataTables::of($surat)
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
}
