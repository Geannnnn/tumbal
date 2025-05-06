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
        return back()->withErrors(['identity' => 'NIP atau password salah.']);
    }
}

       
        // $staffCredentials = ['nip' => $identity, 'password' => $password];

        // if (Auth::guard('staff')->attempt($staffCredentials)) {
        //     Log::info('Login staff berhasil', ['nip' => $identity]);
        //     $request->session()->regenerate();
        //     $staff = Auth::guard('staff')->user();
        //     Log::info('Staff yang login:', ['staff' => $staff]);
        //     switch ($staff->role) {
        //         case 'Tata Usaha':
        //             return redirect('/staff/tata-usaha')->with('success', 'Berhasil login sebagai Tata Usaha!');
        //         case 'Staff Umum':
        //             return redirect('/staff/staff-umum')->with('success', 'Berhasil login sebagai Staff Umum!');
        //         default:
        //             return redirect('/')->with('info', 'Login berhasil, tapi role staff tidak dikenali.');
        //     }
        // }

    //     return back()->with('error', 'NIM / NIP atau password salah.');
    // }




  // $pengusul = Auth::guard('pengusul')->user();

    //     dd($pengusul);
    // Login sebagai Pengusul
    // if (Auth::guard('pengusul')->attempt($credentials)) {
    //     $request->session()->regenerate();
    //     $pengusul = Auth::guard('pengusul')->user();
    //     Log::info('Pengusul login berhasil', ['nim' => $pengusul->nim]);
    //     // Arahkan sesuai dengan role
    //     switch ($pengusul->id_role_pengusul) {
    //         case 1:
    //             return redirect('/pengusul/dosen')->with('success', 'Berhasil login sebagai Dosen!');
    //         case 2:
    //             return redirect('/pengusul/mahasiswa')->with('success', 'Berhasil login sebagai Mahasiswa!');
    //         default:
    //             return redirect('/')->with('error', 'Role tidak dikenal.');
    //     }
    // }

    // // Login sebagai Staff
    // if (Auth::guard('staff')->attempt($credentials)) {
    //     $request->session()->regenerate();
    //     $staff = Auth::guard('staff')->user();

    //     switch ($staff->role) {
    //         case 'Tata Usaha':
    //             return redirect('/staff/tata-usaha')->with('success', 'Berhasil login sebagai Tata Usaha!');
    //         case 'Staff Umum':
    //             return redirect('/staff/staff-umum')->with('success', 'Berhasil login sebagai Staff Umum!');
    //         default:
    //             return redirect('/')->with('info', 'Login berhasil, tapi role staff tidak dikenali.');
    //     }
    // }


    //===========================//

    // if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {
    //     $credentials = [
    //         'email' => $identity,
    //         'password' => $request->password,
    //     ];
    // } else {
    //     $credentials = [
    //         'nim' => $identity,
    //         'password' => $request->password,
    //     ];
    // }


    // if (Auth::guard('pengusul')->attempt($credentials)) {
    //     // dd('Pengusul berhasil login!');
    //     $request->session()->regenerate();
    //     $pengusul = Auth::guard('pengusul')->user();
    //     Log::info('Pengusul login berhasil', ['nim' => $pengusul->nim]);
    //     switch ($pengusul->id_role_pengusul) {
    //         case 1:
    //             return redirect('/dosen')->with('success', 'Berhasil login sebagai Dosen!');
    //         case 2:
    //             return redirect()->intended('/mahasiswa')->with('success', 'Berhasil login sebagai Mahasiswa!');
    //             // dd('Berhasil login sebagai Mahasiswa!');
    //         default:
    //             return redirect('/')->with('error', 'Role tidak dikenal.');
    //     }
    // } else {
    //     // dd('Pengusul login gagal!');
    //     Log::info('Login sebagai Pengusul gagal', ['credentials' => $credentials]);
    // }
    
    // if (Auth::guard('staff')->attempt($credentials)) {
    //     $request->session()->regenerate();
    //     $staff = Auth::guard('staff')->user();
    //     Log::info('Staff login berhasil', ['role' => $staff->role]);
    //     switch ($staff->role) {
    //         case 'Tata Usaha':
    //             return redirect('/staff/tata-usaha')->with('success', 'Berhasil login sebagai Tata Usaha!');
    //         case 'Staff Umum':
    //             return redirect('/staff/staff-umum')->with('success', 'Berhasil login sebagai Staff Umum!');
    //         default:
    //             return redirect('/')->with('info', 'Login berhasil, tapi role staff tidak dikenali.');
    //     }
    // } else {
    //     Log::info('Login sebagai Staff gagal', ['credentials' => $credentials]);
    // }