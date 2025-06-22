<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\Pengusul;
use App\Models\StatusSurat;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Surat;

class adminController extends Controller
{
    public function index () {

        $suratDiterima = Surat::where('is_draft',0)->whereHas('riwayatStatus',function($q){
            $q->where('id_status_surat',1);
        })->count();

        $suratDitolak = Surat::where('is_draft',0)->whereHas('riwayatStatus',function($q){
            $q->where('id_status_surat',2);
        })->count();

        $notifikasiSurat = collect();

        $columns = [
            'nomor_surat' => "Nomor Surat",
            'judul_surat' =>'Nama Surat', 
            'tanggal_surat_dibuat' => 'Tanggal Terbit', 
            'lampiran' => 'Dokumen', 
            'tanggal_pengajuan' => 'Dibuat Pada'];

        return view('admin.index',compact('columns','notifikasiSurat', 'suratDiterima', 'suratDitolak'));
    }

    public function kelolapengusul () {
        return view('admin.kelolapengusul');
    }

    public function pengusulData(Request $request)
    {
        try {
            $query = Pengusul::with('role');

            // Total records
            $totalRecords = $query->count();

            // Search
            if ($request->has('search') && !empty($request->search['value'])) {
                $searchValue = $request->search['value'];
                $query->where(function($q) use ($searchValue) {
                    $q->where('nama', 'like', "%{$searchValue}%")
                      ->orWhere('email', 'like', "%{$searchValue}%")
                      ->orWhere('nim', 'like', "%{$searchValue}%")
                      ->orWhere('nip', 'like', "%{$searchValue}%")
                      ->orWhereHas('role', function($q) use ($searchValue) {
                          $q->where('role', 'like', "%{$searchValue}%");
                      });
                });
            }

            // Total filtered records
            $totalFiltered = $query->count();

            // Ordering
            if ($request->has('order')) {
                $orderColumn = $request->order[0]['column'];
                $orderDir = $request->order[0]['dir'];
                $columns = ['nama', 'email', 'nim', 'nip', 'role'];

                if (isset($columns[$orderColumn - 1])) { // -1 because we have a 'No' column
                    $column = $columns[$orderColumn - 1];
                    if ($column === 'role') {
                        $query->join('role_pengusul', 'pengusul.id_role_pengusul', '=', 'role_pengusul.id_role_pengusul')
                              ->orderBy('role_pengusul.role', $orderDir);
                    } else {
                        $query->orderBy($column, $orderDir);
                    }
                }
            }

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 10);
            $query->skip($start)->take($length);

            $data = $query->get()->map(function ($pengusul) {
                return [
                    'id' => $pengusul->id_pengusul,
                    'nama' => $pengusul->nama ?? '-',
                    'email' => $pengusul->email ?? '-',
                    'nim' => $pengusul->nim ?? '-',
                    'nip' => $pengusul->nip ?? '-',
                    'role' => $pengusul->role->role ?? '-',
                    'id_role_pengusul' => $pengusul->id_role_pengusul
                ];
            });

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::error('Error in pengusulData: ' . $e->getMessage());
            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function jenissurat () {
        $jsdata = JenisSurat::all();
        return view('admin.jenissurat', compact('jsdata'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_surat' => 'required|string|max:255',
        ]);

        JenisSurat::create([
            'jenis_surat' => $request->jenis_surat,
        ]);

        return redirect()->back()->with('success', 'Jenis surat berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'jenis_surat' => 'required|string|max:255',
        ]);

        try {
            JenisSurat::where('id_jenis_surat', $id)->update([
                'jenis_surat' => $request->jenis_surat,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Jenis surat berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui jenis surat: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        JenisSurat::destroy($id);
        return redirect()->back()->with('success', 'Jenis surat berhasil dihapus.');
    }

    public function create()
    {
        return view('admin.pengusul.create');
    }

    public function edit($id)
    {
        $pengusul = Pengusul::findOrFail($id);
        return view('admin.pengusul.edit', compact('pengusul'));
    }

    public function storePengusul(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|max:255',
                'email' => 'required|email|unique:pengusul,email',
                'password' => 'required|min:6',
                'id_role_pengusul' => 'required|exists:role_pengusul,id_role_pengusul',
                'nim' => 'nullable|string|max:20',
                'nip' => 'nullable|string|max:20'
            ], [
                'nama.required' => 'Nama harus diisi',
                'email.required' => 'Email harus diisi',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah terdaftar',
                'password.required' => 'Kata sandi harus diisi',
                'password.min' => 'Kata sandi minimal 6 karakter',
                'id_role_pengusul.required' => 'Peran harus dipilih',
                'id_role_pengusul.exists' => 'Peran tidak valid',
                'nim.max' => 'NIM maksimal 20 karakter',
                'nip.max' => 'NIP maksimal 20 karakter'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $pengusul = Pengusul::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'id_role_pengusul' => $request->id_role_pengusul,
                'nim' => $request->nim,
                'nip' => $request->nip
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengusul berhasil ditambahkan',
                'data' => $pengusul
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updatePengusul(Request $request, $id)
    {
        try {
            $pengusul = Pengusul::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|max:255',
                'email' => 'required|email|unique:pengusul,email,' . $id . ',id_pengusul',
                'password' => 'nullable|min:6',
                'id_role_pengusul' => 'required|exists:role_pengusul,id_role_pengusul',
                'nim' => 'nullable|string|max:20',
                'nip' => 'nullable|string|max:20'
            ], [
                'nama.required' => 'Nama harus diisi',
                'email.required' => 'Email harus diisi',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah terdaftar',
                'password.min' => 'Kata sandi minimal 6 karakter',
                'id_role_pengusul.required' => 'Peran harus dipilih',
                'id_role_pengusul.exists' => 'Peran tidak valid',
                'nim.max' => 'NIM maksimal 20 karakter',
                'nip.max' => 'NIP maksimal 20 karakter'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = [
                'nama' => $request->nama,
                'email' => $request->email,
                'id_role_pengusul' => $request->id_role_pengusul,
                'nim' => $request->nim,
                'nip' => $request->nip
            ];

            if ($request->filled('password')) {
                $data['password'] = bcrypt($request->password);
            }

            $pengusul->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Pengusul berhasil diperbarui',
                'data' => $pengusul
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPengusul($id)
    {
        $pengusul = Pengusul::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $pengusul
        ]);
    }


    public function kelolaStatusSurat()
    {
        $statusSurats = StatusSurat::all();
        return view('admin.statussurat', compact('statusSurats'));
    }

    public function destroyPengusul($id)
    {
        try {
            $pengusul = Pengusul::findOrFail($id);
            $pengusul->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pengusul berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeStatusSurat(Request $request)
    {
        $request->validate([
            'status_surat' => 'required|string|max:255|unique:status_surat,status_surat',
        ]);

        StatusSurat::create($request->all());

        return redirect()->route('admin.kelolastatussurat')->with('success', 'Status surat berhasil ditambahkan.');
    }

    public function updateStatusSurat(Request $request, $id)
    {
        $request->validate([
            'status_surat' => 'required|string|max:255|unique:status_surat,status_surat,' . $id . ',id_status_surat',
        ]);

        $status = StatusSurat::findOrFail($id);
        $status->update($request->all());

        return redirect()->route('admin.kelolastatussurat')->with('success', 'Status surat berhasil diperbarui.');
    }

    public function destroyStatusSurat($id)
    {
        $status = StatusSurat::findOrFail($id);
        // Di masa mendatang, kita bisa menambahkan pengecekan apakah status sedang digunakan
        $status->delete();
        return redirect()->route('admin.kelolastatussurat')->with('success', 'Status surat berhasil dihapus.');
    }

}
