-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1
-- Üretim Zamanı: 17 Ara 2025, 22:33:30
-- Sunucu sürümü: 10.4.32-MariaDB
-- PHP Sürümü: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `sky_social`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `parent_id` int(11) DEFAULT NULL,
  `is_pinned` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `comments`
--

INSERT INTO `comments` (`id`, `user_id`, `post_id`, `comment`, `created_at`, `parent_id`, `is_pinned`) VALUES
(18, 4, 12, 'ssdasd', '2025-12-01 18:05:49', NULL, 0),
(19, 6, 13, 'merhaba', '2025-12-01 19:02:26', NULL, 0);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `comment_likes`
--

CREATE TABLE `comment_likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `follows`
--

CREATE TABLE `follows` (
  `id` int(11) NOT NULL,
  `follower_id` int(11) NOT NULL,
  `following_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `follows`
--

INSERT INTO `follows` (`id`, `follower_id`, `following_id`, `created_at`) VALUES
(4, 3, 4, '2025-12-01 07:52:31'),
(5, 4, 3, '2025-12-01 07:55:08'),
(6, 6, 3, '2025-12-01 18:52:27');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `post_id`, `created_at`) VALUES
(17, 3, 4, '2025-12-01 05:39:56'),
(25, 4, 10, '2025-12-01 07:53:54'),
(35, 3, 10, '2025-12-01 17:39:56'),
(37, 3, 9, '2025-12-01 17:39:59'),
(43, 3, 12, '2025-12-01 18:06:18'),
(44, 4, 12, '2025-12-01 18:06:43'),
(46, 6, 15, '2025-12-01 18:52:16'),
(47, 6, 14, '2025-12-01 18:52:18'),
(49, 6, 12, '2025-12-01 18:52:21'),
(50, 6, 9, '2025-12-01 18:52:33'),
(51, 6, 4, '2025-12-01 18:52:34'),
(52, 6, 10, '2025-12-01 18:52:37'),
(54, 6, 13, '2025-12-01 19:02:20');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `type` varchar(20) DEFAULT NULL,
  `post_id` int(11) DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `notifications`
--

INSERT INTO `notifications` (`id`, `receiver_id`, `sender_id`, `type`, `post_id`, `is_read`, `created_at`) VALUES
(6, 4, 3, 'follow', NULL, 1, '2025-12-01 07:52:31'),
(7, 3, 4, 'like', 10, 1, '2025-12-01 07:53:54'),
(8, 3, 4, 'follow', NULL, 1, '2025-12-01 07:55:08'),
(9, 4, 3, 'like', 12, 1, '2025-12-01 17:26:29'),
(10, 4, 3, 'like', 12, 1, '2025-12-01 17:27:06'),
(11, 4, 3, 'comment', 12, 1, '2025-12-01 17:27:25'),
(12, 4, 3, 'like', 12, 1, '2025-12-01 17:32:13'),
(13, 4, 3, 'comment', 12, 1, '2025-12-01 17:32:18'),
(14, 4, 3, 'comment', 12, 1, '2025-12-01 17:33:42'),
(15, 4, 3, 'like', 12, 1, '2025-12-01 17:34:55'),
(16, 4, 3, 'like', 12, 1, '2025-12-01 17:39:44'),
(17, 4, 3, 'like', 12, 1, '2025-12-01 17:39:48'),
(18, 4, 3, 'like', 12, 1, '2025-12-01 17:39:51'),
(19, 4, 3, 'comment', 12, 1, '2025-12-01 17:40:53'),
(20, 4, 3, 'like', 12, 1, '2025-12-01 17:58:22'),
(21, 4, 3, 'comment', 12, 1, '2025-12-01 17:58:26'),
(22, 4, 3, 'comment', 12, 0, '2025-12-01 18:01:53'),
(23, 4, 3, 'like', 12, 0, '2025-12-01 18:03:52'),
(24, 4, 3, 'like', 12, 0, '2025-12-01 18:06:18'),
(25, 3, 5, 'like', 13, 1, '2025-12-01 18:38:41'),
(26, 5, 6, 'like', 14, 0, '2025-12-01 18:52:18'),
(27, 3, 6, 'like', 13, 1, '2025-12-01 18:52:19'),
(28, 4, 6, 'like', 12, 0, '2025-12-01 18:52:21'),
(29, 3, 6, 'follow', NULL, 1, '2025-12-01 18:52:27'),
(30, 3, 6, 'like', 9, 1, '2025-12-01 18:52:33'),
(31, 3, 6, 'like', 4, 1, '2025-12-01 18:52:34'),
(32, 3, 6, 'like', 10, 1, '2025-12-01 18:52:37'),
(33, 3, 6, 'like', 13, 1, '2025-12-01 18:57:09'),
(34, 3, 6, 'like', 13, 1, '2025-12-01 19:02:20'),
(35, 3, 6, 'comment', 13, 1, '2025-12-01 19:02:26');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `location_text` varchar(100) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `captured_at` datetime DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `title`, `description`, `image_path`, `location_text`, `tags`, `captured_at`, `uploaded_at`) VALUES
(4, 3, 'Herkese Merhaba', 'Herkese merhabalar, bu benim ilk gönderim :)', NULL, 'Bartın', '#yeni', '2025-11-30 22:50:00', '2025-11-30 19:51:42'),
(9, 3, 'deneme', '', 'uploads/1764564067_692d1c63af3bb.png', 'Bartın', '', '2025-12-01 07:40:00', '2025-12-01 04:41:21'),
(10, 3, 'Nebula', '', 'uploads/1764567473_692d29b1c8c1b.jpg', 'Bartın Merkez', 'nebula', '2025-12-01 08:37:00', '2025-12-01 05:37:53'),
(12, 4, 'space', 'deneme', 'uploads/1764575763_692d4a13a548f.png', 'Bartın Merkez', 'deneme', '2025-12-01 10:55:00', '2025-12-01 07:56:03'),
(13, 3, 'sdasdasdasd', '', NULL, 'Bartın Merkez', '#ay #uzay #dolunay #zaman', '2025-12-01 21:09:00', '2025-12-01 18:10:07'),
(14, 5, 'a', '', NULL, 'Türkiye', '#ay #uzay #dolunay #zaman', '2025-12-01 21:46:00', '2025-12-01 18:46:46'),
(15, 6, 'Orion', 'İnternet görsellerden buldum ve güzel göründüğü için sizinle paylaşmak istedim', 'uploads/1764615131_692de3db9e638.jpg', 'Space', '#orion #uzay #space', '2025-11-11 23:55:00', '2025-12-01 18:52:12'),
(17, 7, 'Nebula', 'Mükemmel ya', 'uploads/1766006274_69431e0293a8b.webp', 'Dünya', '#nebula #uzat', '2025-12-18 00:17:00', '2025-12-17 21:17:54');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT 'default_avatar.png',
  `role_id` int(11) DEFAULT 2,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `bio` varchar(255) DEFAULT 'Merhaba, ben yeni bir gözlemciyim!',
  `is_verified` tinyint(4) DEFAULT 0,
  `email_otp` varchar(6) DEFAULT NULL,
  `phone_otp` varchar(6) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `theme_bg` varchar(7) DEFAULT '#0f101a',
  `theme_surface` varchar(7) DEFAULT '#1c1d2b',
  `theme_primary` varchar(7) DEFAULT '#3f88ff',
  `image_quality` varchar(10) DEFAULT 'medium',
  `language` varchar(5) DEFAULT 'auto',
  `birth_date` date DEFAULT NULL,
  `last_username_change` datetime DEFAULT NULL,
  `last_fullname_change` datetime DEFAULT NULL,
  `pending_email` varchar(100) DEFAULT NULL,
  `pending_phone` varchar(20) DEFAULT NULL,
  `update_otp` varchar(6) DEFAULT NULL,
  `is_banned` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Tablo döküm verisi `users`
--

INSERT INTO `users` (`id`, `username`, `full_name`, `email`, `phone`, `password`, `location`, `profile_pic`, `role_id`, `created_at`, `bio`, `is_verified`, `email_otp`, `phone_otp`, `otp_expiry`, `theme_bg`, `theme_surface`, `theme_primary`, `image_quality`, `language`, `birth_date`, `last_username_change`, `last_fullname_change`, `pending_email`, `pending_phone`, `update_otp`, `is_banned`) VALUES
(3, 'yasinozen', 'Yasin ÖZEN', 'spacebsnss@gmail.com', '5385998492', '$2y$10$.grLz7a0yJJctbzT2osc4.KptQQQMOv3rE5G4ikQ7SQBF7sgzE2wm', 'Bartın Merkez', 'uploads/p_3_1764532227.jpg', 2, '2025-11-30 19:46:58', 'Merhaba, ben yeni bir gözlemciyim!', 1, '290897', '847778', '2025-11-30 21:03:08', '#0f101a', '#1c1d2b', '#3f88ff', 'low', 'tr', '2003-09-29', NULL, NULL, NULL, NULL, NULL, 0),
(4, 'dersdeneme', 'Serkan AKSU', 'dedededme@gmail.com', '589589589589', '$2y$10$kaA8Bp4735LoC.5cEy9QDOJu0csg2/eLYB/VUJXwmSeuOxH2dieuO', 'Bartın Merkez', 'https://ui-avatars.com/api/?name=Serkan+AKSU&background=3f88ff&color=fff', 1, '2025-12-01 04:51:58', 'Merhaba, ben yeni bir gözlemciyim!', 1, NULL, NULL, '2025-12-01 06:06:58', '#0f101a', '#1c1d2b', '#3f88ff', 'low', 'auto', '1970-07-28', NULL, NULL, NULL, NULL, NULL, 0),
(5, 'admin1', 'admin1', 'admin@gmail.com', '5555555551', '$2y$10$sTUsv1xseFv318X7BUpr2.1G8Qpj2sMnc59fXLHyJajsSKyu.t.Hi', 'Türkiye', 'https://ui-avatars.com/api/?name=admin&background=3f88ff&color=fff', 0, '2025-12-01 18:34:52', 'Merhaba, ben yeni bir gözlemciyim!', 1, NULL, NULL, '2025-12-01 19:49:52', '#0f101a', '#1c1d2b', '#3f88ff', 'medium', 'auto', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(6, 'space_odsye', 'Space ODSYE', 'mail@gmail.com', '5635638962', '$2y$10$A5wVeqiN1.xHGuUS4PzdfuHKg1h.S4dgay4GIdIxRgmuLC5dyQtAq', 'Space', 'https://ui-avatars.com/api/?name=Space+ODSYE&background=3f88ff&color=fff', 2, '2025-12-01 18:50:30', 'Merhaba, ben yeni bir gözlemciyim!', 1, NULL, NULL, '2025-12-01 20:05:30', '#0f101a', '#1c1d2b', '#3f88ff', 'medium', 'auto', NULL, NULL, NULL, NULL, NULL, NULL, 0),
(7, 'yasinvefizik', 'Yasin ÖZEN', 'yasinvefizik@gmail.com', '05385998492', '$2y$10$Fh6K/5jF2tAl6.qtYGNaGOy8J37MyRaKpcPqpr8vifYYMtubiBYVu', 'bartın', 'uploads/p_7_1766006308.jpg', 2, '2025-12-17 21:15:26', 'Merhaba, ben yeni bir gözlemciyim!', 1, NULL, NULL, '2025-12-17 22:30:26', '#0f101a', '#1c1d2b', '#3f88ff', 'medium', 'auto', '2003-09-29', NULL, NULL, NULL, NULL, NULL, 0);

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Tablo için indeksler `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`comment_id`),
  ADD KEY `comment_id` (`comment_id`);

--
-- Tablo için indeksler `follows`
--
ALTER TABLE `follows`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `follower_id` (`follower_id`,`following_id`),
  ADD KEY `follower_id_2` (`follower_id`),
  ADD KEY `following_id` (`following_id`);

--
-- Tablo için indeksler `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Tablo için indeksler `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `sender_id` (`sender_id`);

--
-- Tablo için indeksler `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `uploaded_at` (`uploaded_at`);

--
-- Tablo için indeksler `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Tablo için AUTO_INCREMENT değeri `comment_likes`
--
ALTER TABLE `comment_likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Tablo için AUTO_INCREMENT değeri `follows`
--
ALTER TABLE `follows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Tablo için AUTO_INCREMENT değeri `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- Tablo için AUTO_INCREMENT değeri `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Tablo için AUTO_INCREMENT değeri `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Tablo için AUTO_INCREMENT değeri `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Dökümü yapılmış tablolar için kısıtlamalar
--

--
-- Tablo kısıtlamaları `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `comment_likes`
--
ALTER TABLE `comment_likes`
  ADD CONSTRAINT `comment_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comment_likes_ibfk_2` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `follows`
--
ALTER TABLE `follows`
  ADD CONSTRAINT `follows_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `follows_ibfk_2` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Tablo kısıtlamaları `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
