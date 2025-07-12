@extends('layouts.app')

@section('title','Pengaturan')

@section('content')
<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        <main class="flex-1 bg-gray-50 p-6">
            <div class="max-w-4xl mx-auto space-y-8">
                <!-- Judul -->
                <div class="title-page flex justify-between mb-2">
                    <div class="flex justify-start">
                        <h1 class="text-[32px] text-[#1F384C] font-medium">Informasi Data Diri</h1>
                    </div>
                </div>
                <!-- Profil -->
                <div class="bg-white rounded-lg shadow-md p-6 flex flex-col md:flex-row items-center md:items-center gap-8">
                    <div class="flex-shrink-0 flex justify-center items-center w-1/3">
                        <img src="{{ asset('images/default-profile.png') }}" alt="Profile Image" class="w-32 h-32 rounded-full object-cover border-4 border-gray-200">
                    </div>
                    <div class="space-y-6 w-full md:w-2/3">
                        <div class="flex items-center gap-4">
                            <div class="flex items-center justify-center w-9 h-9 bg-gray-200 rounded-full text-xl font-bold text-black">N</div>
                            <div>
                                <p class="text-gray-600 text-xs">Nama Lengkap</p>
                                <p class="text-base font-semibold">
                                    @if(Auth::guard('pengusul')->check())
                                        {{ Auth::guard('pengusul')->user()->nama }}
                                    @elseif(Auth::guard('staff')->check())
                                        {{ Auth::guard('staff')->user()->nama }}
                                    @elseif (Auth::guard('admin')->check())
                                        {{ Auth::guard('admin')->user()->nama }}
                                    @elseif (Auth::guard('kepala_sub')->check())
                                        {{ Auth::guard('kepala_sub')->user()->nama }}
                                    @elseif (Auth::guard('direktur')->check())
                                        {{ Auth::guard('direktur')->user()->nama }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center justify-center w-9 h-9 bg-gray-200 rounded-full text-xl font-bold text-black">P</div>
                            <div>
                                <p class="text-gray-600 text-xs">Peran</p>
                                <p class="text-base font-semibold">
                                    @if(Auth::guard('pengusul')->check())
                                        @if(Auth::guard('pengusul')->user()->id_role_pengusul == 2)
                                            Mahasiswa
                                        @elseif(Auth::guard('pengusul')->user()->id_role_pengusul == 1)
                                            Dosen
                                        @endif
                                    @elseif(Auth::guard('staff')->check())
                                        {{ Auth::guard('staff')->user()->role }}
                                    @elseif(Auth::guard('kepala_sub')->check())
                                        Kepala Sub
                                    @elseif(Auth::guard('admin')->check())
                                        Admin 
                                    @elseif(Auth::guard('direktur')->check())
                                        Direktur
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center justify-center w-9 h-9 bg-gray-200 rounded-full">
                                <i class="fas fa-envelope text-xl text-black"></i>
                            </div>
                            <div>
                                <p class="text-gray-600 text-xs">Email</p>
                                <p class="text-base font-semibold">
                                    @if(Auth::guard('pengusul')->check())
                                        {{ Auth::guard('pengusul')->user()->email }}
                                    @elseif(Auth::guard('staff')->check())
                                        {{ Auth::guard('staff')->user()->email }}
                                    @elseif (Auth::guard('admin')->check())
                                        {{ Auth::guard('admin')->user()->email }}
                                    @elseif (Auth::guard('kepala_sub')->check())
                                        {{ Auth::guard('kepala_sub')->user()->email }}
                                    @elseif (Auth::guard('direktur')->check())
                                        {{ Auth::guard('direktur')->user()->email }}
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Ubah Kata Sandi -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex flex-col md:flex-row md:gap-10">
                        <div class="flex-1 mb-8 md:mb-0">
                            <h2 class="text-2xl font-bold mb-2">Ubah Kata Sandi</h2>
                            <p class="text-gray-600">Ubah kata sandi akun anda</p>
                        </div>
                        <form method="POST" action="{{ route('profile.update') }}" class="flex-1 space-y-5">
                            @csrf
                            <div>
                                <label class="block mb-1 text-sm font-semibold">Kata Sandi Saat Ini</label>
                                <input type="password" name="current_password" placeholder="Masukkan Kata Sandi Saat Ini ..." class="w-full p-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
                            </div>
                            <div>
                                <label class="block mb-1 text-sm font-semibold">Kata Sandi Baru</label>
                                <input type="password" name="new_password" placeholder="Masukkan Kata Sandi Baru" class="w-full p-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
                            </div>
                            <div>
                                <label class="block mb-1 text-sm font-semibold">Konfirmasi Kata Sandi Baru</label>
                                <input type="password" name="new_password_confirmation" placeholder="Masukkan kembali kata sandi baru ..." class="w-full p-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:outline-none" required>
                            </div>
                            <div class="flex space-x-4 pt-4">
                                <button type="reset" class="flex-1 p-2 border border-[#5A67BA] text-[#5A67BA] rounded-md transition text-sm hover:cursor-pointer hover:scale-105">Batal</button>
                                <button type="submit" class="flex-1 p-2 bg-[#5A67BA] text-white rounded-md hover:bg-blue-800 transition text-sm hover:cursor-pointer hover:scale-105">Ubah Kata Sandi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
@endsection