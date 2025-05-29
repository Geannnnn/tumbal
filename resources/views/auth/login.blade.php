@extends('layouts.app')

@include('components.alertnotif')

@section('title', 'Halaman Login')

@section('content')
<x-logo></x-logo>
<x-alertnotif />
<div class="flex w-full justify-around items-center space-x-20">
    <!-- Form Login -->
    
    <x-form.loginform />


    <!-- Form Lupa Password -->
    <x-form.lostpasswordform />

    <div class="col-6">
        <img src="{{ asset('images/loginpreview.png') }}" alt="Login Image" class="max-w-[616px] h-[810px] rounded-4xl">
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
