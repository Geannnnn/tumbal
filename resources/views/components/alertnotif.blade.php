@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Sukses!',
            text: '{{ session('success') }}',
            confirmButtonColor: '#3085d6'
        });
    </script>
@endif

@if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('error') }}',
            confirmButtonColor: '#d33'
        });
    </script>
@endif

@if(session('info'))
    <script>
        Swal.fire({
            icon: 'info',
            title: 'Info',
            text: '{{ session('info') }}',
            confirmButtonColor: '#3498db'
        });
    </script>
@endif

@if(session('warning'))
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Peringatan',
            text: '{{ session('warning') }}',
            confirmButtonColor: '#f39c12'
        });
    </script>
@endif

@if($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ $errors->first() }}',
            confirmButtonColor: '#d33'
        });
    </script>
@endif

{{-- Toast Notifikasi Surat --}}
@if(
    request()->routeIs('dosen.dashboard') ||
    request()->routeIs('mahasiswa.dashboard') ||
    request()->routeIs('admin.dashboard') ||
    request()->routeIs('kepalasub.dashboard') ||
    request()->routeIs('tatausaha.dashboard') ||
    request()->routeIs('staffumum.dashboard')
)
    @if(isset($notifikasiSurat))
        @php
            $adaDiterima = $notifikasiSurat->contains(fn($s) => $s->statusTerakhir?->statusSurat?->status_surat === 'Diterima');
            $adaDitolak = $notifikasiSurat->contains(fn($s) => $s->statusTerakhir?->statusSurat?->status_surat === 'Ditolak');
        @endphp

        @if($adaDiterima)
            <div x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 4000)"
                x-show="show"
                x-transition
                class="fixed top-4 right-4 bg-green-600 text-white px-4 py-3 rounded-lg shadow-lg z-50">
                <strong class="font-bold">Notifikasi Baru</strong>
                <span class="block sm:inline">Ada surat yang telah <b>diterima</b>.</span>
            </div>
        @endif

        @if($adaDitolak)
            <div x-data="{ show: true }"
                x-init="setTimeout(() => show = false, 4000)"
                x-show="show"
                x-transition
                class="fixed top-20 right-4 bg-red-600 text-white px-4 py-3 rounded-lg shadow-lg z-50">
                <strong class="font-bold">Notifikasi Baru</strong>
                <span class="block sm:inline">Ada surat yang telah <b>ditolak</b>.</span>
            </div>
        @endif
    @endif
@endif


<script>
    function showConfirmationAlert({ formId, event, title = 'Konfirmasi', text = 'Apakah Anda yakin?', confirmText = 'Ya', cancelText = 'Batal', icon = 'warning' }) {
        event.preventDefault();

        Swal.fire({
            title: title,
            text: text,
            icon: icon,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: confirmText,
            cancelButtonText: cancelText
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }

    function showSweetAlertConfirmation(confirmText, cancelText, confirmationUrl) {
        Swal.fire({
            icon: 'warning',
            title: 'Apakah Anda yakin?',
            text: 'Perubahan yang belum disimpan akan hilang!',
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            cancelButtonColor: '#d33',
            confirmButtonText: confirmText || 'Ya, Kembali',
            cancelButtonText: cancelText || 'Tidak, Tetap di sini'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = confirmationUrl;
            }
        });
    }
</script>
