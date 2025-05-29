# Aplikasi Pengajuan Surat (Laravel 12)

Ini adalah aplikasi berbasis Laravel 12 untuk mengelola pengajuan surat oleh mahasiswa dan dosen.

## 🛠️ Fitur Utama
- Multi-role: Mahasiswa, Dosen, Staff
- Pengajuan dan riwayat surat
- Multi-select anggota pengusul
- Server-side pagination dan search

## 📦 Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/Geannnnn/tumbal.git
cd tumbal
```

### 2. Install Dependency
```bash
composer install
npm install
```

### 3. Buat File `.env`
```bash
copy .env.example .env
cp .env.example .env jika menggunakan terminal
```

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_DATABASE=db_surat
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Generate App Key
```bash
php artisan key:generate
```

### 5. Jalankan Migration (jika menggunakan)
```bash
php artisan migrate
```

Atau import manual file SQL:
```bash
# contoh: database/db_surat.sql
```

### 6. Jalankan Vite (untuk frontend)
```bash
npm run dev
atau bisa lebih simpel dengan npm run start
```

### 7. Jalankan Laravel Server
```bash
php artisan serve
```

Aplikasi akan berjalan di: [http://localhost:8000](http://localhost:8000)

## ✅ Catatan
- Pastikan PHP ≥ 8.2, Node.js ≥ 18.x, Composer dan NPM sudah terinstall.
- Folder `vendor/` dan `node_modules/` tidak disertakan di Git (lihat `.gitignore`).
- Jika kamu mengalami masalah dengan `vite`, coba hapus `node_modules` dan install ulang.

## 📃 Lisensi
Proyek ini menggunakan lisensi MIT.
