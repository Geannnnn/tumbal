<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/politeknik_negeri_batam.png') }}" type="image/x-icon">
    {{-- External CSS/JS --}}
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- App CSS --}}
    @vite('resources/css/app.css')
    
    @stack('styles')

    <title>@yield('title', 'Default Title')</title>
</head>
<body class="bg-gray-100" style="font-family: 'Poppins', sans-serif;">
    
    @yield('content')
    
    @include('components.alertnotif')
    @include('components.access-alert')


    @stack('scripts')

    @auth
    <!-- Script untuk mengecek akses secara berkala -->
    <script>
        // Daftar halaman yang tidak perlu dicek aksesnya
        const excludedPages = [
            '/access-revoked',
            '/access-restored'
        ];

        // Cek apakah halaman saat ini adalah halaman khusus
        const currentPath = window.location.pathname;
        const isExcludedPage = excludedPages.some(page => currentPath.includes(page));

        // Hanya jalankan pengecekan akses jika bukan halaman khusus
        if (!isExcludedPage) {
            // Fungsi untuk mengecek akses
            function checkUserAccess() {
                fetch('/check-access', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    // Hanya redirect jika user tidak punya akses
                    if (!data.has_access) {
                        // Akses dicabut, redirect ke halaman akses dicabut
                        window.location.href = '/access-revoked';
                    }
                })
                .catch(error => {
                    console.error('Error checking access:', error);
                    // Jika terjadi error, asumsikan akses dicabut
                    window.location.href = '/access-revoked';
                });
            }

            // Cek akses setiap 30 detik
            setInterval(checkUserAccess, 30000);

            // Cek akses saat halaman dimuat
            document.addEventListener('DOMContentLoaded', function() {
                checkUserAccess();
            });
        }
    </script>
    @endauth

    <script>
        // Simpan URL terakhir sebelum diarahkan ke access-revoked.html
        if (!window.location.pathname.endsWith('/access-revoked.html')) {
            localStorage.setItem('last_url_before_revoke', window.location.pathname + window.location.search + window.location.hash);
        }
    </script>
</body>
</html>