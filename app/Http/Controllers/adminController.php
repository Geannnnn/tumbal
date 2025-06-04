<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\Pengusul;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class adminController extends Controller
{
    public function index () {
        return view('admin.index');
    }

    public function kelolapengusul () {
        return view('admin.kelolapengusul');
    }

    public function pengusulData() {

        $data = Pengusul::with('role')->select('pengusul.*');

        return DataTables::of($data)
            ->addColumn('role', fn($row) => $row->role->role ?? '-')
            ->addColumn('nim',fn($row) => $row->nim ?? '-')
            ->addColumn('nip',fn($row) => $row->nip ?? '-')
            ->addColumn('id', fn($row) => $row->id_pengusul)
            ->make(true);
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

        JenisSurat::where('id_jenis_surat', $id)->update([
            'jenis_surat' => $request->jenis_surat,
        ]);

        return redirect()->back()->with('success', 'Jenis surat berhasil diperbarui.');
    }

    public function destroy($id)
    {
        JenisSurat::destroy($id);
        return redirect()->back()->with('success', 'Jenis surat berhasil dihapus.');
    }

}
