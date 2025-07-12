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
                <!-- Tombol lonceng dengan badge minimalis -->
                <button id="Notification" class="text-gray-600 text-xl hover:text-custom-blue relative z-10 focus:outline-none">
                    <i class="fas fa-bell hover:cursor-pointer"></i>
                    @php
                        $unreadCount = $notifikasiSurat->where('read_at', null)->count();
                    @endphp
                    @if($unreadCount > 0)
                        <span class="absolute -top-1 -right-1 bg-red-600 text-white cursor-pointer text-[10px] font-bold rounded-full px-1.5 py-0.5 border-2 border-white shadow">{{ $unreadCount }}</span>
                    @endif
                </button>
            
                <!-- Notifikasi -->
                <div id="notifShow" class="absolute top-full right-0 mt-2 z-50 w-[350px] rounded-2xl bg-white/80 backdrop-blur-md border border-[#1090CB]/30 shadow-xl p-0 invisible opacity-0 scale-95 pointer-events-none transition-all duration-200 font-poppins overflow-hidden">
                    <div class="flex items-center justify-between px-6 pt-4 pb-2">
                        <h1 class="font-semibold text-lg text-[#002E8B] tracking-tight">Notifikasi</h1>
                        <i class="fas fa-envelope-open-text text-custom-blue text-lg"></i>
                    </div>
                    <hr class="mx-6 border-t border-[#1090CB]/10">
                    <div class="max-h-96 overflow-y-auto py-2 px-1 scrollbar-thin scrollbar-thumb-[#F1F2F7] scrollbar-track-white">
                        @forelse($notifikasiSurat as $notif)
                            <a href="{{ route(Auth::guard('pengusul')->check() && Auth::guard('pengusul')->user()->id_role_pengusul == 2 ? 'mahasiswa.statussurat.show' : 'dosen.statussurat.show', $notif->data['id_surat']) }}"
                               class="flex items-center gap-3 px-6 py-3 group transition cursor-pointer notif-item {{ is_null($notif->read_at) ? 'bg-[#E6F0FA] font-semibold' : 'hover:bg-[#1090CB]/10' }}"
                               data-id="{{ $notif->id }}">
                                <div class="w-9 h-9 flex items-center justify-center rounded-full {{ is_null($notif->read_at) ? 'bg-[#1090CB]/80' : 'bg-[#1090CB]/10' }} group-hover:bg-[#1090CB]/20 transition">
                                    <i class="fa-solid fa-envelope {{ is_null($notif->read_at) ? 'text-white' : 'text-custom-blue' }} text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-sm {{ is_null($notif->read_at) ? 'text-[#002E8B]' : 'text-custom-blue' }} group-hover:text-custom-blue truncate">Surat Masuk Baru</span>
                                        <span class="text-xs text-gray-400 ml-auto whitespace-nowrap">{{ \Carbon\Carbon::parse($notif->created_at)->diffForHumans() }}</span>
                                    </div>
                                    <div class="text-[13px] {{ is_null($notif->read_at) ? 'text-[#002E8B]' : 'text-gray-700' }} group-hover:text-[#002E8B] truncate">{!! $notif->data['pesan'] ?? '-' !!}</div>
                                </div>
                            </a>
                        @empty
                            <p class="text-sm text-gray-400 text-center mt-6">Belum ada notifikasi surat.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Nama & Role --}}
                <div class="">
                    <h1 class="">
                        @if(Auth::guard('pengusul')->check())
                            {{ Auth::guard('pengusul')->user()->nama }}
                        @elseif(Auth::guard('staff')->check())
                            {{ Auth::guard('staff')->user()->nama }}
                        @elseif (Auth::guard('admin')->check())
                            {{ Auth::guard('admin')->user()->nama }}
                        @elseif (Auth::guard('kepala_sub')->check())
                            {{ Auth::guard('kepala_sub')->user()->nama }}
                        @elseif (Auth::guard('direktur')->check())
                        {{ Auth::guard('direktur')->user()->nama }}
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
                            @elseif(Auth::guard('kepala_sub')->check())
                                Kepala Sub
                            @elseif(Auth::guard('admin')->check())
                                Admin 
                            @elseif(Auth::guard('direktur')->check())
                                Direktur
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
                let notifTimeout;

                bellBtn.addEventListener("click", function (e) {
                    e.stopPropagation();
                    const isHidden = notifBox.classList.contains("invisible");

                    if (isHidden) {
                        notifBox.classList.remove("invisible", "opacity-0", "scale-95", "pointer-events-none");
                        notifBox.classList.add("opacity-100", "scale-100");
                        notifBox.style.transform = "translateY(-20px)";
                        setTimeout(() => {
                            notifBox.style.transform = "translateY(0)";
                        }, 10);
                    } else {
                        notifBox.classList.add("invisible", "opacity-0", "scale-95", "pointer-events-none");
                        notifBox.classList.remove("opacity-100", "scale-100");
                        notifBox.style.transform = "";
                    }
                });

                document.addEventListener("click", function (e) {
                    if (!notifBox.contains(e.target) && e.target !== bellBtn) {
                        notifBox.classList.add("invisible", "opacity-0", "scale-95", "pointer-events-none");
                        notifBox.classList.remove("opacity-100", "scale-100");
                        notifBox.style.transform = "";
                    }
                });

                // AJAX mark as read
                const notifLinks = document.querySelectorAll('.notif-item');
                const badgeElement = document.querySelector('#Notification span');
                let unreadCount = {{ $notifikasiSurat->where('read_at', null)->count() }};
                
                notifLinks.forEach(link => {
                    link.addEventListener('click', function(e) {
                        const notifId = this.getAttribute('data-id');
                        const isUnread = this.classList.contains('bg-[#E6F0FA]');
                        
                        fetch(`/notifikasi/mark-read/${notifId}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json',
                            },
                        });
                        
                        // Update badge count if notification was unread
                        if (isUnread) {
                            unreadCount--;
                            if (unreadCount <= 0) {
                                if (badgeElement) {
                                    badgeElement.remove();
                                }
                            } else {
                                if (badgeElement) {
                                    badgeElement.textContent = unreadCount;
                                }
                            }
                        }
                        
                        // (Optional) Ubah style langsung tanpa reload
                        this.classList.remove('bg-[#E6F0FA]', 'font-semibold');
                        this.classList.add('hover:bg-[#1090CB]/10');
                        this.querySelector('div').classList.remove('bg-[#1090CB]/80');
                        this.querySelector('div').classList.add('bg-[#1090CB]/10');
                        this.querySelector('i').classList.remove('text-white');
                        this.querySelector('i').classList.add('text-custom-blue');
                        this.querySelector('span.font-semibold').classList.remove('text-[#002E8B]');
                        this.querySelector('span.font-semibold').classList.add('text-custom-blue');
                        this.querySelector('div.text-[13px]').classList.remove('text-[#002E8B]');
                        this.querySelector('div.text-[13px]').classList.add('text-gray-700');
                    });
                });
            </script>
            
            
        </div>
        <!-- Garis bawah header -->
        <hr class="w-full border-t-[0.5px] border-gray-300">
    </div>

