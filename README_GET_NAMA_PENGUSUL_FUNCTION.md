# Function GetNamaPengusul - Dokumentasi

## Overview

Function `GetNamaPengusul` adalah function database yang digunakan untuk mengambil nama pengusul berdasarkan ID pengusul. Function ini sangat berguna untuk mengurangi join query dan meningkatkan performa aplikasi.

## Definition Function

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

## Area Penggunaan di Aplikasi

### 1. Detail Surat Views

-   `resources/views/staff/tata-usaha/detail-surat.blade.php`
-   `resources/views/staff/staff-umum/detail-surat.blade.php`
-   `resources/views/staff/tata-usaha/terbitkan-detail.blade.php`
-   `resources/views/kepalasub/tinjau-surat.blade.php`

**Penggunaan saat ini:**

```php
{{ $surat->dibuatOleh ? $surat->dibuatOleh->nama : '-' }}
```

**Bisa dioptimasi menjadi:**

```php
{{ DB::select("SELECT GetNamaPengusul(?) as nama", [$surat->dibuat_oleh])[0]->nama }}
```

### 2. DataTables Controllers

-   `tatausahaController.php` - Lines 189, 273, 376
-   `staffumumController.php` - Lines 208, 310
-   `dosenController.php` - Line 274
-   `mahasiswaController.php` - Line 306
-   `SuratController.php` - Line 155
-   `KepalaSubController.php` - Lines 148, 321

**Penggunaan saat ini:**

```php
$oleh = $surat->dibuatOleh->nama;
```

**Bisa dioptimasi menjadi:**

```php
$oleh = DB::select("SELECT GetNamaPengusul(?) as nama", [$surat->dibuat_oleh])[0]->nama;
```

### 3. Riwayat Status Surat

Semua controller yang menampilkan riwayat status surat.

## Keuntungan Menggunakan Function

### 1. **Performance**

-   Mengurangi join query
-   Query lebih cepat karena langsung ke database
-   Mengurangi beban memory Laravel

### 2. **Consistency**

-   Format data konsisten di seluruh aplikasi
-   Handling null value terpusat

### 3. **Maintainability**

-   Logic terpusat di database
-   Mudah diubah tanpa mengubah kode aplikasi

### 4. **Security**

-   Mengurangi SQL injection
-   Validasi input di level database

## Contoh Implementasi

### 1. Di Controller

```php
// Cara 1: Menggunakan DB::select
$nama = DB::select("SELECT GetNamaPengusul(?) as nama", [$id_pengusul])[0]->nama;

// Cara 2: Menggunakan DB::raw dalam query
$surat = DB::table('surat')
    ->select('*', DB::raw('GetNamaPengusul(dibuat_oleh) as nama_pembuat'))
    ->get();
```

### 2. Di View (Blade)

```php
{{ DB::select("SELECT GetNamaPengusul(?) as nama", [$surat->dibuat_oleh])[0]->nama }}
```

### 3. Di DataTables

```php
->addColumn('dibuat_oleh', function($row) {
    return DB::select("SELECT GetNamaPengusul(?) as nama", [$row->dibuat_oleh])[0]->nama;
})
```

## Migration

### Membuat Function

```bash
php artisan migrate
```

### Rollback Function

```bash
php artisan migrate:rollback
```

## Optimasi yang Bisa Dilakukan

### 1. **Bulk Operations**

Untuk multiple records, gunakan:

```sql
SELECT id_surat, GetNamaPengusul(dibuat_oleh) as nama_pembuat
FROM surat
WHERE id_surat IN (1,2,3,4,5);
```

### 2. **Caching**

Implementasi caching untuk data yang sering diakses:

```php
$nama = Cache::remember("pengusul_nama_{$id_pengusul}", 3600, function() use ($id_pengusul) {
    return DB::select("SELECT GetNamaPengusul(?) as nama", [$id_pengusul])[0]->nama;
});
```

### 3. **Batch Processing**

Untuk processing data dalam jumlah besar:

```php
$suratIds = [1, 2, 3, 4, 5];
$namaPengusul = DB::select("
    SELECT id_surat, GetNamaPengusul(dibuat_oleh) as nama_pembuat
    FROM surat
    WHERE id_surat IN (" . implode(',', $suratIds) . ")
");
```

## Catatan Penting

1. **Performance**: Function ini optimal untuk single record lookup
2. **Indexing**: Pastikan `id_pengusul` di tabel `pengusul` sudah di-index
3. **Error Handling**: Function sudah handle null value dengan `COALESCE`
4. **Testing**: Test function di environment development terlebih dahulu

## Monitoring

Untuk monitoring performa function:

```sql
SHOW FUNCTION STATUS WHERE Name = 'GetNamaPengusul';
```
