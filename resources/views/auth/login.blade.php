@extends('layouts.app')

@section('title', 'Halaman Login')

@section('content')
<x-logo></x-logo>
<div class="flex w-full justify-around items-center space-x-20">
    <!-- Form Login -->
    <x-alertnotif />
    <x-form.loginform />


    <!-- Form Lupa Password -->
    <x-form.lostpasswordform />

    <div class="col-6">
        <img src="{{ asset('images/loginpreview.png') }}" alt="Login Image" class="lg:max-w[400px] lg:h-[450px]   xl:max-w-[450px] xl:h-[500px]   2xl:max-w-[616px] 2xl:h-[810px] rounded-4xl">
    </div>
</div>

<script>
    const forgotPasswordBtn = document.getElementById('forgot-password-btn');
    const backToLoginBtn = document.getElementById('back-to-login-btn');
    const loginForm = document.querySelector('form[action="{{ route('login') }}"]');
    const forgotPasswordForm = document.getElementById('forgot-password-form');

    forgotPasswordBtn.addEventListener('click', function() {
        loginForm.classList.add('hidden');
        forgotPasswordForm.classList.remove('hidden');
    });

    backToLoginBtn.addEventListener('click', function() {
        forgotPasswordForm.classList.add('hidden');
        loginForm.classList.remove('hidden');
    });

    document.addEventListener("DOMContentLoaded", function () {
            @if(session('showForgotForm'))
                const loginForm = document.querySelector('form[action="{{ route('login') }}"]');
                const forgotPasswordForm = document.getElementById('forgot-password-form');
                if (loginForm && forgotPasswordForm) {
                    loginForm.classList.add('hidden');
                    forgotPasswordForm.classList.remove('hidden');
                }
            @endif
        });
</script>

@endsection
