@props(['lampiran' => null])

<div class="flex flex-col gap-3">
    <div class="mb-4 flex items-center">
        <label for="lampiranToggle" class="text-sm mr-2">Lampiran</label>
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" id="lampiranToggle" class="sr-only peer" {{ $lampiran ? 'checked' : '' }}>
            <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-green-500 peer-focus:ring-2 peer-focus:ring-blue-500 transition-all duration-300"></div>
            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-all duration-300 peer-checked:translate-x-5"></div>
        </label>
    </div>

    <div class="flex flex-col gap-2 w-full {{ $lampiran ? '' : 'hidden' }}" id="fileInputWrapper">
        <span class="text-sm text-gray-600">*Upload file jika ada</span>
        <label for="real-file" class="w-full cursor-pointer bg-[#F0F2FF] text-[#6D727C] py-3 px-4 rounded-lg outline-none transition text-start hover:bg-[#e6e9f0]">
            <i class="fa-regular fa-file mr-2"></i>Upload File
        </label>
        <input type="hidden" name="lampiran_enabled" id="lampiran_enabled" value="{{ $lampiran ? '1' : '0' }}">
        <input type="file" id="real-file" name="lampiran" class="hidden" accept="application/pdf, image/*, .docx, .xlsx, .txt">
        <span id="file-name" class="text-sm text-gray-500 mt-2">{{ $lampiran ? basename($lampiran) : 'Belum ada file' }}</span>
    </div>
</div>
