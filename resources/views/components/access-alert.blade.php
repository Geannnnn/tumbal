@if(session('access_restored'))
<div id="access-restored-alert" class="fixed top-4 right-4 z-50 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded shadow-lg">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <i class="fas fa-check-circle text-green-500"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium">
                Hak akses Anda telah dikembalikan!
            </p>
        </div>
        <div class="ml-auto pl-3">
            <div class="-mx-1.5 -my-1.5">
                <button onclick="closeAccessAlert()" class="text-green-500 hover:text-green-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function closeAccessAlert() {
        document.getElementById('access-restored-alert').style.display = 'none';
    }

    // Auto hide setelah 5 detik
    setTimeout(function() {
        const alert = document.getElementById('access-restored-alert');
        if (alert) {
            alert.style.display = 'none';
        }
    }, 5000);
</script>
@endif 