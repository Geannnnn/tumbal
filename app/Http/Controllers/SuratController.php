<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\PivotPengusulSurat;
use App\Models\RiwayatStatusSurat;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SuratController extends Controller
{
    public function store(Request $request)
{
    
    $isDraft = $request->input('is_draft') == 0;

    
    $rules = [
        'judul_surat' => 'required|string|max:255',
        'lampiran' => 'nullable|file|mimes:pdf,jpeg,png,jpg,docx,xlsx|max:10240',
        'anggota' => 'array',
        'anggota.*' => 'exists:pengusul,id_pengusul',
    ];

    if ($isDraft) {
        $rules['id_pengusul'] = 'nullable|exists:pengusul,id_pengusul';
        $rules['jenis_surat'] = 'nullable|exists:jenis_surat,id_jenis_surat';
        $rules['deskripsi'] = 'nullable|string|max:300';
    } else {
        $rules['id_pengusul'] = 'required|exists:pengusul,id_pengusul';
        $rules['jenis_surat'] = 'required|exists:jenis_surat,id_jenis_surat';
        $rules['deskripsi'] = 'required|string|max:300';
    }

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
            'is_draft' => $isDraft ? 0 : 1,
            'id_jenis_surat' => $request->filled('jenis_surat') ? $request->jenis_surat : null,
            'deskripsi' => $request->filled('deskripsi') ? $request->deskripsi : null,
        ];

        $surat = Surat::create($dataSurat);

        
        $statusSuratId = $isDraft ? 3 : 2;
        RiwayatStatusSurat::create([
            'id_surat' => $surat->id_surat,
            'id_status_surat' => $statusSuratId,
            'tanggal_rilis' => now(),
        ]);

        
        if (!$isDraft) {
            PivotPengusulSurat::create([
                'id_surat' => $surat->id_surat,
                'id_pengusul' => $request->id_pengusul,
                'id_peran_keanggotaan' => 1,
            ]);

            if ($request->filled('anggota')) {
                foreach ($request->anggota as $anggotaId) {
                    PivotPengusulSurat::create([
                        'id_surat' => $surat->id_surat,
                        'id_pengusul' => $anggotaId,
                        'id_peran_keanggotaan' => 2,
                    ]);
                }
            }
        }

        DB::commit();

        // Redirect sesuai role user
        $user = auth('pengusul')->user();
        $redirectRoute = 'mahasiswa.pengajuansurat';
        if ($user->role === 'dosen') {
            $redirectRoute = 'dosen.pengajuansurat';
        }

        $message = $isDraft ? 'Surat berhasil disimpan sebagai draft.' : 'Surat berhasil diajukan.';
        return redirect()->route($redirectRoute)->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Gagal menyimpan surat: ' . $e->getMessage());
    }
}

    public function pengajuansearch(Request $request)
{
    $userId = auth('pengusul')->id();

    $query = Surat::with(['jenisSurat', 'dibuatOleh', 'pengusul'])
        ->where('is_draft', 1) // hanya surat yang diajukan
        ->where(function ($q) use ($userId) {
            $q->whereHas('pengusul', function ($q2) use ($userId) {
                $q2->where('pivot_pengusul_surat.id_pengusul', $userId);
            })
            ->orWhereHas('dibuatOleh', function ($q3) use ($userId) {
                $q3->where('id_pengusul', $userId);
            });
        });

    if ($search = $request->input('search.value')) {
        $query->where(function ($q) use ($search) {
            $q->where('judul_surat', 'like', "%{$search}%")
                ->orWhereHas('dibuatOleh', function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%");
                })
                ->orWhereHas('pengusul', function ($sub) use ($search) {
                    $sub->where('nama', 'like', "%{$search}%");
                });
        });
    }
  
    $query->orderBy('tanggal_pengajuan', 'desc')
      ->orderBy('id_surat', 'desc');

    $recordsTotal = $query->count();

    $suratList = $query->skip($request->start)
        ->take($request->length)
        ->get();

    $data = [];
    foreach ($suratList as $surat) {
        $anggota = $surat->pengusul->where('pivot.id_peran_keanggotaan', 2)->pluck('nama')->join(', ');
        $ketua = $surat->pengusul->firstWhere('pivot.id_peran_keanggotaan', 1)?->nama ?? '';
        $shortDescription = \Illuminate\Support\Str::limit(strip_tags($surat->deskripsi), 50, '...');

        $data[] = [
            'judul_surat' => $surat->judul_surat,
            'tanggal_pengajuan' => $surat->tanggal_pengajuan ?? '-',
            'jenis_surat' => '<div class="flex items-center gap-1 text-md">
                <span>' . e($surat->jenisSurat->jenis_surat ?? '-') . '</span>
             </div>',
            'dibuat_oleh' => $surat->dibuatOleh->nama ?? '-',
            'ketua' => $ketua,
            'anggota' => $anggota,
            'lampiran' => $surat->lampiran ?? null,
            'deskripsi' => $shortDescription,
            'id' => $surat->id_surat
        ];
    }
    
    return response()->json([
        'draw' => intval($request->draw),
        'recordsTotal' => $recordsTotal,
        'recordsFiltered' => $recordsTotal,
        'data' => $data
    ]);

}


    
    public function edit($id)
        {
            $surat = Surat::with(['pengusul'])->findOrFail($id);

            $jenisSurat = JenisSurat::pluck('jenis_surat', 'id_jenis_surat');

            $ketua = $surat->pengusul->where('pivot.id_peran_keanggotaan', 1); 
            $anggota = $surat->pengusul->where('pivot.id_peran_keanggotaan', 2);

            return view('pengusul.mahasiswa.edit', compact('surat', 'jenisSurat','ketua','anggota'));
        }

        public function update(Request $request, $id)
{

    $request->validate([
        'judul_surat' => 'required|string|max:255',
        'jenis_surat' => 'required|exists:jenis_surat,id_jenis_surat',
        'deskripsi' => 'nullable|string|max:300',
        'lampiran' => 'nullable|file|mimes:pdf,docx,xlsx,txt,jpeg,jpg,png|max:2048',
        'anggota' => 'nullable|array', 
        'ketua' => 'required|exists:pengusul,id_pengusul', 
    ]);

    Log::info($request->anggota, $request->ketua);
    $surat = Surat::findOrFail($id);

    $surat->judul_surat = $request->judul_surat;
    $surat->id_jenis_surat = $request->jenis_surat;
    $surat->deskripsi = $request->deskripsi;
    $surat->is_draft = 1; // Tetapkan status draft sebagai 1

    if ($request->lampiran_enabled == '1') {
        if ($request->hasFile('lampiran')) {
            if ($surat->lampiran && Storage::disk('public')->exists($surat->lampiran)) {
                Storage::disk('public')->delete($surat->lampiran);
            }

            // Simpan lampiran baru
            $lampiranPath = $request->file('lampiran')->store('lampiran', 'public');
            $surat->lampiran = $lampiranPath;
        }
    } else {
        
        if ($surat->lampiran && Storage::disk('public')->exists($surat->lampiran)) {
            Storage::disk('public')->delete($surat->lampiran);
        }
        $surat->lampiran = null;
    }

    $surat->save();

    DB::table('riwayat_status_surat')
        ->where('id_surat', $surat->id_surat)
        ->update([
            'id_status_surat' => 3 
        ]);

    PivotPengusulSurat::create([
        'id_surat' => $surat->id_surat,
        'id_pengusul' => $request->ketua, 
        'id_peran_keanggotaan' => 1, 
    ]);

    if ($request->filled('anggota')) {
        foreach ($request->anggota as $anggotaId) {
            PivotPengusulSurat::create([
                'id_surat' => $surat->id_surat,
                'id_pengusul' => $anggotaId,
                'id_peran_keanggotaan' => 2, 
            ]);
        }
    }

    return redirect()->route('mahasiswa.draft')->with('success', 'Surat berhasil diperbarui sebagai draft.');
}



    
    public function destroy($id)
    {
        DB::table('riwayat_status_surat')->where('id_surat', $id)->delete();
        Surat::findOrFail($id)->delete();
        return redirect()->route('mahasiswa.draft')->with('success', 'Surat berhasil dihapus');
    }


    public function show($id){
        $surat = Surat::with(['jenisSurat', 'dibuatOleh', 'pengusul'])->findOrFail($id);
        return view('pengusul.detail', compact('surat'));
    }

}
