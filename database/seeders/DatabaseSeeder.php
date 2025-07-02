<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin
        DB::table('admin')->insert([
            'username' => 'admin',
            'nama' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => '$2y$12$7./K8pLVhII66KbtuV80Nu1.zVKXqbYWULUwZ.IGr.lWCrYTbEEai',
            'created_at' => now(),
            'updated_at' => '2025-05-22 09:12:09',
        ]);

        // Jenis Surat
        DB::table('jenis_surat')->insert([
            ['jenis_surat' => 'Surat Tugas'],
            ['jenis_surat' => 'Surat Permohonan'],
            ['jenis_surat' => 'Surat Pengantar'],
            ['jenis_surat' => 'Surat Undangan Kegiatan'],
            ['jenis_surat' => 'Surat Izin Tidak Masuk'],
            ['jenis_surat' => 'Surat Cuti Akademik'],
        ]);

        // Kepala Sub
        DB::table('kepala_sub')->insert([
            'nama' => 'Kepala Sub',
            'nip' => '111111',
            'email' => 'kepalasub@gmail.com',
            'password' => '$2y$12$UZ/6pVu2OgUNjgQ4JTdxSeWvVHOb7sBxj6BJs.XzD89ytXS4HggAC',
            'created_at' => now(),
            'updated_at' => '2025-05-22 09:13:15',
        ]);

        // Role Pengusul
        DB::table('role_pengusul')->insert([
            ['role' => 'Dosen'],
            ['role' => 'Mahasiswa'],
        ]);

        // Peran Anggota
        DB::table('peran_anggota')->insert([
            ['peran' => 'Ketua'],
            ['peran' => 'Anggota'],
        ]);

        // Pengusul
        DB::table('pengusul')->insert([
            ['nama' => 'Adhyca', 'nim' => '4342401080', 'nip' => null, 'password' => '$2y$12$zKO9jUVN.o1NWLYVToaG2OZhW7EFQFuHA47o6yxf4FKfeagOdcqD6', 'id_role_pengusul' => 2, 'email' => 'Adhyca@gmail.com', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Putri', 'nim' => '4342401062', 'nip' => null, 'password' => '$2y$12$9vEmSQFu7bBeXx81x8D0T.Q7E3BanrjbcfK8pYKLq7J5zoV.Sfdiy', 'id_role_pengusul' => 2, 'email' => 'Putri@gmail.com', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Fahri', 'nim' => '4342401073', 'nip' => null, 'password' => '$2y$12$jnaW31F1V7AQ/JbnFsPH5.yGDjti7/DsnfozMZSiQTt0vLvB6uyUO', 'id_role_pengusul' => 2, 'email' => 'Fahri@gmail.com', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Ali', 'nim' => '4342401078', 'nip' => null, 'password' => '$2y$12$UvMcamaVEYHpvcV16SLwHeaaY2T97rW/0obM4Q9ez/U0OofLcDdKu', 'id_role_pengusul' => 2, 'email' => 'Ali@gmail.com', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Hermansa', 'nim' => '4342401084', 'nip' => null, 'password' => '$2y$12$C53T4qRqsphUYSRjp4hMcuDrxoRNHIRwZ2JHRI1j6UEdUZ925fwlO', 'id_role_pengusul' => 2, 'email' => 'Hermansa@gmail.com', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Aqilah', 'nim' => '4342401087', 'nip' => null, 'password' => '$2y$12$0RXOMrt38cNCkatIksUO7ucsCUn.xMRgsC.2vc2AL5dz0rTmCw15q', 'id_role_pengusul' => 2, 'email' => 'Aqilah@gmail.com', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Supardianto, S.ST., M.Eng', 'nim' => null, 'nip' => '113105', 'password' => '$2y$12$LOY57vYfe.tXYj.Zg9EWzuwTAe9oSoJ/d2Mo5WnGngjzrfqoPCshi', 'id_role_pengusul' => 1, 'email' => 'Supardianto@gmail.com', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'Gilang Bagus Ramadhan, A.Md.Kom', 'nim' => null, 'nip' => '222331', 'password' => '$2y$12$E572pZF2N/ih8HWqreCPVuV9JpZ67gm5OOkVuW7GYgz3FIejlQ.Pq', 'id_role_pengusul' => 1, 'email' => 'Gilang@gmail.com', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Staff
        DB::table('staff')->insert([
            [
                'nama' => 'Staff Umum',
                'nip' => '1234',
                'email' => 'staffumum@gmail.com',
                'password' => '$2y$12$1TK55dLR1JZeKU2A2GQOLu3ukPdVuFhmlzyv8AYXpeYwBPIxM1fqK',
                'role' => 'Staff Umum',
                'created_at' => now(),
                'updated_at' => '2025-05-02 08:41:57',
            ],
            [
                'nama' => 'Tata Usaha',
                'nip' => '12345',
                'email' => 'tatausaha@gmail.com',
                'password' => '$2y$12$ZYJPHXotVlVSOkvQbYUEW.Q8cZWOGLBvP/fdhc02jgVdqOg1rMK6S',
                'role' => 'Tata Usaha',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Status Surat
        DB::table('status_surat')->insert([
            ['status_surat' => 'Diterbitkan'],
            ['status_surat' => 'Diajukan'],
            ['status_surat' => 'Draft'],
            ['status_surat' => 'Ditolak'],
            ['status_surat' => 'Diterima'],
            ['status_surat' => 'Menunggu Persetujuan'],
            ['status_surat' => 'Menunggu Penerbitan'],
            ['status_surat' => 'Divalidasi'],
            ['status_surat' => 'Disetujui'],
        ]);
    }
}
