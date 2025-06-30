<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hak Akses Dikembalikan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <!-- Icon Success -->
        <div class="mb-6">
            <div class="mx-auto w-20 h-20 bg-green-100 rounded-full flex items-center justify-center">
                <i class="fas fa-check-circle text-green-500 text-3xl"></i>
            </div>
        </div>

        <!-- Title -->
        <h1 class="text-2xl font-bold text-gray-800 mb-4">Hak Akses Dikembalikan!</h1>

        <!-- Message -->
        <div class="mb-6">
            <p class="text-gray-600 mb-4">
                Selamat! Hak akses Anda telah dikembalikan oleh administrator sistem.
            </p>
            <p class="text-sm text-gray-500">
                Anda sekarang dapat mengakses sistem kembali.
            </p>
        </div>

        <!-- Divider -->
        <hr class="my-6 border-gray-200">

        <!-- Info Box -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-green-500 mr-3"></i>
                <div class="text-left">
                    <p class="text-sm font-medium text-green-800">Informasi</p>
                    <p class="text-xs text-green-700 mt-1">
                        Anda akan otomatis diarahkan ke dashboard dalam beberapa detik.
                    </p>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="space-y-3">
            <button onclick="goToDashboard()" 
                    class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition duration-300 flex items-center justify-center">
                <i class="fas fa-tachometer-alt mr-2"></i>
                Masuk ke Dashboard
            </button>
            
            <a href="{{ route('login') }}" 
               class="block w-full bg-gray-600 text-white py-2 px-4 rounded-lg hover:bg-gray-700 transition duration-300 text-center">
                <i class="fas fa-sign-in-alt mr-2"></i>
                Login Ulang
            </a>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-500">
                Sistem Pengelolaan Surat Polibatam
            </p>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Fungsi untuk pergi ke dashboard
        function goToDashboard() {
            window.location.href = '/dashboard';
        }

        // Auto redirect ke dashboard setelah 5 detik
        setTimeout(function() {
            Swal.fire({
                icon: 'success',
                title: 'Mengarahkan ke Dashboard...',
                text: 'Anda akan diarahkan ke dashboard dalam beberapa detik.',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false
            }).then(() => {
                window.location.href = '/dashboard';
            });
        }, 2000);

        // Tampilkan notifikasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Hak Akses Dikembalikan!',
                text: 'Selamat! Anda dapat mengakses sistem kembali.',
                confirmButtonColor: '#3085d6'
            });
        });
    </script>
</body>
</html> 