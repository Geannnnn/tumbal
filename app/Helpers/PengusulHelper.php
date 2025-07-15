<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use App\Models\Pengusul;
use App\Models\Staff;
use App\Models\KepalaSub;

class PengusulHelper
{
    /**
     * Get nama pengusul using database function
     *
     * @param int $id_pengusul
     * @return string
     */
    public static function getNamaPengusul($id_pengusul)
    {
        if (!$id_pengusul) {
            return '-';
        }
        
        try {
            // Fallback to direct query to get complete information
            $pengusul = DB::table('pengusul')->where('id_pengusul', $id_pengusul)->first();
            if ($pengusul) {
                $identifier = $pengusul->nim ? "NIM: {$pengusul->nim}" : ($pengusul->nip ? "NIP: {$pengusul->nip}" : '');
                return $identifier ? "{$pengusul->nama} ({$identifier})" : $pengusul->nama;
            }
            return '-';
        } catch (\Exception $e) {
            return '-';
        }
    }

    /**
     * Get multiple nama pengusul for bulk operations
     *
     * @param array $id_pengusul_list
     * @return array
     */
    public static function getMultipleNamaPengusul($id_pengusul_list)
    {
        if (empty($id_pengusul_list)) {
            return [];
        }

        $placeholders = str_repeat('?,', count($id_pengusul_list) - 1) . '?';
        
        try {
            $results = DB::select("
                SELECT id_pengusul, GetNamaPengusul(id_pengusul) as nama 
                FROM pengusul 
                WHERE id_pengusul IN ($placeholders)
            ", $id_pengusul_list);
            
            $namaMap = [];
            foreach ($results as $result) {
                $namaMap[$result->id_pengusul] = $result->nama;
            }
            
            return $namaMap;
        } catch (\Exception $e) {
            // Fallback to direct query
            $pengusul = DB::table('pengusul')
                ->whereIn('id_pengusul', $id_pengusul_list)
                ->pluck('nama', 'id_pengusul')
                ->toArray();
            
            return $pengusul;
        }
    }

    /**
     * Get nama user berdasarkan tipe dan ID
     *
     * @param int $id_user
     * @param string $tipe_user
     * @return string
     */
    public static function getNamaUserByTipe($id_user, $tipe_user)
    {
        if (!$id_user || !$tipe_user) {
            return '-';
        }

        try {
            switch ($tipe_user) {
                case 'pengusul':
                    $pengusul = Pengusul::find($id_user);
                    if ($pengusul) {
                        $identifier = $pengusul->nim ? "| {$pengusul->nim}" : ($pengusul->nip ? "| {$pengusul->nip}" : '');
                        return $identifier ? "{$pengusul->nama} {$identifier}" : $pengusul->nama;
                    }
                    return '-';
                case 'staff':
                    $staff = Staff::where('id_staff', $id_user)->first();
                    if ($staff) {
                        $identifier = $staff->nip ? "| {$staff->nip}" : '';
                        return $identifier ? "{$staff->nama} {$identifier}" : $staff->nama;
                    }
                    return '-';
                case 'kepala_sub':
                    $kepalaSub = KepalaSub::find($id_user);
                    if ($kepalaSub) {
                        $identifier = $kepalaSub->nip ? "| {$kepalaSub->nip}" : '';
                        return $identifier ? "{$kepalaSub->nama} {$identifier}" : $kepalaSub->nama;
                    }
                    return '-';
                default:
                    return '-';
            }
        } catch (\Exception $e) {
            return '-';
        }
    }
} 