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
    // Debug untuk melihat nilai is_draft yang diterima

    $isDraft = $request->input('is_draft') === '0' || $request->input('is_draft') === 0;

    $request->validate([
        'judul_surat' => 'required|string|max:255',
        'id_pengusul' => $isDraft ? 'nullable' : 'required|exists:pengusul,id_pengusul',
        'anggota' => 'array',
        'anggota.*' => 'exists:pengusul,id_pengusul',
        'jenis_surat' => $isDraft ? 'nullable' : 'required|exists:jenis_surat,id_jenis_surat',
        'deskripsi' => $isDraft ? 'nullable|string|max:300' : 'required|string|max:300',
        'lampiran' => 'nullable|file|mimes:pdf,jpeg,png,jpg,docx,xlsx|max:10240',
    ]);

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
            'is_draft' => $request->input('is_draft', 0), // Pastikan is_draft sesuai input
        ];

        $dataSurat['id_jenis_surat'] = $request->filled('jenis_surat') ? $request->jenis_surat : null;
        $dataSurat['deskripsi'] = $request->filled('deskripsi') ? $request->deskripsi : null;



        $surat = Surat::create($dataSurat);

        // Tentukan status berdasarkan is_draft
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
        return redirect()->route('mahasiswa.pengajuansurat')->with('success', $isDraft ? 'Surat berhasil disimpan sebagai draft.' : 'Surat berhasil diajukan.');
    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->route('mahasiswa.pengajuansurat')->with('error', 'Gagal mengajukan surat: ' . $e->getMessage());
    }
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
        // Hapus lampiran jika toggle dimatikan
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

    public function search(Request $request)
    {
        $query = $request->get('query'); // Ambil query pencarian

        // Query data berdasarkan pencarian
        $suratList = Surat::with('jenisSurat', 'dibuatOleh', 'pengusul')
            ->where('judul_surat', 'like', "%$query%")
            ->orWhere('deskripsi', 'like', "%$query%")
            ->orWhereHas('jenisSurat', function ($q) use ($query) {
                $q->where('jenis_surat', 'like', "%$query%");
            })
            ->orWhereHas('dibuatOleh', function ($q) use ($query) {
                $q->where('nama', 'like', "%$query%");
            })
            ->paginate(10);

        // Generate data tabel
        $columns = ['Judul', 'Tanggal Pengajuan', 'Jenis Surat', 'Diajukan Oleh', 'Diketuai Oleh', 'Anggota', 'Deskripsi'];
        $data = $this->generateTableData($suratList);

        return response()->json([
            'html' => view('pengusul.mahasiswa.pengajuansurat_table', compact('suratList', 'columns', 'data'))->render(),
            'pagination' => (string) $suratList->links(), // Mengirim pagination
        ]);
    }

    private function generateTableData($suratList)
    {
        // Menyiapkan data untuk ditampilkan di tabel
        $data = [];
        foreach ($suratList as $surat) {
            $data[] = [
                'id' => $surat->id_surat,
                'judul_surat' => $surat->judul_surat,
                'tanggal_pengajuan' => $surat->tanggal_pengajuan ?? '-',
                'jenis_surat' => $surat->jenisSurat->jenis_surat ?? '-',
                'dibuat_oleh' => $surat->dibuatOleh->nama ?? '-',
                // Tambahkan data lainnya jika perlu
            ];
        }

        return $data;
    }

}
