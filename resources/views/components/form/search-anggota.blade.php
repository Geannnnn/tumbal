@props(['selected' => []])

            <span>Anggota</span>
            <div class="relative">
                <input type="search" id="searchAnggota" placeholder="Cari anggota..." class="bg-[#F0F2FF] py-2 px-4 rounded-lg outline-none w-full" autocomplete="off">
                <div id="resultsAnggota" class="border bg-white rounded shadow-md mt-1 max-h-48 overflow-y-auto hidden absolute z-10 w-full"></div>
            </div>

<div id="selectedAnggota" class="mt-2 space-y-1">
    @foreach($selected as $anggota)
        <div id="anggota-{{ $anggota->id_pengusul }}" class="flex items-center justify-between bg-blue-100 rounded px-3 py-1 text-sm">
            <span>{{ $anggota->nim ?? $anggota->nip }} - {{ $anggota->nama }}</span>
            <button type="button" class="text-red-500 ml-2" onclick="removeAnggota('{{ $anggota->id_pengusul }}')">x</button>
        </div>
    @endforeach
</div>
<div id="anggotaInputs">
    @foreach($selected as $anggota)
        <input type="hidden" name="anggota[]" value="{{ $anggota->id_pengusul }}" id="input-anggota-{{ $anggota->id_pengusul }}">
    @endforeach
</div>

            @push('scripts')
            <script>
                const searchAnggota = document.getElementById('searchAnggota');
                const resultsAnggota = document.getElementById('resultsAnggota');
                const selectedAnggota = document.getElementById('selectedAnggota');
                const anggotaInputs = document.getElementById('anggotaInputs');

                let debounceTimeout = null;

                searchAnggota.addEventListener('input', function () {
                    const query = this.value.trim();
                    clearTimeout(debounceTimeout);

                    if (query.length < 1) {
                        resultsAnggota.classList.add('hidden');
                        return;
                    }

                    debounceTimeout = setTimeout(() => {
                        fetch(`/anggota/search?query=${encodeURIComponent(query)}`)
                            .then(res => res.json())
                            .then(data => {
                                resultsAnggota.innerHTML = '';

                                if (data.length === 0) {
                                    resultsAnggota.innerHTML = '<div class="px-4 py-2 text-gray-500">Tidak ditemukan</div>';
                                    resultsAnggota.classList.remove('hidden');
                                    return;
                                }

                                data.forEach(user => {
                        // Skip jika user sudah dipilih
                        if (document.getElementById(`anggota-${user.id_pengusul}`)) return;

                                    const display = `${user.nim ?? user.nip} - ${user.nama}`;
                                    const div = document.createElement('div');
                                    div.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm';
                                    div.textContent = display;

                                    div.addEventListener('click', () => {
                                        const id = user.id_pengusul;

                                        // Tampilkan tag anggota terpilih
                                        const tag = document.createElement('div');
                                        tag.id = `anggota-${id}`;
                                        tag.className = 'flex items-center justify-between bg-blue-100 rounded px-3 py-1 text-sm';
                                        tag.innerHTML = `
                                            <span>${display}</span>
                                            <button type="button" class="text-red-500 ml-2" onclick="removeAnggota('${id}')">x</button>
                                        `;
                                        selectedAnggota.appendChild(tag);

                                        // Hidden input
                                        const hidden = document.createElement('input');
                                        hidden.type = 'hidden';
                                        hidden.name = 'anggota[]';
                                        hidden.value = id;
                                        hidden.id = `input-anggota-${id}`;
                                        anggotaInputs.appendChild(hidden);

                                        // Kosongkan
                                        resultsAnggota.classList.add('hidden');
                                        resultsAnggota.innerHTML = '';
                                        searchAnggota.value = '';
                                    });

                                    resultsAnggota.appendChild(div);
                                });

                                resultsAnggota.classList.remove('hidden');
                            });
                    }, 300);
                });

                function removeAnggota(id) {
                    document.getElementById(`anggota-${id}`)?.remove();
                    document.getElementById(`input-anggota-${id}`)?.remove();
                }

                document.addEventListener('click', function (e) {
                    if (!searchAnggota.contains(e.target) && !resultsAnggota.contains(e.target)) {
                        resultsAnggota.classList.add('hidden');
                    }
                });
            </script>
            @endpush