
    <!-- Header -->
    <div class="flex flex-col bg-white shadow-md">
        <div class="flex items-center justify-between p-4">
            <!-- (Kosongkan kiri kalau mau biar rata) -->
            <div class="flex">
                <img src="{{ asset('images/Politeknik_Negeri_Batam.png') }}" alt="Polibatam Image" class="max-w-[122px] max-h-[50px] object-contain" width="122" height="50">
                <h1 class="flex flex-col text-[20px] font-semibold text-[#002E8B]">Polibatam
                    <span class="inline-block text-semibol text-[13px]">Aplikasi Pengelolaan Surat di Polibatam</span>
                </h1>
            </div>

            <div class="flex items-center space-x-6 relative">
                <!-- Tombol lonceng -->
                <button id="Notification" class="text-gray-600 text-xl hover:text-blue-500 relative z-10">
                    <i class="fas fa-bell hover:cursor-pointer"></i>
                </button>
            
                <!-- Notifikasi -->
                <div id="notifShow" class="absolute top-full right-0 mt-2 z-50 w-[300px] rounded-[15px] bg-[#F0EDF2] p-4 invisible opacity-0 scale-95 pointer-events-none transition-all duration-200 ease-out">
                    <div class="flex justify-center pt-2">
                        <h1 class="font-semibold text-2xl">Notifikasi</h1>
                    </div>
                    <hr class="my-2">
                    <div class="flex p-2 gap-3">
                        <div class="w-9 h-9 bg-black rounded-full relative">
                            <i class="absolute top-2.5 left-2.5 fa-solid fa-envelope text-white"></i>
                        </div>
                        <div class="flex flex-col">
                            <h1 class="font-semibold text-base">Surat Tugas</h1>
                            <h1 class="text-sm text-gray-700">Surat Nomor ###</h1>
                            <span class="text-[13px] font-extralight">0 menit yang lalu</span>
                        </div>
                    </div>
                </div>

                {{-- Nama & Role --}}
                <div class="">
                    <h1 class="">
                        @if(Auth::guard('pengusul')->check())
                            {{ Auth::guard('pengusul')->user()->nama }}
                        @elseif(Auth::guard('staff')->check())
                            {{ Auth::guard('staff')->user()->nama }}
                        @endif
                    </h1>  <!-- Ambil nama dari user --> 
                    <div class="flex justify-end">
                        <span class="font-regular text-[11px] text-[#1F384C] justify-end">
                            @if(Auth::guard('pengusul')->check())
                                {{-- Ambil role dari pengusul, 1 = Dosen, 2 = Mahasiswa --}}
                                @if(Auth::guard('pengusul')->user()->id_role_pengusul == 2)
                                    Mahasiswa
                                @elseif(Auth::guard('pengusul')->user()->id_role_pengusul == 1)
                                    Dosen
                                @endif
                            @elseif(Auth::guard('staff')->check())
                                {{-- Ambil role dari staff --}}
                                {{ Auth::guard('staff')->user()->role }}
                            @endif
                        </span>
                    </div>
                </div>
            
                <!-- Foto Profil -->
                <img src="{{ asset('images/default-profile.png') }}" alt="profile" class="w-13 h-13 rounded-full">
            </div>
            
            <script>
                const bellBtn = document.getElementById("Notification");
                const notifBox = document.getElementById("notifShow");
            
                bellBtn.addEventListener("click", function (e) {
                    e.stopPropagation();
                    const isHidden = notifBox.classList.contains("invisible");

                    if (isHidden) {
                        notifBox.classList.remove("invisible", "opacity-0", "scale-95", "pointer-events-none");
                        notifBox.classList.add("opacity-100", "scale-100");
                    } else {
                        notifBox.classList.add("invisible", "opacity-0", "scale-95", "pointer-events-none");
                        notifBox.classList.remove("opacity-100", "scale-100");
                    }
                });

                document.addEventListener("click", function (e) {
                    if (!notifBox.contains(e.target) && e.target !== bellBtn) {
                        notifBox.classList.add("invisible", "opacity-0", "scale-95", "pointer-events-none");
                        notifBox.classList.remove("opacity-100", "scale-100");
                    }
                });
            </script>
            
            
        </div>
        <!-- Garis bawah header -->
        <hr class="w-full border-t-[0.5px] border-gray-300">
    </div>

