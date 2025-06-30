<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

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
            $result = DB::select("SELECT GetNamaPengusul(?) as nama", [$id_pengusul]);
            return $result[0]->nama ?? '-';
        } catch (\Exception $e) {
            // Fallback to direct query if function doesn't exist
            $pengusul = DB::table('pengusul')->where('id_pengusul', $id_pengusul)->first();
            return $pengusul ? $pengusul->nama : '-';
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
} 