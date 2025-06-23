@extends('layouts.app')

@section('title','Pengaturan')

@section('content')
<div class="flex h-screen">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        
        <main class="flex-1 bg-white p-6">
            <div class="max-w-6xl mx-auto p-8 space-y-10">

        <!-- Profil -->
        <div class="rounded-3xl p-8 flex flex-col md:flex-row items-center md:items-center space-y-6 md:space-y-0 md:space-x-10" style="background-color: #F0F2FF;">
        <div class="flex-shrink-0 flex justify-center items-center w-1/3">
            <img src="{{ asset('images/default-profile.png') }}" alt="Profile Image" class="w-40 h-40 rounded-full object-cover">
        </div>
        <div class="space-y-6 w-2/3">
            <div class="flex items-center space-x-4">
                <div class="flex items-center justify-center w-9 h-9 bg-gray-300 rounded-full text-xl font-bold">N</div>
                <div>
                    <p class="text-gray-600 text-xs">Nama Lengkap</p>
                    <p class="text-sm font-semibold">
                        @if(Auth::guard('pengusul')->check())
                            {{ Auth::guard('pengusul')->user()->nama }}
                        @elseif(Auth::guard('staff')->check())
                            {{ Auth::guard('staff')->user()->nama }}
                        @elseif (Auth::guard('admin')->check())
                            {{ Auth::guard('admin')->user()->nama }}
                        @elseif (Auth::guard('kepala_sub')->check())
                            {{ Auth::guard('kepala_sub')->user()->nama }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center justify-center w-9 h-9 bg-gray-300 rounded-full text-xl font-bold">P</div>
                <div>
                    <p class="text-gray-600 text-xs">Peran</p>
                    <p class="text-sm font-semibold">
                        @if(Auth::guard('pengusul')->check())
                            {{-- Ambil role dari pengusul, 1 = Dosen, 2 = Mahasiswa --}}
                            @if(Auth::guard('pengusul')->user()->id_role_pengusul == 2)
                                Mahasiswa
                            @elseif(Auth::guard('pengusul')->user()->id_role_pengusul == 1)
                                Dosen
                            @endif
                        @elseif(Auth::guard('staff')->check())
                            {{-- Ambil role dari staff --}}
                            {{ Auth::guard('staff')->user()->role }}
                        @elseif(Auth::guard('kepala_sub')->check())
                            Kepala Sub
                        @elseif(Auth::guard('admin')->check())
                            Admin 
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center justify-center w-9 h-9 bg-gray-300 rounded-full">
                    <i class="fas fa-phone text-xl"></i>
                </div>
                <div>
                    <p class="text-gray-600 text-xs">Email</p>
                    <p class="text-sm font-semibold">
                        @if(Auth::guard('pengusul')->check())
                            {{ Auth::guard('pengusul')->user()->email }}
                        @elseif(Auth::guard('staff')->check())
                            {{ Auth::guard('staff')->user()->email }}
                        @elseif (Auth::guard('admin')->check())
                            {{ Auth::guard('admin')->user()->email }}
                        @elseif (Auth::guard('kepala_sub')->check())
                            {{ Auth::guard('kepala_sub')->user()->email }}
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

        <!-- Lupa Kata Sandi -->
        <div class="rounded-3xl p-8" style="background-color: #F0F2FF;">
            <div class="flex flex-col md:flex-row md:space-x-10">
            
            <div class="flex-1 mb-8 md:mb-0">
                <h2 class="text-2xl font-bold mb-2">Ubah Kata Sandi</h2>
                <p class="text-gray-600">ubah kata sandi akun anda</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="flex-1 space-y-5">
                @csrf

                <div>
                    <label class="block mb-1 text-sm font-semibold">Kata Sandi Saat Ini</label>
                    <input type="password" name="current_password" placeholder="Masukkan Kata Sandi Saat Ini ..." class="w-full p-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-semibold">Kata Sandi Baru</label>
                    <input type="password" name="new_password" placeholder="Masukkan Kata Sandi Baru" class="w-full p-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
                </div>

                <div>
                    <label class="block mb-1 text-sm font-semibold">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" name="new_password_confirmation" placeholder="Masukkan kembali kata sandi baru ..." class="w-full p-2 text-xs border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
                </div>

                <div class="flex space-x-4 pt-4">
                    <button type="reset" class="flex-1 p-2 border border-[#5A67BA] text-[#5A67BA] rounded-sm transition text-xs hover:cursor-pointer hover:scale-105">Batal</button>
                    <button type="submit" class="flex-1 p-2 bg-[#5A67BA] text-white rounded-sm hover:bg-blue-800 transition text-xs hover:cursor-pointer hover:scale-105">Ubah Kata Sandi</button>
                </div>
            </form>

            </div>
        </div>

        </div>

        </main>
</div>
</div>