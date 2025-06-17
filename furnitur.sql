-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 08, 2025 at 03:53 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `furnitur`
--

-- --------------------------------------------------------

--
-- Table structure for table `alamat_pengiriman`
--

CREATE TABLE `alamat_pengiriman` (
  `id` bigint UNSIGNED NOT NULL,
  `id_pelanggan` bigint UNSIGNED NOT NULL,
  `nama_penerima` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomor_telepon` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label_alamat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_lengkap` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `provinsi` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kota` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_pos` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(11,7) DEFAULT NULL,
  `is_utama` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `alamat_pengiriman`
--

INSERT INTO `alamat_pengiriman` (`id`, `id_pelanggan`, `nama_penerima`, `nomor_telepon`, `label_alamat`, `alamat_lengkap`, `provinsi`, `kota`, `kode_pos`, `latitude`, `longitude`, `is_utama`, `created_at`, `updated_at`) VALUES
(9, 1, 'Luqman Anas Naufal', '088216686432', 'Toko', 'Trimulyo', 'Jawa Tengah', 'Kota Semarang', '50118', '-6.9359945', '110.4656832', 0, '2025-05-24 06:02:40', '2025-05-25 14:43:48'),
(12, 1, 'Luqman Anas Naufal', '088216686432', 'Rumah', 'Selogiri', 'Jawa Tengah', 'Kabupaten Wonogiri', '57611', '-7.7938545', '110.8994703', 1, '2025-05-24 08:32:44', '2025-05-25 14:43:48'),
(13, 1, 'Luqman Anas Naufal', '088216686432', 'Kampus', 'Gendongan', 'Jawa Tengah', 'Kota Salatiga', '50743', '-7.3366467', '110.5111180', 0, '2025-05-25 13:22:20', '2025-05-25 14:43:48');

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
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id` bigint UNSIGNED NOT NULL,
  `id_pesanan` bigint UNSIGNED NOT NULL,
  `id_produk` bigint UNSIGNED NOT NULL,
  `nama_produk_saat_order` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `harga_satuan_saat_order` decimal(15,2) NOT NULL,
  `jumlah` int NOT NULL,
  `subtotal` decimal(15,2) NOT NULL COMMENT 'harga_satuan_saat_order * jumlah',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id`, `id_pesanan`, `id_produk`, `nama_produk_saat_order`, `harga_satuan_saat_order`, `jumlah`, `subtotal`, `created_at`, `updated_at`) VALUES
(5, 5, 6, 'Rak Voluptatibus Unde 2', '2639405.00', 2, '5278810.00', '2025-05-21 21:49:00', '2025-05-21 21:49:00'),
(6, 6, 6, 'Rak Voluptatibus Unde 2', '2639405.00', 1, '2639405.00', '2025-05-21 22:00:31', '2025-05-21 22:00:31'),
(7, 7, 7, 'Sofa Modi Qui 1', '215654.00', 1, '215654.00', '2025-05-21 22:06:35', '2025-05-21 22:06:35'),
(8, 8, 9, 'Table Necessitatibus Quia 1', '1840729.00', 1, '1840729.00', '2025-05-21 22:08:59', '2025-05-21 22:08:59'),
(9, 9, 4, 'Chair Omnis Atque 2', '3384286.00', 1, '3384286.00', '2025-05-21 22:26:32', '2025-05-21 22:26:32'),
(10, 10, 5, 'Rak Incidunt Culpa 1', '4813359.00', 1, '4813359.00', '2025-05-22 06:22:17', '2025-05-22 06:22:17'),
(11, 11, 7, 'Sofa Modi Qui 1', '215654.00', 1, '215654.00', '2025-05-22 06:27:57', '2025-05-22 06:27:57'),
(12, 12, 2, 'Cabinet Eos Eaque 2', '3867019.00', 1, '3867019.00', '2025-05-22 07:03:22', '2025-05-22 07:03:22'),
(13, 13, 6, 'Rak Voluptatibus Unde 2', '2639405.00', 1, '2639405.00', '2025-05-22 07:22:56', '2025-05-22 07:22:56'),
(14, 13, 8, 'Sofa Cumque Omnis 2', '3091211.00', 1, '3091211.00', '2025-05-22 07:22:56', '2025-05-22 07:22:56'),
(15, 14, 10, 'Table Maxime Hic 2', '1581218.00', 1, '1581218.00', '2025-05-22 14:30:00', '2025-05-22 14:30:00'),
(16, 15, 7, 'Sofa Modi Qui 1', '215654.00', 1, '215654.00', '2025-05-23 03:09:29', '2025-05-23 03:09:29'),
(17, 16, 5, 'Rak Incidunt Culpa 1', '4813359.00', 1, '4813359.00', '2025-05-23 03:12:20', '2025-05-23 03:12:20'),
(18, 17, 5, 'Rak Incidunt Culpa 1', '4813359.00', 1, '4813359.00', '2025-05-23 03:20:36', '2025-05-23 03:20:36'),
(19, 18, 5, 'Rak Incidunt Culpa 1', '4813359.00', 1, '4813359.00', '2025-05-24 04:00:44', '2025-05-24 04:00:44'),
(20, 19, 3, 'Chair Ipsum Consectetur 1', '4953448.00', 1, '4953448.00', '2025-05-24 04:17:19', '2025-05-24 04:17:19'),
(21, 20, 6, 'Rak Voluptatibus Unde 2', '2639405.00', 1, '2639405.00', '2025-05-24 04:23:26', '2025-05-24 04:23:26'),
(22, 21, 1, 'Cabinet Sint Quo 1', '3147273.00', 1, '3147273.00', '2025-05-24 06:03:31', '2025-05-24 06:03:31'),
(23, 22, 4, 'Chair Omnis Atque 2', '3384286.00', 1, '3384286.00', '2025-05-24 06:20:01', '2025-05-24 06:20:01'),
(24, 23, 9, 'Table Necessitatibus Quia 1', '1840729.00', 1, '1840729.00', '2025-05-24 06:28:06', '2025-05-24 06:28:06'),
(25, 24, 7, 'Sofa Modi Qui 1', '215654.00', 1, '215654.00', '2025-05-24 06:47:06', '2025-05-24 06:47:06'),
(26, 25, 9, 'Table Necessitatibus Quia 1', '1840729.00', 1, '1840729.00', '2025-05-24 07:16:11', '2025-05-24 07:16:11'),
(27, 26, 6, 'Rak Voluptatibus Unde 2', '2639405.00', 1, '2639405.00', '2025-05-24 07:42:14', '2025-05-24 07:42:14'),
(28, 27, 8, 'Sofa Cumque Omnis 2', '3091211.00', 1, '3091211.00', '2025-05-24 07:46:50', '2025-05-24 07:46:50'),
(30, 29, 9, 'Table Necessitatibus Quia 1', '1840729.00', 1, '1840729.00', '2025-05-24 08:33:10', '2025-05-24 08:33:10'),
(32, 31, 8, 'Sofa Cumque Omnis 2', '3091211.00', 1, '3091211.00', '2025-05-25 07:03:56', '2025-05-25 07:03:56'),
(33, 32, 6, 'Rak Voluptatibus Unde 2', '2639405.00', 1, '2639405.00', '2025-05-25 07:37:30', '2025-05-25 07:37:30'),
(34, 33, 5, 'Rak Incidunt Culpa 1', '4813359.00', 1, '4813359.00', '2025-05-25 11:18:03', '2025-05-25 11:18:03'),
(36, 35, 8, 'Sofa Cumque Omnis 2', '3091211.00', 1, '3091211.00', '2025-05-28 04:28:07', '2025-05-28 04:28:07'),
(41, 40, 7, 'Sofa Modi Qui 1', '215654.00', 1, '215654.00', '2025-06-02 04:10:17', '2025-06-02 04:10:17'),
(42, 41, 8, 'Sofa Cumque Omnis 2', '3091211.00', 1, '3091211.00', '2025-06-02 04:34:08', '2025-06-02 04:34:08'),
(43, 42, 10, 'Table Maxime Hic 2', '1581218.00', 1, '1581218.00', '2025-06-02 13:38:41', '2025-06-02 13:38:41'),
(44, 43, 8, 'Sofa Cumque Omnis 2', '3091211.00', 1, '3091211.00', '2025-06-02 14:02:55', '2025-06-02 14:02:55'),
(45, 44, 5, 'Rak Incidunt Culpa 1', '4813359.00', 1, '4813359.00', '2025-06-02 15:14:14', '2025-06-02 15:14:14'),
(46, 45, 7, 'Sofa Modi Qui 1', '215654.00', 1, '215654.00', '2025-06-02 15:17:49', '2025-06-02 15:17:49'),
(48, 47, 1, 'Cabinet Sint Quo 1', '3147273.00', 1, '3147273.00', '2025-06-05 03:58:26', '2025-06-05 03:58:26'),
(49, 48, 4, 'Chair Omnis Atque 2', '3384286.00', 1, '3384286.00', '2025-06-05 04:28:03', '2025-06-05 04:28:03'),
(50, 49, 7, 'Sofa Modi Qui 1', '215654.00', 1, '215654.00', '2025-06-05 04:50:17', '2025-06-05 04:50:17'),
(51, 50, 1, 'Cabinet Sint Quo 1', '3147273.00', 1, '3147273.00', '2025-06-05 06:26:46', '2025-06-05 06:26:46');

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
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id` bigint UNSIGNED NOT NULL,
  `nama_kategori` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Cabinet', 'cabinet', '2025-05-21 21:32:23', '2025-05-21 21:32:23'),
(2, 'Chair', 'chair', '2025-05-21 21:32:23', '2025-05-21 21:32:23'),
(3, 'Rak', 'rak', '2025-05-21 21:32:23', '2025-05-21 21:32:23'),
(4, 'Sofa', 'sofa', '2025-05-21 21:32:23', '2025-05-21 21:32:23'),
(5, 'Table', 'table', '2025-05-21 21:32:23', '2025-05-21 21:32:23');

-- --------------------------------------------------------

--
-- Table structure for table `kendaraan_pengirim`
--

CREATE TABLE `kendaraan_pengirim` (
  `id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `plate_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `driver_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `driver_phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('available','on_delivery') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kendaraan_pengirim`
--

INSERT INTO `kendaraan_pengirim` (`id`, `type`, `plate_number`, `driver_name`, `driver_phone`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Mobil', 'B 1234 XYZ', 'Pak Budi', '081234567890', 'available', '2025-06-05 06:01:47', '2025-06-05 06:01:47'),
(2, 'Motor', 'D 5678 ABC', 'Pak Joko', '082345678901', 'available', '2025-06-05 06:01:47', '2025-06-05 06:01:47');

-- --------------------------------------------------------

--
-- Table structure for table `keranjang`
--

CREATE TABLE `keranjang` (
  `id` bigint UNSIGNED NOT NULL,
  `id_pelanggan` bigint UNSIGNED NOT NULL,
  `id_produk` bigint UNSIGNED NOT NULL,
  `jumlah` int NOT NULL,
  `harga_saat_dibeli` decimal(15,2) NOT NULL,
  `subtotal_harga` decimal(15,2) NOT NULL,
  `berat_satuan_saat_dibeli` decimal(8,2) NOT NULL DEFAULT '0.00',
  `subtotal_berat` decimal(8,2) NOT NULL DEFAULT '0.00',
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
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_05_20_010545_create_kategori_table', 1),
(5, '2025_05_20_010552_create_produk_table', 1),
(6, '2025_05_20_031534_add_berat_to_produk_table', 1),
(7, '2025_05_20_032618_create_tb_pelanggan_table', 1),
(8, '2025_05_20_033307_create_keranjang_table', 1),
(10, '2025_05_22_033437_create_alamat_pengiriman_table', 2),
(11, '2025_05_22_032438_create_vouchers_table', 3),
(12, '2025_05_22_032438_create_pesanan_table', 4),
(13, '2025_05_22_032441_create_detail_pesanan_table', 5),
(14, '2025_05_22_044403_add_label_alamat_to_alamat_pengiriman_table', 6),
(15, '2025_05_23_083426_create_pesan_table', 7),
(16, '2025_05_23_095723_create_notifikasi_table', 8),
(17, '2025_05_24_104834_add_estimasi_pengiriman_to_pesanan_table', 9),
(18, '2025_05_24_113002_add_coordinates_to_alamat_pengiriman_table', 10),
(19, '2025_05_25_111413_add_waktu_bukti_diunggah_to_pesanan_table', 11),
(20, '2025_05_25_141417_add_bukti_diterima_to_pesanan_table', 12),
(21, '2025_05_28_122835_add_midtrans_fields_to_pesanan_table', 13),
(22, '2025_06_05_110509_remove_unused_columns_from_pesanan_table', 14),
(23, '2025_06_05_123609_create_kendaraan_pengirim_table', 15),
(24, '2025_06_05_123625_add_id_kendaraan_pengirim_to_pesanan_table', 15);

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` bigint UNSIGNED NOT NULL,
  `id_pelanggan` bigint UNSIGNED NOT NULL,
  `tipe_notifikasi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Contoh: pembayaran, proses_pesanan, pengiriman, pesanan_selesai',
  `judul` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pesan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `link_aksi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'URL untuk aksi terkait notifikasi, misal link ke detail pesanan',
  `id_pesanan_terkait` bigint UNSIGNED DEFAULT NULL,
  `sudah_dibaca` tinyint(1) NOT NULL DEFAULT '0',
  `dibaca_pada` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifikasi`
--

INSERT INTO `notifikasi` (`id`, `id_pelanggan`, `tipe_notifikasi`, `judul`, `pesan`, `link_aksi`, `id_pesanan_terkait`, `sudah_dibaca`, `dibaca_pada`, `created_at`, `updated_at`) VALUES
(83, 1, 'pembayaran_pending', 'Pesanan Dibuat - Menunggu Pembayaran', 'Pesanan Anda #INV-AT9A1749099017 telah berhasil dibuat. Segera lakukan pembayaran.', 'https://d8d7-114-10-152-158.ngrok-free.app/pesanan-saya/49', 49, 0, NULL, '2025-06-05 04:50:17', '2025-06-05 04:50:17'),
(84, 1, 'pembayaran_pending_gateway', 'Pembayaran Pending', 'Pembayaran untuk pesanan #INV-AT9A1749099017 sedang menunggu. Segera selesaikan pembayaran Anda.', 'https://d8d7-114-10-152-158.ngrok-free.app/pesanan-saya/49', 49, 0, NULL, '2025-06-05 04:50:45', '2025-06-05 04:50:45'),
(85, 1, 'pembayaran_berhasil', 'Pembayaran Berhasil', 'Pembayaran untuk pesanan #INV-AT9A1749099017 telah berhasil dan pesanan akan segera diproses.', 'https://d8d7-114-10-152-158.ngrok-free.app/pesanan-saya/49', 49, 0, NULL, '2025-06-05 04:50:59', '2025-06-05 04:50:59'),
(86, 1, 'pembayaran_pending', 'Pesanan Dibuat - Menunggu Pembayaran', 'Pesanan Anda #INV-PDG91749104806 telah berhasil dibuat. Segera lakukan pembayaran.', 'https://d8d7-114-10-152-158.ngrok-free.app/pesanan-saya/50', 50, 0, NULL, '2025-06-05 06:26:46', '2025-06-05 06:26:46'),
(87, 1, 'pembayaran_pending_gateway', 'Pembayaran Pending', 'Pembayaran untuk pesanan #INV-PDG91749104806 sedang menunggu. Segera selesaikan pembayaran Anda.', 'https://d8d7-114-10-152-158.ngrok-free.app/pesanan-saya/50', 50, 0, NULL, '2025-06-05 06:27:11', '2025-06-05 06:27:11'),
(88, 1, 'pembayaran_berhasil', 'Pembayaran Berhasil', 'Pembayaran untuk pesanan #INV-PDG91749104806 telah berhasil dan pesanan akan segera diproses.', 'https://d8d7-114-10-152-158.ngrok-free.app/pesanan-saya/50', 50, 0, NULL, '2025-06-05 06:27:23', '2025-06-05 06:27:23');

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
-- Table structure for table `pesan`
--

CREATE TABLE `pesan` (
  `id` bigint UNSIGNED NOT NULL,
  `id_pelanggan` bigint UNSIGNED NOT NULL COMMENT 'ID pelanggan yang terlibat dalam percakapan',
  `pengirim_adalah_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'TRUE jika pengirim adalah admin, FALSE jika pelanggan',
  `isi_pesan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_produk_konteks` bigint UNSIGNED DEFAULT NULL COMMENT 'Konteks produk jika pesan terkait produk tertentu',
  `nomor_pesanan_konteks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Konteks nomor pesanan jika pesan terkait pesanan tertentu',
  `sudah_dibaca_oleh_pelanggan` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Status dibaca untuk pesan yang dikirim oleh admin',
  `sudah_dibaca_oleh_admin` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Status dibaca untuk pesan yang dikirim oleh pelanggan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pesan`
--

INSERT INTO `pesan` (`id`, `id_pelanggan`, `pengirim_adalah_admin`, `isi_pesan`, `id_produk_konteks`, `nomor_pesanan_konteks`, `sudah_dibaca_oleh_pelanggan`, `sudah_dibaca_oleh_admin`, `created_at`, `updated_at`) VALUES
(1, 1, 0, 'Saya ingin bertanya tentang produk Rak Voluptatibus Unde 2...', 6, NULL, 1, 0, '2025-05-23 01:51:32', '2025-05-23 01:51:32'),
(2, 1, 0, 'tes', 6, NULL, 1, 0, '2025-05-23 01:51:51', '2025-05-23 01:51:51'),
(3, 1, 0, 'Saya ingin bertanya tentang pesanan saya #INV-3R7K1747923776...', NULL, 'INV-3R7K1747923776', 1, 0, '2025-05-23 01:55:30', '2025-05-23 01:55:30'),
(4, 1, 0, 'Saya ingin bertanya tentang pesanan saya #INVCRT-B4FK1747920137...', NULL, 'INVCRT-B4FK1747920137', 1, 0, '2025-05-23 01:57:17', '2025-05-23 01:57:17');

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` bigint UNSIGNED NOT NULL,
  `id_pelanggan` bigint UNSIGNED NOT NULL,
  `nomor_pesanan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_alamat_pengiriman` bigint UNSIGNED NOT NULL,
  `id_kendaraan_pengirim` bigint UNSIGNED DEFAULT NULL,
  `id_voucher` bigint UNSIGNED DEFAULT NULL,
  `subtotal_produk` decimal(15,2) NOT NULL,
  `diskon` decimal(15,2) NOT NULL DEFAULT '0.00',
  `ongkos_kirim` decimal(15,2) NOT NULL DEFAULT '0.00',
  `estimasi_pengiriman` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_pembayaran` decimal(15,2) NOT NULL,
  `status_pesanan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu_pembayaran' COMMENT 'Contoh: menunggu_pembayaran, diproses, dikirim, selesai, dibatalkan',
  `catatan_pembeli` text COLLATE utf8mb4_unicode_ci,
  `metode_pembayaran` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `snap_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_gateway_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_gateway_response` text COLLATE utf8mb4_unicode_ci,
  `waktu_pembayaran_gateway` timestamp NULL DEFAULT NULL,
  `tanggal_pesanan` datetime NOT NULL,
  `tanggal_pengiriman` datetime DEFAULT NULL,
  `nomor_resi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bukti_diterima` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id`, `id_pelanggan`, `nomor_pesanan`, `id_alamat_pengiriman`, `id_kendaraan_pengirim`, `id_voucher`, `subtotal_produk`, `diskon`, `ongkos_kirim`, `estimasi_pengiriman`, `total_pembayaran`, `status_pesanan`, `catatan_pembeli`, `metode_pembayaran`, `snap_token`, `payment_gateway_name`, `payment_gateway_response`, `waktu_pembayaran_gateway`, `tanggal_pesanan`, `tanggal_pengiriman`, `nomor_resi`, `bukti_diterima`, `created_at`, `updated_at`) VALUES
(31, 1, 'INV-CUQZ1748156636', 12, NULL, 2, '3091211.00', '50000.00', '288390.00', '2-4 hari', '3329601.00', 'selesai', NULL, 'Bank Transfer Mandiri', NULL, NULL, NULL, NULL, '2025-05-25 14:03:56', '2025-05-25 19:04:22', 'JNE123456789', 'bukti_terima/bukti_terima_31_1748406383.jpg', '2025-05-25 07:03:56', '2025-05-28 04:26:30'),
(44, 1, 'INV-KS5Z1748877254', 12, NULL, NULL, '4813359.00', '0.00', '215715.00', '1-2 hari', '5029074.00', 'dibatalkan', NULL, NULL, 'ef24d13b-02b8-4b8b-8082-fa9fc3104231', 'midtrans', NULL, NULL, '2025-06-02 22:14:14', NULL, NULL, NULL, '2025-06-02 15:14:14', '2025-06-02 15:17:12'),
(48, 1, 'INV-QC291749097683', 9, NULL, NULL, '3384286.00', '0.00', '14220.00', '1-2 hari', '3398506.00', 'dikirim', 'pastikan pengemasan barang dengan baik!!!', 'bank_transfer (midtrans)', '6c897b27-1f77-4ddf-bc9a-634322355e69', 'midtrans', '{\"va_numbers\":[{\"va_number\":\"34788403787635805655695\",\"bank\":\"bca\"}],\"transaction_time\":\"2025-06-05 11:28:11\",\"transaction_status\":\"settlement\",\"transaction_id\":\"55a18fa4-2131-43f2-b4a8-797ad53103cc\",\"status_message\":\"midtrans payment notification\",\"status_code\":\"200\",\"signature_key\":\"2f71a90ce24e8d61867ad994f84d9483a93e2740f41ff8527c0b4acac8669d6d20ba914a827b4c694784dcf8f23620b1aa2e2600824b76f292cd0a0fda11a154\",\"settlement_time\":\"2025-06-05 11:28:24\",\"payment_type\":\"bank_transfer\",\"payment_amounts\":[],\"order_id\":\"INV-QC291749097683_1749097684\",\"merchant_id\":\"G478634788\",\"gross_amount\":\"3398506.00\",\"fraud_status\":\"accept\",\"expiry_time\":\"2025-06-05 12:28:04\",\"currency\":\"IDR\"}', '2025-06-05 04:28:25', '2025-06-05 11:28:03', '2025-06-05 11:54:40', 'JNT236gda24', NULL, '2025-06-05 04:28:03', '2025-06-05 04:54:40'),
(49, 1, 'INV-AT9A1749099017', 12, NULL, 1, '215654.00', '21565.40', '215715.00', '1-2 hari', '409803.60', 'diproses', NULL, 'BCA', 'f0f0d8e6-ea13-4681-b348-c3730036801e', 'midtrans', '{\"va_numbers\":[{\"va_number\":\"34788939756142158682984\",\"bank\":\"bca\"}],\"transaction_time\":\"2025-06-05 11:50:44\",\"transaction_status\":\"settlement\",\"transaction_id\":\"318537f9-3dc7-45da-835a-b03ed5d3e8b2\",\"status_message\":\"midtrans payment notification\",\"status_code\":\"200\",\"signature_key\":\"f67918ba95eceebcf0f569bb37aa1191e2bb35ff3f4d267f36ce145eb7a94f49c4026db8e98dc3351620a39afee7d8bf5da930105de1eb83f45ab3440e952abe\",\"settlement_time\":\"2025-06-05 11:50:58\",\"payment_type\":\"bank_transfer\",\"payment_amounts\":[],\"order_id\":\"INV-AT9A1749099017_1749099018\",\"merchant_id\":\"G478634788\",\"gross_amount\":\"409804.00\",\"fraud_status\":\"accept\",\"expiry_time\":\"2025-06-05 12:50:18\",\"currency\":\"IDR\"}', '2025-06-05 04:50:59', '2025-06-05 11:50:17', NULL, NULL, NULL, '2025-06-05 04:50:17', '2025-06-05 04:50:59'),
(50, 1, 'INV-PDG91749104806', 13, NULL, 2, '3147273.00', '50000.00', '88320.00', '1-2 hari', '3185593.00', 'diproses', NULL, 'BCA', 'f602b33e-cce7-48f2-9dde-c96ccc43f851', 'midtrans', '{\"va_numbers\":[{\"va_number\":\"34788728983022270584457\",\"bank\":\"bca\"}],\"transaction_time\":\"2025-06-05 13:27:11\",\"transaction_status\":\"settlement\",\"transaction_id\":\"bd388c50-5329-47e5-aaae-9c6ab4a3c29b\",\"status_message\":\"midtrans payment notification\",\"status_code\":\"200\",\"signature_key\":\"65a4d594c86b26192f3a8b46ba552fe245899a7e98c84180b88c0cb4968de5e2d68b476dc9061d648090cbf5c6f2a81e058678af62a5e3d8804f71b5c7d8dc9a\",\"settlement_time\":\"2025-06-05 13:27:23\",\"payment_type\":\"bank_transfer\",\"payment_amounts\":[],\"order_id\":\"INV-PDG91749104806_1749104807\",\"merchant_id\":\"G478634788\",\"gross_amount\":\"3185593.00\",\"fraud_status\":\"accept\",\"expiry_time\":\"2025-06-05 14:26:47\",\"currency\":\"IDR\"}', '2025-06-05 06:27:23', '2025-06-05 13:26:46', NULL, NULL, NULL, '2025-06-05 06:26:46', '2025-06-05 06:27:23');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` bigint UNSIGNED NOT NULL,
  `id_kategori` bigint UNSIGNED NOT NULL,
  `nama_produk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi_singkat` text COLLATE utf8mb4_unicode_ci,
  `deskripsi_lengkap` longtext COLLATE utf8mb4_unicode_ci,
  `harga` decimal(15,2) NOT NULL,
  `stok` int NOT NULL DEFAULT '0',
  `gambar_produk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warna` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `berat` decimal(8,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `id_kategori`, `nama_produk`, `slug`, `deskripsi_singkat`, `deskripsi_lengkap`, `harga`, `stok`, `gambar_produk`, `warna`, `berat`, `created_at`, `updated_at`) VALUES
(1, 1, 'Cabinet Sint Quo 1', 'cabinet-sint-quo-1', 'Odio corrupti nulla maiores praesentium nisi consequatur est at enim reiciendis.', 'Voluptatem natus vel tempora dolor repellendus error inventore. Dignissimos veniam aut possimus consectetur ut voluptatum eum. Architecto ad aliquid provident rerum repellendus. Cupiditate magni commodi corporis tempore.\n\nSoluta autem deserunt porro iste. Voluptatem sint sint commodi recusandae. Excepturi in voluptas provident velit.\n\nEarum repellendus quasi iusto sunt ad. Enim illo dolores occaecati soluta ipsam. Consequatur qui quos non quaerat qui rerum exercitationem quis.', '3147273.00', 14, 'assets/img/product/cabinet/2.png', 'Biru', '0.59', '2025-05-21 21:32:23', '2025-06-05 06:26:46'),
(2, 1, 'Cabinet Eos Eaque 2', 'cabinet-eos-eaque-2', 'Commodi blanditiis et assumenda assumenda molestiae laboriosam officiis quis culpa modi maiores.', 'Architecto minima ex magnam laudantium error. Inventore placeat voluptas nemo omnis repellat cupiditate voluptatem. Eveniet nobis incidunt ipsa voluptas nemo.\n\nCulpa qui perferendis cupiditate nam sint labore repellendus. Nemo harum voluptatem ea. A officiis tenetur harum expedita ut asperiores.\n\nQuaerat vel praesentium corporis. Quo tempora laboriosam voluptas autem impedit voluptatum. Suscipit explicabo ipsum consectetur voluptate et nesciunt similique. Qui architecto pariatur corrupti rerum architecto voluptatum fuga.', '3867019.00', 13, 'assets/img/product/cabinet/3.png', 'Kuning', '4.39', '2025-05-21 21:32:23', '2025-05-24 04:34:55'),
(3, 2, 'Chair Ipsum Consectetur 1', 'chair-ipsum-consectetur-1', 'Porro atque nesciunt ullam vel assumenda quas non quod id dolorem nemo aperiam.', 'Quos ipsam illo ipsa sit in incidunt. Sit suscipit qui dolores debitis. Provident sint cumque sit.\n\nEt omnis eveniet et sint nam magni ratione ea. Ut quidem aut voluptas atque omnis suscipit. Et consectetur aut aperiam laboriosam aut labore modi. Consequatur saepe molestiae in nulla dignissimos et.\n\nIpsum omnis et velit eum. Sit quaerat ea quam cum aut laudantium ratione. Consequuntur aut a voluptates voluptas voluptate quis non. Animi et quia ipsa velit iste.', '4953448.00', 21, 'assets/img/product/chair/2.png', 'Putih', '1.38', '2025-05-21 21:32:23', '2025-05-25 14:25:18'),
(4, 2, 'Chair Omnis Atque 2', 'chair-omnis-atque-2', 'Soluta expedita sint vel id iusto earum dolores ducimus voluptas fugit nulla ea neque.', 'Sit culpa tempora nam fugiat officiis. Voluptate voluptatum et pariatur ducimus similique porro beatae vel. Vero amet fuga dolor et qui nulla. Necessitatibus officia commodi a eum quia consequatur sunt.\n\nEa ut fugiat et minus consequatur numquam. Deserunt ipsam quo mollitia a alias id voluptas quas. Minus consequatur dolor architecto temporibus facilis.\n\nNam nihil sunt molestias. Et similique mollitia voluptates. Sint accusamus debitis reiciendis omnis dolores non. Tempora enim quasi ut consequatur aut magnam quo.', '3384286.00', 11, 'assets/img/product/chair/3.png', 'Biru', '4.74', '2025-05-21 21:32:23', '2025-06-05 04:28:03'),
(5, 3, 'Rak Incidunt Culpa 1', 'rak-incidunt-culpa-1', 'Perspiciatis quisquam perferendis labore consequatur alias officiis ex totam at eum sint.', 'Sed deserunt libero vel rem. Aut quo quo similique. Dolores enim iusto reprehenderit fuga saepe unde et. Harum reiciendis illo nam est voluptates optio nulla optio.\n\nCorrupti praesentium quo et et quia. Autem tempora perspiciatis maxime sed recusandae. Illum praesentium quod quia officia magni voluptas. Delectus et dolor sint quas ab voluptatem aut.\n\nSit deserunt dicta sunt et. Commodi minima voluptatem voluptatum repellendus rem non assumenda. Dolorem laborum sed nulla ipsa. Et ut autem nihil.', '4813359.00', 23, 'assets/img/product/rak/1.png', 'Hitam', '3.03', '2025-05-21 21:32:23', '2025-06-02 15:17:12'),
(6, 3, 'Rak Voluptatibus Unde 2', 'rak-voluptatibus-unde-2', 'Reprehenderit quia nisi quo vero sint saepe harum modi consectetur perspiciatis molestias quibusdam.', 'Eos debitis non fuga autem. Placeat aut quidem at maxime odio aut ad. Repellat molestiae sunt sit qui qui.\n\nEligendi sapiente et sint ut enim assumenda autem. Porro eius possimus libero vel iusto. Atque sit fugiat facere.\n\nIn et quibusdam id quasi vitae non. Est qui qui tenetur esse aspernatur. Sunt voluptatibus eos placeat eaque. Corporis dolores repellendus quia rerum facilis.', '2639405.00', 0, 'assets/img/product/rak/2.png', 'Coklat', '4.12', '2025-05-21 21:32:23', '2025-05-25 07:37:30'),
(7, 4, 'Sofa Modi Qui 1', 'sofa-modi-qui-1', 'Ea hic sint laborum maxime voluptatem nemo dolores quam non minus qui sint sapiente voluptas.', 'Molestias molestiae sequi voluptatem voluptas dolorum autem quaerat. Est at sed velit ut. Laborum iure qui optio aliquam nulla.\n\nSoluta blanditiis enim rerum expedita provident. Consectetur similique quos rerum accusamus provident qui enim. Tenetur rerum facere suscipit totam. Quod quia repudiandae culpa earum quo. Eius saepe nam tempora minus.\n\nAd libero non mollitia sunt iste repudiandae. Nihil ratione minima eius voluptatem tempora itaque laborum perspiciatis. Iusto commodi voluptas fugit quibusdam.', '215654.00', 31, 'assets/img/product/table/1.png', 'Coklat', '2.22', '2025-05-21 21:32:23', '2025-06-05 04:50:17'),
(8, 4, 'Sofa Cumque Omnis 2', 'sofa-cumque-omnis-2', 'Exercitationem quia voluptas veritatis rerum earum esse et iusto.', 'Est quo et libero sed neque quidem sit id. Quasi et qui dolorem illo quibusdam adipisci repellat.\n\nVoluptatem rerum aspernatur accusantium enim. Officia vel facere rem ratione qui est. Perferendis neque sed earum quidem cumque at.\n\nNostrum eos quis reprehenderit. Sunt earum odit consequatur enim iste et quaerat. Rerum ipsam non consequatur sit veritatis expedita tempora. Pariatur minus minima voluptate dignissimos facilis enim.', '3091211.00', 32, 'assets/img/product/table/2.png', 'Merah', '2.84', '2025-05-21 21:32:23', '2025-06-02 14:02:55'),
(9, 5, 'Table Necessitatibus Quia 1', 'table-necessitatibus-quia-1', 'Magni cupiditate rerum officiis error necessitatibus a dolores aut nemo atque modi repudiandae molestias.', 'Repudiandae molestiae id dicta sint officiis aut. Velit placeat minima excepturi saepe vel vel eaque ut. Ut consequuntur et non. Sit est sit qui pariatur incidunt aut. Nobis ipsum id soluta.\n\nQuia modi aut ipsam aperiam laudantium enim voluptatum eos. Ut minima sit vero ut inventore eos aliquid cum.\n\nConsectetur natus fuga neque in repellendus. Est nemo beatae unde officia in ut. Totam excepturi aut voluptatem ex. Aut quasi eius nisi similique voluptatum veritatis.', '1840729.00', 4, 'assets/img/product/table/3.png', 'Hitam', '2.44', '2025-05-21 21:32:23', '2025-05-30 05:28:57'),
(10, 5, 'Table Maxime Hic 2', 'table-maxime-hic-2', 'Et aut suscipit voluptatem maiores quis consectetur beatae.', 'Vel iste animi nostrum iusto ipsam voluptas nobis. Illum et pariatur iusto quo voluptates aut doloribus perferendis. Minus maxime non quas non quas. Excepturi voluptatem quos provident voluptatem expedita quisquam. Soluta id quaerat voluptas sequi.\n\nMinima iusto rem tempore. Dicta dolorum laudantium eos quisquam.\n\nAperiam et doloribus et. Nostrum aut ut maxime alias. Quo commodi sint rerum et iusto. Et magnam iste qui quia.', '1581218.00', 43, 'assets/img/product/cabinet/2.png', 'Putih', '3.42', '2025-05-21 21:32:23', '2025-06-02 13:38:41');

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

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('6t5ojnhstiLlm5XIf4cDpwW8iYYcFidaouPgOsc6', NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQjJLY0w3Z2cyREpTTWZqQmlkekhjaGNFOU9WVE5uVHdhUzg0NE9uTCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9kOGQ3LTExNC0xMC0xNTItMTU4Lm5ncm9rLWZyZWUuYXBwIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749106035),
('AtxrONhR546o5gLx5g0lucGt6dObKUqGVwYMurc6', NULL, '127.0.0.1', 'Veritrans', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiemlHSldMTXZZZGFWRTRmQUYyR3VhUEZwMVZmcktoTWRZUlBaaGY3WSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749104843),
('DHndST5cZkF7SfA8rCHoVOVCJL0r1Unxop0kYR6n', NULL, '127.0.0.1', 'Veritrans', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiQmpLWElSaURyTEFVdDVodkhDM044NmRsaXg1MGZwdTVVVmFSWFZ6diI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749099045),
('gr3Z1bHL0m941tQrvLyGEpVuy2oYK8TjIOtJAH11', NULL, '127.0.0.1', 'Veritrans', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiZ1BmRGowaHN4VXlJM3JOYVBqc1p5UGxIUGpxd0dFdVBOUGFTMmJHRiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749104831),
('H7f1VJ7zvniI5FCpdsACneWbF4u00xT8mi0D5YXa', NULL, '127.0.0.1', 'WhatsApp/2.2514.4 W', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiejAzdjhJM05OMVZpOGNIUGcwQzQwa0wwOVcwUzVQemxwTDVuZ2VYayI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9kOGQ3LTExNC0xMC0xNTItMTU4Lm5ncm9rLWZyZWUuYXBwIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749105279),
('IaSveZfuKJ5LoAQ23ZLMriXpPAXNiC6wOxr6NL2c', NULL, '127.0.0.1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiV2xsZWVmYVJLWk51d0pNaHlNWU9vVzRKcGxhc3VNZFVodHR4ZGlrZyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9kOGQ3LTExNC0xMC0xNTItMTU4Lm5ncm9rLWZyZWUuYXBwIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749105800),
('RlgyhEmBr7YOHgdIygMXC1Rmwhdn8DpHP3qGUDwg', NULL, '127.0.0.1', 'Veritrans', 'YToyOntzOjY6Il90b2tlbiI7czo0MDoiYkFMR1NzY0lKT3FYT1BjOGl5RG5reE5HbWZranBjemx4blU3cXRLTiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1749099059),
('t4vTB4hkRPURbXY6LHjL8LLLW7kTFgISgcHNzpnB', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiSGFpN25ZNmVLU1ltcGVTMDRCZDhkN0VDVGNPV0RQb0tONFFYRVRHMyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDE6Imh0dHA6Ly9kOGQ3LTExNC0xMC0xNTItMTU4Lm5ncm9rLWZyZWUuYXBwIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJwZWxhbmdnYW4iO086MjA6IkFwcFxNb2RlbHNcUGVsYW5nZ2FuIjozMjp7czoxMzoiACoAY29ubmVjdGlvbiI7czo1OiJteXNxbCI7czo4OiIAKgB0YWJsZSI7czoxMjoidGJfcGVsYW5nZ2FuIjtzOjEzOiIAKgBwcmltYXJ5S2V5IjtzOjEyOiJpZF9wZWxhbmdnYW4iO3M6MTA6IgAqAGtleVR5cGUiO3M6MzoiaW50IjtzOjEyOiJpbmNyZW1lbnRpbmciO2I6MTtzOjc6IgAqAHdpdGgiO2E6MDp7fXM6MTI6IgAqAHdpdGhDb3VudCI7YTowOnt9czoxOToicHJldmVudHNMYXp5TG9hZGluZyI7YjowO3M6MTA6IgAqAHBlclBhZ2UiO2k6MTU7czo2OiJleGlzdHMiO2I6MTtzOjE4OiJ3YXNSZWNlbnRseUNyZWF0ZWQiO2I6MDtzOjI4OiIAKgBlc2NhcGVXaGVuQ2FzdGluZ1RvU3RyaW5nIjtiOjA7czoxMzoiACoAYXR0cmlidXRlcyI7YTo4OntzOjEyOiJpZF9wZWxhbmdnYW4iO2k6MTtzOjQ6Im5hbWEiO3M6MTE6Ikx1cW1hbiBBbmFzIjtzOjY6ImFsYW1hdCI7czoxNzoiTmd1dGVyLCBTdWtvaGFyam8iO3M6MTA6Im5vX3RlbGVwb24iO3M6MTI6IjA4ODIxNjY4NjQzMiI7czo1OiJlbWFpbCI7czoyNToibHVxbWFubmF1ZmFsMTcwQGdtYWlsLmNvbSI7czo4OiJ1c2VybmFtZSI7czo0OiJVc2VyIjtzOjg6InBhc3N3b3JkIjtzOjYwOiIkMnkkMTIkb3FKZldhc3dhWUpESmMuTS5hbUVpTzRBM0N0TnlvTUVWQXJjT3ZFb3ZpcmNTWXdVT1hDeTYiO3M6MTg6InByb2ZpbGVfcGhvdG9fcGF0aCI7czo1OToicHJvZmlsZS1waG90b3MvOUZsaklUM1NHV3JUWG5TYlI5cExJakZtQXdJbnl6QXVVeGhORUJSay5qcGciO31zOjExOiIAKgBvcmlnaW5hbCI7YTo4OntzOjEyOiJpZF9wZWxhbmdnYW4iO2k6MTtzOjQ6Im5hbWEiO3M6MTE6Ikx1cW1hbiBBbmFzIjtzOjY6ImFsYW1hdCI7czoxNzoiTmd1dGVyLCBTdWtvaGFyam8iO3M6MTA6Im5vX3RlbGVwb24iO3M6MTI6IjA4ODIxNjY4NjQzMiI7czo1OiJlbWFpbCI7czoyNToibHVxbWFubmF1ZmFsMTcwQGdtYWlsLmNvbSI7czo4OiJ1c2VybmFtZSI7czo0OiJVc2VyIjtzOjg6InBhc3N3b3JkIjtzOjYwOiIkMnkkMTIkb3FKZldhc3dhWUpESmMuTS5hbUVpTzRBM0N0TnlvTUVWQXJjT3ZFb3ZpcmNTWXdVT1hDeTYiO3M6MTg6InByb2ZpbGVfcGhvdG9fcGF0aCI7czo1OToicHJvZmlsZS1waG90b3MvOUZsaklUM1NHV3JUWG5TYlI5cExJakZtQXdJbnl6QXVVeGhORUJSay5qcGciO31zOjEwOiIAKgBjaGFuZ2VzIjthOjA6e31zOjg6IgAqAGNhc3RzIjthOjA6e31zOjE3OiIAKgBjbGFzc0Nhc3RDYWNoZSI7YTowOnt9czoyMToiACoAYXR0cmlidXRlQ2FzdENhY2hlIjthOjA6e31zOjEzOiIAKgBkYXRlRm9ybWF0IjtOO3M6MTA6IgAqAGFwcGVuZHMiO2E6MDp7fXM6MTk6IgAqAGRpc3BhdGNoZXNFdmVudHMiO2E6MDp7fXM6MTQ6IgAqAG9ic2VydmFibGVzIjthOjA6e31zOjEyOiIAKgByZWxhdGlvbnMiO2E6MDp7fXM6MTA6IgAqAHRvdWNoZXMiO2E6MDp7fXM6Mjc6IgAqAHJlbGF0aW9uQXV0b2xvYWRDYWxsYmFjayI7TjtzOjI2OiIAKgByZWxhdGlvbkF1dG9sb2FkQ29udGV4dCI7TjtzOjEwOiJ0aW1lc3RhbXBzIjtiOjA7czoxMzoidXNlc1VuaXF1ZUlkcyI7YjowO3M6OToiACoAaGlkZGVuIjthOjA6e31zOjEwOiIAKgB2aXNpYmxlIjthOjA6e31zOjExOiIAKgBmaWxsYWJsZSI7YTo3OntpOjA7czo0OiJuYW1hIjtpOjE7czo2OiJhbGFtYXQiO2k6MjtzOjEwOiJub190ZWxlcG9uIjtpOjM7czo1OiJlbWFpbCI7aTo0O3M6ODoidXNlcm5hbWUiO2k6NTtzOjg6InBhc3N3b3JkIjtpOjY7czoxODoicHJvZmlsZV9waG90b19wYXRoIjt9czoxMDoiACoAZ3VhcmRlZCI7YToxOntpOjA7czoxOiIqIjt9fXM6MTY6ImlkX3Blc2FuYW5fYWt0aWYiO2k6NTA7fQ==', 1749106929);

-- --------------------------------------------------------

--
-- Table structure for table `tb_pelanggan`
--

CREATE TABLE `tb_pelanggan` (
  `id_pelanggan` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `no_telepon` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profile_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tb_pelanggan`
--

INSERT INTO `tb_pelanggan` (`id_pelanggan`, `nama`, `alamat`, `no_telepon`, `email`, `username`, `password`, `profile_photo_path`) VALUES
(1, 'Luqman Anas', 'Nguter, Sukoharjo', '088216686432', 'luqmannaufal170@gmail.com', 'User', '$2y$12$oqJfWaswaYJDJc.M.amEiO4A3CtNyoMEVArcOvEovircSYwUOXCy6', 'profile-photos/9FljIT3SGWrTXnSbR9pLIjFmAwInyzAuUxhNEBRk.jpg');

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

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` bigint UNSIGNED NOT NULL,
  `kode` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_voucher` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `tipe_diskon` enum('persen','tetap') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nilai_diskon` decimal(15,2) NOT NULL,
  `min_pembelian` decimal(15,2) NOT NULL DEFAULT '0.00',
  `maks_diskon` decimal(15,2) DEFAULT NULL COMMENT 'Untuk tipe persen, batas maksimal diskon',
  `kuota` int DEFAULT NULL,
  `digunakan` int NOT NULL DEFAULT '0',
  `tanggal_mulai` datetime NOT NULL,
  `tanggal_berakhir` datetime NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `kode`, `nama_voucher`, `deskripsi`, `tipe_diskon`, `nilai_diskon`, `min_pembelian`, `maks_diskon`, `kuota`, `digunakan`, `tanggal_mulai`, `tanggal_berakhir`, `aktif`, `created_at`, `updated_at`) VALUES
(1, 'HEMAT10', 'Diskon Hemat 10%', 'Dapatkan potongan harga 10% untuk semua produk tanpa minimum pembelian.', 'persen', '10.00', '0.00', '50000.00', 100, 0, '2025-05-22 04:32:23', '2025-08-22 04:32:23', 1, '2025-05-21 21:32:23', '2025-05-21 21:32:23'),
(2, 'POTONG50K', 'Potongan Langsung Rp 50.000', 'Potongan Rp 50.000 untuk minimal pembelian Rp 200.000.', 'tetap', '50000.00', '200000.00', NULL, 50, 0, '2025-05-22 04:32:23', '2025-07-22 04:32:23', 1, '2025-05-21 21:32:23', '2025-05-21 21:32:23'),
(3, 'ONGKIRGRATIS', 'Gratis Ongkir', 'Gratis ongkos kirim dengan minimal pembelian Rp 150.000 (Maks. Potongan Ongkir Rp 20.000).', 'tetap', '20000.00', '150000.00', '20000.00', 200, 0, '2025-05-12 04:32:23', '2025-06-11 04:32:23', 1, '2025-05-21 21:32:23', '2025-05-21 21:32:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alamat_pengiriman`
--
ALTER TABLE `alamat_pengiriman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alamat_pengiriman_id_pelanggan_foreign` (`id_pelanggan`);

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
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `detail_pesanan_id_pesanan_foreign` (`id_pesanan`),
  ADD KEY `detail_pesanan_id_produk_foreign` (`id_produk`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kategori_nama_kategori_unique` (`nama_kategori`),
  ADD UNIQUE KEY `kategori_slug_unique` (`slug`);

--
-- Indexes for table `kendaraan_pengirim`
--
ALTER TABLE `kendaraan_pengirim`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `keranjang_id_pelanggan_id_produk_unique` (`id_pelanggan`,`id_produk`),
  ADD KEY `keranjang_id_produk_foreign` (`id_produk`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifikasi_id_pesanan_terkait_foreign` (`id_pesanan_terkait`),
  ADD KEY `notifikasi_id_pelanggan_sudah_dibaca_created_at_index` (`id_pelanggan`,`sudah_dibaca`,`created_at`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pesan`
--
ALTER TABLE `pesan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesan_id_produk_konteks_foreign` (`id_produk_konteks`),
  ADD KEY `pesan_id_pelanggan_index` (`id_pelanggan`),
  ADD KEY `pesan_id_pelanggan_created_at_index` (`id_pelanggan`,`created_at`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pesanan_nomor_pesanan_unique` (`nomor_pesanan`),
  ADD KEY `pesanan_id_pelanggan_foreign` (`id_pelanggan`),
  ADD KEY `pesanan_id_alamat_pengiriman_foreign` (`id_alamat_pengiriman`),
  ADD KEY `pesanan_id_voucher_foreign` (`id_voucher`),
  ADD KEY `pesanan_status_pesanan_index` (`status_pesanan`),
  ADD KEY `pesanan_id_kendaraan_pengirim_foreign` (`id_kendaraan_pengirim`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `produk_slug_unique` (`slug`),
  ADD KEY `produk_id_kategori_foreign` (`id_kategori`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `tb_pelanggan`
--
ALTER TABLE `tb_pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD UNIQUE KEY `tb_pelanggan_email_unique` (`email`),
  ADD UNIQUE KEY `tb_pelanggan_username_unique` (`username`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vouchers_kode_unique` (`kode`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alamat_pengiriman`
--
ALTER TABLE `alamat_pengiriman`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `kendaraan_pengirim`
--
ALTER TABLE `kendaraan_pengirim`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `pesan`
--
ALTER TABLE `pesan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tb_pelanggan`
--
ALTER TABLE `tb_pelanggan`
  MODIFY `id_pelanggan` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alamat_pengiriman`
--
ALTER TABLE `alamat_pengiriman`
  ADD CONSTRAINT `alamat_pengiriman_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `tb_pelanggan` (`id_pelanggan`) ON DELETE CASCADE;

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_id_pesanan_foreign` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_id_produk_foreign` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `keranjang_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `tb_pelanggan` (`id_pelanggan`) ON DELETE CASCADE,
  ADD CONSTRAINT `keranjang_id_produk_foreign` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `notifikasi_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `tb_pelanggan` (`id_pelanggan`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifikasi_id_pesanan_terkait_foreign` FOREIGN KEY (`id_pesanan_terkait`) REFERENCES `pesanan` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pesan`
--
ALTER TABLE `pesan`
  ADD CONSTRAINT `pesan_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `tb_pelanggan` (`id_pelanggan`) ON DELETE CASCADE,
  ADD CONSTRAINT `pesan_id_produk_konteks_foreign` FOREIGN KEY (`id_produk_konteks`) REFERENCES `produk` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `pesanan_id_alamat_pengiriman_foreign` FOREIGN KEY (`id_alamat_pengiriman`) REFERENCES `alamat_pengiriman` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `pesanan_id_kendaraan_pengirim_foreign` FOREIGN KEY (`id_kendaraan_pengirim`) REFERENCES `kendaraan_pengirim` (`id`),
  ADD CONSTRAINT `pesanan_id_pelanggan_foreign` FOREIGN KEY (`id_pelanggan`) REFERENCES `tb_pelanggan` (`id_pelanggan`) ON DELETE CASCADE,
  ADD CONSTRAINT `pesanan_id_voucher_foreign` FOREIGN KEY (`id_voucher`) REFERENCES `vouchers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `produk_id_kategori_foreign` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
