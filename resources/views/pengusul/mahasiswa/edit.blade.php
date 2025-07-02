@extends('layouts.app')

@section('title', 'Pengajuan Surat')

@section('content')

<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header')
        @include('components.alertnotif')
        <main class="flex-1 bg-white p-10">
            @yield('content')

            <div id="ajukanForm">
                <form id="formAjukan" action="{{ route('mahasiswa.surat.update', $surat->id_surat) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="flex justify-end">
                        <div class="flex gap-4">
                            <button type="button" 
                                id="ajukanFormHide"
                                onclick="showSweetAlertConfirmation(
                                    'Ya, Kembali', 
                                    'Tidak, Tetap di sini',  
                                    '{{ route('mahasiswa.draft') }}'  
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

                            <!-- Input Nama Pengaju (readonly, default hidden) -->
                            <div id="input-nama-pengaju" class="flex flex-col gap-2 hidden">
                                <span>Nama Pengaju</span>
                                <input type="text" name="nama_pengaju" class="bg-[#F0F2FF] py-2 px-4 rounded-lg outline-none" value="{{ $namaPengaju ?? ($surat->dibuatOleh->nama ?? '') }}" readonly>
                            </div>

                            <div id="input-ketua"><x-form.search-ketua :selected="$ketua" /></div>
                            <div id="input-anggota"><x-form.search-anggota :selected="$anggota" /></div>

                            <div class="flex flex-col gap-3">
                                <div class="mb-4 flex items-center">
                                    <label for="lampiranToggle" class="text-sm mr-2">Lampiran</label>
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" id="lampiranToggle" class="sr-only peer" {{ $surat->lampiran ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-green-500 peer-focus:ring-2 peer-focus:ring-blue-500 transition-all duration-300"></div>
                                        <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-5"></div>
                                    </label>
                                </div>

                                <div class="flex flex-col gap-2 w-full {{ $surat->lampiran ? '' : 'hidden' }}" id="fileInputWrapper">
                                    <span class="text-sm text-gray-600">*Upload file jika ada</span>
                                    <label for="real-file" class="w-full cursor-pointer bg-[#F0F2FF] text-[#6D727C] py-3 px-4 rounded-lg outline-none transition text-start hover:bg-[#e6e9f0]">
                                        <i class="fa-regular fa-file mr-2"></i>Upload File
                                    </label>
                                    <input type="hidden" name="lampiran_enabled" id="lampiran_enabled" value="{{ $surat->lampiran ? '1' : '0' }}">
                                    <input type="file" id="real-file" name="lampiran" class="hidden" accept="application/pdf, image/*, .docx, .xlsx, .txt">
                                    <span id="file-name" class="text-sm text-gray-500 mt-2">{{ $surat->lampiran ? basename($surat->lampiran) : 'Belum ada file' }}</span>
                                </div>
                            </div>
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
            </div>
        </main>
    </div>
</div>
<script>
    const realFile = document.getElementById("real-file");
    const fileName = document.getElementById("file-name");
    const toggle = document.getElementById("lampiranToggle");
    const wrapper = document.getElementById("fileInputWrapper");
    const lampiranEnabled = document.getElementById("lampiran_enabled");

    toggle.addEventListener("change", function () {
        if (this.checked) {
            wrapper.classList.remove("hidden");
            lampiranEnabled.value = "1";
        } else {
            wrapper.classList.add("hidden");
            lampiranEnabled.value = "0";
        }
    });

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
            'Surat Cuti Akademik',
            'Surat Izin Tidak Masuk'
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
        
        // Konfirmasi sebelum submit form
        const form = document.getElementById('formAjukan');
        form.addEventListener('submit', function(e) {
            const submitButton = e.submitter;
            if (submitButton && submitButton.name === 'is_draft' && submitButton.value === '1') {
                // Jika tombol Ajukan ditekan, tampilkan konfirmasi
                e.preventDefault();
                
                Swal.fire({
                    title: 'Ajukan Surat?',
                    text: 'Pastikan semua data sudah benar sebelum diajukan.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Ajukan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Set nilai is_draft = 1 dan submit form
                        const isDraftInput = document.createElement('input');
                        isDraftInput.type = 'hidden';
                        isDraftInput.name = 'is_draft';
                        isDraftInput.value = '1';
                        form.appendChild(isDraftInput);
                        form.submit();
                    }
                });
            }
        });
    });
                
</script>

@endsection
