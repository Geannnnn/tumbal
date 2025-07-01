-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 01, 2025 at 02:09 AM
-- Server version: 8.4.3
-- PHP Version: 8.4.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_surat1`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetAllJenisSurat` ()   BEGIN
    SELECT
        id_jenis_surat,
        jenis_surat
    FROM jenis_surat
    ORDER BY id_jenis_surat ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_GetJenisSuratForSelect` ()   BEGIN
                SELECT
                    id_jenis_surat,
                    jenis_surat
                FROM jenis_surat
                ORDER BY jenis_surat ASC;
            END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `GetNamaPengusul` (`p_id_pengusul` INT) RETURNS VARCHAR(255) CHARSET utf8mb4 DETERMINISTIC BEGIN
	DECLARE nama_pengusul VARCHAR(255);
    
    SELECT nama into nama_pengusul 
    FROM pengusul
    WHERE id_pengusul = p_id_pengusul;
    
    RETURN nama_pengusul;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` bigint UNSIGNED NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `nama`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin', 'admin@gmail.com', '$2y$12$7./K8pLVhII66KbtuV80Nu1.zVKXqbYWULUwZ.IGr.lWCrYTbEEai', '2025-05-27 10:50:39', '2025-05-22 02:12:09');

-- --------------------------------------------------------

--
-- Table structure for table `jenis_surat`
--

CREATE TABLE `jenis_surat` (
  `id_jenis_surat` bigint UNSIGNED NOT NULL,
  `jenis_surat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jenis_surat`
--

INSERT INTO `jenis_surat` (`id_jenis_surat`, `jenis_surat`) VALUES
(1, 'Surat Tugas'),
(2, 'Surat Permohonan'),
(8, 'Surat Pengantar'),
(27, 'Surat Undangan Kegiatan'),
(28, 'Surat Cuti Akademik'),
(29, 'Surat Izin Tidak Masuk');

-- --------------------------------------------------------

--
-- Table structure for table `kepala_sub`
--

CREATE TABLE `kepala_sub` (
  `id_kepala_sub` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kepala_sub`
--

INSERT INTO `kepala_sub` (`id_kepala_sub`, `nama`, `nip`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Kepala Sub', '111111', 'kepalasub@gmail.com', '$2y$12$UZ/6pVu2OgUNjgQ4JTdxSeWvVHOb7sBxj6BJs.XzD89ytXS4HggAC', '2025-05-27 10:50:39', '2025-05-22 02:13:15');

-- --------------------------------------------------------

--
-- Table structure for table `komentar_surat`
--

CREATE TABLE `komentar_surat` (
  `id` bigint UNSIGNED NOT NULL,
  `id_riwayat_status_surat` bigint UNSIGNED NOT NULL,
  `id_surat` bigint UNSIGNED NOT NULL,
  `id_user` bigint UNSIGNED NOT NULL,
  `komentar` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_05_22_145158_create_jenis_surat_table', 1),
(2, '2025_05_22_145305_create_pengusul_table', 1),
(3, '2025_05_22_145347_create_peran_anggota_table', 1),
(4, '2025_05_22_145443_create_pivot_pengusul_surat_table', 1),
(5, '2025_05_22_145736_create_riwayat_status_surat_table', 1),
(6, '2025_05_22_150207_create_role_pengusul_table', 1),
(7, '2025_05_22_150232_create_staff_table', 1),
(8, '2025_05_22_150250_create_status_surat_table', 1),
(9, '2025_05_22_151237_create_surat_table', 1),
(10, '2025_05_22_152332_create_admin_table', 1),
(11, '2025_05_22_152616_create_kepala_sub_table', 1),
(12, '2025_05_27_070242_relation', 1),
(13, '2025_05_29_090249_create_password_resets_table', 2),
(14, '2025_05_29_123232_create_password_reset_tokens_table', 3),
(15, '2024_06_01_000000_create_komentar_surat_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('Putri@gmail.com', '$2y$12$XQAFa.l3N/kihBLhQjNX5ulCZE9P.HT3SYbtv6rv1gYaCtD8B5t2e', '2025-05-29 05:58:08');

-- --------------------------------------------------------

--
-- Table structure for table `pengusul`
--

CREATE TABLE `pengusul` (
  `id_pengusul` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nim` char(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nip` char(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_role_pengusul` bigint UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengusul`
--

INSERT INTO `pengusul` (`id_pengusul`, `nama`, `nim`, `nip`, `password`, `id_role_pengusul`, `email`, `created_at`, `updated_at`) VALUES
(1, 'Adhyca', '4342401080', NULL, '$2y$12$zKO9jUVN.o1NWLYVToaG2OZhW7EFQFuHA47o6yxf4FKfeagOdcqD6', 2, 'adhykarya@gmail.com', '2025-05-27 10:50:40', '2025-05-27 10:50:40'),
(2, 'Putri', '4342401062', NULL, '$2y$12$9vEmSQFu7bBeXx81x8D0T.Q7E3BanrjbcfK8pYKLq7J5zoV.Sfdiy', 2, 'Putri@gmail.com', '2025-05-27 10:50:40', '2025-05-27 10:50:40'),
(3, 'Fahri', '4342401073', NULL, '$2y$12$jnaW31F1V7AQ/JbnFsPH5.yGDjti7/DsnfozMZSiQTt0vLvB6uyUO', 2, 'Fahri@gmail.com', '2025-05-27 10:50:40', '2025-05-27 10:50:40'),
(4, 'Ali', '4342401078', NULL, '$2y$12$29gOlozCZsZwgP8pQJ89UezIOlwBkGDvzeF33DGn44sc5lVxACJMu', 2, 'Ali@gmail.com', '2025-05-27 10:50:40', '2025-05-27 10:50:40'),
(5, 'Hermansa', '4342401084', NULL, '$2y$12$C53T4qRqsphUYSRjp4hMcuDrxoRNHIRwZ2JHRI1j6UEdUZ925fwlO', 2, 'Hermansa@gmail.com', '2025-05-27 10:50:40', '2025-05-27 10:50:40'),
(6, 'Aqilah', '4342401087', NULL, '$2y$12$0RXOMrt38cNCkatIksUO7ucsCUn.xMRgsC.2vc2AL5dz0rTmCw15q', 2, 'Aqilah@gmail.com', '2025-05-27 10:50:40', '2025-05-27 10:50:40'),
(7, 'Supardianto, S.ST., M.Eng', NULL, '113105', '$2y$12$LOY57vYfe.tXYj.Zg9EWzuwTAe9oSoJ/d2Mo5WnGngjzrfqoPCshi', 1, 'Supardianto@gmail.com', '2025-05-27 10:50:40', '2025-05-27 10:50:40'),
(8, 'Gilang Bagus Ramadhan, A.Md.Kom', NULL, '222331', '$2y$12$E572pZF2N/ih8HWqreCPVuV9JpZ67gm5OOkVuW7GYgz3FIejlQ.Pq', 1, 'Gilang@gmail.com', '2025-05-27 10:50:40', '2025-05-27 10:50:40');

-- --------------------------------------------------------

--
-- Table structure for table `peran_anggota`
--

CREATE TABLE `peran_anggota` (
  `id_peran_keanggotaan` bigint UNSIGNED NOT NULL,
  `peran` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `peran_anggota`
--

INSERT INTO `peran_anggota` (`id_peran_keanggotaan`, `peran`) VALUES
(1, 'Ketua'),
(2, 'Anggota');

-- --------------------------------------------------------

--
-- Table structure for table `pivot_pengusul_surat`
--

CREATE TABLE `pivot_pengusul_surat` (
  `id` bigint UNSIGNED NOT NULL,
  `id_pengusul` bigint UNSIGNED NOT NULL,
  `id_surat` bigint UNSIGNED NOT NULL,
  `id_peran_keanggotaan` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pivot_pengusul_surat`
--

INSERT INTO `pivot_pengusul_surat` (`id`, `id_pengusul`, `id_surat`, `id_peran_keanggotaan`) VALUES
(1, 7, 1, 1),
(2, 4, 1, 2),
(3, 2, 1, 2),
(4, 7, 5, 1),
(5, 8, 5, 2),
(6, 4, 5, 2),
(7, 8, 6, 1),
(8, 7, 6, 2),
(9, 2, 6, 2),
(10, 7, 7, 1),
(11, 4, 7, 2),
(12, 1, 7, 2),
(13, 7, 8, 1),
(14, 1, 8, 2),
(15, 3, 8, 2),
(16, 8, 9, 1),
(17, 1, 9, 2),
(18, 5, 9, 2),
(19, 7, 10, 1),
(20, 4, 10, 2),
(21, 2, 10, 2),
(22, 7, 11, 1),
(23, 4, 11, 2),
(24, 2, 11, 2),
(25, 8, 12, 1),
(26, 5, 12, 2),
(27, 6, 12, 2),
(28, 7, 13, 1),
(29, 5, 13, 2),
(33, 7, 16, 1),
(34, 4, 16, 2),
(35, 7, 17, 1),
(36, 1, 17, 2),
(37, 7, 18, 1),
(38, 4, 18, 2),
(39, 7, 19, 1),
(40, 4, 19, 2),
(41, 7, 20, 1),
(42, 4, 20, 2),
(49, 7, 23, 1),
(50, 4, 23, 2),
(51, 7, 28, 1),
(52, 3, 28, 2),
(53, 7, 29, 1),
(54, 5, 29, 2),
(55, 7, 31, 1),
(56, 6, 31, 2),
(57, 7, 34, 1),
(58, 1, 34, 2),
(59, 7, 36, 1),
(60, 1, 36, 2),
(61, 7, 37, 1),
(62, 1, 37, 2),
(63, 4, 37, 2),
(64, 7, 38, 1),
(65, 1, 38, 2),
(66, 4, 38, 2),
(67, 7, 39, 1),
(68, 4, 39, 2),
(69, 3, 39, 2),
(70, 7, 40, 1),
(71, 3, 40, 2),
(72, 7, 42, 1),
(73, 1, 42, 2),
(74, 3, 42, 2),
(75, 7, 41, 1),
(76, 4, 41, 2),
(77, 7, 44, 1),
(78, 1, 44, 2),
(79, 4, 44, 2),
(90, 7, 15, 1),
(91, 1, 15, 2),
(92, 5, 15, 2),
(93, 7, 3, 1),
(94, 5, 3, 2),
(96, 7, 45, 1),
(97, 5, 45, 2),
(98, 7, 47, 1),
(99, 5, 47, 2),
(100, 7, 48, 1),
(101, 4, 48, 2),
(102, 7, 49, 1),
(103, 3, 49, 2),
(104, 7, 50, 1),
(105, 1, 50, 2),
(106, 7, 51, 1),
(107, 5, 51, 2),
(108, 7, 52, 1),
(109, 5, 52, 2);

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_status_surat`
--

CREATE TABLE `riwayat_status_surat` (
  `id` bigint UNSIGNED NOT NULL,
  `id_status_surat` bigint UNSIGNED NOT NULL,
  `id_surat` bigint UNSIGNED NOT NULL,
  `tanggal_rilis` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `riwayat_status_surat`
--

INSERT INTO `riwayat_status_surat` (`id`, `id_status_surat`, `id_surat`, `tanggal_rilis`) VALUES
(1, 2, 1, '2025-05-27 10:58:25'),
(3, 2, 3, '2025-06-22 13:56:12'),
(4, 3, 4, '2025-05-27 12:06:44'),
(5, 2, 5, '2025-05-27 12:10:56'),
(6, 2, 6, '2025-05-27 12:14:06'),
(7, 2, 7, '2025-05-27 12:28:04'),
(8, 2, 8, '2025-05-27 12:37:32'),
(9, 2, 9, '2025-05-27 12:46:48'),
(10, 2, 10, '2025-05-29 00:01:37'),
(11, 2, 11, '2025-05-29 01:09:45'),
(12, 2, 12, '2025-05-29 06:34:29'),
(13, 2, 13, '2025-06-15 07:28:35'),
(15, 2, 15, '2025-06-22 13:54:52'),
(16, 2, 16, '2025-06-15 08:09:03'),
(17, 2, 17, '2025-06-15 16:30:42'),
(18, 2, 18, '2025-06-15 16:36:24'),
(19, 3, 19, '2025-06-15 17:10:11'),
(20, 3, 20, '2025-06-16 00:18:03'),
(21, 3, 23, '2025-06-16 20:06:49'),
(22, 3, 28, '2025-06-16 20:20:32'),
(23, 3, 29, '2025-06-16 20:20:48'),
(24, 3, 31, '2025-06-16 20:23:45'),
(25, 3, 34, '2025-06-16 20:35:59'),
(26, 3, 36, '2025-06-16 20:39:55'),
(27, 3, 37, '2025-06-22 03:13:52'),
(28, 2, 38, '2025-06-22 03:16:21'),
(29, 2, 39, '2025-06-22 03:43:08'),
(30, 2, 40, '2025-06-22 04:02:25'),
(31, 2, 41, '2025-06-22 05:59:29'),
(32, 2, 44, '2025-06-22 13:41:38'),
(33, 2, 45, '2025-06-22 14:03:36'),
(34, 11, 9, '2025-06-23 18:15:49'),
(35, 10, 9, '2025-06-23 18:15:50'),
(36, 11, 39, '2025-06-23 18:17:48'),
(37, 6, 39, '2025-06-23 18:17:49'),
(38, 1, 9, '2025-06-24 01:40:22'),
(39, 2, 47, '2025-06-25 16:45:40'),
(40, 11, 47, '2025-06-25 16:45:53'),
(41, 6, 47, '2025-06-25 16:45:54'),
(42, 2, 48, '2025-06-27 08:16:48'),
(43, 11, 48, '2025-06-27 08:16:49'),
(44, 6, 48, '2025-06-27 08:16:50'),
(45, 10, 48, '2025-06-27 01:31:21'),
(46, 2, 49, '2025-06-27 08:33:37'),
(47, 11, 49, '2025-06-27 08:33:38'),
(48, 6, 49, '2025-06-27 08:33:39'),
(49, 2, 50, '2025-06-28 10:53:25'),
(50, 11, 3, '2025-06-22 13:56:13'),
(51, 6, 3, '2025-06-22 13:56:14'),
(52, 1, 1, '2025-06-28 13:31:59'),
(53, 2, 51, '2025-06-28 15:13:18'),
(54, 2, 52, '2025-06-28 16:02:25'),
(55, 11, 6, '2025-05-27 12:14:07'),
(56, 6, 6, '2025-05-27 12:14:08'),
(57, 11, 45, '2025-06-22 14:03:37'),
(58, 6, 45, '2025-06-22 14:03:38'),
(59, 10, 45, '2025-06-29 09:26:21'),
(60, 11, 52, '2025-06-28 16:02:26'),
(61, 6, 52, '2025-06-28 16:02:27'),
(62, 10, 52, '2025-06-29 09:47:01'),
(63, 1, 45, '2025-06-29 09:54:44'),
(64, 10, 48, '2025-06-29 10:47:38');

-- --------------------------------------------------------

--
-- Table structure for table `role_pengusul`
--

CREATE TABLE `role_pengusul` (
  `id_role_pengusul` bigint UNSIGNED NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_pengusul`
--

INSERT INTO `role_pengusul` (`id_role_pengusul`, `role`) VALUES
(1, 'Dosen'),
(2, 'Mahasiswa');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id_staff` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nip` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('Tata Usaha','Staff Umum') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id_staff`, `nama`, `nip`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Staff Umum', '1234', 'staffumum@gmail.com', '$2y$12$1TK55dLR1JZeKU2A2GQOLu3ukPdVuFhmlzyv8AYXpeYwBPIxM1fqK', 'Staff Umum', '2025-05-27 10:50:40', '2025-05-02 01:41:57'),
(2, 'Tata Usaha', '12345', 'tatausaha@gmail.com', '$2y$12$ZYJPHXotVlVSOkvQbYUEW.Q8cZWOGLBvP/fdhc02jgVdqOg1rMK6S', 'Tata Usaha', '2025-05-27 10:50:40', '2025-05-27 10:50:40');

-- --------------------------------------------------------

--
-- Table structure for table `status_surat`
--

CREATE TABLE `status_surat` (
  `id_status_surat` bigint UNSIGNED NOT NULL,
  `status_surat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `status_surat`
--

INSERT INTO `status_surat` (`id_status_surat`, `status_surat`) VALUES
(1, 'Diterbitkan'),
(2, 'Diajukan'),
(3, 'Draft'),
(4, 'Diterima'),
(5, 'Ditolak'),
(6, 'Menunggu Persetujuan'),
(10, 'Menunggu Penerbitan'),
(11, 'Divalidasi');

-- --------------------------------------------------------

--
-- Table structure for table `surat`
--

CREATE TABLE `surat` (
  `id_surat` bigint UNSIGNED NOT NULL,
  `nomor_surat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `judul_surat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_pengajuan` date NOT NULL,
  `tanggal_surat_dibuat` date DEFAULT NULL,
  `id_jenis_surat` bigint UNSIGNED DEFAULT NULL,
  `dibuat_oleh` bigint UNSIGNED DEFAULT NULL,
  `deskripsi` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan_surat` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_draft` tinyint(1) NOT NULL DEFAULT '0',
  `lampiran` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `surat`
--

INSERT INTO `surat` (`id_surat`, `nomor_surat`, `judul_surat`, `tanggal_pengajuan`, `tanggal_surat_dibuat`, `id_jenis_surat`, `dibuat_oleh`, `deskripsi`, `tujuan_surat`, `is_draft`, `lampiran`, `created_at`, `updated_at`) VALUES
(1, 'TGS-2025', 'Uji Coba', '2025-05-27', NULL, 1, 4, 'ini tes', NULL, 1, 'lampiran/fDLBwXq2Vxj5RGDRqjJEluJ3cuybnYzMnKDkUFF9.pdf', '2025-05-27 10:58:25', '2025-05-27 10:58:25'),
(3, NULL, 'ini id 3 sebelumnya', '2025-06-22', NULL, 2, 4, 'aetetat', 'etaetata', 1, NULL, '2025-05-27 11:24:48', '2025-06-22 06:56:12'),
(4, NULL, 'Uji Coba 2', '2025-05-27', NULL, 1, 4, 'tes 2', NULL, 1, NULL, '2025-05-27 12:06:44', '2025-05-27 12:06:44'),
(5, NULL, 'Uji Coba 3', '2025-05-27', NULL, 1, 4, 'tes 3', NULL, 1, NULL, '2025-05-27 12:10:56', '2025-05-27 12:10:56'),
(6, NULL, 'tes 4', '2025-05-27', NULL, 2, 4, 'tes 4', NULL, 1, 'lampiran/3GiXV4nisBuaai25bFjHUk45cmgLt22Hu58tO2rB.docx', '2025-05-27 12:14:06', '2025-05-27 12:14:06'),
(7, NULL, 'Uji Coba 5', '2025-05-27', NULL, 1, 4, 'tes 5', NULL, 1, 'lampiran/9vK1jDofm69uCl7Rh4AyfYTgMoYZJrsBnneKnv1o.docx', '2025-05-27 12:28:04', '2025-05-27 12:28:04'),
(8, NULL, 'uji coba dosen', '2025-05-27', NULL, 2, 7, 'tes dosn', NULL, 1, 'lampiran/sDTc0sV01UqBnuEIGilIMQYFmCoKVuTdkvm7udvj.pdf', '2025-05-27 12:37:32', '2025-05-27 12:37:32'),
(9, 'TGS TGS', 'tes 6', '2025-05-27', '2025-06-27', 1, 7, 'tes 6', 'tes 6', 1, NULL, '2025-05-27 12:46:48', '2025-05-27 12:46:48'),
(10, NULL, 'ujii 7', '2025-05-29', NULL, 1, 4, 'uji 7', NULL, 1, NULL, '2025-05-29 00:01:37', '2025-05-29 00:01:37'),
(11, NULL, 'Uji Coba 8', '2025-05-29', NULL, 1, 4, 'uji 8', NULL, 1, NULL, '2025-05-29 01:09:45', '2025-05-29 01:09:45'),
(12, NULL, 'Uji Coba 9', '2025-05-29', NULL, 2, 7, '99', NULL, 1, NULL, '2025-05-29 06:34:29', '2025-05-29 06:34:29'),
(13, NULL, 'asdasda', '2025-06-15', NULL, 2, 4, 'ssss', NULL, 1, 'lampiran/MbrP2AdhjZ6W2pT9VFZJZTVGh2Bo64pph2xHXog9.docx', '2025-06-15 07:28:20', '2025-06-15 07:28:35'),
(15, NULL, 'Tes pengajuan dari draft', '2025-06-22', NULL, 2, 4, 'SEMANGAT', 'PT AJUAJUAJU', 1, 'lampiran/ZqS41tIq9eUxlsRZlkojYcWMqU5xtZbgUS8xQcGE.xlsx', '2025-06-15 07:37:25', '2025-06-22 06:54:52'),
(16, NULL, 'Uji Coba 9999', '2025-06-15', NULL, 1, 4, 'ssssss', NULL, 1, 'lampiran/8xHkK6CDtiVio1QAaFAmOVu3kWTgigJVJW9vYo2a.xlsx', '2025-06-15 08:03:10', '2025-06-15 08:09:03'),
(17, NULL, 'status', '2025-06-15', NULL, 1, 4, 'ssss', NULL, 1, NULL, '2025-06-15 16:30:42', '2025-06-15 16:30:42'),
(18, NULL, 'status 2', '2025-06-15', NULL, 2, 4, 'statuys', NULL, 1, NULL, '2025-06-15 16:36:24', '2025-06-15 16:36:24'),
(19, NULL, 'statuys 3', '2025-06-16', NULL, 2, 4, 'asdwadwda', NULL, 1, NULL, '2025-06-15 17:10:11', '2025-06-15 17:10:11'),
(20, NULL, 'tes status 111', '2025-06-16', NULL, 1, 4, 'asdawdwa', NULL, 1, NULL, '2025-06-15 17:18:03', '2025-06-15 17:18:03'),
(23, NULL, 'tespu', '2025-06-17', NULL, 1, 2, 'asdwad', NULL, 1, 'lampiran/HUwvNWw9BS2nWrL9GawGL6rPB5Hae5HNOWgFLLdZ.pdf', '2025-06-16 13:06:49', '2025-06-16 13:06:49'),
(28, NULL, 'tes drasss', '2025-06-17', NULL, 1, 7, 'adawdwa', NULL, 1, 'lampiran/KiqvGTyrazaI72t3tFjcXBJ4gQRVeT0Lao9O003W.docx', '2025-06-16 13:20:32', '2025-06-16 13:20:32'),
(29, NULL, 'wadadwd', '2025-06-17', NULL, 2, 7, 'waddaw', NULL, 1, NULL, '2025-06-16 13:20:48', '2025-06-16 13:20:48'),
(31, NULL, 'awdwd', '2025-06-17', NULL, 2, 7, 'adwadwa', NULL, 1, NULL, '2025-06-16 13:23:45', '2025-06-16 13:23:45'),
(34, NULL, 'adawd', '2025-06-17', NULL, 1, 7, 'wadawdwad', NULL, 1, NULL, '2025-06-16 13:35:59', '2025-06-16 13:35:59'),
(36, NULL, 'tes12312312', '2025-06-17', NULL, 1, 7, 'adawd', NULL, 1, NULL, '2025-06-16 13:39:55', '2025-06-16 13:39:55'),
(37, NULL, 'tes status surat', '2025-06-22', NULL, 8, 4, 'awadwadawdwad', NULL, 1, NULL, '2025-06-21 20:13:52', '2025-06-21 20:13:52'),
(38, NULL, 'uji coba status surat', '2025-06-22', NULL, 1, 4, 'wadawdawdaw', NULL, 1, NULL, '2025-06-21 20:16:21', '2025-06-21 20:16:21'),
(39, NULL, 'tes', '2025-06-22', NULL, 27, 7, 'Surat yang ditujukan kepada individu atau instansi lain sebagai undangan resmi untuk menghadiri acara yang diselenggarakan oleh fakultas/universitas (contoh: seminar, pelatihan, diskusi ilmiah).', NULL, 1, NULL, '2025-06-21 20:43:08', '2025-06-21 20:43:08'),
(40, NULL, 'tes', '2025-06-22', NULL, 29, 4, 'tes', NULL, 1, NULL, '2025-06-21 21:02:25', '2025-06-21 21:02:25'),
(41, NULL, 'Penugasan Surat Di PT Sukajadi', '2025-06-22', NULL, 1, 4, 'Surat INI DITUJUKAN KEPADA PT  SUKAJADI', 'PT Sukajadi', 1, NULL, '2025-06-21 21:07:54', '2025-06-21 22:59:29'),
(42, NULL, 'Permintaan Permohonan', '2025-06-22', NULL, 2, 4, 'Surat Permohonan yang ditujukan kepada PT tertera', 'PT Permohonan Persatuan Hidup', 1, 'lampiran/Jx97330eAJlDAC6wYGXKwBGsDx8NAce97hKivdaA.docx', '2025-06-21 21:24:39', '2025-06-21 22:20:11'),
(43, NULL, 'draft dosen', '2025-06-22', NULL, NULL, 7, NULL, NULL, 0, NULL, '2025-06-22 06:25:42', '2025-06-22 06:25:42'),
(44, NULL, 'pengajuan after sedikit update yey', '2025-06-22', NULL, 1, 4, 'asdasdadadadsd', 'PT BERSAMA', 1, 'lampiran/Gvvgz5MfqLrkYHQ9xhsuQ7onnESBI2fXB1GDg92J.docx', '2025-06-22 06:41:38', '2025-06-22 06:41:38'),
(45, 'teskjeyua', 'tes jkeyua', '2025-06-22', NULL, 29, 4, 'izin ketua', NULL, 1, 'lampiran/AmT0MmK54pXmD4xKSvpsay9hgcntF8m5c8CO6TaH.pdf', '2025-06-22 06:57:34', '2025-06-29 09:54:44'),
(46, NULL, 'terstes draft', '2025-06-23', NULL, NULL, 4, NULL, NULL, 0, NULL, '2025-06-23 05:31:19', '2025-06-23 05:31:19'),
(47, NULL, 'tes swdadawd', '2025-06-25', NULL, 1, 7, 'dwadawdwa', 'dsdwadaw', 1, NULL, '2025-06-25 09:45:40', '2025-06-25 09:45:40'),
(48, NULL, 'Surat Tugas KEPADA PT PTPTTPPTT', '2025-06-27', NULL, 1, 7, 'INI ADALAH SURAT YANG DITUJUKAN KEPADA PT PTPTPTPTPT', 'PTPTPTPTPT', 1, 'lampiran/usuaA6qBiz1SCH91EpC5KxRVWP2vNhIO5cryuASl.pdf', '2025-06-27 01:16:48', '2025-06-27 01:16:48'),
(49, NULL, 'tes surat batu PTPTPTPT', '2025-06-27', NULL, 27, 7, 'PTPTPWADWADAWDWA', 'awdwadsadasdwa', 1, 'lampiran/giSwgmkNbCu073miHKYZ5LwaM2xC7dniRzLl5uoe.docx', '2025-06-27 01:33:37', '2025-06-27 01:33:37'),
(50, NULL, 'tesawdw adawdwadasdsa', '2025-06-28', NULL, 2, 4, 'wadasdawd', 'adawdasd', 1, 'lampiran/o1IUAKnlI5bENAMKGs6QUm5S3UcihOWqYLAHYMVX.pdf', '2025-06-28 03:53:25', '2025-06-28 03:53:25'),
(51, NULL, 'dikajagongoding1234567', '2025-06-28', NULL, 1, 4, 'dadadwadwadwad', 'wadwadwa', 1, 'lampiran/MCrmh6S5NnZJgN5Ds2cs3hNEUNrtfsTT7KAmfmyY.pdf', '2025-06-28 08:13:18', '2025-06-28 08:13:18'),
(52, NULL, 'teasdadwad', '2025-06-28', NULL, 1, 7, 'dwadwadwa', 'wadwadwadwa', 1, NULL, '2025-06-28 09:02:25', '2025-06-28 09:02:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `admin_username_unique` (`username`),
  ADD UNIQUE KEY `admin_email_unique` (`email`);

--
-- Indexes for table `jenis_surat`
--
ALTER TABLE `jenis_surat`
  ADD PRIMARY KEY (`id_jenis_surat`);

--
-- Indexes for table `kepala_sub`
--
ALTER TABLE `kepala_sub`
  ADD PRIMARY KEY (`id_kepala_sub`),
  ADD UNIQUE KEY `kepala_sub_nip_unique` (`nip`),
  ADD UNIQUE KEY `kepala_sub_email_unique` (`email`);

--
-- Indexes for table `komentar_surat`
--
ALTER TABLE `komentar_surat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `komentar_surat_id_riwayat_status_surat_foreign` (`id_riwayat_status_surat`),
  ADD KEY `komentar_surat_id_surat_foreign` (`id_surat`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD KEY `password_reset_tokens_email_index` (`email`);

--
-- Indexes for table `pengusul`
--
ALTER TABLE `pengusul`
  ADD PRIMARY KEY (`id_pengusul`),
  ADD UNIQUE KEY `pengusul_email_unique` (`email`),
  ADD UNIQUE KEY `pengusul_nim_unique` (`nim`),
  ADD UNIQUE KEY `pengusul_nip_unique` (`nip`),
  ADD KEY `pengusul_id_role_pengusul_foreign` (`id_role_pengusul`);

--
-- Indexes for table `peran_anggota`
--
ALTER TABLE `peran_anggota`
  ADD PRIMARY KEY (`id_peran_keanggotaan`);

--
-- Indexes for table `pivot_pengusul_surat`
--
ALTER TABLE `pivot_pengusul_surat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pivot_pengusul_surat_id_pengusul_foreign` (`id_pengusul`),
  ADD KEY `pivot_pengusul_surat_id_surat_foreign` (`id_surat`),
  ADD KEY `pivot_pengusul_surat_id_peran_keanggotaan_foreign` (`id_peran_keanggotaan`);

--
-- Indexes for table `riwayat_status_surat`
--
ALTER TABLE `riwayat_status_surat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `riwayat_status_surat_id_status_surat_foreign` (`id_status_surat`),
  ADD KEY `riwayat_status_surat_id_surat_foreign` (`id_surat`);

--
-- Indexes for table `role_pengusul`
--
ALTER TABLE `role_pengusul`
  ADD PRIMARY KEY (`id_role_pengusul`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id_staff`),
  ADD UNIQUE KEY `staff_email_unique` (`email`),
  ADD UNIQUE KEY `staff_nip_unique` (`nip`);

--
-- Indexes for table `status_surat`
--
ALTER TABLE `status_surat`
  ADD PRIMARY KEY (`id_status_surat`);

--
-- Indexes for table `surat`
--
ALTER TABLE `surat`
  ADD PRIMARY KEY (`id_surat`),
  ADD KEY `surat_id_jenis_surat_foreign` (`id_jenis_surat`),
  ADD KEY `surat_dibuat_oleh_foreign` (`dibuat_oleh`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jenis_surat`
--
ALTER TABLE `jenis_surat`
  MODIFY `id_jenis_surat` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `kepala_sub`
--
ALTER TABLE `kepala_sub`
  MODIFY `id_kepala_sub` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `komentar_surat`
--
ALTER TABLE `komentar_surat`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `pengusul`
--
ALTER TABLE `pengusul`
  MODIFY `id_pengusul` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `peran_anggota`
--
ALTER TABLE `peran_anggota`
  MODIFY `id_peran_keanggotaan` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pivot_pengusul_surat`
--
ALTER TABLE `pivot_pengusul_surat`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `riwayat_status_surat`
--
ALTER TABLE `riwayat_status_surat`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `role_pengusul`
--
ALTER TABLE `role_pengusul`
  MODIFY `id_role_pengusul` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id_staff` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `status_surat`
--
ALTER TABLE `status_surat`
  MODIFY `id_status_surat` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `surat`
--
ALTER TABLE `surat`
  MODIFY `id_surat` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `komentar_surat`
--
ALTER TABLE `komentar_surat`
  ADD CONSTRAINT `komentar_surat_id_riwayat_status_surat_foreign` FOREIGN KEY (`id_riwayat_status_surat`) REFERENCES `riwayat_status_surat` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `komentar_surat_id_surat_foreign` FOREIGN KEY (`id_surat`) REFERENCES `surat` (`id_surat`) ON DELETE CASCADE;

--
-- Constraints for table `pengusul`
--
ALTER TABLE `pengusul`
  ADD CONSTRAINT `pengusul_id_role_pengusul_foreign` FOREIGN KEY (`id_role_pengusul`) REFERENCES `role_pengusul` (`id_role_pengusul`);

--
-- Constraints for table `pivot_pengusul_surat`
--
ALTER TABLE `pivot_pengusul_surat`
  ADD CONSTRAINT `pivot_pengusul_surat_id_pengusul_foreign` FOREIGN KEY (`id_pengusul`) REFERENCES `pengusul` (`id_pengusul`),
  ADD CONSTRAINT `pivot_pengusul_surat_id_peran_keanggotaan_foreign` FOREIGN KEY (`id_peran_keanggotaan`) REFERENCES `peran_anggota` (`id_peran_keanggotaan`),
  ADD CONSTRAINT `pivot_pengusul_surat_id_surat_foreign` FOREIGN KEY (`id_surat`) REFERENCES `surat` (`id_surat`);

--
-- Constraints for table `riwayat_status_surat`
--
ALTER TABLE `riwayat_status_surat`
  ADD CONSTRAINT `riwayat_status_surat_id_status_surat_foreign` FOREIGN KEY (`id_status_surat`) REFERENCES `status_surat` (`id_status_surat`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `riwayat_status_surat_id_surat_foreign` FOREIGN KEY (`id_surat`) REFERENCES `surat` (`id_surat`);

--
-- Constraints for table `surat`
--
ALTER TABLE `surat`
  ADD CONSTRAINT `surat_dibuat_oleh_foreign` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengusul` (`id_pengusul`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `surat_id_jenis_surat_foreign` FOREIGN KEY (`id_jenis_surat`) REFERENCES `jenis_surat` (`id_jenis_surat`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
