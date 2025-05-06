{{-- <button onclick="showSuccess()">SweetAlert Success</button>
            <button onclick="showConfirm()">SweetAlert Confirm Delete</button>

<script>
function showSuccess() {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Data berhasil disimpan',
    });
}

function showConfirm() {
    Swal.fire({
        title: 'Yakin ingin menghapus?',
        text: 'Data tidak bisa dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Terhapus!', 'Data sudah dihapus.', 'success');
            
        }
    });
}
</script> --}}

{{-- Key --}}
{{-- return redirect()->back()->with('success', 'Data berhasil ditambahkan.');
return redirect()->back()->with('error', 'Gagal menambahkan data.');
return redirect()->back()->with('info', 'Ini hanya informasi biasa.'); --}}


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