<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Mail\ResetPasswordMail;
use App\Models\Admin;
use App\Models\KepalaSub;
use App\Models\Pengusul;
use App\Models\Staff;
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

        $direkturCredentials = ['nip' => $identity, 'password' => $password];
        if (Auth::guard('direktur')->attempt($direkturCredentials)) {
            $request->session()->regenerate();
            return redirect('/direktur')->with('success', 'Berhasil login sebagai Direktur!');
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

    public function updatePassword(Request $request){
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = $request->email;

        if (DB::table('pengusul')->where('email', $email)->exists()) {
            $broker = 'pengusuls';
            $modelClass = Pengusul::class;
        } elseif (DB::table('admin')->where('email', $email)->exists()) {
            $broker = 'admins';
            $modelClass = Admin::class;
        } elseif (DB::table('kepala_sub')->where('email', $email)->exists()) {
            $broker = 'kepala_subs';
            $modelClass = KepalaSub::class;
        } elseif (DB::table('staff')->where('email', $email)->exists()) {
            $broker = 'staffs';
            $modelClass = Staff::class;
        } else {
            return back()->withErrors(['email' => 'Email tidak ditemukan.']);
        }

        $status = Password::broker($broker)->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($modelClass) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function profileUpdatePassword(Request $request){
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'min:8', 'confirmed'],
        ]);

         $guards = ['pengusul', 'staff', 'admin', 'kepala_sub'];
        /** @var \Illuminate\Database\Eloquent\Model|null $user */
        $user = null;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                $user = Auth::guard($guard)->user();
                break;
            }
        }

        if (!$user) {
            return back()->withErrors(['auth' => 'Tidak ada pengguna yang login.']);
        }

        // Cek apakah current_password cocok
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Kata sandi saat ini salah.']);
        }

        // Update password baru
        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }

}

       
    