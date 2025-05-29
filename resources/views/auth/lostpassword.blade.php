@extends('layouts.app')

@include('components.alertnotif')

@section('title', 'Atur Ulang Kata Sandi')

@section('content')
<x-logo></x-logo>
<x-alertnotif />
<div class="flex w-full justify-around items-center space-x-20">

    <form action="{{ route('password.update') }}" method="POST" class="flex-col w-150">
    @csrf
    <h1 class="text-4xl font-bold">Lupa Kata Sandi</h1>
    <p class="mt-5 text-gray-500">Masukkan kata sandi baru yang akan ditetapkan untuk akun anda.</p>

    <div class="mt-5 relative mb-6">
        <label for="password" class="absolute -top-3 left-4 bg-none px-2 text-gray-600 font-medium">Kata Sandi Baru</label>
            <input type="password" id="password" name="password" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300">
        <span class="eye-icon absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 cursor-pointer">
            <i class="fa-solid fa-eye"></i>
        </span>
    </div>

    <div class="mt-5 relative mb-6">
        <label for="password_confirm" class="absolute -top-3 left-4 bg-none px-2 text-gray-600 font-medium">Ulang Kata Sandi Baru</label>
            <input type="password" id="password_confirm" name="password_confirmation" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300">
        <span class="eye-icon absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 cursor-pointer">
            <i class="fa-solid fa-eye"></i>
        </span>
    </div>

    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ $email }}">

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".eye-icon").forEach(function (icon) {
                const input = icon.previousElementSibling;

                icon.addEventListener("click", function () {
                    const isPassword = input.type === "password";
                    input.type = isPassword ? "text" : "password";
                    icon.innerHTML = isPassword
                        ? '<i class="fa-solid fa-eye-slash"></i>'
                        : '<i class="fa-solid fa-eye"></i>';
                });
            });
        });
    </script>

    <div class="flex items-center justify-center mt-2">
        <button class="p-5 w-55 bg-blue-700 text-white cursor-pointer transition-transform duration-300 transform hover:scale-110 rounded-full">Terapkan Kata Sandi</button>
    </div>

</form>

    <div class="col-6">
        <img src="{{ asset('images/loginpreview.png') }}" alt="Login Image" class="max-w-[616px] h-[810px] rounded-4xl">
    </div>
</div>

@endsection
