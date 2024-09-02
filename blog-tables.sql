-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 02, 2024 at 07:17 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `blog1`
--

-- --------------------------------------------------------

--
-- Table structure for table `analytics`
--

CREATE TABLE `analytics` (
                             `id` int(11) NOT NULL,
                             `post_id` int(11) NOT NULL,
                             `views` int(11) DEFAULT 0,
                             `likes` int(11) DEFAULT 0,
                             `comments` int(11) DEFAULT 0,
                             `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                             `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blog_posts`
--

CREATE TABLE `blog_posts` (
                              `id` int(11) NOT NULL,
                              `user_id` int(11) NOT NULL,
                              `title` varchar(255) NOT NULL,
                              `content` longtext DEFAULT NULL,
                              `category_id` int(11) NOT NULL,
                              `image_url` varchar(255) DEFAULT NULL,
                              `tags` varchar(255) DEFAULT NULL,
                              `published_at` timestamp NULL DEFAULT NULL,
                              `template_id` int(11) DEFAULT NULL,
                              `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                              `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                              `is_featured` tinyint(1) DEFAULT 0,
                              `restricted_to_followers` tinyint(1) DEFAULT 0,
                              `status` enum('draft','published') DEFAULT 'published'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `user_id`, `title`, `content`, `category_id`, `image_url`, `tags`, `published_at`, `template_id`, `created_at`, `updated_at`, `is_featured`, `restricted_to_followers`, `status`) VALUES
    (12, 2, 'first user blog', '<p>this is my first blog okk</p><p>bbb</p>', 6, '1724823611_1656388031blog6.jpg', NULL, NULL, NULL, '2024-07-29 02:27:09', '2024-08-29 06:26:57', 1, 0, 'published');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
                              `id` int(11) NOT NULL,
                              `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
                                            (1, 'Art'),
                                            (5, 'Food'),
                                            (4, 'Music'),
                                            (2, 'Technology'),
                                            (6, 'Travel'),
                                            (3, 'Wildlife');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
                            `id` int(11) NOT NULL,
                            `post_id` int(11) NOT NULL,
                            `user_id` int(11) NOT NULL,
                            `content` text NOT NULL,
                            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                            `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                            `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `post_id`, `user_id`, `content`, `created_at`, `updated_at`, `parent_id`) VALUES
    (561, 12, 2, 'f', '2024-08-29 11:14:34', '2024-08-29 11:14:34', 0);

-- --------------------------------------------------------

--
-- Table structure for table `followers`
--

CREATE TABLE `followers` (
                             `id` int(11) NOT NULL,
                             `follower_id` int(11) NOT NULL,
                             `followed_id` int(11) NOT NULL,
                             `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `followers`
--

INSERT INTO `followers` (`id`, `follower_id`, `followed_id`, `created_at`) VALUES
                                                                               (234, 1, 2, '2024-08-29 06:35:29'),
                                                                               (247, 2, 1, '2024-09-02 17:12:12');

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
                         `id` int(11) NOT NULL,
                         `post_id` int(11) NOT NULL,
                         `user_id` int(11) NOT NULL,
                         `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `post_id`, `user_id`, `created_at`) VALUES
                                                                   (160, 12, 1, '2024-08-29 06:35:30'),
                                                                   (165, 12, 2, '2024-09-02 17:11:59');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
                                   `id` int(11) NOT NULL,
                                   `email` varchar(255) NOT NULL,
                                   `token` varchar(255) NOT NULL,
                                   `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`) VALUES
                                                                         (37, 'suchitmashelkar11@gmail.com', 'f9e8b61fd6f4af556e1e48720c354715a630cbe869f64ad96619ba09086962f2015b0284f272b8c556eddeb881cfae78d834', '2024-08-08 22:03:00'),
                                                                         (39, 'suchitmashelkar11@gmail.com', '4edb698f20473b86025269771e2ade930cb73db27f4eab59e5366917745cb192f9c2bb8bad257796d164687510c0f226c53b', '2024-08-30 06:42:15'),
                                                                         (41, 'suchitmashelkar11@gmail.com', '81a9ab3d9e884622ad4b913444e02ba7d83b1336c9325e090d976637146047bedf3899d412f080d5c1c793b0359a9842ee0d', '2024-09-02 08:14:51'),
                                                                         (42, 'suchitmashelkar11@gmail.com', 'b4f13ce759ba3d3507c9e40f639f829fbe50b5b8b5ac0715c5c7ecb57ce002a0a48ae0d3f8e301a33e38342c184e5fc30c51', '2024-09-02 18:21:01'),
                                                                         (43, 'suchitmashelkar007@gmail.com', '21a9eced52f71f56f6cce8b4cafd3da28ca80379336a6efafe28ce112ee4cfcea875df6de9489fbf43a6b52af5c9cc31503b', '2024-09-02 18:21:50'),
                                                                         (44, 'suchitmashelkar007@gmail.com', '3a43ac81caed6c4cc6e63a741728abf3329f700babb8e350156cb0f4ced9e6c3e1248b5e91fa6e1ab97c1672dee6689f04aa', '2024-09-02 18:24:27'),
                                                                         (45, 'suchitmashelkar007@gmail.com', '0e3dd3056cd36f41ea7b5ed93868f53508eaf6d1700021a5e169dab7c49111b7032e01429394df0f7cb967fe9e9f07b9b20c', '2024-09-02 18:25:16'),
                                                                         (46, 'suchitmashelkar007@gmail.com', 'a7e22867eae50453d32417e1c3971d5552db81fe6ef231737d5a1f9cb36703e836bff0db7e888c2a27003116ed296e5d9ee3', '2024-09-02 21:46:29');

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE `templates` (
                             `id` int(11) NOT NULL,
                             `name` varchar(100) NOT NULL,
                             `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                             `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `templates`
--

INSERT INTO `templates` (`id`, `name`, `created_at`, `updated_at`) VALUES
                                                                       (1, 'Template 1', '2024-07-29 09:20:29', '2024-07-29 09:20:29'),
                                                                       (2, 'Template 2', '2024-07-29 09:20:29', '2024-07-29 09:20:29'),
                                                                       (3, 'Template 3', '2024-07-29 09:20:29', '2024-07-29 09:20:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
                         `id` int(11) NOT NULL,
                         `username` varchar(50) NOT NULL,
                         `email` varchar(100) NOT NULL,
                         `password` varchar(255) NOT NULL,
                         `profile_info` text DEFAULT NULL,
                         `avatar_url` varchar(255) DEFAULT NULL,
                         `is_admin` tinyint(1) NOT NULL DEFAULT 0,
                         `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                         `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `profile_info`, `avatar_url`, `is_admin`, `created_at`, `updated_at`) VALUES
                                                                                                                                      (1, 'adminuser', 'suchitmashelkar11@gmail.com', '$2y$10$iEAhT6DdqGRiIymGN2TdOOxdmlRithwc/qKEuPSLIvE5hBmcT2mE.', 'this is the admin of this website ok', '1725268250-1656388031blog6.jpg', 1, '2024-07-25 05:30:52', '2024-09-02 09:10:50'),
                                                                                                                                      (2, 'suchit_12', 'suchitmashelkar007@gmail.com', '$2y$10$YrShcB.m/ABoSoDa1wnTkOICNf5t9pHxaAWESoZtxI3rDZHlp5x9G', 'this is me okk alrightt', '1725290669-1656394367blog60.jpg', 0, '2024-07-25 06:51:13', '2024-09-02 15:24:29'),
                                                                                                                                      (26, 'suchit_09', 'suchitmashelkar09@gmail.com', '$2y$10$a36eirhO9/506we37ISSsOwaywPxx.NVQk/55WvPiA0ejrXgSGUPG', '', 'default_avatar.png', 0, '2024-08-30 06:35:17', '2024-08-30 06:51:45'),
                                                                                                                                      (28, 'suchit_14', 'suchitmashelkar14@gmail.com', '$2y$10$DaaIZhzIpVs3ZRgVMS42D.CPc21dL1sezrC3rWPMQesroII709XqO', NULL, 'default_avatar.png', 0, '2024-08-30 10:14:19', '2024-08-30 10:14:19'),
                                                                                                                                      (29, 'dehfhh', 'dsdsdh@gmail.com', '$2y$10$d7zf8FdS2hVtyyfxe7u1kOYsnPihxZSC0dI9xGf8Sqz8LKa4l4pEO', NULL, 'default_avatar.png', 0, '2024-09-02 01:17:03', '2024-09-02 01:17:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `analytics`
--
ALTER TABLE `analytics`
    ADD PRIMARY KEY (`id`),
    ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `blog_posts`
--
ALTER TABLE `blog_posts`
    ADD PRIMARY KEY (`id`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `category_id` (`category_id`),
    ADD KEY `template_id` (`template_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
    ADD PRIMARY KEY (`id`),
    ADD KEY `post_id` (`post_id`),
    ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `followers`
--
ALTER TABLE `followers`
    ADD PRIMARY KEY (`id`),
    ADD KEY `follower_id` (`follower_id`),
    ADD KEY `followed_id` (`followed_id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `post_id` (`post_id`,`user_id`),
    ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
    ADD PRIMARY KEY (`id`),
    ADD KEY `email` (`email`),
    ADD KEY `token` (`token`);

--
-- Indexes for table `templates`
--
ALTER TABLE `templates`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `username` (`username`),
    ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `analytics`
--
ALTER TABLE `analytics`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `blog_posts`
--
ALTER TABLE `blog_posts`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2726;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=576;

--
-- AUTO_INCREMENT for table `followers`
--
ALTER TABLE `followers`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=248;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `templates`
--
ALTER TABLE `templates`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `analytics`
--
ALTER TABLE `analytics`
    ADD CONSTRAINT `analytics_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`);

--
-- Constraints for table `blog_posts`
--
ALTER TABLE `blog_posts`
    ADD CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    ADD CONSTRAINT `blog_posts_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
    ADD CONSTRAINT `blog_posts_ibfk_3` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`);

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
    ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`),
    ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `followers`
--
ALTER TABLE `followers`
    ADD CONSTRAINT `followers_ibfk_1` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`),
    ADD CONSTRAINT `followers_ibfk_2` FOREIGN KEY (`followed_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
    ADD CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`),
    ADD CONSTRAINT `likes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
