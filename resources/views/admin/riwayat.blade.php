@extends('layouts.app')
@section('title','Riwayat Pengajuan')
@section('content')
<div class="container mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Riwayat Pengajuan Surat</h1>
    <form method="GET" action="{{ route('admin.riwayatPengajuan') }}" class="mb-4 flex gap-2 items-center">
        <label for="status" class="font-semibold">Filter Status:</label>
        <select name="status" id="status" class="bg-[#707FDD] text-white px-4 py-2 rounded-lg" onchange="this.form.submit()">
            <option value="all" {{ $status == 'all' ? 'selected' : '' }}>Semua</option>
            <option value="Diterbitkan" {{ $status == 'Diterbitkan' ? 'selected' : '' }}>Diterima</option>
            <option value="Ditolak" {{ $status == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
        </select>
    </form>
    <x-datatable
        id="riwayat-table"
        :columns="$columns"
        ajaxUrl="{{ route('admin.search', ['status' => $status]) }}"
        :ordering="false"
        :lengthMenu="false"
        :pageLength="10"
        :showEdit="false"
        :showDelete="false"
        :search="true"
    />
</div>
@endsection