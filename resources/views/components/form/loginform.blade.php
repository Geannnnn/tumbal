<form action="{{ route('login') }}" method="POST" class="flex-col w-150">
    @csrf
    <h1 class="text-4xl font-bold">Masuk</h1>
    <p class="mt-5 text-gray-500">Login untuk mengakses akun Anda.</p>

    <div class="mt-5 relative mb-6">
    <label for="username" class="absolute -top-2 left-3 px-1 text-sm text-gray-600 font-medium bg-gray-100 z-10">Username</label>
    <input type="text" id="username" name="identity" 
        class="w-full p-3 pt-5 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300">
</div>

    <div class="mt-5 relative mb-6">
        <label for="password" class="absolute -top-3 left-4 bg-none px-2 text-gray-600 bg-gray-100 font-medium">Password</label>
        <input type="password" id="password" name="password" class="w-full p-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-300">
        <span id="eye-icon" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 cursor-pointer">
            <i class="fa-solid fa-eye"></i>
        </span>
    </div>

    <div class="flex items-center space-x-2">
        <div id="checkbox" class="w-6 h-6 border-2 border-gray-300 rounded-sm bg-white flex items-center justify-center cursor-pointer transition-all duration-300">
            <i id="checkmark" class="hidden"></i>
        </div>
        <span class="text-gray-700 font-medium">Ingatkan Saya.</span>
    </div>

    <script>
        const eyeIcon = document.getElementById("eye-icon");
        const passwordInput = document.getElementById('password');

        eyeIcon.addEventListener("click", () => {
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                eyeIcon.innerHTML = '<i class="fa-solid fa-eye-slash"></i>';
            } else {
                passwordInput.type = "password";
                eyeIcon.innerHTML = '<i class="fa-solid fa-eye"></i>';
            }
        });

        const checkbox = document.getElementById("checkbox");
        const checkmark = document.getElementById("checkmark");

        checkbox.addEventListener("click", function() {
            if (checkmark.classList.contains("hidden")) {
                checkmark.classList.remove("hidden");
                checkmark.classList.add("text-[#7B76F1]", "fa-solid", "fa-check");
                checkbox.classList.add("bg-purple-900");
            } else {
                checkmark.classList.add("hidden");
                checkmark.classList.remove("text-[#7B76F1]", "fa-solid", "fa-check");
            }
        });
    </script>

    <div class="flex items-center justify-center mt-2">
        <button class="p-5 w-55 bg-blue-700 text-white cursor-pointer transition-transform duration-300 transform hover:scale-110 rounded-full">Masuk</button>
    </div>

    <div class="flex items-center justify-center">
        <button id="forgot-password-btn" type="button" class="bg-none text-red-600 cursor-pointer mt-3">Lupa Kata Sandi</button>
    </div>
</form>

