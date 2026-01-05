-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- ホスト: mysql
-- 生成日時: 2026 年 1 月 04 日 06:27
-- サーバのバージョン： 8.4.7
-- PHP のバージョン: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- データベース: `laravel`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `care_records`
--

CREATE TABLE `care_records` (
  `id` bigint UNSIGNED NOT NULL,
  `client_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `body_temp` decimal(4,1) DEFAULT NULL,
  `blood_pressure_high` int DEFAULT NULL,
  `blood_pressure_low` int DEFAULT NULL,
  `water_intake` int DEFAULT NULL,
  `recorded_by` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recorded_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `care_records`
--

INSERT INTO `care_records` (`id`, `client_id`, `content`, `body_temp`, `blood_pressure_high`, `blood_pressure_low`, `water_intake`, `recorded_by`, `recorded_at`, `created_at`, `updated_at`) VALUES
(1, 'A001', 'うどんを買った', 36.8, 123, 78, 200, 'kyo', '2025-12-01 10:42:00', '2025-12-29 22:42:46', '2026-01-01 16:13:19'),
(2, 'A001', 'うどんを完食しました', 36.7, 123, 87, 120, 'kyo', '2025-12-28 11:35:00', '2025-12-29 23:36:23', '2025-12-29 23:36:23'),
(3, 'A001', '咳と発熱と風邪症状あり要観察', 37.8, 145, 90, 300, 'kyo', '2025-12-29 11:46:00', '2025-12-29 23:47:19', '2026-01-01 15:35:36'),
(4, 'A001', '食事の少し前から元気がない様子だった。 今日の献立は好みのメニューではなく、ほとんど箸をつけられません。体調不良ではなく嗜好の問題のため、無理強いはせずに様子を見ることにしました。', 36.8, 123, 89, 123, 'kyo', '2025-12-16 12:00:00', '2026-01-01 14:18:43', '2026-01-01 14:18:43'),
(5, 'A001', '普段から食事をよく噛まずに飲み込んでしまうことが多いようで、そのためむせ込んでしまうことがある。なるべく職員が近くにいるようにして、ゆっくり食べることと、口に詰め込み過ぎないように声かけを行う。今後も、むせ混むことが多いようであれば、食事形態の変更も必要と思われる。', 36.7, 145, 120, 100, 'kyo', '2025-12-17 10:00:00', '2026-01-01 14:20:32', '2026-01-01 14:20:32'),
(6, 'A001', '検温すると平熱より高かったため、看護師に報告し本日の入浴は中止した。', 37.9, 123, 89, 200, 'kyo', '2025-12-19 11:00:00', '2026-01-01 14:21:38', '2026-01-01 14:21:38'),
(7, 'A001', '便意をもよおして、トイレに行こうとされるも、場所がわからなくなってしまうので、職員が声かけ、誘導し排泄した。', 37.0, 110, 77, 120, 'kyo', '2025-12-19 11:00:00', '2026-01-01 14:23:09', '2026-01-01 14:23:09'),
(8, 'A001', 'ポータブルトイレでなんとか自力で排泄することが出来た。ポータブルトイレの後始末は職員が行った。', 36.8, 123, 78, 120, 'kyo', '2025-12-21 11:00:00', '2026-01-01 14:24:43', '2026-01-01 14:24:43'),
(9, 'A001', '昨晩より高熱がつつく、倦怠感が強く食事もとれていない状況。\n娘さんに相談してかかりつけ医の井上内科クリニックを受診。\nインフルエンザA型要請との診断となり足元がふらついている為2,3日の入院となった。', 38.9, 145, 110, 65, 'kyo', '2025-12-25 10:00:00', '2026-01-01 15:08:16', '2026-01-03 14:19:07'),
(10, 'A001', '血圧が高めだったので入浴は停止してお風呂で清拭を行った。', 36.9, 145, 115, 230, 'kyo', '2025-12-17 12:00:00', '2026-01-03 14:20:20', '2026-01-03 14:20:20'),
(11, 'A001', '今日は体調もよく食事を完食されました', 36.9, 134, 111, 120, 'kyo', '2025-12-20 11:00:00', '2026-01-03 16:05:34', '2026-01-03 16:05:34');

-- --------------------------------------------------------

--
-- テーブルの構造 `clients`
--

CREATE TABLE `clients` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `office_id` bigint UNSIGNED DEFAULT NULL,
  `client_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postcode` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_tel` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `insurace_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `care_start_date` date DEFAULT NULL,
  `care_end_date` date DEFAULT NULL,
  `care_manager` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `care_manager_tel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `clients`
--

INSERT INTO `clients` (`id`, `office_id`, `client_name`, `postcode`, `address`, `contact_tel`, `insurace_number`, `care_start_date`, `care_end_date`, `care_manager`, `care_manager_tel`, `created_at`, `updated_at`) VALUES
('A0003', 1, '利用者　虎次郎', '8120002', '福岡県福岡市博多区空港前2-1', '090-7777-8888', '1234567890', '2025-10-01', '2026-09-30', '田中ケアマネ', '090-9999-2222', '2026-01-03 14:20:59', '2026-01-03 14:20:59'),
('A001', 1, '田中　太郎', '8000001', '北九州市戸畑区中原新町2-1', '000-111-2222', '0123456788', '2025-01-01', '2026-10-31', '田中ケアマネジャー', '090-2222-3333', '2025-12-29 21:42:57', '2025-12-29 21:42:57'),
('A002', 1, '介護　イサオ', '8000001', 'bhogqh', '000-222-2222', '1234567890', '2025-07-01', '2026-06-30', '田中ケアマネジャー', '090-333-4444', '2026-01-01 13:59:43', '2026-01-01 13:59:43');

-- --------------------------------------------------------

--
-- テーブルの構造 `failed_jobs`
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
-- テーブルの構造 `jobs`
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
-- テーブルの構造 `job_batches`
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
-- テーブルの構造 `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(19, '0001_01_01_000000_create_users_table', 1),
(20, '0001_01_01_000001_create_cache_table', 1),
(21, '0001_01_01_000002_create_jobs_table', 1),
(22, '2025_12_28_140201_create_personal_access_tokens_table', 1),
(23, '2025_12_28_150339_create_care_tables', 1),
(24, '2025_12_28_161951_add_vitals_to_care_records_table', 1),
(25, '2025_12_29_143926_create_offices_table', 1),
(26, '2025_12_29_144833_add_office_id_to_users_and_clients_table', 1),
(27, '2025_12_29_202847_add_details_to_clients_table', 1),
(28, '2025_12_29_222248_add_vitals_to_care_records_table', 2);

-- --------------------------------------------------------

--
-- テーブルの構造 `offices`
--

CREATE TABLE `offices` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postcode` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `offices`
--

INSERT INTO `offices` (`id`, `name`, `postcode`, `address`, `tel`, `created_at`, `updated_at`) VALUES
(1, 'テスト介護事業所', '8020001', '福岡県北九州市小倉北区', '093-000-0000', '2025-12-29 21:24:57', '2025-12-29 21:24:57');

-- --------------------------------------------------------

--
-- テーブルの構造 `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- テーブルの構造 `sessions`
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
-- テーブルのデータのダンプ `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('qOo3iFvbUuo0dvTyoGRMGD9uFGZa40rIdQMHCZEb', 1, '172.18.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoidUZUWDBxZ3lhbTRleVZRcU8xUlhhWUtXa0VFaFYyRm8xcEI2VXdjZyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly9sb2NhbGhvc3Qvd2ViLWFwaS9hbGwtcmVjb3JkcyI7czo1OiJyb3V0ZSI7Tjt9czozOiJ1cmwiO2E6MDp7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1767429238);

-- --------------------------------------------------------

--
-- テーブルの構造 `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `office_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- テーブルのデータのダンプ `users`
--

INSERT INTO `users` (`id`, `office_id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, 'kyo', 'kyoukonomura@emsystems.co.jp', NULL, '$2y$12$OjCP5Hf675a5hCfnih/NLuQdDz8c9q7qdYDtXfVg1fiaHTb5wb/uy', 'UsQQ4lVM6ZaNFBna0SiQUEKGLahn0I3tdtcJ9CyEzpy9bd2mwMNXzBufTOKg', '2025-12-29 21:09:18', '2026-01-03 15:03:20'),
(2, 1, '職員　ハナコ', 'hanako@co.jp', NULL, '$2y$12$og.mJ7jxtT0m2YNnuoP8J.2E8YlrcmVvJpuOz4I/2DGzTeUOzcZXW', NULL, '2026-01-02 16:23:35', '2026-01-02 16:23:35');

--
-- ダンプしたテーブルのインデックス
--

--
-- テーブルのインデックス `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- テーブルのインデックス `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- テーブルのインデックス `care_records`
--
ALTER TABLE `care_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `care_records_client_id_foreign` (`client_id`);

--
-- テーブルのインデックス `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `clients_office_id_foreign` (`office_id`);

--
-- テーブルのインデックス `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- テーブルのインデックス `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- テーブルのインデックス `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`id`);

--
-- テーブルのインデックス `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- テーブルのインデックス `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- テーブルのインデックス `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- テーブルのインデックス `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_office_id_foreign` (`office_id`);

--
-- ダンプしたテーブルの AUTO_INCREMENT
--

--
-- テーブルの AUTO_INCREMENT `care_records`
--
ALTER TABLE `care_records`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- テーブルの AUTO_INCREMENT `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- テーブルの AUTO_INCREMENT `offices`
--
ALTER TABLE `offices`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- テーブルの AUTO_INCREMENT `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- テーブルの AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- ダンプしたテーブルの制約
--

--
-- テーブルの制約 `care_records`
--
ALTER TABLE `care_records`
  ADD CONSTRAINT `care_records_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- テーブルの制約 `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE;

--
-- テーブルの制約 `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_office_id_foreign` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
