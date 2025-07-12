<div class="hidden" id="ajukanForm">
    <form action="{{ route('surat.store') }}" method="post" enctype="multipart/form-data">
        @csrf

        <div class="flex justify-end">
            <div class="flex gap-4">
                <button type="button" id="ajukanFormHide" class="hover:cursor-pointer px-5 py-2 bg-red-600 font-semibold rounded-2xl hover:scale-110 duration-300 flex items-center">
                    <i class="fa-solid fa-xmark pr-2"></i>Kembali
                </button>

                <button type="submit" name="is_draft" value="0" class="hover:cursor-pointer px-6 py-2 bg-[#C4CAF0] text-[#273240] font-semibold rounded-2xl hover:scale-110 duration-300 flex items-center">
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
                <input type="text" name="judul_surat" class="bg-[#F0F2FF] py-2 px-4 rounded-lg outline-none" id="" placeholder="Contoh Pengajuan Izin Cuti">
                <!-- Input Nama Pengaju (readonly, default hidden) -->
                <div id="input-nama-pengaju" class="flex flex-col gap-2 hidden">
                    <span>Nama Pengaju</span>
                    <input type="text" name="nama_pengaju" class="bg-[#F0F2FF] py-2 px-4 rounded-lg outline-none" value="{{ $namaPengaju ?? '' }}" readonly>
                </div>

                <div id="input-ketua"><x-form.search-ketua /></div>
                <div id="input-anggota"><x-form.search-anggota /></div>

                <div class="flex flex-col gap-3">
                    <div class="mb-4 flex items-center">
                        <label for="lampiranToggle" class="text-sm mr-2">Lampiran</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="lampiranToggle" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-green-500 peer-focus:ring-2 peer-focus:ring-blue-500 transition-all duration-300"></div>
                            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-5"></div>
                        </label>
                    </div>

                    <div class="flex flex-col gap-2 w-full hidden" id="fileInputWrapper">

                        <span class="text-sm text-gray-600">
                            *Upload file jika ada <br>
                            <span class="text-xs text-red-500">Hanya diperbolehkan 1 file PDF, maksimal ukuran 10 MB.</span>
                        </span>
                        <label for="real-file" class="w-full cursor-pointer bg-[#F0F2FF] text-[#6D727C] py-3 px-4 rounded-lg outline-none transition text-start hover:bg-[#e6e9f0]">
                            <i class="fa-regular fa-file mr-2"></i>Upload File
                        </label>
                        <input type="file" id="real-file" name="lampiran" class="hidden" accept="application/pdf">
                        <span id="file-name" class="text-sm text-gray-500 mt-2">Belum ada file</span>
                    </div>
                    
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
                    </script>
                </div>
            </div>

            <div class="flex flex-col w-2/5 gap-4">
                <span>Jenis Surat</span>
                <x-form.select
                    name="jenis_surat"
                    id="jenis_surat"
                    :options="$jenisSurat"
                    placeholder="Pilih Jenis Surat"
                />

                <!-- Input Tujuan Surat yang muncul secara dinamis -->
                <div id="tujuan-surat-container" class="hidden">
                    <span class="flex">Tujuan Surat <p class="text-[#6D727C] text-xs">\Max 100 huruf</p></span>
                    <textarea 
                        name="tujuan_surat" 
                        id="tujuan_surat"
                        class="w-full h-24 p-3 border border-gray-300 rounded-md bg-[#F0F2FF] outline-none" 
                        placeholder="Masukkan tujuan surat..."
                        maxlength="100"
                    ></textarea>
                </div>

                <script>
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

                <span class="flex">Deskripsi<p class="text-[#6D727C] text-xs">\Max 300 huruf</p></span>
                <textarea maxlength="300" name="deskripsi" class="w-full h-32 p-3 border border-gray-300 rounded-md bg-[#F0F2FF] outline-none" placeholder="Deskripsi Surat..."></textarea>
            </div>  
        </div>
    </form>
</div>
