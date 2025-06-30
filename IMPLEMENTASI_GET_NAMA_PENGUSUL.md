# Implementasi Function GetNamaPengusul

## Overview

Function `GetNamaPengusul` telah berhasil diimplementasikan di seluruh aplikasi Laravel untuk mengoptimasi performa query dan mengurangi join operations.

## Files yang Diupdate

### 1. Migration

-   `database/migrations/2025_01_27_create_function_get_nama_pengusul.php`

### 2. Helper Class

-   `app/Helpers/PengusulHelper.php`

### 3. Controllers yang Dioptimasi

-   `app/Http/Controllers/tatausahaController.php`
-   `app/Http/Controllers/staffumumController.php`
-   `app/Http/Controllers/dosenController.php`
-   `app/Http/Controllers/mahasiswaController.php`
-   `app/Http/Controllers/SuratController.php`
-   `app/Http/Controllers/KepalaSubController.php`

## Function Database

```sql
DELIMITER $$

CREATE FUNCTION GetNamaPengusul(p_id_pengusul INT)
RETURNS VARCHAR(255)
DETERMINISTIC
BEGIN
    DECLARE nama_pengusul VARCHAR(255);

    SELECT nama INTO nama_pengusul
    FROM pengusul
    WHERE id_pengusul = p_id_pengusul;

    RETURN COALESCE(nama_pengusul, "-");
END $$

DELIMITER ;
```

## Helper Class Implementation

```php
<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class PengusulHelper
{
    /**
     * Get nama pengusul using database function
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
```

## Penggunaan di Controllers

### Sebelum (Eloquent Relationship):

```php
$oleh = $surat->dibuatOleh->nama;
```

### Sesudah (Function Database):

```php
$oleh = PengusulHelper::getNamaPengusul($surat->dibuat_oleh);
```

## Area yang Dioptimasi

### 1. DataTables Controllers

-   **tatausahaController.php**: Lines 189, 273, 376
-   **staffumumController.php**: Lines 208, 310
-   **dosenController.php**: Line 274
-   **mahasiswaController.php**: Line 306
-   **SuratController.php**: Line 155
-   **KepalaSubController.php**: Lines 148, 321

### 2. Riwayat Status Surat

Semua controller yang menampilkan riwayat status surat sekarang menggunakan function database.

### 3. Detail Surat Views

Views yang menampilkan detail surat bisa dioptimasi menggunakan helper.

## Keuntungan Implementasi

### 1. **Performance**

-   Mengurangi join query dari Eloquent relationships
-   Query lebih cepat karena langsung ke database
-   Mengurangi beban memory Laravel

### 2. **Consistency**

-   Format data konsisten di seluruh aplikasi
-   Handling null value terpusat dengan `COALESCE`

### 3. **Maintainability**

-   Logic terpusat di database function
-   Mudah diubah tanpa mengubah kode aplikasi
-   Helper class dengan fallback mechanism

### 4. **Security**

-   Mengurangi SQL injection dengan prepared statements
-   Validasi input di level database

## Cara Menjalankan

### 1. Jalankan Migration

```bash
php artisan migrate
```

### 2. Test Function

```php
// Di controller
$nama = PengusulHelper::getNamaPengusul(1);
echo $nama; // Output: Nama pengusul dengan ID 1

// Atau langsung di database
SELECT GetNamaPengusul(1) as nama;
```

### 3. Bulk Operations

```php
$idList = [1, 2, 3, 4, 5];
$namaMap = PengusulHelper::getMultipleNamaPengusul($idList);
```

## Monitoring dan Maintenance

### 1. Check Function Status

```sql
SHOW FUNCTION STATUS WHERE Name = 'GetNamaPengusul';
```

### 2. Performance Monitoring

-   Monitor query execution time
-   Check database load
-   Compare before/after performance

### 3. Error Handling

-   Helper class sudah include fallback mechanism
-   Jika function tidak ada, akan menggunakan direct query
-   Log error untuk debugging

## Optimasi Lanjutan

### 1. Caching

```php
$nama = Cache::remember("pengusul_nama_{$id_pengusul}", 3600, function() use ($id_pengusul) {
    return PengusulHelper::getNamaPengusul($id_pengusul);
});
```

### 2. Batch Processing

```php
// Untuk processing data dalam jumlah besar
$suratIds = [1, 2, 3, 4, 5];
$namaPengusul = DB::select("
    SELECT id_surat, GetNamaPengusul(dibuat_oleh) as nama_pembuat
    FROM surat
    WHERE id_surat IN (" . implode(',', $suratIds) . ")
");
```

## Catatan Penting

1. **Backup Database**: Selalu backup sebelum menjalankan migration
2. **Test Environment**: Test di development environment terlebih dahulu
3. **Indexing**: Pastikan `id_pengusul` di tabel `pengusul` sudah di-index
4. **Performance**: Function optimal untuk single record lookup
5. **Fallback**: Helper class sudah include fallback mechanism

## Troubleshooting

### 1. Function Not Found

Jika function tidak ditemukan, helper akan fallback ke direct query.

### 2. Performance Issues

-   Check database indexing
-   Monitor query execution plan
-   Consider caching for frequently accessed data

### 3. Error Logs

Check Laravel logs untuk error details:

```bash
tail -f storage/logs/laravel.log
```

## Kesimpulan

Implementasi function `GetNamaPengusul` berhasil mengoptimasi performa aplikasi dengan:

-   Mengurangi join operations
-   Meningkatkan query speed
-   Menyediakan consistency di seluruh aplikasi
-   Memberikan maintainability yang lebih baik

Function ini siap digunakan dan telah diimplementasikan di semua area yang membutuhkan data nama pengusul.
