<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\PivotPengusulSurat;
use App\Models\StatusSurat;
use App\Models\Surat;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\Pengusul;
use App\Models\RiwayatStatusSurat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Helpers\PengusulHelper;
use Illuminate\Notifications\DatabaseNotification;
use Barryvdh\Snappy\Facades\SnappyPdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Barryvdh\DomPDF\Facade\Pdf;

class mahasiswaController extends Controller
{   

    public function index()
{
    $user = auth('pengusul')->id();

    $statusDiterbitkan = StatusSurat::where('status_surat', 'Diterbitkan')->first();
    $statusDitolak = StatusSurat::where('status_surat', 'Ditolak')->first();

    // Hitung surat DITERIMA (status terakhir Diterbitkan)
    $suratDiterima = 0;
    if ($statusDiterbitkan) {
        $suratDiterima = Surat::whereHas('statusTerakhir', function ($q) use ($statusDiterbitkan) {
            $q->where('id_status_surat', $statusDiterbitkan->id_status_surat);
        })
        ->where(function ($q) use ($user) {
            $q->whereHas('pengusul', function ($q2) use ($user) {
                $q2->where('pivot_pengusul_surat.id_pengusul', $user)
                    ->whereIn('pivot_pengusul_surat.id_peran_keanggotaan', [1, 2]);
            })
            ->orWhere('dibuat_oleh', $user);
        })
        ->count();
    }

    // Hitung surat DITOLAK (status terakhir Ditolak)
    $suratDitolak = 0;
    if ($statusDitolak) {
        $suratDitolak = Surat::whereHas('statusTerakhir', function ($q) use ($statusDitolak) {
                $q->where('id_status_surat', $statusDitolak->id_status_surat);
            })
            ->where(function ($q) use ($user) {
                $q->whereHas('pengusul', function ($q2) use ($user) {
                    $q2->where('pivot_pengusul_surat.id_pengusul', $user)
                        ->whereIn('pivot_pengusul_surat.id_peran_keanggotaan', [1, 2]);
                })
                ->orWhere('dibuat_oleh', $user);
            })
            ->count();
    }

    $notifikasiSurat = DatabaseNotification::where('notifiable_type', Pengusul::class)
        ->where('notifiable_id', $user)
        ->latest()
        ->limit(5)
        ->get();

    $columns = [
        'no' => "No",
        'nomor_surat' => "Nomor Surat",
        'judul_surat' => 'Nama Surat',
        'tanggal_surat_dibuat' => 'Tanggal Terbit',
        'lampiran' => 'Dokumen',
        'tanggal_pengajuan' => 'Dibuat Pada'
    ];

    return view('pengusul.mahasiswa.index', compact(
        'columns',
        'suratDiterima',
        'suratDitolak',
        'notifikasiSurat'
    ));
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
                ->where(function($q) use ($user) {
                    $q->whereHas('pengusul', function ($q2) use ($user) {
                        $q2->where('pivot_pengusul_surat.id_pengusul', $user)
                           ->whereIn('pivot_pengusul_surat.id_peran_keanggotaan', [1, 2]);
                    })
                    ->orWhere('dibuat_oleh', $user);
                })
                ->whereHas('statusTerakhir', function($q) use ($statusDiterbitkan) {
                    $q->where('id_status_surat', $statusDiterbitkan->id_status_surat);
                });

            $start = $request->get('start', 0);
            $length = $request->get('length', 5);

            $total = $query->count();

            $surat = $query->skip($start)->take($length)->get();

            $data = $surat->map(function ($item) {
                return [
                    'no' => $item->no,
                    'nomor_surat' => $item->nomor_surat,
                    'judul_surat' => $item->judul_surat,
                    'tanggal_surat_dibuat' => $item->tanggal_surat_dibuat ? date('d-m-Y', strtotime($item->tanggal_surat_dibuat)) : '-',
                    'jenis_surat' => $item->jenisSurat ? $item->jenisSurat->jenis_surat : '-',
                    'lampiran' => $item->lampiran,
                    'tanggal_pengajuan' => $item->tanggal_pengajuan ? date('d-m-Y', strtotime($item->tanggal_pengajuan)) : '-',
                    'aksi' => '<a href="' . route('mahasiswa.surat.downloadPdf', $item->id_surat) . '" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg shadow hover:bg-blue-700 transition duration-300" target="_blank"> <i class="fa-solid fa-file-pdf"></i> Unduh PDF</a>'
                ];
            });
            return response()->json([
                'draw' => $request->get('draw'),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'draw' => $request->get('draw'),
                'error' => $e->getMessage(),
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
        ->where('is_draft', 1)
        ->orderBy('id_surat','desc');

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

        return view('pengusul.mahasiswa.riwayatstatus', [
            'riwayat' => $riwayat,
            'judulSurat' => $surat->judul_surat,
            'jenisSurat' => $surat->jenisSurat ? $surat->jenisSurat->jenis_surat : ''
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
                'diubah_oleh' => auth('pengusul')->id(),
                'diubah_oleh_tipe' => 'pengusul',
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


