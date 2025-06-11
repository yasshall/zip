-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:3306
-- Généré le : mer. 11 juin 2025 à 17:17
-- Version du serveur : 10.6.21-MariaDB-cll-lve-log
-- Version de PHP : 8.3.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `hazubjvk_watches`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Category display name',
  `slug` varchar(100) NOT NULL COMMENT 'URL-friendly category identifier',
  `description` text DEFAULT NULL COMMENT 'Category description',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Product categories table';

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(5, 'test', 'test', '', '2025-06-11 06:34:02');

-- --------------------------------------------------------

--
-- Structure de la table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL COMMENT 'Subscriber email address',
  `status` enum('active','unsubscribed') DEFAULT 'active' COMMENT 'Subscription status',
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Newsletter subscribers table';

--
-- Déchargement des données de la table `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`id`, `email`, `status`, `subscribed_at`) VALUES
(1, 'peekmatecom@gmail.com', 'active', '2025-06-11 04:23:52'),
(2, 'yasserhallou99@gmail.com', 'active', '2025-06-11 05:54:36');

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL COMMENT 'Unique order identifier (LW2024001)',
  `customer_name` varchar(255) NOT NULL COMMENT 'Customer full name',
  `customer_phone` varchar(50) NOT NULL COMMENT 'Customer phone number',
  `customer_email` varchar(255) DEFAULT NULL COMMENT 'Customer email (optional)',
  `customer_address` text NOT NULL COMMENT 'Delivery address',
  `customer_city` varchar(100) NOT NULL COMMENT 'Delivery city',
  `customer_postal_code` varchar(20) DEFAULT NULL COMMENT 'Postal/ZIP code',
  `notes` text DEFAULT NULL COMMENT 'Special delivery instructions',
  `subtotal` decimal(10,2) NOT NULL COMMENT 'Order subtotal before discounts',
  `discount` decimal(10,2) DEFAULT 0.00 COMMENT 'Applied discount amount',
  `total` decimal(10,2) NOT NULL COMMENT 'Final order total',
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'pending' COMMENT 'Order status',
  `payment_method` varchar(50) DEFAULT 'cod' COMMENT 'Payment method (cash on delivery)',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Customer orders table';

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_name`, `customer_phone`, `customer_email`, `customer_address`, `customer_city`, `customer_postal_code`, `notes`, `subtotal`, `discount`, `total`, `status`, `payment_method`, `created_at`, `updated_at`) VALUES
(1, 'LW2025000001', 'yasser hallou', '+212 6 98 877 665', '', 'skdjhv jsdhv ksdjhv', 'casablanca', '2000', '', 3200.00, 0.00, 3200.00, 'delivered', 'cod', '2025-06-11 04:15:23', '2025-06-11 04:24:16'),
(2, 'LW2025000002', 'sisso alkjd lkj', '+212 9 88 776 654', '', 'kjh jkljkh ', 'sjh jh', '5000', '', 4300.00, 860.00, 3440.00, 'pending', 'cod', '2025-06-11 04:42:18', '2025-06-11 04:42:18');

-- --------------------------------------------------------

--
-- Structure de la table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL COMMENT 'Reference to orders table',
  `product_id` int(11) NOT NULL COMMENT 'Reference to products table',
  `product_name` varchar(255) NOT NULL COMMENT 'Product name at time of order',
  `product_price` decimal(10,2) NOT NULL COMMENT 'Product price at time of order',
  `quantity` int(11) NOT NULL COMMENT 'Quantity ordered',
  `total` decimal(10,2) NOT NULL COMMENT 'Line total (price × quantity)'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Order line items table';

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL COMMENT 'Product name/title',
  `slug` varchar(255) NOT NULL COMMENT 'URL-friendly product identifier',
  `description` text DEFAULT NULL COMMENT 'Product description',
  `price` decimal(10,2) NOT NULL COMMENT 'Current selling price',
  `old_price` decimal(10,2) DEFAULT NULL COMMENT 'Original price (for discounts)',
  `image` varchar(500) DEFAULT NULL COMMENT 'Main product image URL',
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array of additional product images' CHECK (json_valid(`images`)),
  `category_id` int(11) DEFAULT NULL COMMENT 'Reference to categories table',
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Product specifications (movement, material, etc.)' CHECK (json_valid(`features`)),
  `is_new` tinyint(1) DEFAULT 0 COMMENT 'Mark as new arrival',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Product visibility status',
  `stock_quantity` int(11) DEFAULT 0 COMMENT 'Available stock count',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Products catalog table';

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `name`, `slug`, `description`, `price`, `old_price`, `image`, `images`, `category_id`, `features`, `is_new`, `is_active`, `stock_quantity`, `created_at`, `updated_at`) VALUES
(8, 'pro1', 'pro1', 'soidfjs odifj sdoifjsodifjsodifjsd oifj', 100.00, 200.00, 'https://images.pexels.com/photos/277390/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=400', '[\"https:\\/\\/images.pexels.com\\/photos\\/277390\\/pexels-photo-277390.jpeg?auto=compress&cs=tinysrgb&w=400\"]', 5, '[]', 1, 1, 200, '2025-06-11 06:34:34', '2025-06-11 06:34:34');

-- --------------------------------------------------------

--
-- Structure de la table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL COMMENT 'Setting identifier',
  `setting_value` text DEFAULT NULL COMMENT 'Setting value',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci COMMENT='Site settings and configuration';

--
-- Déchargement des données de la table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'Sisso Store', '2025-06-11 03:46:58', '2025-06-11 04:31:37'),
(2, 'contact_email', 'info@montreselite.com', '2025-06-11 03:46:58', '2025-06-11 04:23:30'),
(3, 'contact_phone', '+212 691057232', '2025-06-11 03:46:58', '2025-06-11 04:22:55'),
(4, 'whatsapp_number', '+212 691057232', '2025-06-11 03:46:58', '2025-06-11 04:22:55'),
(5, 'facebook_pixel_id', 'YOUR_PIXEL_ID', '2025-06-11 03:46:58', '2025-06-11 03:46:58'),
(6, 'google_analytics_id', 'GA_MEASUREMENT_ID', '2025-06-11 03:46:58', '2025-06-11 03:46:58'),
(7, 'admin_username', 'admin', '2025-06-11 03:46:58', '2025-06-11 03:46:58'),
(8, 'admin_password', 'luxury2024', '2025-06-11 03:46:58', '2025-06-11 03:46:58'),
(9, 'currency', 'MAD', '2025-06-11 03:46:58', '2025-06-11 03:46:58'),
(10, 'timezone', 'Africa/Casablanca', '2025-06-11 03:46:58', '2025-06-11 03:46:58');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_name` (`name`);

--
-- Index pour la table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_customer_phone` (`customer_phone`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_orders_date_status` (`created_at`,`status`);

--
-- Index pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_order_items_reporting` (`product_id`,`quantity`,`total`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_new` (`is_new`),
  ADD KEY `idx_price` (`price`),
  ADD KEY `idx_products_search` (`name`,`description`(100));

--
-- Index pour la table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_setting_key` (`setting_key`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
