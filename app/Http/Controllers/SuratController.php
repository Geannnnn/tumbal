<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\PivotPengusulSurat;
use App\Models\RiwayatStatusSurat;
use App\Models\StatusSurat;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Helpers\PengusulHelper;
use Illuminate\Support\Str;

class SuratController extends Controller
{
    public function store(Request $request)
    {
        $user = auth('pengusul')->user();
        $role = $user->role; // 'mahasiswa' atau 'dosen'
        $isDraft = $request->input('is_draft') == 0; // 0 = draft, 1 = diajukan

        // Deteksi jenis surat personal
        $jenisSuratId = $request->jenis_surat;
        $jenisSurat = $jenisSuratId ? JenisSurat::find($jenisSuratId) : null;
        $personalSurat = $jenisSurat && in_array($jenisSurat->jenis_surat, ['Surat Cuti Akademik', 'Surat Izin Tidak Masuk']);

        $rules = [
            'judul_surat' => 'required|string|max:255',
            'lampiran' => 'nullable|file|mimes:pdf|max:10240',
            'tujuan_surat' => 'nullable|string|max:500',
        ];
        if (!$personalSurat) {
            $rules['anggota'] = 'array';
            $rules['anggota.*'] = 'exists:pengusul,id_pengusul';
        }
        if ($isDraft) {
            $rules['id_pengusul'] = $personalSurat ? 'nullable' : 'nullable|exists:pengusul,id_pengusul';
            $rules['jenis_surat'] = 'nullable|exists:jenis_surat,id_jenis_surat';
            $rules['deskripsi'] = 'nullable|string|max:300';
        } else {
            $rules['id_pengusul'] = $personalSurat ? 'nullable' : 'required|exists:pengusul,id_pengusul';
            $rules['jenis_surat'] = 'required|exists:jenis_surat,id_jenis_surat';
            $rules['deskripsi'] = 'required|string|max:300';
        }
        $request->validate($rules,[
            'lampiran.mimes'=> 'Format file yang diterima hanya PDF',
        ]);

        DB::beginTransaction();
        try {
            $lampiranPath = null;
            if ($request->hasFile('lampiran')) {
                $file = $request->file('lampiran');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $timestamp = now()->format('Ymd_His');
                $fileName = Str::slug($originalName) . '_' . $timestamp . '.' . $extension;
            
                $file->storeAs('lampiran', $fileName, 'public');
            
                $lampiranPath = 'lampiran/' . $fileName;
            }

            $dataSurat = [
                'judul_surat' => $request->judul_surat,
                'tanggal_pengajuan' => now('Asia/Jakarta'),
                'dibuat_oleh' => $user->id_pengusul,
                'lampiran' => $lampiranPath,
                'is_draft' => $isDraft ? 0 : 1, // 0 = draft, 1 = diajukan
                'id_jenis_surat' => $request->filled('jenis_surat') ? $request->jenis_surat : null,
                'deskripsi' => $request->filled('deskripsi') ? $request->deskripsi : null,
                'tujuan_surat' => $request->filled('tujuan_surat') ? $request->tujuan_surat : null,
            ];

            $surat = Surat::create($dataSurat);

            if (!$isDraft) {
                RiwayatStatusSurat::create([
                    'id_surat' => $surat->id_surat,
                    'id_status_surat' => 2, 
                    'tanggal_rilis' => now('Asia/Jakarta'),
                    'keterangan' => 'Diajukan oleh Pengusul',
                    'diubah_oleh' => $user->id_pengusul,
                    'diubah_oleh_tipe' => 'pengusul',
                ]);
            }

            // Simpan ketua/anggota hanya jika bukan personal
            if (!$personalSurat) {
                if ($request->filled('id_pengusul')) {
                    PivotPengusulSurat::create([
                        'id_surat' => $surat->id_surat,
                        'id_pengusul' => $request->id_pengusul,
                        'id_peran_keanggotaan' => 1,
                    ]);
                }
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

            $roleName = is_array($role) ? ($role['role'] ?? null) : (is_object($role) ? $role->role : $role);
            if ($isDraft) {
                $redirectRoute = $roleName === 'Dosen' ? 'dosen.draft' : 'mahasiswa.draft';
                $message = 'Surat berhasil disimpan sebagai draft.';
            } else {
                $redirectRoute = $roleName === 'Dosen' ? 'dosen.pengajuansurat' : 'mahasiswa.pengajuansurat';
                $message = 'Surat berhasil diajukan.';
            }
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
            ->where('is_draft', 1) // hanya surat yang diajukan (is_draft = 1)
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
        foreach ($suratList as $no => $surat) {
            $anggota = $surat->pengusul->where('pivot.id_peran_keanggotaan', 2)->pluck('nama')->join(', ');
            $ketua = $surat->pengusul->firstWhere('pivot.id_peran_keanggotaan', 1)?->nama ?? '';
            $shortDescription = Str::limit(strip_tags($surat->deskripsi), 50, '...');

            $data[] = [
                'no' => $no,
                'judul_surat' => $surat->judul_surat,
                'tanggal_pengajuan' => $surat->tanggal_pengajuan ?? '-',
                'jenis_surat' => '<div class="flex items-center gap-1 text-md">
                    <span>' . e($surat->jenisSurat->jenis_surat ?? '-') . '</span>
                 </div>',
                'dibuat_oleh' => $surat->dibuatOleh ? $surat->dibuatOleh->nama : '-',
                'ketua' => $ketua ? $ketua : '-',
                'anggota' => $anggota ? $anggota : '-',
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
        $user = auth('pengusul')->user();
        $role = $user->role;
        $roleName = is_array($role) ? ($role['role'] ?? null) : (is_object($role) ? $role->role : $role);
        
        $surat = Surat::with(['pengusul' => function($query) {
            $query->select('pengusul.id_pengusul', 'pengusul.nama', 'pengusul.nim', 'pengusul.nip', 'pivot_pengusul_surat.id_peran_keanggotaan');
        }])->findOrFail($id);
        
        $namaPengaju = $surat->dibuatOleh->nama ?? null;

        // Filter jenis surat berdasarkan role
        $jenisSuratQuery = collect(DB::select('CALL sp_GetJenisSuratForSelect()'));
        
        if ($roleName === 'Mahasiswa') {
            // Mahasiswa hanya bisa melihat Surat Permohonan, Surat Pengantar, Surat Cuti Akademik
            $jenisSurat = $jenisSuratQuery->filter(function($item) {
                return in_array($item->jenis_surat, ['Surat Permohonan', 'Surat Pengantar', 'Surat Cuti Akademik']);
            })->pluck('jenis_surat', 'id_jenis_surat');
        } else {
            // Dosen hanya bisa melihat Surat Tugas, Surat Undangan Kegiatan, Surat Izin Tidak Masuk
            $jenisSurat = $jenisSuratQuery->filter(function($item) {
                return in_array($item->jenis_surat, ['Surat Tugas', 'Surat Undangan Kegiatan', 'Surat Izin Tidak Masuk']);
            })->pluck('jenis_surat', 'id_jenis_surat');
        }

        // Ambil data ketua dan anggota yang sudah tersimpan
        $ketua = $surat->pengusul->where('pivot.id_peran_keanggotaan', 1)->first();
        $anggota = $surat->pengusul->where('pivot.id_peran_keanggotaan', 2);

        // Siapkan data untuk view
        $data = [
            'surat' => $surat,
            'jenisSurat' => $jenisSurat,
            'ketua' => $ketua,
            'anggota' => $anggota,
            'role' => $role,
            'namaPengaju' => $namaPengaju,

            'action' => $roleName === 'Dosen'
            ? route('dosen.surat.update', $surat->id_surat)
            : route('mahasiswa.surat.update', $surat->id_surat),

            'routeDraft' => $roleName === 'Dosen'
            ? route('dosen.draft')
            : route('mahasiswa.draft'),
        ];

        // Tentukan view berdasarkan role
        $view = $roleName === 'Dosen' ? 'pengusul.dosen.edit' : 'pengusul.mahasiswa.edit';
        
        return view($view, $data);
    }

    public function update(Request $request, $id)
    {
        $user = auth('pengusul')->user();
        $role = $user->role;
        $isDraft = $request->input('is_draft') == 0; // 0 = draft, 1 = diajukan

        // Deteksi jenis surat personal
        $jenisSuratId = $request->jenis_surat;
        $jenisSurat = $jenisSuratId ? JenisSurat::find($jenisSuratId) : null;
        $personalSurat = $jenisSurat && in_array($jenisSurat->jenis_surat, ['Surat Cuti Akademik', 'Surat Izin Tidak Masuk']);

        $rules = [
            'judul_surat' => 'required|string|max:255',
            'lampiran' => 'nullable|file|mimes:pdf|max:10240',
            'tujuan_surat' => 'nullable|string|max:500',
        ];
        if (!$personalSurat) {
            $rules['anggota'] = 'array';
            $rules['anggota.*'] = 'exists:pengusul,id_pengusul';
        }
        if ($isDraft) {
            $rules['id_pengusul'] = $personalSurat ? 'nullable' : 'nullable|exists:pengusul,id_pengusul';
            $rules['jenis_surat'] = 'nullable|exists:jenis_surat,id_jenis_surat';
            $rules['deskripsi'] = 'nullable|string|max:300';
        } else {
            $rules['id_pengusul'] = $personalSurat ? 'nullable' : 'required|exists:pengusul,id_pengusul';
            $rules['jenis_surat'] = 'required|exists:jenis_surat,id_jenis_surat';
            $rules['deskripsi'] = 'required|string|max:300';
        }
        $request->validate($rules,[
            'lampiran.mimes'=> 'Format file yang diterima hanya PDF',
        ]);

        DB::beginTransaction();
        try {
            $surat = Surat::findOrFail($id);

            // Update data surat
            $surat->judul_surat = $request->judul_surat;
            $surat->id_jenis_surat = $request->filled('jenis_surat') ? $request->jenis_surat : null;
            $surat->deskripsi = $request->filled('deskripsi') ? $request->deskripsi : null;
            $surat->tujuan_surat = $request->filled('tujuan_surat') ? $request->tujuan_surat : null;
            $surat->is_draft = $isDraft ? 0 : 1; // 0 = draft, 1 = diajukan
            
            // Update tanggal pengajuan jika surat diubah dari draft menjadi diajukan
            if (!$isDraft && $surat->getOriginal('is_draft') == 0) {
                $surat->tanggal_pengajuan = now('Asia/Jakarta');
            }

            // Menangani lampiran
            if ($request->hasFile('lampiran')) {
                if ($surat->lampiran && Storage::disk('public')->exists($surat->lampiran)) {
                    Storage::disk('public')->delete($surat->lampiran);
                }
                $lampiranPath = $request->file('lampiran')->store('lampiran', 'public');
                $surat->lampiran = $lampiranPath;
            }

            $surat->save();

            if (!$isDraft) {
                RiwayatStatusSurat::create([
                    'id_surat' => $surat->id_surat,
                    'id_status_surat' => 2, 
                    'tanggal_rilis' => now('Asia/Jakarta'),
                    'keterangan' => 'Diajukan oleh Pengusul',
                    'diubah_oleh' => $user->id_pengusul,
                    'diubah_oleh_tipe' => 'pengusul',
                ]);
            }

            // Update riwayat status surat
            if (!$isDraft) { // Jika surat diajukan (is_draft = 1)
                $lastRiwayat = RiwayatStatusSurat::where('id_surat', $surat->id_surat)
                    ->orderBy('tanggal_rilis', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
                $statusDraft = StatusSurat::where('status_surat', 'Draft')->first();
                $statusDiajukan = StatusSurat::where('status_surat', 'Diajukan')->first();

                // Tambahkan status Diajukan HANYA jika status terakhir adalah Draft
                if ($lastRiwayat && $statusDraft && $statusDiajukan && $lastRiwayat->id_status_surat == $statusDraft->id_status_surat) {
                    RiwayatStatusSurat::create([
                        'id_surat' => $surat->id_surat,
                        'id_status_surat' => $statusDiajukan->id_status_surat,
                        'tanggal_rilis' => now('Asia/Jakarta'),
                        'diubah_oleh' => $user->id_pengusul,
                        'diubah_oleh_tipe' => 'pengusul',
                    ]);
                }
                // Jika status terakhir sudah Diajukan, Divalidasi, Menunggu Persetujuan, Diterbitkan, Ditolak, TIDAK menambah status apapun
            }

            DB::table('pivot_pengusul_surat')
                ->where('id_surat', $surat->id_surat)
                ->delete();

            // Simpan ketua/anggota hanya jika bukan personal
            if (!$personalSurat) {
                if ($request->filled('id_pengusul')) {
                    PivotPengusulSurat::create([
                        'id_surat' => $surat->id_surat,
                        'id_pengusul' => $request->id_pengusul,
                        'id_peran_keanggotaan' => 1,
                    ]);
                }
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

            $roleName = is_array($role) ? ($role['role'] ?? null) : (is_object($role) ? $role->role : $role);
            if ($isDraft) {
                $redirectRoute = $roleName === 'Dosen' ? 'dosen.draft' : 'mahasiswa.draft';
                $message = 'Surat berhasil disimpan sebagai draft.';
            } else {
                $redirectRoute = $roleName === 'Dosen' ? 'dosen.pengajuansurat' : 'mahasiswa.pengajuansurat';
                $message = 'Surat berhasil diajukan.';
            }
            return redirect()->route($redirectRoute)->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui surat: ' . $e->getMessage());
        }
    }

    public function ajukanUlang($id)
    {
        $user = auth('pengusul')->user();
        $role = is_object($user->role) ? $user->role->role : $user->role;

        $surat = Surat::with(['pengusul', 'jenisSurat', 'dibuatOleh'])->findOrFail($id);
        $jenisSurat = collect(DB::select('CALL sp_GetJenisSuratForSelect()'))->pluck('jenis_surat', 'id_jenis_surat');

        $ketua = $surat->pengusul->where('pivot.id_peran_keanggotaan', 1)->first();
        $anggota = $surat->pengusul->where('pivot.id_peran_keanggotaan', 2);

        $data = [
            'surat' => $surat,
            'jenisSurat' => $jenisSurat,
            'ketua' => $ketua,
            'anggota' => $anggota,
            'namaPengaju' => $surat->dibuatOleh->nama ?? null,
            'isMahasiswa' => $role === 'Mahasiswa',
            'routeDraft' => $role === 'Dosen' ? route('dosen.draft') : route('mahasiswa.draft'),
            'action' => route('pengusul.surat.update', $surat->id_surat),
        ];

        return view('pengusul.surat.ajukan-ulang', $data);
    }

    public function destroy($id)
    {
        $user = auth('pengusul')->user();
        $role = $user->role;
        DB::beginTransaction();
        try {
            DB::table('pivot_pengusul_surat')->where('id_surat', $id)->delete();
            
            DB::table('riwayat_status_surat')->where('id_surat', $id)->delete();
            
            $surat = Surat::findOrFail($id);
            
            if ($surat->lampiran && Storage::disk('public')->exists($surat->lampiran)) {
                Storage::disk('public')->delete($surat->lampiran);
            }
            
            $surat->delete();
            
            DB::commit();
            $roleName = is_array($role) ? ($role['role'] ?? null) : (is_object($role) ? $role->role : $role);
            $redirectRoute = $roleName === 'Dosen' ? 'dosen.draft' : 'mahasiswa.draft';
            return redirect()->route($redirectRoute)->with('success', 'Surat berhasil dihapus');
        } catch (\Exception $e) {
            DB::rollBack();
            $roleName = is_array($role) ? ($role['role'] ?? null) : (is_object($role) ? $role->role : $role);
            $redirectRoute = $roleName === 'Dosen' ? 'dosen.draft' : 'mahasiswa.draft';
            return redirect()->route($redirectRoute)->with('error', 'Gagal menghapus surat: ' . $e->getMessage());
        }
    }
}
