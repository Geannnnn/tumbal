<form action="{{ route('lostpassword') }}" method="POST" class="hidden flex-col w-150" id="forgot-password-form">
    @csrf
    <h1 class="text-4xl font-bold">Lupa Kata Sandi</h1>
    <p class="mt-5 text-gray-500">Masukkan email Anda untuk menerima link reset password.</p>

    <div class="mt-5 relative mb-6">
        <label for="email" class="absolute -top-3 left-4 bg-none px-2 text-gray-600 font-medium">Email</label>
        <input type="email" id="email" name="email" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300" required>
    </div>

    <div class="flex items-center justify-center mt-2">
        <button type="submit" class="p-5 w-55 bg-blue-700 text-white cursor-pointer transition-transform duration-300 transform hover:scale-110 rounded-full">Kirim</button>
    </div>

    <div class="flex items-center justify-center">
        <button id="back-to-login-btn" type="button" class="bg-none text-blue-600 cursor-pointer mt-3">Kembali ke Halaman Login</button>
    </div>
</form>
