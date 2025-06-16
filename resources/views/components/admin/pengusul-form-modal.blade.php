@props(['id' => 'pengusul-modal'])

<div id="{{ $id }}" class="fixed inset-0 z-50 flex items-center justify-center bg-opacity-40 backdrop-blur-sm hidden transition-opacity duration-300 ease-in-out">
    <div class="relative w-full max-w-md mx-auto bg-white rounded-2xl shadow-2xl p-8 animate-modal-show">
        <button type="button" onclick="hideModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 transition">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        <h3 id="modalTitle" class="text-2xl font-bold text-gray-800 mb-6 text-center"></h3>
        <form id="pengusulForm" class="space-y-5">
            @csrf
            <input type="hidden" id="pengusul_id" name="id">
            <div>
                <label for="nama" class="block text-sm font-semibold text-gray-700 mb-1">Nama</label>
                <input type="text" id="nama" name="nama" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
                <div id="nama-error" class="hidden text-red-500 text-xs mt-1"></div>
            </div>
            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
                <div id="email-error" class="hidden text-red-500 text-xs mt-1"></div>
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                <div id="password-error" class="hidden text-red-500 text-xs mt-1"></div>
                <p class="text-xs text-gray-400 mt-1">Kosongkan jika tidak ingin mengubah password</p>
            </div>
            <div>
                <label for="role" class="block text-sm font-semibold text-gray-700 mb-1">Role</label>
                <select id="role" name="id_role_pengusul" onchange="toggleIdentifierField()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" required>
                    <option value="">Pilih Role</option>
                    <option value="1">Dosen</option>
                    <option value="2">Mahasiswa</option>
                </select>
                <div id="role-error" class="hidden text-red-500 text-xs mt-1"></div>
            </div>
            <div id="nim-field" class="hidden">
                <label for="nim" class="block text-sm font-semibold text-gray-700 mb-1">NIM</label>
                <input type="number" id="nim" name="nim" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                <div id="nim-error" class="hidden text-red-500 text-xs mt-1"></div>
            </div>
            <div id="nip-field" class="hidden">
                <label for="nip" class="block text-sm font-semibold text-gray-700 mb-1">NIP</label>
                <input type="number" id="nip" name="nip" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                <div id="nip-error" class="hidden text-red-500 text-xs mt-1"></div>
            </div>
            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="hideModal()" class="py-2 px-5 rounded-lg bg-gray-200 text-gray-700 font-semibold hover:bg-gray-300 transition">Batal</button>
                <button type="submit" class="py-2 px-5 rounded-lg bg-blue-700 text-white font-semibold hover:bg-blue-800 transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes modal-show {
    from { opacity: 0; transform: scale(0.95); }
    to { opacity: 1; transform: scale(1); }
}

@keyframes modal-hide {
    from { opacity: 1; transform: scale(1); }
    to { opacity: 0; transform: scale(0.95); }
}

.animate-modal-show {
    animation: modal-show 0.3s ease;
}

.animate-modal-hide {
    animation: modal-hide 0.3s ease;
}
</style> 