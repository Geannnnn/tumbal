@props([
    'action',
    'surat',
    'jenisSurat',
    'ketua' => null,
    'anggota' => null,
    'namaPengaju' => null,
    'routeDraft',
])

<form id="formAjukan" action="{{ $action }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="flex justify-end">
        <div class="flex gap-4">
            <button type="button" 
                id="ajukanFormHide"
                onclick="showSweetAlertConfirmation(
                    'Ya, Kembali', 
                    'Tidak, Tetap di sini',  
                    '{{ $routeDraft }}'  
                )"
                class="hover:cursor-pointer px-5 py-2 bg-red-600 font-semibold rounded-2xl hover:scale-110 duration-300 flex items-center"
            >
                <i class="fa-solid fa-xmark pr-2"></i>Kembali
            </button>

            <button type="submit" name="is_draft" value="0" class="hover:cursor-not-allowed px-6 py-2 bg-gray-400 text-gray-600 font-semibold rounded-2xl duration-300 flex items-center opacity-50 cursor-not-allowed" disabled>
                <i class="fa-solid fa-floppy-disk pr-2"></i>Simpan Sebagai Draft
            </button>
            
            <button type="submit" name="is_draft" value="1" class="hover:cursor-pointer px-6 py-2 bg-[#C4CAF0] text-[#273240] font-semibold rounded-2xl hover:scale-110 duration-300 flex items-center">
                <i class="fa-solid fa-paper-plane pr-2"></i>Ajukan
            </button>

            <x-alertnotif />
        </div>
    </div>

    <div class="pt-8">
        <h1 class="font-semibold text-[28px]">Pengajuan Surat</h1>
        <h1 class="text-[#6D727C] font-medium text-[24px] py-4">List Pengajuan Surat Politeknik Negeri Batam</h1>
    </div>
    <hr class="border-[#DEDBDB]">

    <div class="flex justify-between">
        <div class="flex flex-col w-2/5 gap-4">
            <span>Judul Surat</span>
            <input type="text" name="judul_surat" class="bg-[#F0F2FF] py-2 px-4 rounded-lg outline-none" value="{{ old('judul_surat', $surat->judul_surat) }}">

            {{-- Nama Pengaju --}}
            <div id="input-nama-pengaju" class="flex flex-col gap-2 hidden">
                <span>Nama Pengaju</span>
                <input type="text" name="nama_pengaju" class="bg-[#F0F2FF] py-2 px-4 rounded-lg outline-none" value="{{ $namaPengaju ?? ($surat->dibuatOleh->nama ?? '') }}" readonly>
            </div>

            <div id="input-ketua"><x-form.search-ketua :selected="$ketua" /></div>
            <div id="input-anggota"><x-form.search-anggota :selected="$anggota" /></div>

            <x-form.upload-lampiran :lampiran="$surat->lampiran" />
        </div>

        <div class="flex flex-col w-2/5 gap-4">
            <span>Jenis Surat</span>
            <x-form.select
                name="jenis_surat"
                id="jenis_surat"
                :options="$jenisSurat"
                :selected="old('jenis_surat', $surat->id_jenis_surat)"
                placeholder="Pilih Jenis Surat"
            />

            <div id="tujuan-surat-container" class="{{ in_array($surat->jenisSurat->jenis_surat ?? '', ['Surat Tugas', 'Surat Undangan Kegiatan', 'Surat Permohonan', 'Surat Pengantar']) ? '' : 'hidden' }}">
                <span>Tujuan Surat</span>
                <textarea 
                    name="tujuan_surat" 
                    id="tujuan_surat"
                    class="w-full h-24 p-3 border border-gray-300 rounded-md bg-[#F0F2FF] outline-none" 
                    placeholder="Masukkan tujuan surat..."
                    maxlength="500"
                >{{ old('tujuan_surat', $surat->tujuan_surat) }}</textarea>
                <p class="text-xs text-gray-500 mt-1">*Maksimal 500 karakter</p>
            </div>

            <span class="flex">Deskripsi<p class="text-[#6D727C]">\Max 300 huruf</p></span>
            <textarea maxlength="300" name="deskripsi" class="w-full h-32 p-3 border border-gray-300 rounded-md bg-[#F0F2FF] outline-none" placeholder="Deskripsi Surat...">{{ old('deskripsi', $surat->deskripsi) }}</textarea>
        </div>
    </div>
</form>

@push('scripts')
<script>
    const toggle = document.getElementById("lampiranToggle");
    const fileInputWrapper = document.getElementById("fileInputWrapper");

    toggle.addEventListener("change", function () {
        fileInputWrapper.classList.toggle("hidden", !this.checked);
    });

    const realFile = document.getElementById("real-file");
    const fileName = document.getElementById("file-name");

    realFile.addEventListener("change", function () {
        fileName.textContent = this.files.length > 0 ? this.files[0].name : "Belum ada file";
    });


    document.addEventListener('DOMContentLoaded', function() {
        const jenisSuratSelect = document.getElementById('jenis_surat');
        const tujuanSuratContainer = document.getElementById('tujuan-surat-container');
        const inputNamaPengaju = document.getElementById('input-nama-pengaju');
        const inputKetua = document.getElementById('input-ketua');
        const inputAnggota = document.getElementById('input-anggota');

        // Daftar jenis surat personal
        const jenisSuratPersonal = [
            'Surat Cuti Akademik', // mahasiswa
            'Surat Izin Tidak Masuk' // dosen
        ];
        // Daftar jenis surat yang memerlukan tujuan surat
        const jenisSuratDenganTujuan = [
            'Surat Tugas',
            'Surat Undangan Kegiatan',
            'Surat Permohonan',
            'Surat Pengantar'
        ];

        function toggleInputs() {
            const selectedOption = jenisSuratSelect.options[jenisSuratSelect.selectedIndex];
            const selectedText = selectedOption ? selectedOption.text : '';
            // Toggle input personal
            if (jenisSuratPersonal.includes(selectedText)) {
                inputNamaPengaju.classList.remove('hidden');
                inputKetua.classList.add('hidden');
                inputAnggota.classList.add('hidden');
            } else {
                inputNamaPengaju.classList.add('hidden');
                inputKetua.classList.remove('hidden');
                inputAnggota.classList.remove('hidden');
            }
            // Toggle tujuan surat
            if (jenisSuratDenganTujuan.includes(selectedText)) {
                tujuanSuratContainer.classList.remove('hidden');
            } else {
                tujuanSuratContainer.classList.add('hidden');
                document.getElementById('tujuan_surat').value = '';
            }
        }
        jenisSuratSelect.addEventListener('change', toggleInputs);
        toggleInputs(); // jalankan saat load
    });
</script>
@endpush

<script>
    
</script>
