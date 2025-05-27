<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'identity' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $identity = $request->identity;
        $password = $request->password;

        
        $pengusulNimCredentials = ['nim' => $identity, 'password' => $password];
        $pengusulNipCredentials = ['nip' => $identity, 'password' => $password];

        if (Auth::guard('pengusul')->attempt($pengusulNimCredentials) || Auth::guard('pengusul')->attempt($pengusulNipCredentials)) {
            $request->session()->regenerate();
            $pengusul = Auth::guard('pengusul')->user();

            switch ($pengusul->id_role_pengusul) {
                case 1:
                    return redirect('/dosen')->with('success', 'Berhasil login sebagai Dosen!');
                case 2:
                    return redirect('/mahasiswa')->with('success', 'Berhasil login sebagai Mahasiswa!');
                default:
                    return redirect('/')->with('error', 'Role tidak dikenal.');
            }
        }

        $credentials = [
            'nip' => trim( $request->identity),
            'password' => $request->password
        ];
        if (Auth::guard('staff')->attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::guard('staff')->user();

            if ($user->role === 'Staff Umum') {
                return redirect('/staff-umum')->with('success', 'Berhasil login sebagai Staff Umum!');
            } elseif ($user->role === 'Tata Usaha') {
                return redirect('/tata-usaha')->with('success', 'Berhasil login sebagai Tata Usaha!');
            } else {
                Auth::guard('staff')->logout();
                return back()->withErrors(['error', 'Role tidak dikenal.']);
            }
        }

        $adminCredentials = ['username' => $identity, 'password' => $password];
        if (Auth::guard('admin')->attempt($adminCredentials)) {
            $request->session()->regenerate();
            return redirect('/admin')->with('success', 'Berhasil login sebagai Admin!');
        }

        $kepalaSubCredentials = ['nip' => $identity, 'password' => $password];
        if (Auth::guard('kepala_sub')->attempt($kepalaSubCredentials)) {
            $request->session()->regenerate();
            return redirect('/kepala-sub')->with('success', 'Berhasil login sebagai Kepala Sub!');
        }

        // Log::info('Login gagal untuk identity: ' . $identity);
        return back()->withErrors(['identity' => 'NIP atau password salah.']);

        


    }
}

       
    