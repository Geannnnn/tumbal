@props([
    'userRole' => 'Mahasiswa',
    'subtitle' => null,
    'ajaxRoute',
    'searchPlaceholder' => 'Cari...',
    'jenisSurat' => [],
    'statusSurat' => []
])

<div class="flex h-screen ml-[250px] overflow-x-hidden">
    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col">
        @include('layouts.header', ['notifikasiSurat' => $notifikasiSurat ?? collect([])])
        @include('components.alertnotif')

        <main class="flex-1 bg-white p-12">
            <div class="flex gap-4 mb-10">
                <div class="relative">
                    <x-form.select name="jenis_surat" id="jenis_surat" :options="$jenisSurat" placeholder="Jenis Surat" />
                    <button type="button" id="reset-jenis-surat" class="absolute right-8 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="relative">
                    <x-form.select name="status_surat" id="status_surat" :options="$statusSurat" placeholder="Status Surat" />
                    <button type="button" id="reset-status-surat" class="absolute right-8 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="relative">
                    <x-yearselect name="year" id="year" :start="2000" />
                    <button type="button" id="reset-year" class="absolute right-8 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 hidden"></button>
                </div>
            </div>

            <x-backplat 
                :title="'Status Surat'"
                :subtitle="$subtitle"
                :searchPlaceholder="$searchPlaceholder"
                :search="true">
                
                <x-datatable-status 
                    id="mahasiswaStatusTable"
                    :ajaxUrl="$ajaxRoute"
                    :columns="[
                        'nomor_surat' => 'Nomor',
                        'judul_surat' => 'Nama Surat',
                        'tanggal_pengajuan' => 'Tanggal',
                        'jenis_surat' => 'Jenis Surat',
                        'status' => 'Status',
                    ]"
                    :userRole="$userRole"
                />
            </x-backplat>
        </main>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#jenis_surat, #status_surat, #year').on('change', function() {
            updateResetButtons();
            $('#mahasiswaStatusTable').DataTable().ajax.reload();
        });

        $('#reset-jenis-surat').on('click', function() {
            $('#jenis_surat').val('').trigger('change');
        });

        $('#reset-status-surat').on('click', function() {
            $('#status_surat').val('').trigger('change');
        });

        $('#reset-year').on('click', function() {
            $('#year').val('').trigger('change');
        });

        function updateResetButtons() {
            $('#reset-jenis-surat').toggleClass('hidden', !$('#jenis_surat').val());
            $('#reset-status-surat').toggleClass('hidden', !$('#status_surat').val());
            $('#reset-year').toggleClass('hidden', !$('#year').val());
        }

        updateResetButtons();
    });
</script>
@endpush
