-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 13, 2025 at 08:58 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pst`
--

-- --------------------------------------------------------

--
-- Table structure for table `rpos_admin`
--

CREATE TABLE `rpos_admin` (
  `admin_id` varchar(200) NOT NULL,
  `admin_name` varchar(200) NOT NULL,
  `admin_email` varchar(200) NOT NULL,
  `admin_password` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rpos_admin`
--

INSERT INTO `rpos_admin` (`admin_id`, `admin_name`, `admin_email`, `admin_password`) VALUES
('1', 'Administrator', 'pastilsatabi.pagasa@gmail.com', '$2y$10$IAlxlxwhMu9m8FwAyxoUMeOE7iRmboviAOTKRL5pspb/nC3JfUTyy');

-- --------------------------------------------------------

--
-- Table structure for table `rpos_categories`
--

CREATE TABLE `rpos_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rpos_customers`
--

CREATE TABLE `rpos_customers` (
  `customer_id` varchar(200) NOT NULL,
  `customer_name` varchar(200) NOT NULL,
  `customer_phoneno` varchar(200) NOT NULL,
  `customer_email` varchar(200) NOT NULL,
  `customer_password` varchar(200) NOT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `verification_token` varchar(64) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rpos_economic_data`
--

CREATE TABLE `rpos_economic_data` (
  `id` int(11) NOT NULL,
  `data_date` date NOT NULL,
  `inflation_rate` decimal(5,2) NOT NULL,
  `unemployment_rate` decimal(5,2) NOT NULL,
  `consumer_confidence` decimal(5,2) NOT NULL,
  `impact_factor` decimal(4,3) NOT NULL DEFAULT 1.000,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rpos_feedback`
--

CREATE TABLE `rpos_feedback` (
  `feedback_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `feedback_text` text NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rpos_feedback`
--

INSERT INTO `rpos_feedback` (`feedback_id`, `rating`, `feedback_text`, `email`, `created_at`, `is_read`, `read_at`) VALUES
(2, 5, 'Food was flavorful and well-seasoned. The rice was cooked properly and the pastil filling had a good balance of taste. Portions were reasonable for the price. The packaging was neat and easy to handle. Service was friendly and orders were prepared on time. The dining area was clean and organized. It would be better to add more variety for drinks and side dishes. Overall, a good experience and worth recommending.', 'benjamin@gmail.com', '2025-08-31 12:25:12', 1, '2025-09-13 23:35:40'),
(4, 4, 'The pastil was tasty and well-cooked. Serving size matched the price. Service was fast and friendly. Clean and organized place. Would recommend adding more drink options.', 'cardodalisay@gmail.com', '2025-08-31 12:26:31', 1, '2025-09-13 23:35:35');

-- --------------------------------------------------------

--
-- Table structure for table `rpos_feedback_replies`
--

CREATE TABLE `rpos_feedback_replies` (
  `reply_id` int(11) NOT NULL,
  `feedback_id` int(11) NOT NULL,
  `admin_email` varchar(255) NOT NULL,
  `reply_text` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rpos_forecast_history`
--

CREATE TABLE `rpos_forecast_history` (
  `forecast_id` int(11) NOT NULL,
  `product_id` varchar(200) NOT NULL,
  `forecast_date` date NOT NULL,
  `predicted_demand` decimal(10,2) NOT NULL,
  `confidence_level` decimal(5,2) NOT NULL,
  `forecast_method` varchar(50) DEFAULT 'advanced',
  `external_factors` text DEFAULT NULL COMMENT 'JSON of external factors used',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rpos_forecast_validations`
--

CREATE TABLE `rpos_forecast_validations` (
  `validation_id` int(11) NOT NULL,
  `product_id` varchar(200) NOT NULL,
  `validation_date` datetime NOT NULL DEFAULT current_timestamp(),
  `accuracy_7_days` decimal(5,2) DEFAULT NULL,
  `accuracy_14_days` decimal(5,2) DEFAULT NULL,
  `accuracy_30_days` decimal(5,2) DEFAULT NULL,
  `overall_rating` enum('excellent','good','fair','poor','very_poor','insufficient_data') NOT NULL,
  `mae` decimal(10,2) DEFAULT NULL COMMENT 'Mean Absolute Error',
  `mse` decimal(10,2) DEFAULT NULL COMMENT 'Mean Squared Error',
  `rmse` decimal(10,2) DEFAULT NULL COMMENT 'Root Mean Squared Error',
  `mape` decimal(5,2) DEFAULT NULL COMMENT 'Mean Absolute Percentage Error',
  `bias` decimal(10,2) DEFAULT NULL COMMENT 'Forecast Bias',
  `recommendations` text DEFAULT NULL COMMENT 'JSON array of recommendations'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rpos_holiday_data`
--

CREATE TABLE `rpos_holiday_data` (
  `id` int(11) NOT NULL,
  `holiday_date` date NOT NULL,
  `holiday_name` varchar(100) NOT NULL,
  `is_holiday` tinyint(1) NOT NULL DEFAULT 1,
  `impact_factor` decimal(4,3) NOT NULL DEFAULT 1.000,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rpos_holiday_data`
--

INSERT INTO `rpos_holiday_data` (`id`, `holiday_date`, `holiday_name`, `is_holiday`, `impact_factor`, `created_at`, `updated_at`) VALUES
(1, '2024-01-01', 'New Year\'s Day', 1, 1.300, '2025-09-12 17:31:52', '2025-09-12 17:31:52'),
(2, '2024-02-14', 'Valentine\'s Day', 1, 1.200, '2025-09-12 17:31:52', '2025-09-12 17:31:52'),
(3, '2024-03-15', 'Araw ng Kagitingan', 1, 1.100, '2025-09-12 17:31:52', '2025-09-12 17:31:52'),
(4, '2024-04-09', 'Araw ng Kagitingan', 1, 1.100, '2025-09-12 17:31:52', '2025-09-12 17:31:52'),
(5, '2024-05-01', 'Labor Day', 1, 1.100, '2025-09-12 17:31:52', '2025-09-12 17:31:52'),
(6, '2024-06-12', 'Independence Day', 1, 1.200, '2025-09-12 17:31:52', '2025-09-12 17:31:52'),
(7, '2024-08-21', 'Ninoy Aquino Day', 1, 1.000, '2025-09-12 17:31:52', '2025-09-12 17:31:52'),
(8, '2024-08-26', 'National Heroes Day', 1, 1.100, '2025-09-12 17:31:52', '2025-09-12 17:31:52'),
(9, '2024-11-30', 'Bonifacio Day', 1, 1.100, '2025-09-12 17:31:52', '2025-09-12 17:31:52'),
(10, '2024-12-25', 'Christmas Day', 1, 1.400, '2025-09-12 17:31:52', '2025-09-12 17:31:52'),
(11, '2024-12-30', 'Rizal Day', 1, 1.100, '2025-09-12 17:31:52', '2025-09-12 17:31:52'),
(12, '2024-12-31', 'New Year\'s Eve', 1, 1.300, '2025-09-12 17:31:52', '2025-09-12 17:31:52');

-- --------------------------------------------------------

--
-- Table structure for table `rpos_ingredients`
--

CREATE TABLE `rpos_ingredients` (
  `ingredient_id` varchar(200) NOT NULL,
  `ingredient_code` varchar(200) NOT NULL,
  `ingredient_name` varchar(200) NOT NULL,
  `ingredient_img` varchar(200) NOT NULL DEFAULT 'default.png',
  `ingredient_desc` longtext DEFAULT NULL,
  `ingredient_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'Current stock quantity',
  `ingredient_threshold` int(11) NOT NULL DEFAULT 10 COMMENT 'Low stock alert level',
  `ingredient_unit` varchar(50) DEFAULT 'pieces' COMMENT 'Unit of measurement (kg, pieces, liters, etc.)',
  `last_restocked` datetime DEFAULT NULL COMMENT 'Date of last restock',
  `supplier_id` int(11) DEFAULT NULL COMMENT 'Reference to supplier',
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rpos_ingredients`
--

INSERT INTO `rpos_ingredients` (`ingredient_id`, `ingredient_code`, `ingredient_name`, `ingredient_img`, `ingredient_desc`, `ingredient_quantity`, `ingredient_threshold`, `ingredient_unit`, `last_restocked`, `supplier_id`, `created_at`) VALUES
('0bb8c1eab5', 'ING-DBC8E9', 'Bagoong', '68bdc9cdc2a12.jpg', NULL, 250, 100, 'g', NULL, NULL, '2025-09-07 18:07:09.805995'),
('133e6541d2', 'ING-9A7B97', 'Egg', '68bc7259ae19b.webp', NULL, 21, 30, 'pieces', NULL, NULL, '2025-09-06 17:41:45.722513'),
('20025b45ce', 'ING-8AA920', 'Cooking Oil', '68bdc8f8b9a8e.jpg', NULL, 2000, 500, 'ml', NULL, NULL, '2025-09-07 18:03:36.768729'),
('23e423af90', 'ING-6E3157', 'Beef', '68bc70f6ea2ea.jpg', NULL, 2700, 5000, 'g', '2025-09-12 00:00:00', NULL, '2025-09-12 19:15:01.318011'),
('24406de4e2', 'ING-75800F', 'Fish Sauce', '68bdc877661ac.jpg', NULL, 1000, 200, 'ml', '2025-09-08 00:00:00', 8, '2025-09-08 16:35:55.413952'),
('2bfd5313ad', 'ING-C189E0', 'Brown Sugar', '68bdc95c2fa9a.jpg', NULL, 100, 300, 'g', NULL, NULL, '2025-09-07 18:05:16.200431'),
('2da5f76797', 'ING-68C696', 'Oyster Sauce', '68bdc8168cfc5.webp', NULL, 250, 300, 'ml', NULL, NULL, '2025-09-07 17:59:50.583568'),
('4ca09003ba', 'ING-A04EEE', 'Chili', '68bdca2a0f580.jpg', NULL, 2, 5, 'pieces', NULL, NULL, '2025-09-07 18:08:42.144036'),
('5044e66a3e', 'ING-1852DC', 'Banana Leaves', '68bc6ea18592b.webp', NULL, 165, 150, 'pieces', '2025-09-12 00:00:00', NULL, '2025-09-12 19:15:01.315806'),
('59493361ca', 'ING-BAFFFB', 'Onion', '68bdc6dbb969b.jpeg', NULL, 2000, 500, 'g', NULL, NULL, '2025-09-07 17:54:35.764376'),
('6fed599f07', 'ING-4A5781', 'Soy Sauce', '68bdc7a4a6600.jpg', NULL, 100, 250, 'ml', NULL, NULL, '2025-09-07 17:57:56.706180'),
('a59ed9d2a6', 'ING-DEF8A5', 'Garlic', '68bdc67e039aa.webp', NULL, 2000, 300, 'g', '2025-09-08 00:00:00', 9, '2025-09-08 16:36:39.479388'),
('ca04304db4', 'ING-18B346', 'Chicken', '68bc70b18b77e.jpg', NULL, 8425, 5000, 'g', '2025-09-12 00:00:00', NULL, '2025-09-12 19:14:17.512357'),
('cb39dd7e80', 'ING-1DE5A1', 'Ginger', '68bdc731e85a8.webp', NULL, 50, 100, 'g', NULL, NULL, '2025-09-07 17:56:01.955691'),
('fd840c315a', 'ING-AEF71D', 'Rice', '68bc6c2b0ab9c.jpg', NULL, 47250, 18000, 'g', '2025-09-12 00:00:00', 4, '2025-09-12 19:15:01.319207');

-- --------------------------------------------------------

--
-- Table structure for table `rpos_ingredient_logs`
--

CREATE TABLE `rpos_ingredient_logs` (
  `log_id` int(11) NOT NULL,
  `ingredient_id` varchar(200) NOT NULL,
  `activity_type` enum('Add','Restock','Adjustment','Waste','Transfer','Usage','Update') NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `previous_quantity` int(11) NOT NULL,
  `new_quantity` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `activity_date` datetime DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `reference_code` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rpos_ingredient_logs`
--

INSERT INTO `rpos_ingredient_logs` (`log_id`, `ingredient_id`, `activity_type`, `quantity_change`, `previous_quantity`, `new_quantity`, `staff_id`, `activity_date`, `notes`, `reference_code`) VALUES
(1, '5044e66a3e', '', 300, 0, 300, 1, '2025-09-07 01:25:53', 'Added new ingredient: Banana Leaves', 'ADD-68bc6ea18a7b9'),
(2, '5044e66a3e', '', -250, 300, 50, 1, '2025-09-07 01:26:52', 'Ingredient updated: quantity changed', 'UPD-68bc6edcd6ad5'),
(3, 'ca04304db4', '', 2500, 0, 2500, 1, '2025-09-07 01:34:41', 'Added new ingredient: Chicken', 'ADD-68bc70b1aa19d'),
(4, '23e423af90', '', 0, 0, 0, 1, '2025-09-07 01:35:50', 'Added new ingredient: Beef', 'ADD-68bc70f6ed9d5'),
(5, '133e6541d2', '', 21, 0, 21, 1, '2025-09-07 01:41:45', 'Added new ingredient: Egg', 'ADD-68bc7259b922e'),
(6, 'a59ed9d2a6', '', 1000, 0, 1000, 1, '2025-09-08 01:53:02', 'Added new ingredient: Garlic', 'ADD-68bdc67e1a8f5'),
(7, '59493361ca', '', 2000, 0, 2000, 1, '2025-09-08 01:54:35', 'Added new ingredient: Onion', 'ADD-68bdc6dbbbf24'),
(8, 'cb39dd7e80', '', 50, 0, 50, 1, '2025-09-08 01:56:02', 'Added new ingredient: Ginger', 'ADD-68bdc731ea8a9'),
(9, '6fed599f07', '', 100, 0, 100, 1, '2025-09-08 01:57:56', 'Added new ingredient: Soy Sauce', 'ADD-68bdc7a4bd86a'),
(10, '2da5f76797', '', 250, 0, 250, 1, '2025-09-08 01:59:50', 'Added new ingredient: Oyster Sauce', 'ADD-68bdc8168fda3'),
(11, '24406de4e2', '', 700, 0, 700, 1, '2025-09-08 02:01:27', 'Added new ingredient: Fish Sauce', 'ADD-68bdc8776a953'),
(12, '20025b45ce', '', 2000, 0, 2000, 1, '2025-09-08 02:03:36', 'Added new ingredient: Cooking Oil', 'ADD-68bdc8f8be2c4'),
(13, '2bfd5313ad', '', 100, 0, 100, 1, '2025-09-08 02:05:16', 'Added new ingredient: Brown Sugar', 'ADD-68bdc95c33043'),
(14, '0bb8c1eab5', '', 250, 0, 250, 1, '2025-09-08 02:07:09', 'Added new ingredient: Bagoong', 'ADD-68bdc9cdc5f24'),
(15, '4ca09003ba', '', 2, 0, 2, 1, '2025-09-08 02:08:42', 'Added new ingredient: Chili', 'ADD-68bdca2a24ab8'),
(16, '24406de4e2', '', 300, 700, 1000, 1, '2025-09-09 00:35:55', 'Restocked from supplier: Barrio Fiesta Trading. ', 'RST-68bf05eb64d87'),
(17, 'a59ed9d2a6', '', 1000, 1000, 2000, 1, '2025-09-09 00:36:39', 'Restocked from supplier: Fresh Basket Agro Supply. ', 'RST-68bf061774e8e'),
(18, 'fd840c315a', '', 8400, 21600, 30000, 1, '2025-09-09 00:37:52', 'Restocked from supplier: Golden Harvest Rice Trading. ', 'RST-68bf0660540ac'),
(19, '5044e66a3e', '', 31, 50, 81, 1, '2025-09-09 01:36:07', 'Restocked for product: C2 - Spicy Chicken. ', 'RST-68bf14071b84f'),
(20, 'ca04304db4', '', 2325, 2500, 4825, 1, '2025-09-09 01:36:07', 'Restocked for product: C2 - Spicy Chicken. ', 'RST-68bf14071b84f'),
(21, 'fd840c315a', '', 4650, 30000, 34650, 1, '2025-09-09 01:36:07', 'Restocked for product: C2 - Spicy Chicken. ', 'RST-68bf14071b84f'),
(22, '5044e66a3e', '', 48, 81, 129, 1, '2025-09-13 03:14:17', 'Restocked for product: C1 - Regular Chicken. ', 'RST-68c471097b49d'),
(23, 'ca04304db4', '', 3600, 4825, 8425, 1, '2025-09-13 03:14:17', 'Restocked for product: C1 - Regular Chicken. ', 'RST-68c471097b49d'),
(24, 'fd840c315a', '', 7200, 34650, 41850, 1, '2025-09-13 03:14:17', 'Restocked for product: C1 - Regular Chicken. ', 'RST-68c471097b49d'),
(25, '5044e66a3e', '', 36, 129, 165, 1, '2025-09-13 03:15:01', 'Restocked for product: B2 - Spicy Beef. ', 'RST-68c471354cf89'),
(26, '23e423af90', '', 2700, 0, 2700, 1, '2025-09-13 03:15:01', 'Restocked for product: B2 - Spicy Beef. ', 'RST-68c471354cf89'),
(27, 'fd840c315a', '', 5400, 41850, 47250, 1, '2025-09-13 03:15:01', 'Restocked for product: B2 - Spicy Beef. ', 'RST-68c471354cf89');

-- --------------------------------------------------------

--
-- Table structure for table `rpos_inventory_logs`
--

CREATE TABLE `rpos_inventory_logs` (
  `log_id` int(11) NOT NULL,
  `product_id` varchar(200) NOT NULL,
  `activity_type` enum('Restock','Sale','Adjustment','Waste','Transfer','Add','Delete','Update','Supplier Add','Supplier Update') NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `previous_quantity` int(11) NOT NULL,
  `new_quantity` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `activity_date` datetime DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `reference_code` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rpos_inventory_logs`
--

INSERT INTO `rpos_inventory_logs` (`log_id`, `product_id`, `activity_type`, `quantity_change`, `previous_quantity`, `new_quantity`, `staff_id`, `activity_date`, `notes`, `reference_code`) VALUES
(13, '3e82f9082a', 'Update', 2, 3, 5, 1, '2025-08-08 15:14:43', 'Product updated: quantity changed', 'UPD-6895a3e3bfde7'),
(14, '3e82f9082a', 'Restock', 4, 5, 9, 1, '2025-08-08 15:15:28', 'Restocked from supplier: PASTIL. ', 'RST-6895a4101ce31'),
(26, '3e82f9082a', 'Update', 0, 9, 9, 1, '2025-09-08 14:28:58', 'Product updated: ', 'UPD-68be77aa668d1'),
(30, '158635a3c5', 'Add', 0, 0, 0, 1, '2025-09-08 16:29:24', 'Added new product: C4 - Double Spicy', 'ADD-68be93e4049e3'),
(31, '77066847f5', 'Add', 0, 0, 0, 1, '2025-09-08 17:07:18', 'Added new product: C5 - Regular + Spicy', 'ADD-68be9cc6c529c'),
(34, '37f73c89ba', 'Add', 0, 0, 0, 1, '2025-09-08 17:52:36', 'Added new product: B3 - Double Regular', 'ADD-68bea76473135'),
(38, '365c263833', 'Add', 23, 0, 23, 1, '2025-09-08 23:41:38', 'Added new product: Sprite', 'ADD-68bef932ec972'),
(40, '23a2aa277d', 'Add', 28, 0, 28, 1, '2025-09-08 23:59:46', 'Added new product: Royal', 'ADD-68befd72227b9'),
(42, '5ae238eefd', 'Add', 13, 0, 13, 1, '2025-09-09 00:04:13', 'Added new product: B2 - Spicy Beef', 'ADD-68befe7d208fe'),
(43, '37f73c89ba', 'Update', 0, 0, 0, 1, '2025-09-09 00:05:58', 'Product updated: price changed, threshold changed', 'UPD-68befee6a18cc'),
(44, '19320f9872', 'Add', 0, 0, 0, 1, '2025-09-09 00:07:34', 'Added new product: B4 - Double Spicy', 'ADD-68beff468148d'),
(45, '57329a106f', 'Add', 0, 0, 0, 1, '2025-09-09 00:08:31', 'Added new product: B5 - Regular + Spicy', 'ADD-68beff7f43ad5'),
(46, '603094a458', 'Add', 0, 0, 0, 1, '2025-09-09 00:10:00', 'Added new product: C3 - Double Regular', 'ADD-68beffd8c5bd5'),
(47, '39443d8638', 'Update', 0, 67, 67, 1, '2025-09-09 00:10:42', 'Product updated: threshold changed', 'UPD-68bf000251b9e'),
(48, '024781c893', 'Add', 33, 0, 33, 1, '2025-09-09 00:11:52', 'Added new product: Coke', 'ADD-68bf004898858'),
(49, '5718162678', 'Add', 22, 0, 22, 1, '2025-09-09 00:12:34', 'Added new product: Mountain Dew', 'ADD-68bf007253275'),
(50, '5eb8b490a9', 'Add', 21, 0, 21, 1, '2025-09-09 00:14:30', 'Added new product: Egg (Hard-Boiled)', 'ADD-68bf00e65fc75'),
(51, '4556aac394', 'Add', 21, 0, 21, 1, '2025-09-09 00:15:58', 'Added new product: Egg (Sunny Side-up)', 'ADD-68bf013e6c9a4'),
(54, '024781c893', 'Restock', 17, 33, 50, 1, '2025-09-09 01:27:43', 'Restocked product. Ingredients restocked: . ', 'RST-68bf120f1df57'),
(55, '3e82f9082a', 'Restock', 31, 9, 40, 1, '2025-09-09 01:36:07', 'Restocked product. Ingredients restocked: Banana Leaves (+31 pieces), Chicken (+2325 g), Rice (+4650 g). ', 'RST-68bf14071b84f'),
(56, '52b31af7f6', 'Update', -23, 25, 2, 1, '2025-09-09 02:28:47', 'Product updated: quantity changed', 'UPD-68bf205fe218a'),
(57, '5ae238eefd', 'Update', -9, 13, 4, 1, '2025-09-09 02:29:47', 'Product updated: quantity changed', 'UPD-68bf209b65fb2'),
(58, '52b31af7f6', 'Restock', 48, 2, 50, 1, '2025-09-13 03:14:17', 'Restocked product. Ingredients restocked: Banana Leaves (+48 pieces), Chicken (+3600 g), Rice (+7200 g). ', 'RST-68c471097b49d'),
(59, '5ae238eefd', 'Restock', 36, 4, 40, 1, '2025-09-13 03:15:01', 'Restocked product. Ingredients restocked: Banana Leaves (+36 pieces), Beef (+2700 g), Rice (+5400 g). ', 'RST-68c471354cf89');

-- --------------------------------------------------------

--
-- Table structure for table `rpos_local_events`
--

CREATE TABLE `rpos_local_events` (
  `id` int(11) NOT NULL,
  `event_date` date NOT NULL,
  `location` varchar(100) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `event_name` varchar(255) DEFAULT NULL,
  `impact_factor` decimal(4,3) NOT NULL DEFAULT 1.000,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rpos_orders`
--

CREATE TABLE `rpos_orders` (
  `order_id` varchar(200) NOT NULL,
  `order_code` varchar(200) NOT NULL,
  `customer_id` varchar(255) DEFAULT NULL,
  `customer_name` varchar(200) NOT NULL,
  `prod_id` varchar(200) NOT NULL,
  `prod_name` varchar(200) NOT NULL,
  `prod_price` varchar(200) NOT NULL,
  `prod_qty` varchar(200) NOT NULL,
  `order_status` varchar(200) NOT NULL,
  `order_type` varchar(20) DEFAULT 'dine-in',
  `additional_charge` decimal(10,2) DEFAULT 0.00,
  `order_group_id` varchar(200) DEFAULT NULL COMMENT 'Groups multiple order items from same customer session',
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rpos_orders`
--

INSERT INTO `rpos_orders` (`order_id`, `order_code`, `customer_id`, `customer_name`, `prod_id`, `prod_name`, `prod_price`, `prod_qty`, `order_status`, `order_type`, `additional_charge`, `order_group_id`, `created_at`) VALUES
('0467807b26', 'VKJU-9162', 'CUST-1757489519814', 'Maomao', '19320f9872', 'B4 - Double Spicy', '71', '1', 'Cancelled', 'takeout', 0.00, 'CUST-1757489519814_VKJU-9162_1757489577', '2025-09-10 07:49:47.896237'),
('08b6cb94c9', 'IHLT-9470', 'CUST-1757488484608', 'Coco', '52b31af7f6', 'C1 - Regular Chicken', '21', '1', 'Completed', 'dine-in', 0.00, 'CUST-1757488484608_IHLT-9470_1757488502', '2025-09-10 16:40:09.291854'),
('0e07648166', 'HZWY-0198', 'CUST-1757522463054', 'Lebron', '365c263833', 'Sprite', '15', '1', 'Pending', 'dine-in', 0.00, 'CUST-1757522463054_HZWY-0198_1757522571', '2025-09-10 16:42:51.946128'),
('0e68066922', 'XCZS-8953', 'CUST-1757524916122', 'Mike', '3e82f9082a', 'C2 - Spicy Chicken', '21', '1', 'Pending', 'dine-in', 0.00, 'CUST-1757524916122_XCZS-8953_1757524938', '2025-09-10 17:22:18.673147'),
('12cd42c84a', 'XCZS-8953', 'CUST-1757524916122', 'Mike', '603094a458', 'C3 - Double Regular', '41', '1', 'Pending', 'dine-in', 0.00, 'CUST-1757524916122_XCZS-8953_1757524938', '2025-09-10 17:22:18.682042'),
('16bd1a77b0', 'DVYW-5923', 'CUST-1757490462046', 'Zed', '23a2aa277d', 'Royal', '15', '1', 'Completed', 'takeout', 0.00, 'CUST-1757490462046_DVYW-5923_1757490578', '2025-09-10 16:40:04.263428'),
('17d6f62455', 'VKJU-9162', 'CUST-1757489519814', 'Maomao', '158635a3c5', 'C4 - Double Spicy', '41', '1', 'Cancelled', 'takeout', 0.00, 'CUST-1757489519814_VKJU-9162_1757489577', '2025-09-10 07:49:47.896237'),
('17f0c5284b', 'PLDJ-5317', 'CUST-1757685464076', 'Marco', '024781c893', 'Coke', '15', '1', 'Completed', 'takeout', 0.00, 'CUST-1757685464076_PLDJ-5317_1757685504', '2025-09-12 13:59:53.531749'),
('1ca7d8946d', 'PVYC-9160', 'CUST-1757522604194', 'Franc', '37f73c89ba', 'B3 - Double Regular', '71', '1', 'Preparing', 'takeout', 1.00, 'CUST-1757522604194_PVYC-9160_1757522738', '2025-09-10 17:53:54.770106'),
('2224f1452a', 'EORU-7856', 'CUST-1757704611559', 'Austin', '77066847f5', 'C5 - Regular + Spicy', '41', '5', 'Completed', 'dine-in', 0.00, 'CUST-1757704611559_EORU-7856_1757705328', '2025-09-12 19:29:21.463935'),
('26ef623570', 'PVYC-9160', 'CUST-1757522604194', 'Franc', '57329a106f', 'B5 - Regular + Spicy', '71', '1', 'Preparing', 'takeout', 1.00, 'CUST-1757522604194_PVYC-9160_1757522738', '2025-09-10 17:53:54.770106'),
('2a23afa30c', 'CFQE-1764', 'CUST-1757491778959', 'Kent', '57329a106f', 'B5 - Regular + Spicy', '71', '1', 'Cancelled', 'takeout', 0.00, 'CUST-1757491778959_CFQE-1764_1757491818', '2025-09-10 08:13:09.453171'),
('2cbf18b67d', 'VKJU-9162', 'CUST-1757489519814', 'Maomao', '57329a106f', 'B5 - Regular + Spicy', '71', '1', 'Cancelled', 'takeout', 0.00, 'CUST-1757489519814_VKJU-9162_1757489577', '2025-09-10 07:49:47.896237'),
('2dc4298417', 'VKJU-9162', 'CUST-1757489519814', 'Maomao', '603094a458', 'C3 - Double Regular', '41', '1', 'Cancelled', 'takeout', 0.00, 'CUST-1757489519814_VKJU-9162_1757489577', '2025-09-10 07:49:47.896237'),
('30dd42ab6f', 'FOIL-2470', 'CUST-1757527418839', 'Carl', '365c263833', 'Sprite', '15', '1', 'Paid', 'dine-in', 0.00, 'CUST-1757527418839_FOIL-2470_1757527474', '2025-09-10 18:04:45.676222'),
('346f131126', 'CFQE-1764', 'CUST-1757491778959', 'Kent', '37f73c89ba', 'B3 - Double Regular', '71', '1', 'Cancelled', 'takeout', 0.00, 'CUST-1757491778959_CFQE-1764_1757491818', '2025-09-10 08:13:09.453171'),
('37deb6b57a', 'EORU-7856', 'CUST-1757704611559', 'Austin', '5eb8b490a9', 'Egg (Hard-Boiled)', '14', '1', 'Completed', 'dine-in', 0.00, 'CUST-1757704611559_EORU-7856_1757705328', '2025-09-12 19:29:21.463935'),
('38cafd8d76', 'PVYC-9160', 'CUST-1757522604194', 'Franc', '603094a458', 'C3 - Double Regular', '41', '1', 'Preparing', 'takeout', 1.00, 'CUST-1757522604194_PVYC-9160_1757522738', '2025-09-10 17:53:54.770106'),
('3cfa874100', 'HZWY-0198', 'CUST-1757522463054', 'Lebron', '3e82f9082a', 'C2 - Spicy Chicken', '21', '2', 'Pending', 'dine-in', 0.00, 'CUST-1757522463054_HZWY-0198_1757522571', '2025-09-10 16:42:51.910913'),
('3d36bb378b', 'PVYC-9160', 'CUST-1757522604194', 'Franc', '365c263833', 'Sprite', '15', '1', 'Preparing', 'takeout', 0.00, 'CUST-1757522604194_PVYC-9160_1757522738', '2025-09-10 17:53:54.770106'),
('3ff43d7b39', 'DVYW-5923', 'CUST-1757490462046', 'Zed', '365c263833', 'Sprite', '15', '1', 'Completed', 'takeout', 0.00, 'CUST-1757490462046_DVYW-5923_1757490578', '2025-09-10 16:40:04.263428'),
('45f8073295', 'VKJU-9162', 'CUST-1757489519814', 'Maomao', '37f73c89ba', 'B3 - Double Regular', '71', '1', 'Cancelled', 'takeout', 0.00, 'CUST-1757489519814_VKJU-9162_1757489577', '2025-09-10 07:49:47.896237'),
('4bded20ee5', 'DVYW-5923', 'CUST-1757490462046', 'Zed', '024781c893', 'Coke', '15', '1', 'Completed', 'takeout', 0.00, 'CUST-1757490462046_DVYW-5923_1757490578', '2025-09-10 16:40:04.263428'),
('503505a5df', 'ORXY-8390', 'CUST-1757491999100', 'Kim', '77066847f5', 'C5 - Regular + Spicy', '41', '1', 'Completed', 'takeout', 1.00, 'CUST-1757491999100_ORXY-8390_1757492022', '2025-09-10 16:40:03.185007'),
('52b80a8a6e', 'IHLT-9470', 'CUST-1757488484608', 'Coco', '024781c893', 'Coke', '15', '1', 'Completed', 'dine-in', 0.00, 'CUST-1757488484608_IHLT-9470_1757488502', '2025-09-10 16:40:09.291854'),
('58f90b20d2', 'HZWY-0198', 'CUST-1757522463054', 'Lebron', '5ae238eefd', 'B2 - Spicy Beef', '36', '1', 'Pending', 'dine-in', 0.00, 'CUST-1757522463054_HZWY-0198_1757522571', '2025-09-10 16:42:51.879858'),
('61f90a1ecc', 'CFQE-1764', 'CUST-1757491778959', 'Kent', '158635a3c5', 'C4 - Double Spicy', '41', '1', 'Cancelled', 'takeout', 0.00, 'CUST-1757491778959_CFQE-1764_1757491818', '2025-09-10 08:13:09.453171'),
('6cb00364bb', 'DVYW-5923', 'CUST-1757490462046', 'Zed', '4556aac394', 'Egg (Sunny Side-up)', '15', '3', 'Completed', 'takeout', 0.00, 'CUST-1757490462046_DVYW-5923_1757490578', '2025-09-10 16:40:04.263428'),
('6e4fd59df2', 'HZWY-0198', 'CUST-1757522463054', 'Lebron', '5718162678', 'Mountain Dew', '15', '2', 'Pending', 'dine-in', 0.00, 'CUST-1757522463054_HZWY-0198_1757522571', '2025-09-10 16:42:51.929124'),
('713e031159', 'PVYC-9160', 'CUST-1757522604194', 'Franc', '77066847f5', 'C5 - Regular + Spicy', '41', '1', 'Preparing', 'takeout', 1.00, 'CUST-1757522604194_PVYC-9160_1757522738', '2025-09-10 17:53:54.770106'),
('71629eebd4', 'CFQE-1764', 'CUST-1757491778959', 'Kent', '77066847f5', 'C5 - Regular + Spicy', '41', '1', 'Cancelled', 'takeout', 0.00, 'CUST-1757491778959_CFQE-1764_1757491818', '2025-09-10 08:13:09.453171'),
('71758a6ce0', 'XGQH-8501', 'CUST-1757491439715', 'Mel', '19320f9872', 'B4 - Double Spicy', '71', '2', 'Cancelled', 'takeout', 0.00, 'CUST-1757491439715_XGQH-8501_1757491523', '2025-09-10 08:08:49.080700'),
('74e1756078', 'PLDJ-5317', 'CUST-1757685464076', 'Marco', '39443d8638', 'B1 - Regular Beef', '36', '1', 'Completed', 'takeout', 0.00, 'CUST-1757685464076_PLDJ-5317_1757685504', '2025-09-12 13:59:53.531749'),
('7ac4191a19', 'TYPR-5018', 'CUST-1757704512214', 'Charlie', '5718162678', 'Mountain Dew', '15', '2', 'Completed', 'dine-in', 0.00, 'CUST-1757704512214_TYPR-5018_1757704573', '2025-09-12 19:17:21.915967'),
('7b5149e941', 'DVYW-5923', 'CUST-1757490462046', 'Zed', '19320f9872', 'B4 - Double Spicy', '71', '2', 'Completed', 'takeout', 0.00, 'CUST-1757490462046_DVYW-5923_1757490578', '2025-09-10 16:40:04.263428'),
('7ce2d7af7e', 'TYPR-5018', 'CUST-1757704512214', 'Charlie', '024781c893', 'Coke', '15', '3', 'Completed', 'dine-in', 0.00, 'CUST-1757704512214_TYPR-5018_1757704573', '2025-09-12 19:17:21.915967'),
('7f50b5d053', 'IHLT-9470', 'CUST-1757488484608', 'Coco', '5ae238eefd', 'B2 - Spicy Beef', '36', '1', 'Completed', 'dine-in', 0.00, 'CUST-1757488484608_IHLT-9470_1757488502', '2025-09-10 16:40:09.291854'),
('8120883ee6', 'OVUK-9503', 'CUST-1757524961472', 'Jose', '37f73c89ba', 'B3 - Double Regular', '71', '2', 'Pending', 'dine-in', 0.00, 'CUST-1757524961472_OVUK-9503_1757525042', '2025-09-10 17:24:02.114675'),
('81b514baa3', 'RYXM-0362', 'CUST-1757521827312', 'Kevin', '52b31af7f6', 'C1 - Regular Chicken', '21', '10', 'Ready', 'dine-in', 0.00, 'CUST-1757521827312_RYXM-0362_1757521888', '2025-09-10 17:53:52.928844'),
('81f4221ba3', 'PVYC-9160', 'CUST-1757522604194', 'Franc', '19320f9872', 'B4 - Double Spicy', '71', '1', 'Preparing', 'takeout', 1.00, 'CUST-1757522604194_PVYC-9160_1757522738', '2025-09-10 17:53:54.770106'),
('866a10fadb', 'FOIL-2470', 'CUST-1757527418839', 'Carl', '57329a106f', 'B5 - Regular + Spicy', '71', '4', 'Paid', 'dine-in', 0.00, 'CUST-1757527418839_FOIL-2470_1757527474', '2025-09-10 18:04:45.676222'),
('8a92e14d3e', 'PLDJ-5317', 'CUST-1757685464076', 'Marco', '4556aac394', 'Egg (Sunny Side-up)', '15', '1', 'Completed', 'takeout', 0.00, 'CUST-1757685464076_PLDJ-5317_1757685504', '2025-09-12 13:59:53.531749'),
('8b9d924360', 'ORXY-8390', 'CUST-1757491999100', 'Kim', '603094a458', 'C3 - Double Regular', '41', '1', 'Completed', 'takeout', 1.00, 'CUST-1757491999100_ORXY-8390_1757492022', '2025-09-10 16:40:03.185007'),
('97943967f9', 'OVUK-9503', 'CUST-1757524961472', 'Jose', '77066847f5', 'C5 - Regular + Spicy', '41', '1', 'Pending', 'dine-in', 0.00, 'CUST-1757524961472_OVUK-9503_1757525042', '2025-09-10 17:24:02.128753'),
('99a6da9902', 'ORXY-8390', 'CUST-1757491999100', 'Kim', '158635a3c5', 'C4 - Double Spicy', '41', '1', 'Completed', 'takeout', 1.00, 'CUST-1757491999100_ORXY-8390_1757492022', '2025-09-10 16:40:03.185007'),
('99bfba03b5', 'CFQE-1764', 'CUST-1757491778959', 'Kent', '603094a458', 'C3 - Double Regular', '41', '1', 'Cancelled', 'takeout', 0.00, 'CUST-1757491778959_CFQE-1764_1757491818', '2025-09-10 08:13:09.453171'),
('9b1ab74fef', 'ORXY-8390', 'CUST-1757491999100', 'Kim', '19320f9872', 'B4 - Double Spicy', '71', '1', 'Completed', 'takeout', 1.00, 'CUST-1757491999100_ORXY-8390_1757492022', '2025-09-10 16:40:03.185007'),
('9cc87e6e34', 'XCZS-8953', 'CUST-1757524916122', 'Mike', '52b31af7f6', 'C1 - Regular Chicken', '21', '1', 'Pending', 'dine-in', 0.00, 'CUST-1757524916122_XCZS-8953_1757524938', '2025-09-10 17:22:18.665925'),
('9d2a4a00b0', 'TYPR-5018', 'CUST-1757704512214', 'Charlie', '603094a458', 'C3 - Double Regular', '41', '5', 'Completed', 'dine-in', 0.00, 'CUST-1757704512214_TYPR-5018_1757704573', '2025-09-12 19:17:21.915967'),
('9f8a26c461', 'ORXY-8390', 'CUST-1757491999100', 'Kim', '37f73c89ba', 'B3 - Double Regular', '71', '1', 'Completed', 'takeout', 1.00, 'CUST-1757491999100_ORXY-8390_1757492022', '2025-09-10 16:40:03.185007'),
('a1147ecbe7', 'EORU-7856', 'CUST-1757704611559', 'Austin', '365c263833', 'Sprite', '15', '1', 'Completed', 'dine-in', 0.00, 'CUST-1757704611559_EORU-7856_1757705328', '2025-09-12 19:29:21.463935'),
('a135912526', 'IHLT-9470', 'CUST-1757488484608', 'Coco', '3e82f9082a', 'C2 - Spicy Chicken', '21', '1', 'Completed', 'dine-in', 0.00, 'CUST-1757488484608_IHLT-9470_1757488502', '2025-09-10 16:40:09.291854'),
('a135a7401c', 'EORU-7856', 'CUST-1757704611559', 'Austin', '52b31af7f6', 'C1 - Regular Chicken', '21', '1', 'Completed', 'dine-in', 0.00, 'CUST-1757704611559_EORU-7856_1757705328', '2025-09-12 19:29:21.463935'),
('a2c6e3cab2', 'BMHR-6739', 'CUST-1757787559995', 'Kirk', '158635a3c5', 'C4 - Double Spicy', '41', '2', 'Paid', 'dine-in', 0.00, 'CUST-1757787559995_BMHR-6739_1757787572', '2025-09-13 18:19:48.010673'),
('a4a8d925f8', 'EORU-7856', 'CUST-1757704611559', 'Austin', '024781c893', 'Coke', '15', '2', 'Completed', 'dine-in', 0.00, 'CUST-1757704611559_EORU-7856_1757705328', '2025-09-12 19:29:21.463935'),
('a5ced7c06d', 'EORU-7856', 'CUST-1757704611559', 'Austin', '5718162678', 'Mountain Dew', '15', '3', 'Completed', 'dine-in', 0.00, 'CUST-1757704611559_EORU-7856_1757705328', '2025-09-12 19:29:21.463935'),
('a608aa0759', 'DVYW-5923', 'CUST-1757490462046', 'Zed', '5eb8b490a9', 'Egg (Hard-Boiled)', '14', '3', 'Completed', 'takeout', 0.00, 'CUST-1757490462046_DVYW-5923_1757490578', '2025-09-10 16:40:04.263428'),
('a9c11f7f55', 'PVYC-9160', 'CUST-1757522604194', 'Franc', '5718162678', 'Mountain Dew', '15', '1', 'Preparing', 'takeout', 0.00, 'CUST-1757522604194_PVYC-9160_1757522738', '2025-09-10 17:53:54.770106'),
('acbceffb98', 'HZWY-0198', 'CUST-1757522463054', 'Lebron', '23a2aa277d', 'Royal', '15', '1', 'Pending', 'dine-in', 0.00, 'CUST-1757522463054_HZWY-0198_1757522571', '2025-09-10 16:42:51.935638'),
('aeb825992f', 'DVYW-5923', 'CUST-1757490462046', 'Zed', '5718162678', 'Mountain Dew', '15', '1', 'Completed', 'takeout', 0.00, 'CUST-1757490462046_DVYW-5923_1757490578', '2025-09-10 16:40:04.263428'),
('b3f5472a2a', 'HZWY-0198', 'CUST-1757522463054', 'Lebron', '4556aac394', 'Egg (Sunny Side-up)', '15', '2', 'Pending', 'dine-in', 0.00, 'CUST-1757522463054_HZWY-0198_1757522571', '2025-09-10 16:42:51.916757'),
('b47f7dd6fd', 'EORU-7856', 'CUST-1757704611559', 'Austin', '4556aac394', 'Egg (Sunny Side-up)', '15', '2', 'Completed', 'dine-in', 0.00, 'CUST-1757704611559_EORU-7856_1757705328', '2025-09-12 19:29:21.463935'),
('b6585c8786', 'DVYW-5923', 'CUST-1757490462046', 'Zed', '603094a458', 'C3 - Double Regular', '41', '2', 'Completed', 'takeout', 0.00, 'CUST-1757490462046_DVYW-5923_1757490578', '2025-09-10 16:40:04.263428'),
('b79778637c', 'HZWY-0198', 'CUST-1757522463054', 'Lebron', '5eb8b490a9', 'Egg (Hard-Boiled)', '14', '1', 'Pending', 'dine-in', 0.00, 'CUST-1757522463054_HZWY-0198_1757522571', '2025-09-10 16:42:51.922073'),
('b7f906fedb', 'PVYC-9160', 'CUST-1757522604194', 'Franc', '158635a3c5', 'C4 - Double Spicy', '41', '1', 'Preparing', 'takeout', 1.00, 'CUST-1757522604194_PVYC-9160_1757522738', '2025-09-10 17:53:54.770106'),
('b93cce01f6', 'XCZS-8953', 'CUST-1757524916122', 'Mike', '57329a106f', 'B5 - Regular + Spicy', '71', '1', 'Pending', 'dine-in', 0.00, 'CUST-1757524916122_XCZS-8953_1757524938', '2025-09-10 17:22:18.654951'),
('c183e7620e', 'VKJU-9162', 'CUST-1757489519814', 'Maomao', '77066847f5', 'C5 - Regular + Spicy', '41', '1', 'Cancelled', 'takeout', 0.00, 'CUST-1757489519814_VKJU-9162_1757489577', '2025-09-10 07:49:47.896237'),
('c3c9c9ca78', 'EORU-7856', 'CUST-1757704611559', 'Austin', '603094a458', 'C3 - Double Regular', '41', '1', 'Completed', 'dine-in', 0.00, 'CUST-1757704611559_EORU-7856_1757705328', '2025-09-12 19:29:21.463935'),
('c4121ee26d', 'BVDO-9713', 'CUST-20250831141925', 'Jerico', '39443d8638', 'B1 - Regular Beef', '36.00', '4', 'Completed', 'dine-in', 0.00, 'CUST-20250831141925_BVDO-9713', '2025-09-10 16:39:41.605431'),
('c53ec835cf', 'ACFX-4528', 'CUST-20250831141946', 'Ben', '3e82f9082a', 'C2 - Spicy Chicken', '21.00', '10', 'Completed', 'dine-in', 0.00, 'CUST-20250831141946_ACFX-4528', '2025-09-10 16:40:08.344732'),
('c64884fc8c', 'DVYW-5923', 'CUST-1757490462046', 'Zed', '77066847f5', 'C5 - Regular + Spicy', '41', '2', 'Completed', 'takeout', 0.00, 'CUST-1757490462046_DVYW-5923_1757490578', '2025-09-10 16:40:04.263428'),
('cb7f59a502', 'FOIL-2470', 'CUST-1757527418839', 'Carl', '5eb8b490a9', 'Egg (Hard-Boiled)', '14', '1', 'Paid', 'dine-in', 0.00, 'CUST-1757527418839_FOIL-2470_1757527474', '2025-09-10 18:04:45.676222'),
('cf1359f2e5', 'FOIL-2470', 'CUST-1757527418839', 'Carl', '024781c893', 'Coke', '15', '1', 'Paid', 'dine-in', 0.00, 'CUST-1757527418839_FOIL-2470_1757527474', '2025-09-10 18:04:45.676222'),
('d36ad63e32', 'EORU-7856', 'CUST-1757704611559', 'Austin', '57329a106f', 'B5 - Regular + Spicy', '71', '1', 'Completed', 'dine-in', 0.00, 'CUST-1757704611559_EORU-7856_1757705328', '2025-09-12 19:29:21.463935'),
('d3ab2d957f', 'OVUK-9503', 'CUST-1757524961472', 'Jose', '5eb8b490a9', 'Egg (Hard-Boiled)', '14', '1', 'Pending', 'dine-in', 0.00, 'CUST-1757524961472_OVUK-9503_1757525042', '2025-09-10 17:24:02.133748'),
('d80ba843ff', 'TYPR-5018', 'CUST-1757704512214', 'Charlie', '37f73c89ba', 'B3 - Double Regular', '71', '3', 'Completed', 'dine-in', 0.00, 'CUST-1757704512214_TYPR-5018_1757704573', '2025-09-12 19:17:21.915967'),
('e0a94a3ead', 'TYPR-5018', 'CUST-1757704512214', 'Charlie', '365c263833', 'Sprite', '15', '3', 'Completed', 'dine-in', 0.00, 'CUST-1757704512214_TYPR-5018_1757704573', '2025-09-12 19:17:21.915967'),
('e4658b83b6', 'FOIL-2470', 'CUST-1757527418839', 'Carl', '23a2aa277d', 'Royal', '15', '1', 'Paid', 'dine-in', 0.00, 'CUST-1757527418839_FOIL-2470_1757527474', '2025-09-10 18:04:45.676222'),
('e4f6e1ba25', 'FOIL-2470', 'CUST-1757527418839', 'Carl', '5718162678', 'Mountain Dew', '15', '1', 'Paid', 'dine-in', 0.00, 'CUST-1757527418839_FOIL-2470_1757527474', '2025-09-10 18:04:45.676222'),
('efced48e50', 'HZWY-0198', 'CUST-1757522463054', 'Lebron', '52b31af7f6', 'C1 - Regular Chicken', '21', '1', 'Pending', 'dine-in', 0.00, 'CUST-1757522463054_HZWY-0198_1757522571', '2025-09-10 16:42:51.901426'),
('f1ec896ec8', 'PVYC-9160', 'CUST-1757522604194', 'Franc', '024781c893', 'Coke', '15', '1', 'Preparing', 'takeout', 0.00, 'CUST-1757522604194_PVYC-9160_1757522738', '2025-09-10 17:53:54.770106'),
('f60c39448c', 'EORU-7856', 'CUST-1757704611559', 'Austin', '23a2aa277d', 'Royal', '15', '1', 'Completed', 'dine-in', 0.00, 'CUST-1757704611559_EORU-7856_1757705328', '2025-09-12 19:29:21.463935'),
('f65110c509', 'ORXY-8390', 'CUST-1757491999100', 'Kim', '57329a106f', 'B5 - Regular + Spicy', '71', '1', 'Completed', 'takeout', 1.00, 'CUST-1757491999100_ORXY-8390_1757492022', '2025-09-10 16:40:03.185007'),
('fcb9321471', 'CFQE-1764', 'CUST-1757491778959', 'Kent', '19320f9872', 'B4 - Double Spicy', '71', '1', 'Cancelled', 'takeout', 0.00, 'CUST-1757491778959_CFQE-1764_1757491818', '2025-09-10 08:13:09.453171'),
('ffc034e28c', 'PVYC-9160', 'CUST-1757522604194', 'Franc', '23a2aa277d', 'Royal', '15', '1', 'Preparing', 'takeout', 0.00, 'CUST-1757522604194_PVYC-9160_1757522738', '2025-09-10 17:53:54.770106');

-- --------------------------------------------------------

--
-- Table structure for table `rpos_pass_resets`
--

CREATE TABLE `rpos_pass_resets` (
  `reset_id` int(20) NOT NULL,
  `reset_code` varchar(200) NOT NULL,
  `reset_token` varchar(200) NOT NULL,
  `reset_email` varchar(200) NOT NULL,
  `reset_status` varchar(200) NOT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rpos_payments`
--

CREATE TABLE `rpos_payments` (
  `pay_id` varchar(200) NOT NULL,
  `pay_code` varchar(200) NOT NULL,
  `order_code` varchar(200) NOT NULL,
  `customer_id` varchar(200) NOT NULL,
  `pay_amt` varchar(200) NOT NULL,
  `pay_method` varchar(200) NOT NULL,
  `order_type` varchar(20) DEFAULT 'dine-in',
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rpos_payments`
--

INSERT INTO `rpos_payments` (`pay_id`, `pay_code`, `order_code`, `customer_id`, `pay_amt`, `pay_method`, `order_type`, `created_at`) VALUES
('1874fa', 'ELYMA1XOBN', 'TYPR-5018', 'CUST-1757704512214', '538', 'Cash', 'dine-in', '2025-09-12 19:16:23.569376'),
('45df15', '8E1WKPTYLZ', 'DVYW-5923', 'CUST-1757490462046', '453', 'Cash', 'takeout', '2025-09-10 07:52:15.932635'),
('84c1ed', 'SVRAEFYX7D', 'IHLT-9470', 'CUST-1757488484608', '93', 'Cash', 'dine-in', '2025-09-10 16:28:40.590738'),
('873588', 'ORNEH7ZLPM', 'ACFX-4528', 'CUST-20250831141946', '210', 'Cash', 'dine-in', '2025-08-31 12:19:56.851137'),
('8c4d71', '38GSUZ19CP', 'BVDO-9713', 'CUST-20250831141925', '144', 'Cash', 'dine-in', '2025-08-31 12:20:08.844656'),
('9d189c', 'NMIZYXQ85S', 'EORU-7856', 'CUST-1757704611559', '487', 'Cash', 'dine-in', '2025-09-12 19:28:55.634031'),
('a35976', 'U1VIKFWO75', 'PVYC-9160', 'CUST-1757522604194', '402', 'GCash', 'takeout', '2025-09-10 17:24:20.813553'),
('a6d696', 'VO26ZXSQAY', 'PLDJ-5317', 'CUST-1757685464076', '66', 'Cash', 'takeout', '2025-09-12 13:58:41.592420'),
('b7db15', '1LTFISQCAU', 'ORXY-8390', 'CUST-1757491999100', '342', 'GCash', 'takeout', '2025-09-10 08:14:02.045627'),
('d06efd', 'JXZM827GCW', 'RYXM-0362', 'CUST-1757521827312', '210', 'GCash', 'dine-in', '2025-09-10 16:31:56.667546'),
('f13f2c', '23XBEMARST', 'BMHR-6739', 'CUST-1757787559995', '82', 'Cash', 'dine-in', '2025-09-13 18:19:48.006014'),
('fc645d', 'PJK5EO7RSL', 'FOIL-2470', 'CUST-1757527418839', '358', 'Cash', 'dine-in', '2025-09-10 18:04:45.666119');

-- --------------------------------------------------------

--
-- Table structure for table `rpos_products`
--

CREATE TABLE `rpos_products` (
  `prod_id` varchar(200) NOT NULL,
  `prod_code` varchar(200) NOT NULL,
  `prod_name` varchar(200) NOT NULL,
  `prod_img` varchar(200) NOT NULL,
  `prod_desc` longtext NOT NULL,
  `prod_price` decimal(10,2) NOT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `prod_quantity` int(11) NOT NULL DEFAULT 0 COMMENT 'Current stock quantity',
  `prod_status` tinyint(1) DEFAULT 1,
  `prod_threshold` int(11) NOT NULL DEFAULT 10 COMMENT 'Low stock alert level',
  `prod_category` varchar(100) DEFAULT NULL COMMENT 'Product category',
  `last_restocked` datetime DEFAULT NULL COMMENT 'Date of last restock',
  `supplier_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rpos_products`
--

INSERT INTO `rpos_products` (`prod_id`, `prod_code`, `prod_name`, `prod_img`, `prod_desc`, `prod_price`, `created_at`, `prod_quantity`, `prod_status`, `prod_threshold`, `prod_category`, `last_restocked`, `supplier_id`) VALUES
('024781c893', 'KPXW-3879', 'Coke', '68bf004894ddc.jpg', '', 15.00, '2025-09-12 19:28:48.137538', 45, 1, 10, 'Beverage', '2025-09-08 00:00:00', NULL),
('158635a3c5', 'RPIY-1068', 'C4 - Double Spicy', '68be93e3d6e8f.jpg', '', 41.00, '2025-09-13 15:54:35.203110', 0, 0, 10, 'Food', NULL, NULL),
('19320f9872', 'ZPOH-7528', 'B4 - Double Spicy', '68beff463b176.jpg', '', 71.00, '2025-09-13 15:54:35.203110', 0, 0, 10, 'Food', NULL, NULL),
('23a2aa277d', 'KXOJ-8369', 'Royal', '68befd71e2909.jpg', '', 15.00, '2025-09-12 19:28:48.128915', 27, 1, 10, 'Beverage', NULL, NULL),
('365c263833', 'TFLR-2173', 'Sprite', '68bef932c724d.jpg', '', 15.00, '2025-09-12 19:28:48.135037', 19, 1, 10, 'Beverage', NULL, NULL),
('37f73c89ba', 'MXUW-0253', 'B3 - Double Regular', '68bea7642a860.jpg', '', 71.00, '2025-09-13 15:54:35.203110', 0, 0, 10, 'Food', NULL, NULL),
('39443d8638', 'NMRV-7923', 'B1 - Regular Beef', 'B1.jpg', 'Filipino packed rice dish originating from Maguindanao, Mindanao, traditionally consisting of steamed rice wrapped in banana leaves with shredded chicken, fish, or beef.', 36.00, '2025-09-12 19:28:48.121001', 60, 1, 10, 'Food', '2025-08-08 00:00:00', NULL),
('3e82f9082a', 'EXBC-0954', 'C2 - Spicy Chicken', '6895a3b82ea03.webp', '', 21.00, '2025-09-13 18:19:32.470210', 31, 1, 10, 'Food', '2025-09-08 00:00:00', NULL),
('4556aac394', 'WYAK-4875', 'Egg (Sunny Side-up)', '68bf013d4436d.jpg', '', 15.00, '2025-09-12 19:28:48.124666', 19, 1, 30, 'Food', NULL, NULL),
('52b31af7f6', 'KCNI-7896', 'C1 - Regular Chicken', 'C1.jpg', 'Filipino packed rice dish originating from Maguindanao, Mindanao, traditionally consisting of steamed rice wrapped in banana leaves with shredded chicken, fish, or beef.', 21.00, '2025-09-12 19:28:48.139011', 32, 1, 10, 'Food', '2025-09-12 00:00:00', NULL),
('5718162678', 'DHUE-7159', 'Mountain Dew', '68bf007221a68.jpg', '', 15.00, '2025-09-12 19:28:48.127497', 17, 1, 10, 'Beverage', NULL, NULL),
('57329a106f', 'XFTW-2745', 'B5 - Regular + Spicy', '68beff7f181f4.webp', '', 71.00, '2025-09-13 15:54:35.203110', 0, 0, 10, 'Food', NULL, NULL),
('5ae238eefd', 'QWIF-1378', 'B2 - Spicy Beef', '68befe7ceef4c.webp', '', 36.00, '2025-09-12 19:28:48.121799', 39, 1, 10, 'Food', '2025-09-12 00:00:00', NULL),
('5eb8b490a9', 'QGBE-2135', 'Egg (Hard-Boiled)', '68bf00e65c672.jpg', '', 14.00, '2025-09-12 19:28:48.126058', 20, 1, 30, 'Food', NULL, NULL),
('603094a458', 'BPFA-8903', 'C3 - Double Regular', '68beffd8aa469.jpg', '', 41.00, '2025-09-13 15:54:35.203110', 0, 0, 10, 'Food', NULL, NULL),
('77066847f5', 'KCUQ-6245', 'C5 - Regular + Spicy', '68be9cc689dfa.jpg', '', 41.00, '2025-09-13 15:54:35.203110', 0, 0, 10, 'Food', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `rpos_product_ingredients`
--

CREATE TABLE `rpos_product_ingredients` (
  `id` int(11) NOT NULL,
  `product_id` varchar(200) NOT NULL,
  `ingredient_id` varchar(200) NOT NULL,
  `quantity_required` decimal(10,2) NOT NULL DEFAULT 1.00 COMMENT 'Quantity of ingredient needed per product unit',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rpos_product_ingredients`
--

INSERT INTO `rpos_product_ingredients` (`id`, `product_id`, `ingredient_id`, `quantity_required`, `created_at`) VALUES
(13, '33e8f3b9a0', '5044e66a3e', 2.00, '2025-09-08 05:30:24'),
(14, '33e8f3b9a0', 'ca04304db4', 150.00, '2025-09-08 05:30:24'),
(15, '33e8f3b9a0', 'fd840c315a', 300.00, '2025-09-08 05:30:24'),
(16, '16a7ba603d', '5044e66a3e', 2.00, '2025-09-08 06:17:55'),
(17, '16a7ba603d', 'ca04304db4', 150.00, '2025-09-08 06:17:55'),
(18, '16a7ba603d', 'fd840c315a', 300.00, '2025-09-08 06:17:55'),
(19, 'bf6a711367', '5044e66a3e', 2.00, '2025-09-08 06:28:08'),
(20, 'bf6a711367', 'ca04304db4', 150.00, '2025-09-08 06:28:08'),
(21, 'bf6a711367', 'fd840c315a', 300.00, '2025-09-08 06:28:08'),
(22, '3e82f9082a', '5044e66a3e', 1.00, '2025-09-08 06:28:58'),
(23, '3e82f9082a', 'ca04304db4', 75.00, '2025-09-08 06:28:58'),
(24, '3e82f9082a', 'fd840c315a', 150.00, '2025-09-08 06:28:58'),
(25, '2fd6da40fe', '5044e66a3e', 2.00, '2025-09-08 08:25:37'),
(26, '2fd6da40fe', 'ca04304db4', 150.00, '2025-09-08 08:25:37'),
(27, '2fd6da40fe', 'fd840c315a', 300.00, '2025-09-08 08:25:37'),
(28, 'd89a54f216', '5044e66a3e', 2.00, '2025-09-08 08:27:52'),
(29, 'd89a54f216', 'ca04304db4', 150.00, '2025-09-08 08:27:52'),
(30, 'd89a54f216', 'fd840c315a', 300.00, '2025-09-08 08:27:52'),
(31, '158635a3c5', '5044e66a3e', 2.00, '2025-09-08 08:29:23'),
(32, '158635a3c5', 'ca04304db4', 150.00, '2025-09-08 08:29:24'),
(33, '158635a3c5', 'fd840c315a', 300.00, '2025-09-08 08:29:24'),
(34, '77066847f5', '5044e66a3e', 2.00, '2025-09-08 09:07:18'),
(35, '77066847f5', 'ca04304db4', 150.00, '2025-09-08 09:07:18'),
(36, '77066847f5', 'fd840c315a', 300.00, '2025-09-08 09:07:18'),
(37, 'ff9c91a4c1', '5044e66a3e', 1.00, '2025-09-08 09:13:51'),
(38, 'ff9c91a4c1', '23e423af90', 75.00, '2025-09-08 09:13:51'),
(39, 'ff9c91a4c1', 'fd840c315a', 150.00, '2025-09-08 09:13:51'),
(40, '89ebc6752f', '5044e66a3e', 2.00, '2025-09-08 09:15:58'),
(41, '89ebc6752f', '23e423af90', 150.00, '2025-09-08 09:15:58'),
(42, '89ebc6752f', 'fd840c315a', 300.00, '2025-09-08 09:15:58'),
(46, 'ea91520ea4', '5044e66a3e', 2.00, '2025-09-08 09:59:29'),
(47, 'ea91520ea4', '23e423af90', 150.00, '2025-09-08 09:59:29'),
(48, 'ea91520ea4', 'fd840c315a', 300.00, '2025-09-08 09:59:29'),
(49, 'f649a57df8', '5044e66a3e', 2.00, '2025-09-08 10:02:06'),
(50, 'f649a57df8', '23e423af90', 150.00, '2025-09-08 10:02:06'),
(51, 'f649a57df8', 'fd840c315a', 300.00, '2025-09-08 10:02:06'),
(52, 'fbfe69303a', '133e6541d2', 1.00, '2025-09-08 16:01:44'),
(56, '37f73c89ba', '5044e66a3e', 2.00, '2025-09-08 16:05:58'),
(57, '37f73c89ba', '23e423af90', 150.00, '2025-09-08 16:05:58'),
(58, '37f73c89ba', 'fd840c315a', 300.00, '2025-09-08 16:05:58'),
(59, '19320f9872', '5044e66a3e', 2.00, '2025-09-08 16:07:34'),
(60, '19320f9872', '23e423af90', 150.00, '2025-09-08 16:07:34'),
(61, '19320f9872', 'fd840c315a', 300.00, '2025-09-08 16:07:34'),
(62, '57329a106f', '5044e66a3e', 2.00, '2025-09-08 16:08:31'),
(63, '57329a106f', '23e423af90', 150.00, '2025-09-08 16:08:31'),
(64, '57329a106f', 'fd840c315a', 300.00, '2025-09-08 16:08:31'),
(65, '603094a458', '5044e66a3e', 2.00, '2025-09-08 16:10:00'),
(66, '603094a458', 'ca04304db4', 150.00, '2025-09-08 16:10:00'),
(67, '603094a458', 'fd840c315a', 300.00, '2025-09-08 16:10:00'),
(68, '39443d8638', '5044e66a3e', 1.00, '2025-09-08 16:10:42'),
(69, '39443d8638', '23e423af90', 75.00, '2025-09-08 16:10:42'),
(70, '39443d8638', 'fd840c315a', 150.00, '2025-09-08 16:10:42'),
(71, '5eb8b490a9', '133e6541d2', 1.00, '2025-09-08 16:14:30'),
(72, '4556aac394', '133e6541d2', 1.00, '2025-09-08 16:15:58'),
(73, '52b31af7f6', '5044e66a3e', 1.00, '2025-09-08 18:28:47'),
(74, '52b31af7f6', 'ca04304db4', 75.00, '2025-09-08 18:28:47'),
(75, '52b31af7f6', 'fd840c315a', 150.00, '2025-09-08 18:28:47'),
(76, '5ae238eefd', '5044e66a3e', 1.00, '2025-09-08 18:29:47'),
(77, '5ae238eefd', '23e423af90', 75.00, '2025-09-08 18:29:47'),
(78, '5ae238eefd', 'fd840c315a', 150.00, '2025-09-08 18:29:47');

-- --------------------------------------------------------

--
-- Table structure for table `rpos_product_links`
--

CREATE TABLE `rpos_product_links` (
  `id` int(11) NOT NULL,
  `linked_product_id` varchar(200) NOT NULL,
  `base_product_id` varchar(200) NOT NULL,
  `relation` enum('mirror','combo') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rpos_product_links`
--

INSERT INTO `rpos_product_links` (`id`, `linked_product_id`, `base_product_id`, `relation`, `created_at`) VALUES
(3, '158635a3c5', '3e82f9082a', 'mirror', '2025-09-08 08:29:23'),
(4, '77066847f5', '52b31af7f6', 'combo', '2025-09-08 09:07:18'),
(5, '77066847f5', '3e82f9082a', 'combo', '2025-09-08 09:07:18'),
(7, '37f73c89ba', '39443d8638', 'mirror', '2025-09-08 09:52:36'),
(11, '19320f9872', '5ae238eefd', 'mirror', '2025-09-08 16:07:34'),
(12, '57329a106f', '5ae238eefd', 'combo', '2025-09-08 16:08:31'),
(13, '57329a106f', '39443d8638', 'combo', '2025-09-08 16:08:31'),
(14, '603094a458', '52b31af7f6', 'mirror', '2025-09-08 16:10:00');

-- --------------------------------------------------------

--
-- Table structure for table `rpos_restocking_recommendations`
--

CREATE TABLE `rpos_restocking_recommendations` (
  `recommendation_id` int(11) NOT NULL,
  `product_id` varchar(200) NOT NULL,
  `recommendation_date` datetime NOT NULL DEFAULT current_timestamp(),
  `current_stock` int(11) NOT NULL,
  `avg_daily_demand` decimal(10,2) NOT NULL,
  `days_until_stockout` decimal(5,1) NOT NULL,
  `optimal_reorder_point` decimal(10,2) NOT NULL,
  `economic_order_quantity` decimal(10,2) NOT NULL,
  `recommended_order_quantity` int(11) NOT NULL,
  `urgency_level` enum('critical','high','medium','low','normal') NOT NULL,
  `confidence_score` decimal(5,2) NOT NULL,
  `forecast_summary` text DEFAULT NULL COMMENT 'JSON of forecast summary',
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rpos_staff`
--

CREATE TABLE `rpos_staff` (
  `staff_id` int(20) NOT NULL,
  `staff_name` varchar(200) NOT NULL,
  `staff_number` varchar(200) NOT NULL,
  `staff_email` varchar(200) NOT NULL,
  `staff_password` varchar(200) NOT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `staff_role` varchar(50) NOT NULL DEFAULT 'cashier' COMMENT 'Comma-separated roles: cashier,inventory'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rpos_staff`
--

INSERT INTO `rpos_staff` (`staff_id`, `staff_name`, `staff_number`, `staff_email`, `staff_password`, `created_at`, `staff_role`) VALUES
(1, 'Aria', 'RCOI-2163', 'aria@pastil.com', 'adcd7048512e64b48da55b027577886ee5a36350', '2025-07-28 16:09:46.120003', 'cashier,inventory');

-- --------------------------------------------------------

--
-- Table structure for table `rpos_suppliers`
--

CREATE TABLE `rpos_suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `supplier_phone` varchar(20) NOT NULL,
  `supplier_email` varchar(100) DEFAULT NULL,
  `supplier_address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rpos_suppliers`
--

INSERT INTO `rpos_suppliers` (`supplier_id`, `supplier_name`, `supplier_phone`, `supplier_email`, `supplier_address`, `created_at`) VALUES
(4, 'Golden Harvest Rice Trading', '+63 917 888 1122', 'goldenharvest.rice@gmail.com', '45 A. Mabini Street, Barangay Bagong Ilog, Pasig City, Metro Manila, Philippines', '2025-09-08 16:26:58'),
(5, 'Green Leaf Agri Supply', '+63 917 234 9988', 'greenleaf.agrisupply@gmail.com', '27 M. Suarez Street, Barangay Santolan, Pasig City, Metro Manila, Philippines', '2025-09-08 16:27:49'),
(6, 'Pasig Poultry & Meat Trading', '+63 917 556 4411', 'pasigpoultry.trading@gmail.com', '102 E. Caruncho Avenue, Barangay San Nicolas, Pasig City, Metro Manila, Philippines', '2025-09-08 16:28:47'),
(7, 'Prime Cuts Meat Supply', '+63 917 773 2255', 'primecuts.meatsupply@gmail.com', '89 F. Manalo Street, Barangay Palatiw, Pasig City, Metro Manila, Philippines', '2025-09-08 16:29:46'),
(8, 'Barrio Fiesta Trading', '+63 917 889 3344', 'barriofiesta.trading@gmail.com', '56 C. Raymundo Avenue, Barangay Maybunga, Pasig City, Metro Manila, Philippines', '2025-09-08 16:33:10'),
(9, 'Fresh Basket Agro Supply', '+63 917 665 4422', 'freshbasket.agro@gmail.com', '21 Pio Alvarez Street, Barangay Pinagbuhatan, Pasig City, Metro Manila, Philippines', '2025-09-08 16:33:51'),
(10, 'Sweet Harvest Trading', '+63 917 448 7799', 'sweetharvest.trading@gmail.com', '34 Eusebio Avenue, Barangay San Miguel, Pasig City, Metro Manila, Philippines', '2025-09-08 16:34:29'),
(11, 'Metro Refreshment Trading', '+63 917 882 1144', 'metrorefreshment.trading@gmail.com', '78 Amang Rodriguez Avenue, Barangay Santolan, Pasig City, Metro Manila, Philippines', '2025-09-08 16:50:17');

-- --------------------------------------------------------

--
-- Table structure for table `rpos_system_config`
--

CREATE TABLE `rpos_system_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(100) NOT NULL,
  `config_value` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rpos_system_config`
--

INSERT INTO `rpos_system_config` (`id`, `config_key`, `config_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'weather_api_key', 'free_weather_api', 'API key for weather service', '2025-09-12 17:31:24', '2025-09-12 17:40:48'),
(2, 'ordering_cost', '50.00', 'Default ordering cost for EOQ calculations', '2025-09-12 17:31:24', '2025-09-12 17:31:24'),
(3, 'weather_service', 'openweathermap', 'Weather service provider', '2025-09-12 17:40:49', '2025-09-12 17:40:49'),
(4, 'weather_location', 'Pasig,PH', 'Default weather location', '2025-09-12 17:40:49', '2025-09-12 17:40:49');

-- --------------------------------------------------------

--
-- Table structure for table `rpos_weather_data`
--

CREATE TABLE `rpos_weather_data` (
  `id` int(11) NOT NULL,
  `weather_date` date NOT NULL,
  `location` varchar(100) NOT NULL,
  `condition` varchar(50) NOT NULL,
  `temperature` decimal(5,2) DEFAULT NULL,
  `humidity` decimal(5,2) DEFAULT NULL,
  `impact_factor` decimal(4,3) NOT NULL DEFAULT 1.000,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `rpos_admin`
--
ALTER TABLE `rpos_admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `rpos_categories`
--
ALTER TABLE `rpos_categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `rpos_customers`
--
ALTER TABLE `rpos_customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `rpos_economic_data`
--
ALTER TABLE `rpos_economic_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_economic` (`data_date`),
  ADD KEY `idx_data_date` (`data_date`);

--
-- Indexes for table `rpos_feedback`
--
ALTER TABLE `rpos_feedback`
  ADD PRIMARY KEY (`feedback_id`);

--
-- Indexes for table `rpos_feedback_replies`
--
ALTER TABLE `rpos_feedback_replies`
  ADD PRIMARY KEY (`reply_id`),
  ADD KEY `idx_feedback_id` (`feedback_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `rpos_forecast_history`
--
ALTER TABLE `rpos_forecast_history`
  ADD PRIMARY KEY (`forecast_id`),
  ADD KEY `idx_product_date` (`product_id`,`forecast_date`),
  ADD KEY `idx_forecast_date` (`forecast_date`);

--
-- Indexes for table `rpos_forecast_validations`
--
ALTER TABLE `rpos_forecast_validations`
  ADD PRIMARY KEY (`validation_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_validation_date` (`validation_date`),
  ADD KEY `idx_overall_rating` (`overall_rating`);

--
-- Indexes for table `rpos_holiday_data`
--
ALTER TABLE `rpos_holiday_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_holiday` (`holiday_date`),
  ADD KEY `idx_holiday_date` (`holiday_date`),
  ADD KEY `idx_is_holiday` (`is_holiday`);

--
-- Indexes for table `rpos_ingredients`
--
ALTER TABLE `rpos_ingredients`
  ADD PRIMARY KEY (`ingredient_id`),
  ADD UNIQUE KEY `ingredient_code` (`ingredient_code`),
  ADD UNIQUE KEY `ingredient_name` (`ingredient_name`),
  ADD KEY `idx_supplier` (`supplier_id`);

--
-- Indexes for table `rpos_ingredient_logs`
--
ALTER TABLE `rpos_ingredient_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_ingredient` (`ingredient_id`),
  ADD KEY `idx_staff` (`staff_id`),
  ADD KEY `idx_activity_date` (`activity_date`),
  ADD KEY `idx_reference` (`reference_code`);

--
-- Indexes for table `rpos_inventory_logs`
--
ALTER TABLE `rpos_inventory_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_staff` (`staff_id`),
  ADD KEY `idx_activity_date` (`activity_date`),
  ADD KEY `idx_reference` (`reference_code`);

--
-- Indexes for table `rpos_local_events`
--
ALTER TABLE `rpos_local_events`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_event` (`event_date`,`location`),
  ADD KEY `idx_event_date` (`event_date`),
  ADD KEY `idx_location` (`location`),
  ADD KEY `idx_event_type` (`event_type`);

--
-- Indexes for table `rpos_orders`
--
ALTER TABLE `rpos_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `CustomerOrder` (`customer_id`),
  ADD KEY `ProductOrder` (`prod_id`),
  ADD KEY `idx_order_group` (`order_group_id`);

--
-- Indexes for table `rpos_pass_resets`
--
ALTER TABLE `rpos_pass_resets`
  ADD PRIMARY KEY (`reset_id`);

--
-- Indexes for table `rpos_payments`
--
ALTER TABLE `rpos_payments`
  ADD PRIMARY KEY (`pay_id`),
  ADD KEY `order` (`order_code`);

--
-- Indexes for table `rpos_products`
--
ALTER TABLE `rpos_products`
  ADD PRIMARY KEY (`prod_id`),
  ADD UNIQUE KEY `prod_code` (`prod_code`),
  ADD UNIQUE KEY `prod_name` (`prod_name`),
  ADD KEY `idx_supplier` (`supplier_id`),
  ADD KEY `idx_prod_status` (`prod_status`);

--
-- Indexes for table `rpos_product_ingredients`
--
ALTER TABLE `rpos_product_ingredients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_ingredient` (`product_id`,`ingredient_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_ingredient` (`ingredient_id`);

--
-- Indexes for table `rpos_product_links`
--
ALTER TABLE `rpos_product_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_link` (`linked_product_id`,`base_product_id`,`relation`),
  ADD KEY `idx_linked` (`linked_product_id`),
  ADD KEY `idx_base` (`base_product_id`);

--
-- Indexes for table `rpos_restocking_recommendations`
--
ALTER TABLE `rpos_restocking_recommendations`
  ADD PRIMARY KEY (`recommendation_id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_urgency` (`urgency_level`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_recommendation_date` (`recommendation_date`);

--
-- Indexes for table `rpos_staff`
--
ALTER TABLE `rpos_staff`
  ADD PRIMARY KEY (`staff_id`);

--
-- Indexes for table `rpos_suppliers`
--
ALTER TABLE `rpos_suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `rpos_system_config`
--
ALTER TABLE `rpos_system_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`);

--
-- Indexes for table `rpos_weather_data`
--
ALTER TABLE `rpos_weather_data`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_weather` (`weather_date`,`location`),
  ADD KEY `idx_weather_date` (`weather_date`),
  ADD KEY `idx_location` (`location`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `rpos_categories`
--
ALTER TABLE `rpos_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rpos_economic_data`
--
ALTER TABLE `rpos_economic_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rpos_feedback`
--
ALTER TABLE `rpos_feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rpos_feedback_replies`
--
ALTER TABLE `rpos_feedback_replies`
  MODIFY `reply_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rpos_forecast_history`
--
ALTER TABLE `rpos_forecast_history`
  MODIFY `forecast_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rpos_forecast_validations`
--
ALTER TABLE `rpos_forecast_validations`
  MODIFY `validation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rpos_holiday_data`
--
ALTER TABLE `rpos_holiday_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `rpos_ingredient_logs`
--
ALTER TABLE `rpos_ingredient_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `rpos_inventory_logs`
--
ALTER TABLE `rpos_inventory_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `rpos_local_events`
--
ALTER TABLE `rpos_local_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rpos_pass_resets`
--
ALTER TABLE `rpos_pass_resets`
  MODIFY `reset_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rpos_product_ingredients`
--
ALTER TABLE `rpos_product_ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `rpos_product_links`
--
ALTER TABLE `rpos_product_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `rpos_restocking_recommendations`
--
ALTER TABLE `rpos_restocking_recommendations`
  MODIFY `recommendation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rpos_staff`
--
ALTER TABLE `rpos_staff`
  MODIFY `staff_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `rpos_suppliers`
--
ALTER TABLE `rpos_suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `rpos_system_config`
--
ALTER TABLE `rpos_system_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rpos_weather_data`
--
ALTER TABLE `rpos_weather_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rpos_feedback_replies`
--
ALTER TABLE `rpos_feedback_replies`
  ADD CONSTRAINT `fk_feedback_replies_feedback` FOREIGN KEY (`feedback_id`) REFERENCES `rpos_feedback` (`feedback_id`) ON DELETE CASCADE;

--
-- Constraints for table `rpos_inventory_logs`
--
ALTER TABLE `rpos_inventory_logs`
  ADD CONSTRAINT `fk_invlog_product` FOREIGN KEY (`product_id`) REFERENCES `rpos_products` (`prod_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_invlog_staff` FOREIGN KEY (`staff_id`) REFERENCES `rpos_staff` (`staff_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rpos_orders`
--
ALTER TABLE `rpos_orders`
  ADD CONSTRAINT `ProductOrder` FOREIGN KEY (`prod_id`) REFERENCES `rpos_products` (`prod_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rpos_products`
--
ALTER TABLE `rpos_products`
  ADD CONSTRAINT `fk_product_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `rpos_suppliers` (`supplier_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `rpos_product_links`
--
ALTER TABLE `rpos_product_links`
  ADD CONSTRAINT `fk_link_base` FOREIGN KEY (`base_product_id`) REFERENCES `rpos_products` (`prod_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_link_linked` FOREIGN KEY (`linked_product_id`) REFERENCES `rpos_products` (`prod_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
