-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 06, 2025 at 05:58 PM
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

-- --------------------------------------------------------

--
-- Table structure for table `surat`
--

DROP TABLE IF EXISTS `surat`;
CREATE TABLE IF NOT EXISTS `surat` (
  `id_surat` int NOT NULL AUTO_INCREMENT,
  `nomor_surat` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `judul_surat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_pengajuan` date NOT NULL,
  `tanggal_surat_dibuat` date DEFAULT NULL,
  `id_jenis_surat` int DEFAULT NULL,
  `dibuat_oleh` int DEFAULT NULL,
  `deskripsi` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_draft` tinyint(1) NOT NULL DEFAULT '0',
  `lampiran` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_surat`),
  KEY `id_jenis_surat` (`id_jenis_surat`),
  KEY `surat_ibfk_2` (`dibuat_oleh`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(41, NULL, 'inidraf', '2025-05-06', NULL, 1, 4, 'inidraf', 1, 'lampiran/Tn7Wt0tfVO06hYKems2F8awFOkftj0dNBsw1ROzF.png', '2025-05-06 06:02:08', '2025-05-06 06:03:10'),
(42, NULL, 'awdawd', '2025-05-06', NULL, 1, 4, 'adawdaw', 1, 'lampiran/Ri9CODbs1OWgdZcbsdeppcI6c5bmz11Y7vYG3bsI.docx', '2025-05-06 10:24:19', '2025-05-06 10:24:19');

--
-- Constraints for dumped tables
--

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
