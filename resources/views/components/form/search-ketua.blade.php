@props(['selected' => null])

<span>Ketua Pengaju</span>
<div class="relative w-full">
    <input type="text" id="search_dosen" value="{{ $selected ? $selected->nip . ' - ' . $selected->nama : '' }}" class="bg-[#F0F2FF] py-2 px-4 rounded-lg outline-none w-full pr-10" placeholder="Cari Dosen (NIP / Nama)" autocomplete="off">
    <button type="button" id="clear_dosen" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-red-600 {{ $selected ? '' : 'hidden' }}">
        &times;
    </button>
</div>
<input type="hidden" name="id_pengusul" id="id_pengusul" value="{{ $selected ? $selected->id_pengusul : '' }}">
<div id="dosen-results" class="border bg-white rounded shadow-md mt-1 max-h-48 overflow-y-auto hidden"></div>

            @push('scripts')
            <script>
                const searchInput = document.getElementById('search_dosen');
                const resultsContainer = document.getElementById('dosen-results');
                const hiddenInput = document.getElementById('id_pengusul');
                const clearButton = document.getElementById('clear_dosen');
            
                searchInput.addEventListener('input', function () {
                    const query = searchInput.value.trim();
                    resultsContainer.innerHTML = '';
                    clearButton.classList.add('hidden'); // Sembunyikan tombol X saat input berubah
            
                    if (query.length === 0) {
                        resultsContainer.classList.add('hidden');
                        return;
                    }
            
                    fetch(`/search-dosen?query=${query}`)
                        .then(res => res.json())
                        .then(data => {
                            if (data.length === 0) {
                                resultsContainer.classList.add('hidden');
                                return;
                            }
            
                            resultsContainer.classList.remove('hidden');
                            resultsContainer.innerHTML = '';
            
                            data.forEach(dosen => {
                                const div = document.createElement('div');
                                div.classList.add('px-4', 'py-2', 'hover:bg-gray-100', 'cursor-pointer', 'text-sm');
                                div.textContent = `${dosen.nip} - ${dosen.nama}`;
                                div.addEventListener('click', () => {
                                    searchInput.value = `${dosen.nip} - ${dosen.nama}`;
                                    hiddenInput.value = dosen.id_pengusul;
                                    resultsContainer.classList.add('hidden');
                                    resultsContainer.innerHTML = '';
                                    clearButton.classList.remove('hidden'); // Tampilkan tombol X
                                });
                                resultsContainer.appendChild(div);
                            });
                        });
                });
            
                // Tombol X untuk hapus input
                clearButton.addEventListener('click', () => {
                    searchInput.value = '';
                    hiddenInput.value = '';
                    clearButton.classList.add('hidden');
                    searchInput.focus();
                });
            
                document.addEventListener('click', function (e) {
                    if (!resultsContainer.contains(e.target) && e.target !== searchInput) {
                        resultsContainer.classList.add('hidden');
                    }
                });
            </script>
            @endpush
            
