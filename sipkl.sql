-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 19, 2026 at 12:39 PM
-- Server version: 8.0.41
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sipkl`
--

-- --------------------------------------------------------

--
-- Table structure for table `bimbingans`
--

CREATE TABLE `bimbingans` (
  `id` bigint UNSIGNED NOT NULL,
  `pembimbing_id` bigint UNSIGNED NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
-- Table structure for table `jurusan`
--

CREATE TABLE `jurusan` (
  `id` bigint UNSIGNED NOT NULL,
  `nama_jurusan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_jurusan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jurusan`
--

INSERT INTO `jurusan` (`id`, `nama_jurusan`, `kode_jurusan`, `created_at`, `updated_at`) VALUES
(1, 'Teknik Jaringan Komputer dan Telekomunikasi', 'TJKT', '2026-04-17 20:15:50', '2026-04-17 20:15:50'),
(2, 'Teknik Kendaraan Ringan', 'TKR', '2026-04-17 20:15:50', '2026-04-17 20:15:50'),
(3, 'Desain Komunikasi Visual', 'DKV', '2026-04-17 20:15:50', '2026-04-17 20:15:50'),
(4, 'Manajemen Perkantoran dan Layanan Bisnis', 'MPLB', '2026-04-17 20:15:50', '2026-04-17 20:15:50');

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` bigint UNSIGNED NOT NULL,
  `nama_kelas` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jurusan_id` bigint UNSIGNED NOT NULL,
  `tingkat` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id`, `nama_kelas`, `jurusan_id`, `tingkat`, `created_at`, `updated_at`) VALUES
(1, 'XI TJKT 1', 1, 11, '2026-04-17 20:15:50', '2026-04-17 20:15:50'),
(2, 'XI TJKT 2', 1, 11, '2026-04-17 20:15:50', '2026-04-17 20:15:50'),
(3, 'XI TJKT 3', 1, 11, '2026-04-17 20:15:50', '2026-04-17 20:15:50'),
(4, 'XI TKR 1', 2, 11, '2026-04-17 20:15:50', '2026-04-17 20:15:50'),
(5, 'XI TKR 2', 2, 11, '2026-04-17 20:15:50', '2026-04-17 20:15:50'),
(6, 'XI TKR 3', 2, 11, '2026-04-17 20:15:50', '2026-04-17 20:15:50'),
(7, 'XI DKV 1', 3, 11, '2026-04-17 20:15:50', '2026-04-17 20:15:50'),
(8, 'XI DKV 2', 3, 11, '2026-04-17 20:15:50', '2026-04-17 20:15:50'),
(9, 'XI DKV 3', 3, 11, '2026-04-17 20:15:50', '2026-04-17 20:15:50'),
(10, 'XI MPLB 1', 4, 11, '2026-04-17 20:15:50', '2026-04-17 20:15:50'),
(11, 'XI MPLB 2', 4, 11, '2026-04-17 20:15:50', '2026-04-17 20:15:50');

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
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2025_04_14_061020_create_jurusan_table', 1),
(6, '2025_04_14_061021_create_kelas_table', 1),
(7, '2025_04_14_061022_create_perusahaan_table', 1),
(8, '2025_04_14_061022_create_siswa_table', 1),
(9, '2025_04_14_075349_create_permission_tables', 1),
(10, '2025_04_15_051115_create_surat_izin_ortus_table', 1),
(11, '2025_08_20_035937_create_pembimbings_table', 1),
(12, '2025_08_20_040920_create_pembimbing_perusahaans_table', 1),
(13, '2025_08_21_071029_create_tempat_pkl_table', 1),
(14, '2025_08_23_045256_create_bimbingans_table', 1),
(15, '2025_08_23_045304_create_monitorings_table', 1),
(16, '2025_11_25_030650_create_sppd_settings_table', 1),
(17, '2026_04_18_000001_create_sekolah_table', 1),
(18, '2026_04_18_000002_add_owner_fields_to_perusahaan_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `monitorings`
--

CREATE TABLE `monitorings` (
  `id` bigint UNSIGNED NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL,
  `pembimbing_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `pembimbings`
--

CREATE TABLE `pembimbings` (
  `id` bigint UNSIGNED NOT NULL,
  `nama_pembimbing` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan_pembimbing` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nip_pembimbing` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_hp_pembimbing` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pembimbing_perusahaans`
--

CREATE TABLE `pembimbing_perusahaans` (
  `id` bigint UNSIGNED NOT NULL,
  `nama_pembimbing` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `perusahaan_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `perusahaan`
--

CREATE TABLE `perusahaan` (
  `id` bigint UNSIGNED NOT NULL,
  `nama_perusahaan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_pemilik_perusahaan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon_pemilik_perusahaan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `kontak` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_pemilik` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bidang_usaha` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `perusahaan`
--

INSERT INTO `perusahaan` (`id`, `nama_perusahaan`, `nama_pemilik_perusahaan`, `telepon_pemilik_perusahaan`, `alamat`, `kontak`, `nama_pemilik`, `bidang_usaha`, `created_at`, `updated_at`) VALUES
(1, 'Bengkel Opik Motor', 'SMKN 8 GARUT', '082126574516', 'Jl. Raya Buah Batu', NULL, NULL, NULL, '2026-04-17 20:23:09', '2026-04-17 23:14:36');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sekolah`
--

CREATE TABLE `sekolah` (
  `id` bigint UNSIGNED NOT NULL,
  `nama_kepala_sekolah` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nip_kepala_sekolah` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_mulai_pkl` date NOT NULL,
  `tanggal_selesai_pkl` date NOT NULL,
  `cap_sekolah_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ttd_kepala_sekolah_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sekolah`
--

INSERT INTO `sekolah` (`id`, `nama_kepala_sekolah`, `nip_kepala_sekolah`, `tanggal_mulai_pkl`, `tanggal_selesai_pkl`, `cap_sekolah_path`, `ttd_kepala_sekolah_path`, `created_at`, `updated_at`) VALUES
(1, 'H. Husni Mubarok, S.Pd., M.Pd.', '197712022009121001', '2026-09-01', '2026-09-01', NULL, 'school-assets/1776482469_ttd_ttd_kepsek.png', '2026-04-17 20:21:10', '2026-04-17 20:21:10');

-- --------------------------------------------------------

--
-- Table structure for table `settingtanggal`
--

CREATE TABLE `settingtanggal` (
  `id` bigint UNSIGNED NOT NULL,
  `tanggal_surat` date DEFAULT NULL,
  `tanggal_berangkat` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` bigint UNSIGNED NOT NULL,
  `nama_siswa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nis` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelas_id` bigint UNSIGNED NOT NULL,
  `jk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ortu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_ortu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp_ortu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp_siswa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum_terdaftar',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `nama_siswa`, `nis`, `kelas_id`, `jk`, `nama_ortu`, `alamat_ortu`, `no_hp_ortu`, `no_hp_siswa`, `foto`, `status`, `created_at`, `updated_at`) VALUES
(1, 'AGUNG GUMELAR', '242518221002', 4, NULL, 'Agus M', 'asdasd', '0123213', '0787878', NULL, 'Mendaftar_perusahaan', '2026-04-17 20:22:13', '2026-04-17 23:20:32'),
(2, 'AGUS DENI PERMANA', '242518221003', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(3, 'AJRIL ILHAM SIROJUDIN', '242518221004', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(4, 'ALDI MUHAMAD RIDWAN', '242518221005', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(5, 'BAGAS INDRA RUSMANA', '242518221006', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(6, 'BAYU ALFIANSAH', '242518221007', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(7, 'CUCU MAULANA', '242518221008', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(8, 'DAFFA ADITIA', '242518221009', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(9, 'DAFFA FAUZAN RAMADHANY', '242518221010', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(10, 'EKA RAMDANI SETIAWAN', '242518221011', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(11, 'ENCEP RAHMADANI', '242518221012', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(12, 'FAHRI AHMAD ISMAIL', '242518221013', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(13, 'GALIH JIBRIEL RAMADHAN', '242518221014', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(14, 'HAIKAL MUHAMAD ANWAR', '242518221015', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(15, 'IKHSAN ABDUL ROHMAN', '242518221017', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(16, 'ILHAM ABDUL RAHMAN', '242518221018', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(17, 'ILHAM AULIA AFANDI', '242518221019', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(18, 'LALAN MAULANI', '242518221020', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(19, 'MOCH HAIKAL RAMADHANI', '242518221021', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(20, 'MUHAMAD ABDUL AZIZ', '242518221022', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(21, 'MUHAMAD HIKBAL HANAPI', '242518221024', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(22, 'MUHAMAD NABIEL TAUFIQIL HAKIM', '242518221026', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(23, 'MUHAMMAD WILDAN', '242518221027', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(24, 'NABIL MUHAMMAD RIZQI', '242518221028', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(25, 'NANDY DARUSSMAN', '242518221029', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(26, 'PARHAN PADILAH', '242518221030', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(27, 'RAPI ABDUL AZIZ', '242518221031', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(28, 'REAL CAESAR FIKHSA UTAMA', '242518221032', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(29, 'SAEPUL', '242518221033', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(30, 'SALMAN HANIP HADIYANSYAH', '242518221034', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(31, 'TATANG HIDAYAT', '242518221035', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(32, 'WIKI AZI FAISAL GUNAWAN', '242518221036', 4, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(33, 'ALDI SAPUTRA', '2425182116037', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(34, 'ANDI RAMDAN', '2425182116040', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(35, 'ANDIKA RAMDAN', '2425182116041', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(36, 'BAYU NUGRAHA', '2425182116042', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(37, 'DANIEL MAULIDAN', '2425182116043', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(38, 'DEDE FAHRI', '2425182116044', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(39, 'DEDE SAYFUL MAULANA', '2425182116045', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(40, 'DENDA ROHMANA', '2425182116046', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(41, 'FAJAR', '2425182116047', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(42, 'FAJAR HIDAYAT', '2425182116048', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(43, 'GUGUN GUNAWAN', '2425182116049', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(44, 'HENDI HIDAYAT', '2425182116051', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(45, 'IRFAN FADILAH', '2425182116052', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(46, 'IRWANDI', '2425182116053', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(47, 'MUHAMAD DAVA ALPARIZKI', '2425182116054', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(48, 'MUHAMAD FIRLI ALKAHPI', '2425182116055', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(49, 'MUHAMAD HAIKAL', '2425182116056', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(50, 'MUHAMAD RAMDANI', '2425182116057', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(51, 'MUHAMAD RIZKI RIPALDI', '2425182116058', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(52, 'MUHAMAD TEGUH PRAYOGA', '2425182116059', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(53, 'MUHAMAD YASIN ARDIANSYAH', '2425182116060', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(54, 'MUHAMMAD ALDIYANSYAH', '2425182116061', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(55, 'MUHAMMAD ARIFDRIANSYAH', '2425182116062', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(56, 'NAUVAL MUBAROK', '2425182116063', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(57, 'RAMA JAGANDI', '2425182116065', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(58, 'RENDI RIADI', '2425182116066', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(59, 'RESYA ARDANI', '2425182116067', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(60, 'REZA FEBRIANSYAH', '2425182116068', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(61, 'SANDI RIPALDI', '2425182116070', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(62, 'TAUFIQ NUR AGUSTINA', '2425182116071', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(63, 'YUSUP HAIDAR ALHADI', '2425182116072', 5, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(64, 'ANGGA FASYA DHIKA PRATAMA', '2425182216073', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(65, 'ARI', '2425182216074', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(66, 'ARYA M FAZRIL', '2425182216075', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(67, 'AZWAN ABDUL ROZAB', '2425182216076', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(68, 'BILLAL FADILAH', '2425182216077', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(69, 'DENDI MUHAMMAD FAUZAN', '2425182216078', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(70, 'DENIS ALDIANSYAH', '2425182216080', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(71, 'FAJAR SHOLEHNURDIN', '2425182216082', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(72, 'FAREL DWI APRILIANA PUTRA', '2425182216083', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(73, 'FAUZAN RAY GUMELAR', '2425182216084', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(74, 'GUNTUR HERYANTO', '2425182216085', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(75, 'HILMI MULYANA', '2425182216088', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(76, 'ISMAM SAJIDIN', '2425182216089', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(77, 'JENAL DANIANSYAH MUHTAR', '2425182216090', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(78, 'MUHAMAD RAIHAN SOPPIANSYAH', '2425182216094', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(79, 'MUHAMAD RIFKI GHIPARI', '2425182216098', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(80, 'MUHAMMAD FAZRI', '2425182216092', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(81, 'MUHAMMAD IQBAL', '2425182216093', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(82, 'MUHAMMAD RIFAN MAULANA SOPANDI', '2425182216095', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(83, 'MUHAMMAD YUSRIL FIKRAN KUSMANA', '2425182216097', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(84, 'NOPA SOPANDI', '2425182216099', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(85, 'PANCA WIGUNA UTAMA', '2425182216100', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(86, 'PERI RIANTO', '2425182216101', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(87, 'RIKI AHMAD RAMADANI', '2425182216102', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(88, 'RIO MARIO', '2425182216103', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(89, 'RIZVAN FEBRIAN', '2425182216104', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(90, 'ROBI RYNHAT PRATAMA', '2425182216105', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(91, 'SIHABBUDIN', '2425182216106', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(92, 'TISNA HERLAMBANG', '2425182216107', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(93, 'ZIYAN MUHAMAD SHIDQI', '2425182216108', 6, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(94, 'ADITYA GUNAWAN', '242518421001', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(95, 'AHMAD RAMADANI', '242518421002', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(96, 'AHMAD SHALEH GUNTARA', '242518421004', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(97, 'ALDI FEBRIYANSYAH', '242518421005', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(98, 'ALDI HERDIANSAH', '242518421006', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(99, 'AMELIA PUTRI', '242518421003', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(100, 'ARMAN MAULANA', '242518421008', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(101, 'BAGJA LUTFI ALIFA HAKIM', '242518421009', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(102, 'DAPA YUDA ALPAZRI', '242518421010', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(103, 'DEA NAILA WATI', '242518421011', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(104, 'DESTIAN MUHAMAD FAUZI', '242518421012', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(105, 'ELSA RAMADHANI', '242518421013', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(106, 'FEBRIAN SAFANA', '242518421014', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(107, 'GINA NUR SITI AZIZAH', '242518421015', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(108, 'IHSAN ABDULAH', '242518421016', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(109, 'MOCH. FAIZHAL', '242518421018', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(110, 'MUHAMAD FAHMI ADRIANSYAH', '242518421020', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(111, 'MUHAMAD HAIKAL', '242518421021', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(112, 'MUHAMAD SAEFUL MIQDAR', '242518421112', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(113, 'MUHAMMAD ADLAN AL FAREZA', '242518421019', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(114, 'NAUVAL ANDI SYAH PUTRA', '242518421022', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(115, 'NENDAH FARIDAH', '242518421023', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(116, 'NOVAL NAZRIL JUANA', '242518421025', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(117, 'PANISA HIKMAH TIAR', '242518421027', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(118, 'RAHMA KENIA NURFADILAH', '242518421028', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(119, 'RAKA PUTRA PRATAMA', '242518421029', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(120, 'RAKA SABDA PANGRUNGU', '242518421030', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(121, 'RENDI AGUSTINA', '242518421031', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(122, 'SHAILLA SALSA BILA', '242518421034', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(123, 'SINTA AULIA', '242518421033', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(124, 'SITI SOBARIAH', '242518421032', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(125, 'TRIAN NUGRAHA', '242518421036', 1, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(126, 'ADE LIA OKTAVIANI', '242518421037', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(127, 'AHMAD RIFA\'I', '242518421038', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(128, 'ALGA GIAN AGUSTIN', '242518421039', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(129, 'ALIF YUDISTIRA', '242518421040', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(130, 'ALIT HENDAR M FATURAHMAN', '242518421041', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(131, 'AMIRA ARDIA', '242518421042', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(132, 'ANAS ILHAB', '242518421043', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(133, 'ANDIKA YUDISTIRA', '242518421044', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(134, 'ANDRIAN NUR MUHAMAD KUSUMA', '242518421045', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(135, 'BAGJA RODINI', '242518421046', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(136, 'DERIZKI', '242518421048', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(137, 'DEVANI ELLYSA THALITA SARI', '242518421049', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(138, 'DEVI SAPUTRA', '242518421050', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(139, 'FAHRI PAUJI', '242518421051', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(140, 'FIRDA HIDAYATUL AGNIA', '242518421052', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(141, 'HARIS SULANJANA', '242518421053', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(142, 'IMAY MAELANI', '242518421054', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(143, 'M FAREL ANDIKA PURWANTO', '242518421055', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(144, 'MUHAMAD KIKY FIRMANSYAH', '242518421056', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(145, 'MUHAMAD REXI APRIAN', '242518421057', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(146, 'MUHAMAD RIPKI AL JAUHARI', '242518421058', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(147, 'MUHAMAD SIGIT PERMANA', '242518421059', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(148, 'MUHAMMAD AKBAR MULIA', '242518421060', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(149, 'NAZWA ALIFAH', '242518421061', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(150, 'NENG TIPA RAHMAWATI', '242518421062', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(151, 'NIKEN RATU AYU PRATIWI', '242518421063', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(152, 'NURROHMAH KHARISMA MAHARANI', '242518421072', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(153, 'PUTRA ADRIAN', '242518421064', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(154, 'REZA ADRIAN', '242518421065', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(155, 'RIAN APRIANSYAH', '242518421066', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(156, 'RIANA YUSDISTIRA', '242518421067', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(157, 'SINDY SILVIA', '242518421068', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(158, 'SINGGIH HANRA PURNAMA', '242518421069', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(159, 'TYAS NUR BAROKAH', '242518421070', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(160, 'YUSUF PERMANA', '242518421071', 2, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(161, 'AEP SAEPU MILAH', '242518421107', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(162, 'ALDI MAULANA YUSUF', '242518421007', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(163, 'ARI FIRMANSAH', '242518421073', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(164, 'ARIS LESMANA', '242518421074', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(165, 'ARPHAN MAULANA', '242518421075', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(166, 'ASEP SAPUTRA', '242518421076', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(167, 'ASMIRANDA NURSAFITRI', '242518421077', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(168, 'AZIZ AHMAD SAEPURROHMAN', '242518421078', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(169, 'AZKIAN APGAN', '242518421079', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(170, 'DANDI HERMAWAN', '242518421080', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(171, 'DEWI SITI PATIMAH', '242518421081', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(172, 'DINI', '242518421082', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(173, 'DIVA NUR HAPIPAH', '242518421083', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(174, 'DONI ANGGARA', '242518421084', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(175, 'GANI HARDIANSYAH', '242518421086', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(176, 'ICA JULFA HERAWATI', '242518421087', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(177, 'IWAN IRPAWAN', '242518421088', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(178, 'LAYLA AL AFIFAH', '242518421089', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(179, 'M.RIZKY HENDARSYAH', '242518421090', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(180, 'MUHAMMAD ARDIANSYAH', '242518421091', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(181, 'MUHAMMAD JENAL', '242518421093', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(182, 'MUHAMMAD NURDIANSAH', '242518421094', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(183, 'MUHAMMAD QAWIYUL MATIN', '242518421095', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(184, 'NUR ANISA', '242518421096', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(185, 'NURHAYATI BARRYATILLAH', '242518421097', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(186, 'PUTRI AYU SUNDARI', '242518421098', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(187, 'RESA SUBAGJA', '242518421108', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(188, 'RIFANDI NUGRAHA', '242518421099', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(189, 'RIRIN FITRIA', '242518421100', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(190, 'RIZKY ADITIYA KOSWARA', '242518421101', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:13', '2026-04-17 20:22:13'),
(191, 'ROMI RAMDANI', '242518421102', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(192, 'SITI NURSIFA', '242518421103', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(193, 'SITI SARAH GARNIASIH', '242518421104', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(194, 'YAYAN SOPIYAN', '242518421105', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(195, 'YUSRIEL MIFTAHUDDIN FIRDAUS', '242518421106', 3, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(196, 'AGIS NUHA JAUHARI', '242518102001', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(197, 'AI PATMAWATI', '242518102002', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(198, 'ALIVIA RISTIAN SAHIR', '242518102003', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(199, 'BUNGA PITRIA APRIANI', '242518102004', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(200, 'CHIKA APRILIA PUTRI', '242518102005', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(201, 'DEA NOPITA', '242518102006', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(202, 'DEFI KANIA', '242518102007', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(203, 'ELISA CAHYANTI', '242518102008', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(204, 'M FACHRI ALI FAUZI', '242518102013', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(205, 'MASRI NURSYAMSIAH', '242518102010', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(206, 'MAULANA NUR FARIJ', '242518102011', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(207, 'MELISA JULIANTI', '242518102012', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(208, 'MUHAMAD RAIHAN KHOIRUL ANWAR', '242518102009', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(209, 'NADHIRA FATINA ABIDIN', '242518102014', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(210, 'NOVITA OKTAVIANI', '242518102015', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(211, 'RAISA DWI KIRANA', '242518102016', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(212, 'RAKA PUTRA PRATAMA', '242518102017', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(213, 'RAMADANI FAZRIYANA', '242518102018', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(214, 'RAPI SEPTIA RAMADHAN', '242518102019', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(215, 'RASYA  SYAHPUTRA', '242518102110', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(216, 'REFANDI AJHARI', '242518102020', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(217, 'REHAN NURDIANSYAH', '242518102021', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(218, 'RIZKI PERMANA', '242518102022', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(219, 'SANDI PADILAH', '242518102023', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(220, 'SATRIA BIMA SAKTI SUGIAR', '242518102024', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(221, 'SHAQILA TANIA ALTASYA', '242518102025', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(222, 'SITI AISYAH', '242518102026', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(223, 'SIVA FEBRIANTI', '242518102028', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(224, 'SUKMA AULIA AL ADAWIYAH', '242518102029', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(225, 'TAOPIK RAMADANI', '242518102030', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(226, 'TIARA', '242518102031', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(227, 'TIARA CAHYA MAULIDA', '242518102032', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(228, 'UDEN SAEPUL ROHMAN', '242518102033', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(229, 'VIKI ALAMSYAH SAPUTRA', '242518102034', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(230, 'YULIANTI', '242518102035', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(231, 'ZAHRA AULIA', '242518102036', 7, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(232, 'ADELLA PUTRI ATMALIA', '242518102037', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(233, 'ADINDA MAHARANI', '242518102038', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(234, 'ALYA MAULIDA', '242518102039', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(235, 'ANDIKA SURYA LESMANA', '242518102040', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(236, 'ANDREA MAULIDAN', '242518102041', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(237, 'ANGGI PUTRI NOVIRIANTI', '242518102042', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(238, 'DENIA AFRILIANI', '242518102043', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(239, 'DESKA SAPUTRA', '242518102044', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(240, 'ERNI KUSMIATI', '242518102045', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(241, 'FITRI LESTARI', '242518102046', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(242, 'HENDRI HARULAH', '242518102047', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(243, 'KAMALUDIN', '242518102048', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(244, 'KHERIN INDRIYANTI', '242518102049', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(245, 'LAILA SRI RAHAYU', '242518102050', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(246, 'LUTVIANA RAYA', '242518102051', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(247, 'MILAN MAULIDIN SOBIRIN', '242518102052', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(248, 'MOHAMAD NUR ADIANSYAH', '242518102054', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(249, 'MUHAMAD RIZKI', '242518102055', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(250, 'NAZWA NURIL MAULIDA', '242518102056', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(251, 'NENG QURROTA AYUN', '242518102057', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(252, 'OKTAVIANTI DAVINA', '242518102109', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(253, 'OVI JULIANA', '242518102058', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(254, 'PAREL AGUSTIN SAPUTRA', '242518102059', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(255, 'PUTIH RAHAYU GIANDISTI', '242518102060', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(256, 'REHAN SUGANDI', '242518102061', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(257, 'RESA NURASYAH', '242518102062', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(258, 'RESTU FARDAN RAMADHAN', '242518102063', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(259, 'REVA LUSI SITI AGUSTIN', '242518102064', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(260, 'RIDAN MUNPARIZ', '242518102065', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(261, 'RIFA AISYAH', '242518102066', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(262, 'SIPA PAUJIAH', '242518102067', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(263, 'TRESNA MAULANA', '242518102069', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(264, 'VIKKA DINDA MAHARANI', '242518102070', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(265, 'WINDI ANGGRAENI', '242518102071', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(266, 'YUVI ANDINI', '242518102072', 8, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(267, 'ALYA KHOERUNNISA', '242518102075', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(268, 'ANNISYA NUR INDAH', '242518102073', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(269, 'ANWAR ABDUROHMAN', '242518102074', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(270, 'ARDAN RAHMAN SETIAWAN', '242518102076', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(271, 'ARDI SURYA PRATAMA', '242518102077', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(272, 'CUCU SUMIRAH', '242518102078', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(273, 'DIKA ZATMIKA', '242518102079', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(274, 'DINDA PERMATA SARI', '242518102080', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(275, 'DIRA APRILLIA', '242518102081', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(276, 'FADJAR MOCHAMAD ZIBRAN', '242518102082', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(277, 'FERDI ARDIANSYAH', '242518102083', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(278, 'GIANNINA LARAS PRAMESWARI', '242518102084', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(279, 'HAMDAN SATRIA', '242518102085', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(280, 'JEN STEPANI', '242518102086', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(281, 'JIWA HARIRI', '242518102087', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(282, 'KASIH APRIYANTI', '242518102088', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(283, 'MUHAMAD RIZKI PRATAMA', '242518102090', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(284, 'MUHAMAD ZILLAN ATYA RIZQI', '242518102091', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(285, 'MUHAMMAD DAVID AZKIA', '242518102092', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(286, 'MUHAMMAD FAIZAL', '242518102089', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(287, 'MUHAMMAD IQBAL HOERUDINNAZIB', '242518102093', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(288, 'NABILA AMELIA', '242518102094', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(289, 'NAZRIL ARDIAN SAPUTRA', '242518102095', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(290, 'NENG SITI AMELIA', '242518102096', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(291, 'NIKO ADAM SANJAYA', '242518102097', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(292, 'NURUL FATIMAH', '242518102098', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(293, 'OKTANIA DWI SAFIRA', '242518102099', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(294, 'RICKY PRADITA', '242518102100', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(295, 'RIFA NUR AZIZAH', '242518102101', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(296, 'RISKA INDRIYANI', '242518102102', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(297, 'RONI GUMELAR', '242518102103', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(298, 'SENI SRI RAHAYU', '242518102104', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(299, 'SHABRYAN JULY AT THORIQ', '242518102105', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(300, 'SITI ANISA AGUSTINA', '242518102106', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(301, 'TRIARA SUCI RAMADANI', '242518102107', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(302, 'ZIHAN SAHIRA', '242518102108', 9, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(303, 'AJENG KARTINI', '242518821001', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(304, 'ALZAHRA PUJIYANTI', '242518821002', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(305, 'AMANDA ANDINI', '242518821003', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(306, 'ARJUNA WIJAYA', '242518821004', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(307, 'ASHRAF TAMAMI AL GHUFRON', '242518821005', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(308, 'CHIKA OKTAPIANI', '242518821006', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(309, 'CITRA DESTIANI', '242518821007', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(310, 'CITRA YULIYANTI', '242518821008', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(311, 'DANIA RAMADANI AGUSTIN', '242518821009', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(312, 'DEA ARDILA', '242518821010', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(313, 'ELA NURLAILAH', '242518821011', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(314, 'FATIMAH AZZAHRA', '242518821012', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(315, 'FATMA SA\'BANIYA MARYAM', '242518821013', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(316, 'HENHEN SOLEHAH', '242518821014', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(317, 'INDAH AULA FAIZA', '242518821015', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(318, 'KAILLA KHAIRUNNISA', '242518821017', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(319, 'KENNIA FAJRINA YASMIN', '242518821016', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(320, 'MELINDA', '242518821018', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(321, 'NAZWA SUCI AISYAH', '242518821019', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(322, 'NISA IRA JANU', '242518821021', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(323, 'NOVA ARYANTI', '242518821022', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(324, 'PUTRI APRILLIA', '242518821023', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(325, 'QORI AWALIA RIZKILAH', '242518821024', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(326, 'REZMY ALFAHREZZY', '242518821025', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(327, 'RIKA LESTARI', '242518821026', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(328, 'RIPA KHOPIPAH', '242518821074', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(329, 'RIYANNI PUTRI', '242518821027', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(330, 'SILVIA SRIHIKMAH', '242518821029', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(331, 'SISKA ANJANI', '242518821030', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(332, 'SYARIFA RAMADHANI', '242518821031', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(333, 'SYTA ALNAIRA ABRIYANI', '242518821032', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(334, 'VINA SRI RAHAYU', '242518821033', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(335, 'WINDI TANIA PEBRIANA', '242518821034', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(336, 'WULANSARI', '242518821035', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(337, 'YUSI ALFIATUNNISA', '242518821036', 10, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(338, 'ALYA NURHALIMAH', '242518821037', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(339, 'AMELIA PRATAMA', '242518821038', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(340, 'ANIYAH NAFISAH', '242518821039', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(341, 'ARINI PUSPITA SARI', '242518821040', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(342, 'ARMAL NUR ARMELIA', '242518821041', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14');
INSERT INTO `siswa` (`id`, `nama_siswa`, `nis`, `kelas_id`, `jk`, `nama_ortu`, `alamat_ortu`, `no_hp_ortu`, `no_hp_siswa`, `foto`, `status`, `created_at`, `updated_at`) VALUES
(343, 'AULIA RIZKY SHANDY', '242518821045', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(344, 'DEBI PEBRIANTI', '242518821042', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(345, 'DEWI SRI RAHAYU', '242518821043', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(346, 'DINI ANDIYANI', '242518821044', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(347, 'FEBRI SHAFANI', '242518821046', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(348, 'FIRDA APRILIA', '242518821047', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(349, 'FIRNA FINATA', '242518821048', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(350, 'INDRI WAHYUNI', '242518821049', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(351, 'INTAN NUR AINI', '242518821050', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(352, 'KLARISQ AZHYFA', '242518821051', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(353, 'LARAS SAFFANAH', '242518821052', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(354, 'LENI RAHMAWATI', '242518821053', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(355, 'MANDA LAILATUSSAKINAH', '242518821054', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(356, 'MIRATUN HASANAH', '242518821055', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(357, 'NADIA SRI ASIH', '242518821056', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(358, 'NAILA PUTRI AMANDA', '242518821057', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(359, 'NANDA DELLA AMELIA APRILIYANTI', '242518821073', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(360, 'NOVITA AVIANTI KS', '242518821058', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(361, 'PITRIYANI', '242518821059', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(362, 'PRITA WULANDARI', '242518821060', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(363, 'RAIHANA FITRIYAH', '242518821061', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(364, 'RECHA MEYLANI SARAGIH', '242518821062', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(365, 'REVA ANDRIYANI', '242518821063', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(366, 'SALSA LUSIANA RIANTI', '242518821064', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(367, 'SAYYIDA RABBANIA SAJNA', '242518821065', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(368, 'SELA YULIANA', '242518821066', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(369, 'SEPTIA RAMADANI', '242518821067', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(370, 'SINDY SUMIYATI', '242518821068', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(371, 'SITI AMELIA', '242518821069', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(372, 'TATI SUSILAWATI', '242518821070', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(373, 'WINDI WULANDARI', '242518821071', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14'),
(374, 'YOLANDA', '242518821072', 11, NULL, NULL, NULL, NULL, NULL, NULL, 'belum_terdaftar', '2026-04-17 20:22:14', '2026-04-17 20:22:14');

-- --------------------------------------------------------

--
-- Table structure for table `surat_izin_ortu`
--

CREATE TABLE `surat_izin_ortu` (
  `id` bigint UNSIGNED NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL,
  `nama_ortu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat_ortu` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `surat_izin_ortu`
--

INSERT INTO `surat_izin_ortu` (`id`, `siswa_id`, `nama_ortu`, `alamat_ortu`, `created_at`, `updated_at`) VALUES
(1, 1, 'Agus M', 'asdasd', '2026-04-17 20:22:26', '2026-04-17 20:22:26');

-- --------------------------------------------------------

--
-- Table structure for table `tempat_pkl`
--

CREATE TABLE `tempat_pkl` (
  `id` bigint UNSIGNED NOT NULL,
  `siswa_id` bigint UNSIGNED NOT NULL,
  `perusahaan_id` bigint UNSIGNED NOT NULL,
  `pembimbing_id` bigint UNSIGNED DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `surat_kesediaan_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `surat_izin_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_pembimbing` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jabatan_pembimbing` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_hp_pembimbing` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nip_pembimbing` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tugas_siswa` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tempat_pkl`
--

INSERT INTO `tempat_pkl` (`id`, `siswa_id`, `perusahaan_id`, `pembimbing_id`, `tanggal_mulai`, `tanggal_selesai`, `surat_kesediaan_path`, `surat_izin_path`, `nama_pembimbing`, `jabatan_pembimbing`, `no_hp_pembimbing`, `nip_pembimbing`, `tugas_siswa`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 1, 1, NULL, '2026-09-01', '2026-10-30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, '2026-04-17 23:20:32', '2026-04-17 23:20:32');

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
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kepala_program',
  `jurusan_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `jurusan_id`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Kepala Program', 'kepalaprogram@example.com', NULL, '$2y$12$o30wcs/IQWzw3k7fklSY4eExhGRJz3GX76ThbL6Ccn4rtUHFixmG6', 'kepala_program', NULL, NULL, '2026-04-17 20:15:49', '2026-04-17 20:15:49'),
(2, 'Panitia PKL', 'panitia@example.com', NULL, '$2y$12$C3yYBWt2x3Y.HGIwQLrnSeDImMJ0Bl6onB1lE.e0eqUW/eZRos5nW', 'panitia', NULL, NULL, '2026-04-17 20:15:49', '2026-04-17 20:15:49'),
(3, 'Akon Maulana, S.Pd.', 'akon@example.com', NULL, '$2y$12$orVG3RLNLalv86g9dwjY4uxVteaakefy2Y798Hyp2rViEY5llGb02', 'kepala_program', '2', NULL, '2026-04-17 20:15:50', '2026-04-17 20:15:50'),
(4, 'Pendi Abdul Wahab,S.T.', 'pendi@example.com', NULL, '$2y$12$dPt1k2wAXDtFuwf9w8im7.jVfrY59Dvzs/sIp7aCDKdv5aFIp7eF.', 'kepala_program', '1', NULL, '2026-04-17 20:15:50', '2026-04-17 20:15:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bimbingans`
--
ALTER TABLE `bimbingans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bimbingans_pembimbing_id_foreign` (`pembimbing_id`),
  ADD KEY `bimbingans_siswa_id_foreign` (`siswa_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jurusan`
--
ALTER TABLE `jurusan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jurusan_kode_jurusan_unique` (`kode_jurusan`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelas_jurusan_id_foreign` (`jurusan_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `monitorings`
--
ALTER TABLE `monitorings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `monitorings_siswa_id_foreign` (`siswa_id`),
  ADD KEY `monitorings_pembimbing_id_foreign` (`pembimbing_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pembimbings`
--
ALTER TABLE `pembimbings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pembimbings_nip_pembimbing_unique` (`nip_pembimbing`);

--
-- Indexes for table `pembimbing_perusahaans`
--
ALTER TABLE `pembimbing_perusahaans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `perusahaan`
--
ALTER TABLE `perusahaan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sekolah`
--
ALTER TABLE `sekolah`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settingtanggal`
--
ALTER TABLE `settingtanggal`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `siswa_nis_unique` (`nis`),
  ADD KEY `siswa_kelas_id_foreign` (`kelas_id`);

--
-- Indexes for table `surat_izin_ortu`
--
ALTER TABLE `surat_izin_ortu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `surat_izin_ortu_siswa_id_foreign` (`siswa_id`);

--
-- Indexes for table `tempat_pkl`
--
ALTER TABLE `tempat_pkl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tempat_pkl_siswa_id_foreign` (`siswa_id`),
  ADD KEY `tempat_pkl_perusahaan_id_foreign` (`perusahaan_id`),
  ADD KEY `tempat_pkl_created_by_foreign` (`created_by`),
  ADD KEY `tempat_pkl_pembimbing_id_foreign` (`pembimbing_id`);

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
-- AUTO_INCREMENT for table `bimbingans`
--
ALTER TABLE `bimbingans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jurusan`
--
ALTER TABLE `jurusan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `monitorings`
--
ALTER TABLE `monitorings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pembimbings`
--
ALTER TABLE `pembimbings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pembimbing_perusahaans`
--
ALTER TABLE `pembimbing_perusahaans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `perusahaan`
--
ALTER TABLE `perusahaan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sekolah`
--
ALTER TABLE `sekolah`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settingtanggal`
--
ALTER TABLE `settingtanggal`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=375;

--
-- AUTO_INCREMENT for table `surat_izin_ortu`
--
ALTER TABLE `surat_izin_ortu`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tempat_pkl`
--
ALTER TABLE `tempat_pkl`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bimbingans`
--
ALTER TABLE `bimbingans`
  ADD CONSTRAINT `bimbingans_pembimbing_id_foreign` FOREIGN KEY (`pembimbing_id`) REFERENCES `pembimbings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bimbingans_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `kelas_jurusan_id_foreign` FOREIGN KEY (`jurusan_id`) REFERENCES `jurusan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `monitorings`
--
ALTER TABLE `monitorings`
  ADD CONSTRAINT `monitorings_pembimbing_id_foreign` FOREIGN KEY (`pembimbing_id`) REFERENCES `pembimbings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `monitorings_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_kelas_id_foreign` FOREIGN KEY (`kelas_id`) REFERENCES `kelas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `surat_izin_ortu`
--
ALTER TABLE `surat_izin_ortu`
  ADD CONSTRAINT `surat_izin_ortu_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tempat_pkl`
--
ALTER TABLE `tempat_pkl`
  ADD CONSTRAINT `tempat_pkl_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tempat_pkl_pembimbing_id_foreign` FOREIGN KEY (`pembimbing_id`) REFERENCES `pembimbings` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `tempat_pkl_perusahaan_id_foreign` FOREIGN KEY (`perusahaan_id`) REFERENCES `perusahaan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tempat_pkl_siswa_id_foreign` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
