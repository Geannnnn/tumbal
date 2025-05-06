-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 06, 2025 at 04:14 PM
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
-- Database: `db_surat`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddPengusul` (IN `p_nama` VARCHAR(255), IN `p_nim` CHAR(20), IN `p_password` VARCHAR(255), IN `p_id_role_pengusul` INT, IN `p_id_unit` INT)   BEGIN
    INSERT INTO pengusul (nama, nim, password, id_role_pengusul, id_unit)
    VALUES (p_nama, p_nim, p_password, p_id_role_pengusul, p_id_unit);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `AddSurat` (IN `p_nomor_surat` VARCHAR(255), IN `p_judul_surat` VARCHAR(255), IN `p_tanggal_pengajuan` DATE, IN `p_tanggal_surat_dibuat` DATE, IN `p_id_jenis_surat` INT, IN `p_deskripsi` TEXT, IN `p_lampiran` VARCHAR(255))   BEGIN
    INSERT INTO surat (nomor_surat, judul_surat, tanggal_pengajuan, tanggal_surat_dibuat, id_jenis_surat, deskripsi, lampiran)
    VALUES (p_nomor_surat, p_judul_surat, p_tanggal_pengajuan, p_tanggal_surat_dibuat, p_id_jenis_surat, p_deskripsi, p_lampiran);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `DeleteSurat` (IN `p_id_surat` INT)   BEGIN
    DELETE FROM surat WHERE id_surat = p_id_surat;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GetRiwayatStatusSurat` (IN `p_id_surat` INT)   BEGIN
    SELECT rs.id_status_surat, ss.status_surat, rs.tanggal_rilis
    FROM riwayat_status_surat rs
    JOIN status_surat ss ON rs.id_status_surat = ss.id_status_surat
    WHERE rs.id_surat = p_id_surat
    ORDER BY rs.tanggal_rilis DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `UpdateStatusSurat` (IN `p_id_status_surat` INT, IN `p_id_surat` INT)   BEGIN
    INSERT INTO riwayat_status_surat (id_surat, id_status_surat, tanggal_rilis)
    VALUES (p_id_surat, p_id_status_surat, NOW());
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `GetNamaPengusul` (`p_id_pengusul` INT) RETURNS VARCHAR(255) CHARSET utf8mb4 DETERMINISTIC BEGIN
    DECLARE nama_pengusul VARCHAR(255);
    
    SELECT nama INTO nama_pengusul
    FROM pengusul
    WHERE id_pengusul = p_id_pengusul;
    
    RETURN nama_pengusul;
END$$

CREATE DEFINER=`root`@`localhost` FUNCTION `GetStatusSurat` (`p_id_surat` INT) RETURNS VARCHAR(255) CHARSET utf8mb4 DETERMINISTIC BEGIN
    DECLARE status VARCHAR(255);
    
    SELECT ss.status_surat 
    INTO status
    FROM riwayat_status_surat rs
    JOIN status_surat ss ON rs.id_status_surat = ss.id_status_surat
    WHERE rs.id_surat = p_id_surat
    ORDER BY rs.tanggal_rilis DESC
    LIMIT 1;
    
    RETURN status;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jenis_surat`
--

CREATE TABLE `jenis_surat` (
  `id_jenis_surat` int NOT NULL,
  `jenis_surat` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jenis_surat`
--

INSERT INTO `jenis_surat` (`id_jenis_surat`, `jenis_surat`) VALUES
(1, 'Surat Tugas'),
(2, 'Surat Permohonan');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
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
(7, '0001_01_01_000000_create_users_table', 1),
(8, '0001_01_01_000001_create_cache_table', 1),
(9, '0001_01_01_000002_create_jobs_table', 1),
(10, '2025_05_02_082136_add_timestamps_to_pengusul_table', 1),
(11, '2025_05_06_065406_add_is_draft_to_surat_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengusul`
--

CREATE TABLE `pengusul` (
  `id_pengusul` int NOT NULL,
  `nama` varchar(255) NOT NULL,
  `nim` char(20) DEFAULT NULL,
  `nip` char(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `id_role_pengusul` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pengusul`
--

INSERT INTO `pengusul` (`id_pengusul`, `nama`, `nim`, `nip`, `password`, `id_role_pengusul`, `email`, `created_at`, `updated_at`) VALUES
(1, 'Adhyca', '4342401080', NULL, '$2y$12$zKO9jUVN.o1NWLYVToaG2OZhW7EFQFuHA47o6yxf4FKfeagOdcqD6', 2, '', NULL, NULL),
(2, 'Putri', '4342401062', NULL, '$2y$12$9vEmSQFu7bBeXx81x8D0T.Q7E3BanrjbcfK8pYKLq7J5zoV.Sfdiy', 2, 'putri@gmail.com', NULL, NULL),
(3, 'Fahri', '4342401073', NULL, '$2y$12$jnaW31F1V7AQ/JbnFsPH5.yGDjti7/DsnfozMZSiQTt0vLvB6uyUO', 2, '', NULL, NULL),
(4, 'Ali', '4342401078', NULL, '$2y$12$UvMcamaVEYHpvcV16SLwHeaaY2T97rW/0obM4Q9ez/U0OofLcDdKu', 2, 'ali@gmail.com', NULL, NULL),
(5, 'Herman', '4342401084', NULL, '$2y$12$C53T4qRqsphUYSRjp4hMcuDrxoRNHIRwZ2JHRI1j6UEdUZ925fwlO', 2, '', NULL, NULL),
(6, 'Aqilah', '4342401087', NULL, '$2y$12$0RXOMrt38cNCkatIksUO7ucsCUn.xMRgsC.2vc2AL5dz0rTmCw15q', 2, '', NULL, NULL),
(7, 'Supardianto, S.ST., M.Eng', NULL, '113105', '$2y$12$LOY57vYfe.tXYj.Zg9EWzuwTAe9oSoJ/d2Mo5WnGngjzrfqoPCshi', 1, 'Supardianto@gmail.com', NULL, NULL),
(8, 'Gilang Bagus Ramadhan, A.Md.Kom', NULL, '222331', '$2y$12$E572pZF2N/ih8HWqreCPVuV9JpZ67gm5OOkVuW7GYgz3FIejlQ.Pq', 1, '', NULL, NULL);

--
-- Triggers `pengusul`
--
DELIMITER $$
CREATE TRIGGER `before_insert_default_role` BEFORE INSERT ON `pengusul` FOR EACH ROW BEGIN
  IF NEW.id_role_pengusul IS NULL THEN
    SET NEW.id_role_pengusul = 2;
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_insert_pengusul` BEFORE INSERT ON `pengusul` FOR EACH ROW BEGIN
  IF EXISTS (SELECT 1 FROM pengusul WHERE nim = NEW.nim) THEN
    SIGNAL SQLSTATE '45000'
    SET MESSAGE_TEXT = 'NIM sudah digunakan oleh pengusul lain.';
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `peran_anggota`
--

CREATE TABLE `peran_anggota` (
  `id_peran_keanggotaan` int NOT NULL,
  `peran` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
  `id` int NOT NULL,
  `id_pengusul` int NOT NULL,
  `id_surat` int NOT NULL,
  `id_peran_keanggotaan` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pivot_pengusul_surat`
--

INSERT INTO `pivot_pengusul_surat` (`id`, `id_pengusul`, `id_surat`, `id_peran_keanggotaan`) VALUES
(14, 7, 18, 1),
(15, 4, 18, 2),
(16, 2, 18, 2),
(17, 7, 19, 1),
(18, 4, 19, 2),
(19, 2, 19, 2),
(20, 1, 19, 2),
(21, 3, 19, 2),
(22, 5, 19, 2),
(23, 6, 19, 2),
(24, 7, 20, 1),
(25, 4, 20, 2),
(26, 2, 20, 2),
(27, 8, 21, 1),
(28, 2, 21, 2),
(29, 7, 22, 1),
(30, 4, 22, 2),
(31, 1, 22, 2),
(32, 7, 23, 1),
(33, 4, 23, 2),
(34, 2, 23, 2),
(35, 1, 23, 2),
(36, 7, 29, 1),
(37, 5, 29, 2),
(38, 1, 29, 2),
(39, 7, 30, 1),
(40, 4, 30, 2),
(41, 7, 32, 1),
(42, 4, 32, 2),
(43, 8, 33, 1),
(44, 4, 33, 2),
(45, 3, 33, 2),
(46, 7, 34, 1),
(47, 4, 34, 2),
(48, 5, 34, 2);

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_status_surat`
--

CREATE TABLE `riwayat_status_surat` (
  `id` int NOT NULL,
  `id_status_surat` int NOT NULL,
  `id_surat` int NOT NULL,
  `tanggal_rilis` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `riwayat_status_surat`
--

INSERT INTO `riwayat_status_surat` (`id`, `id_status_surat`, `id_surat`, `tanggal_rilis`) VALUES
(5, 3, 18, '2025-05-04 00:47:22'),
(6, 3, 19, '2025-05-04 06:50:31'),
(7, 3, 20, '2025-05-04 09:08:21'),
(8, 3, 21, '2025-05-04 09:16:45'),
(9, 3, 22, '2025-05-04 09:21:39'),
(10, 3, 23, '2025-05-05 00:23:38'),
(16, 2, 29, '2025-05-06 01:50:51'),
(17, 2, 30, '2025-05-06 01:52:26'),
(19, 2, 32, '2025-05-06 01:57:19'),
(20, 2, 33, '2025-05-06 02:03:28'),
(21, 2, 34, '2025-05-06 02:14:23'),
(23, 3, 36, '2025-05-06 02:16:19'),
(26, 3, 39, '2025-05-06 05:01:51'),
(27, 3, 40, '2025-05-06 05:12:09'),
(28, 3, 41, '2025-05-06 06:02:08');

-- --------------------------------------------------------

--
-- Table structure for table `role_pengusul`
--

CREATE TABLE `role_pengusul` (
  `id_role_pengusul` int NOT NULL,
  `role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `role_pengusul`
--

INSERT INTO `role_pengusul` (`id_role_pengusul`, `role`) VALUES
(1, 'Dosen'),
(2, 'Mahasiswa');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id_staff` int NOT NULL,
  `nama` varchar(255) NOT NULL,
  `nip` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Tata Usaha','Staff Umum') NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id_staff`, `nama`, `nip`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Staff Umum', '1234', 'staffumum@gmail.com', '$2y$12$1TK55dLR1JZeKU2A2GQOLu3ukPdVuFhmlzyv8AYXpeYwBPIxM1fqK', 'Staff Umum', NULL, '2025-05-02 08:41:57'),
(2, 'Tata Usaha', '12345', 'tatausaha@gmail.com', '$2y$12$ZYJPHXotVlVSOkvQbYUEW.Q8cZWOGLBvP/fdhc02jgVdqOg1rMK6S', 'Tata Usaha', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `status_surat`
--

CREATE TABLE `status_surat` (
  `id_status_surat` int NOT NULL,
  `status_surat` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `status_surat`
--

INSERT INTO `status_surat` (`id_status_surat`, `status_surat`) VALUES
(1, 'Diterbitkan'),
(2, 'Diajukan'),
(3, 'Draft');

-- --------------------------------------------------------

--
-- Table structure for table `surat`
--

CREATE TABLE `surat` (
  `id_surat` int NOT NULL,
  `nomor_surat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `judul_surat` varchar(255) NOT NULL,
  `tanggal_pengajuan` date NOT NULL,
  `tanggal_surat_dibuat` date DEFAULT NULL,
  `id_jenis_surat` int DEFAULT NULL,
  `dibuat_oleh` int DEFAULT NULL,
  `deskripsi` varchar(300) DEFAULT NULL,
  `is_draft` tinyint(1) NOT NULL DEFAULT '0',
  `lampiran` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `surat`
--

INSERT INTO `surat` (`id_surat`, `nomor_surat`, `judul_surat`, `tanggal_pengajuan`, `tanggal_surat_dibuat`, `id_jenis_surat`, `dibuat_oleh`, `deskripsi`, `is_draft`, `lampiran`, `created_at`, `updated_at`) VALUES
(18, NULL, 'Uji Coba 3123', '2025-05-04', NULL, 1, 4, 'tesdasdwad', 1, 'lampiran/dNG53f0qPXjOFRZwPijElcmPICwbH7gy10FGl7yI.png', '2025-05-04 00:47:22', '2025-05-04 00:47:22'),
(19, NULL, 'Permohonan pengajuan judul pbl', '2025-05-04', NULL, 2, 4, 'Permohonan pengajuan judul pbl 224, dengan berjudul Aplikasi pengelolaan ruang rapat di polibatam', 1, 'lampiran/QZg9yvvM8uZ6V8fqyEMvL9G41ojDSJibtfxuWzT7.pdf', '2025-05-04 06:50:31', '2025-05-04 06:50:31'),
(20, NULL, 'Uji Coba 3312', '2025-05-04', NULL, 1, 7, 'uji coba 3312', 1, NULL, '2025-05-04 09:08:21', '2025-05-04 09:08:21'),
(21, NULL, 'Uji Coba 1231', '2025-05-04', NULL, 2, 2, 'uji coba 1231', 1, NULL, '2025-05-04 09:16:45', '2025-05-04 09:16:45'),
(22, NULL, 'Uji Coba 999', '2025-05-04', NULL, 1, 2, 'adasdwasdad', 1, NULL, '2025-05-04 09:21:39', '2025-05-04 09:21:39'),
(23, NULL, 'Uji Coba 22131', '2025-05-05', NULL, 1, 4, 'uji uji', 1, NULL, '2025-05-05 00:23:38', '2025-05-05 00:23:38'),
(29, NULL, 'Uji Coba habis draft', '2025-05-06', NULL, 1, 4, 'adadadadada', 1, NULL, '2025-05-06 01:50:51', '2025-05-06 01:50:51'),
(30, NULL, 'tes draft 2', '2025-05-06', NULL, 1, 4, 'adadada', 1, NULL, '2025-05-06 01:52:26', '2025-05-06 01:52:26'),
(32, NULL, 'Uji Coba aju 1233', '2025-05-06', NULL, 1, 4, 'sadadwad', 1, NULL, '2025-05-06 01:57:19', '2025-05-06 01:57:19'),
(33, NULL, 'Uji Coba draft tru', '2025-05-06', NULL, 1, 4, 'adwadsw', 1, 'lampiran/oKPoysTAvQMbZmFBGGNn72xJbGOfpRw9zwQvPY6o.pdf', '2025-05-06 02:03:28', '2025-05-06 02:03:28'),
(34, NULL, 'Uji Coba aju 12332', '2025-05-06', NULL, 1, 4, 'adawdawdwa', 1, 'lampiran/FPURMA99apIOskS8cgKYzlNOOAEb3iM1owsf5AHq.docx', '2025-05-06 02:14:23', '2025-05-06 02:14:23'),
(36, NULL, 'tes aju draft 10k', '2025-05-06', NULL, 1, 4, 'tes untuk pivot', 1, 'lampiran/5YP29EuxnSUUj7nyoQ1rjHSpudiEqs4k2m6UJOMh.docx', '2025-05-06 02:16:19', '2025-05-06 06:00:40'),
(39, NULL, 'Uji Coba 223', '2025-05-06', NULL, NULL, 4, NULL, 0, 'lampiran/I7EaVhyNKmjHqwzwSHCcfe3UZ00RDonwZRDRFlJH.docx', '2025-05-06 05:01:51', '2025-05-06 05:01:51'),
(40, NULL, 'Uji Coba draft ke 2000x', '2025-05-06', NULL, 1, 4, 'Uji Coba draft ke 2000x', 1, 'lampiran/Bn1cgvexWUQPeB7PB0aLz0hh3nGYf5mouFJNHLIj.pdf', '2025-05-06 05:12:09', '2025-05-06 05:53:29'),
(41, NULL, 'inidraf', '2025-05-06', NULL, 1, 4, 'inidraf', 1, 'lampiran/Tn7Wt0tfVO06hYKems2F8awFOkftj0dNBsw1ROzF.png', '2025-05-06 06:02:08', '2025-05-06 06:03:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jenis_surat`
--
ALTER TABLE `jenis_surat`
  ADD PRIMARY KEY (`id_jenis_surat`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pengusul`
--
ALTER TABLE `pengusul`
  ADD PRIMARY KEY (`id_pengusul`),
  ADD UNIQUE KEY `nim` (`nim`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD UNIQUE KEY `unique_nim` (`nim`),
  ADD UNIQUE KEY `unique_nip` (`nip`),
  ADD UNIQUE KEY `nip_2` (`nip`),
  ADD KEY `id_role_pengusul` (`id_role_pengusul`);

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
  ADD KEY `id_peran_keanggotaan` (`id_peran_keanggotaan`),
  ADD KEY `id_pengusul` (`id_pengusul`),
  ADD KEY `id_surat` (`id_surat`);

--
-- Indexes for table `riwayat_status_surat`
--
ALTER TABLE `riwayat_status_surat`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_surat` (`id_surat`),
  ADD KEY `id_status_surat` (`id_status_surat`);

--
-- Indexes for table `role_pengusul`
--
ALTER TABLE `role_pengusul`
  ADD PRIMARY KEY (`id_role_pengusul`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id_staff`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD UNIQUE KEY `unique_staff_nip` (`nip`);

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
  ADD KEY `id_jenis_surat` (`id_jenis_surat`),
  ADD KEY `surat_ibfk_2` (`dibuat_oleh`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jenis_surat`
--
ALTER TABLE `jenis_surat`
  MODIFY `id_jenis_surat` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `pengusul`
--
ALTER TABLE `pengusul`
  MODIFY `id_pengusul` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `peran_anggota`
--
ALTER TABLE `peran_anggota`
  MODIFY `id_peran_keanggotaan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pivot_pengusul_surat`
--
ALTER TABLE `pivot_pengusul_surat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `riwayat_status_surat`
--
ALTER TABLE `riwayat_status_surat`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `role_pengusul`
--
ALTER TABLE `role_pengusul`
  MODIFY `id_role_pengusul` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id_staff` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `status_surat`
--
ALTER TABLE `status_surat`
  MODIFY `id_status_surat` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `surat`
--
ALTER TABLE `surat`
  MODIFY `id_surat` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pengusul`
--
ALTER TABLE `pengusul`
  ADD CONSTRAINT `pengusul_ibfk_3` FOREIGN KEY (`id_role_pengusul`) REFERENCES `role_pengusul` (`id_role_pengusul`);

--
-- Constraints for table `pivot_pengusul_surat`
--
ALTER TABLE `pivot_pengusul_surat`
  ADD CONSTRAINT `pivot_pengusul_surat_ibfk_1` FOREIGN KEY (`id_peran_keanggotaan`) REFERENCES `peran_anggota` (`id_peran_keanggotaan`),
  ADD CONSTRAINT `pivot_pengusul_surat_ibfk_2` FOREIGN KEY (`id_pengusul`) REFERENCES `pengusul` (`id_pengusul`),
  ADD CONSTRAINT `pivot_pengusul_surat_ibfk_3` FOREIGN KEY (`id_surat`) REFERENCES `surat` (`id_surat`);

--
-- Constraints for table `riwayat_status_surat`
--
ALTER TABLE `riwayat_status_surat`
  ADD CONSTRAINT `riwayat_status_surat_ibfk_1` FOREIGN KEY (`id_surat`) REFERENCES `surat` (`id_surat`),
  ADD CONSTRAINT `riwayat_status_surat_ibfk_2` FOREIGN KEY (`id_status_surat`) REFERENCES `status_surat` (`id_status_surat`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `surat`
--
ALTER TABLE `surat`
  ADD CONSTRAINT `surat_ibfk_2` FOREIGN KEY (`dibuat_oleh`) REFERENCES `pengusul` (`id_pengusul`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `surat_ibfk_3` FOREIGN KEY (`id_jenis_surat`) REFERENCES `jenis_surat` (`id_jenis_surat`) ON DELETE RESTRICT ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
