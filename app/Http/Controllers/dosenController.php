<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\Pengusul;
use App\Models\StatusSurat;
use App\Models\Surat;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Helpers\PengusulHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Models\RolePengusul;

class dosenController extends Controller
{
    public function index () {
        $user = auth('pengusul')->id();
        $jenisSuratIds = JenisSurat::whereIn('jenis_surat', [
            'Surat Tugas', 'Surat Undangan Kegiatan', 'Surat Izin Tidak Masuk'
        ])->pluck('id_jenis_surat');
        $roleDosen = RolePengusul::where('role', 'Dosen')->first();
        $statusDiajukan = StatusSurat::where('status_surat', 'Diterbitkan')->first();
        $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();
        $statusDiterbitkan = StatusSurat::where('status_surat', 'Diterbitkan')->first();

        // Surat Diterima: status terakhir Diajukan
        $suratDiterima = Surat::where('is_draft', 1)
            ->whereIn('id_jenis_surat', $jenisSuratIds)
            ->whereHas('dibuatOleh', function($q) use ($roleDosen) {
                $q->where('id_role_pengusul', $roleDosen->id_role_pengusul);
            })
            ->whereHas('statusTerakhir', function($q) use ($statusDiajukan) {
                $q->where('id_status_surat', $statusDiajukan->id_status_surat);
            })
            ->count();

        // Surat Ditolak: status terakhir Ditolak dan diubah_oleh_tipe staff
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

        $notifikasiSurat = Surat::with(['jenisSurat', 'statusTerakhir.statusSurat'])
            ->where('is_draft', 1)
            ->whereNotNull('nomor_surat')
            ->whereHas('riwayatStatus', function($q) use ($statusDiterbitkan) {
                $q->where('id_status_surat', $statusDiterbitkan->id_status_surat);
            })
            ->where(function($q) use ($user) {
                $q->where('dibuat_oleh', $user)
                  ->orWhereHas('pengusul', function($q2) use ($user) {
                      $q2->where('pivot_pengusul_surat.id_pengusul', $user)
                         ->whereIn('pivot_pengusul_surat.id_peran_keanggotaan', [1, 2]);
                  });
            })
            ->orderByDesc('tanggal_surat_dibuat')
            ->limit(5)
            ->get();

        $columns = [
            'no' => "No",
            'nomor_surat' => "Nomor Surat",
            'judul_surat' =>'Nama Surat', 
            'tanggal_surat_dibuat' => 'Tanggal Terbit', 
            'lampiran' => 'Dokumen', 
            'tanggal_pengajuan' => 'Dibuat Pada',
        ];
        return view('pengusul.dosen.index', compact('columns','suratDiterima','suratDitolak','notifikasiSurat'));
    }

    public function search(Request $request){
        $limit = $request->input('length');
        $start = $request->input('start');
        $user = auth('pengusul')->id();
        $statusDiterbitkan = StatusSurat::where('status_surat', 'Diterbitkan')->first();
        if (!$statusDiterbitkan) {
            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ]);
        }
        $query = Surat::with(['dibuatOleh'])
            ->whereNotNull('nomor_surat')
            ->whereHas('riwayatStatus', function($q) use ($statusDiterbitkan) {
                $q->where('id_status_surat', $statusDiterbitkan->id_status_surat);
            })
            ->where(function($q) use ($user) {
                $q->where('dibuat_oleh', $user)
                  ->orWhereHas('pengusul', function($q2) use ($user) {
                      $q2->where('pivot_pengusul_surat.id_pengusul', $user)
                         ->whereIn('pivot_pengusul_surat.id_peran_keanggotaan', [1, 2]);
                  });
            });
        $totalData = $query->count();
        $surats = $query->skip($start)->take($limit)->get();
        $data = $surats->map(function($item, $index) use ($start) {
            return [
                'no' => $index + $start + 1,
                'nomor_surat' => $item->nomor_surat ?? '-',
                'judul_surat' => $item->judul_surat ?? '-',
                'tanggal_surat_dibuat' => $item->tanggal_surat_dibuat ? date('d-m-Y', strtotime($item->tanggal_surat_dibuat)) : '-',
                'lampiran' => $item->lampiran,
                'tanggal_pengajuan' => $item->tanggal_pengajuan ? date('d-m-Y', strtotime($item->tanggal_pengajuan)) : '-',
                'aksi' => '<a href="' . route('dosen.surat.downloadPdf', $item->id_surat) . '" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg shadow hover:bg-blue-700 transition duration-300" target="_blank"> <i class="fa-solid fa-file-pdf"></i> Unduh PDF</a>'
            ];
        });
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalData,
            'recordsFiltered' => $totalData,
            'data' => $data,
        ]);
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
        $jenisSurat = JenisSurat::whereIn('jenis_surat', ['Surat Tugas', 'Surat Undangan Kegiatan', 'Surat Izin Tidak Masuk'])
            ->pluck('jenis_surat', 'id_jenis_surat')
            ->toArray();
        $namaPengaju = auth('pengusul')->user() ? auth('pengusul')->user()->nama : '';
        return view('pengusul.dosen.pengajuansurat', compact('jenisSurat','columns','data','namaPengaju'));
    }

    public function draft() {
        return view('pengusul.dosen.draft');
    }

    public function draftData()
{
    // Ambil hanya draft milik user yang sedang login
    $userId = auth('pengusul')->id();
    $surats = Surat::where('is_draft', 0)
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
                <a href="' . route('dosen.surat.edit', $surat->id_surat) . '" class="py-2 px-4 rounded-[10px] bg-blue-700 text-white">Edit</a>
                <button onclick="hapusSurat(' . $surat->id_surat . ')" class="py-2 px-4 rounded-[10px] bg-red-700 text-white ml-2 hover:cursor-pointer">Hapus</button>
                <form id="form-hapus-' . $surat->id_surat . '" action="' . route('dosen.surat.destroy', $surat->id_surat) . '" method="POST" style="display: none; ">
                    ' . csrf_field() . method_field('DELETE') . '
                </form>
            ';
        })
        ->rawColumns(['action']) // supaya tombol tidak di-escape
        ->make(true);
}

    public function status() {
        $jenisSurat = collect(DB::select('CALL sp_GetJenisSuratForSelect()'))->pluck('jenis_surat', 'id_jenis_surat')->toArray();
        $StatusSurat = StatusSurat::pluck('status_surat', 'id_status_surat')->toArray();
        
        return view('pengusul.dosen.status',compact('jenisSurat','StatusSurat'));
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
                    return $row->tanggal_pengajuan ? date('d-m-Y', strtotime($row->tanggal_pengajuan)) : '-';
            })
                ->rawColumns(['status'])
            ->make(true);
    }

    public function searchDosen(Request $request) {
        $query = $request->input('query'); 

        $dosens = Pengusul::where('id_role_pengusul', 1) 
                        ->where(function($queryBuilder) use ($query) {
                            $queryBuilder->where('nip', 'like', "%$query%")
                                         ->orWhere('nama', 'like', "%$query%");
                        })
                        ->limit(4) 
                        ->get(['id_pengusul', 'nip', 'nama']); 

        return response()->json($dosens);
    }

            public function setting()
        {
            return view('pengusul.dosen.setting');
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

        return view('pengusul.dosen.riwayatstatus', [
            'riwayat' => $riwayat,
            'judulSurat' => $surat->judul_surat,
            'jenisSurat' => $surat->jenisSurat ? $surat->jenisSurat->jenis_surat : ''
        ]);
    }

    public function downloadPdf($id)
    {
        $surat = Surat::with([
            'dibuatOleh',
            'jenisSurat',
            'statusTerakhir.statusSurat',
            'pengusul'
        ])->findOrFail($id);

        $tanggalSurat = $surat->tanggal_surat_dibuat
        ? Carbon::parse($surat->tanggal_surat_dibuat)->translatedFormat('l, d F Y')
        : '-';
        $tanggalPengajuan = $surat->tanggal_pengajuan
        ? Carbon::parse($surat->tanggal_pengajuan)->translatedFormat('l, d F Y')
        : '-';
        $today = Carbon::now()->translatedFormat('d-m-Y');

        // --- Generate QR Pengaju & Kepala Sub (SVG) ---
        $qrDir = storage_path('app/public');
        if (!file_exists($qrDir)) {
            mkdir($qrDir, 0777, true);
        }
        $qrPengajuPath = $qrDir . '/qr_pengaju.png';
        $this->generateQrPNG($surat->dibuatOleh->nama ?? '-', $qrPengajuPath);

        $qrKepalaSubPath = $qrDir . '/qr_kepalasub.png';
        $this->generateQrPNG('https://tte.polibatam.ac.id/index.php?page=qrsign&id=UWtZZ1RNUkdldFU9', $qrKepalaSubPath);

        $qrPengajuSVG = file_exists($qrPengajuPath) ? file_get_contents($qrPengajuPath) : '';
        $qrKepalaSubSVG = file_exists($qrKepalaSubPath) ? file_get_contents($qrKepalaSubPath) : '';

        $qrPengajuSVG = $this->cleanSVG($qrPengajuSVG);
        $qrKepalaSubSVG = $this->cleanSVG($qrKepalaSubSVG);

        $jenisSurat = $surat->jenisSurat->jenis_surat ?? 'Surat';
        $namaPengusul = $surat->dibuatOleh->nama ?? 'Pengusul';
        $tanggalSuratDibuat = $surat->tanggal_surat_dibuat 
            ? Carbon::parse($surat->tanggal_surat_dibuat)->format('Y-m-d') 
            : now()->format('Y-m-d');
        $nomor = $surat->nomor_surat ?? 'no-nomor';

        $filename = Str::slug($jenisSurat) . '_' . 
                    Str::slug($namaPengusul) . '_' . 
                    $tanggalSuratDibuat . '_' . 
                    Str::slug($nomor) . '.pdf';

        $pdf = Pdf::loadView('pdf.surat', [
            'surat' => $surat,
            'tanggalSurat' => $tanggalSurat,
            'tanggalPengajuan' => $tanggalPengajuan,
            'today' => $today,
            'qrPengajuSVG' => $qrPengajuSVG,
            'qrKepalaSubSVG' => $qrKepalaSubSVG,
        ]);
        return $pdf->download($filename);
    }

    private function generateQrPNG($data, $filename)
    {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
            'scale' => 5, // bisa disesuaikan
        ]);
    
        (new QRCode($options))->render($data, $filename);
    }

private function cleanSVG($svg)
{
    return preg_replace('/<\\?xml.*?\\?>/', '', $svg);
}

}