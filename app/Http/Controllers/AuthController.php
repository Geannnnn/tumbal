<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;


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

        return back()->withErrors(['identity' => 'NIP atau password salah.']);
    }

    public function lostpassword(Request $request){
       
        $email = $request->email;

        $found = DB::table('pengusul')->where('email', $email)->exists() ||
                DB::table('admin')->where('email', $email)->exists() ||
                DB::table('staff')->where('email', $email)->exists() ||
                DB::table('kepala_sub')->where('email', $email)->exists();

        if (!$found) {
            return redirect()->route('login.form')
                            ->with('showForgotForm', true)
                            ->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $resetLink = route('password.reset', [
            'token' => $token,
            'email' => $email,
        ]);

        Mail::to($email)->send(new ResetPasswordMail($resetLink));

        return redirect()->route('login')
                    ->with('showForgotForm', true)
                    ->with('success', 'Link reset password sudah dikirim ke email Anda.');
    }

    public function showResetForm(Request $request){
        $token = $request->query('token');
        $email = $request->query('email');

        return view('auth.lostpassword', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    public function updatePassword(Request $request)
{
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|string|min:8|confirmed',
    ]);

    $status = Password::broker('pengusuls')->reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($pengusul, $password) {
            $pengusul->forceFill([
                'password' => Hash::make($password),
            ])->save();

           
            event(new PasswordReset($pengusul));
        }
    );

    return $status === Password::PASSWORD_RESET
        ? redirect()->route('login')->with('success', __($status))
        : back()->withErrors(['email' => [__($status)]]);
}

}

       
    