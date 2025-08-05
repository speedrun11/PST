-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 05, 2025 at 08:40 AM
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
-- Table structure for table `rpos_feedback`
--

CREATE TABLE `rpos_feedback` (
  `feedback_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `feedback_text` text NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rpos_inventory_logs`
--

CREATE TABLE `rpos_inventory_logs` (
  `log_id` int(11) NOT NULL,
  `product_id` varchar(200) NOT NULL,
  `activity_type` enum('Restock','Sale','Adjustment','Waste','Transfer') NOT NULL,
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
(1, '52b31af7f6', 'Restock', 15, 0, 15, 1, '2025-08-01 00:06:56', '', 'RST-688b94a0234e3'),
(2, 'ebd2468100', 'Restock', 9, 0, 9, 1, '2025-08-01 00:07:15', '', 'RST-688b94b37414c'),
(3, '39443d8638', 'Restock', 12, 9, 21, 1, '2025-08-01 11:09:49', '', 'RST-688c2ffccf2f5'),
(5, '39443d8638', 'Restock', 33, 21, 54, 1, '2025-08-02 01:48:46', 'Restocked from supplier: PASTIL. ', 'RST-688cfdfe91c3c'),
(8, '52b31af7f6', 'Restock', 10, 15, 25, 1, '2025-08-05 12:57:41', 'Restocked from supplier: PASTIL. ', 'RST-68918f455c121');

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
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `prod_threshold` int(11) NOT NULL DEFAULT 10 COMMENT 'Low stock alert level',
  `prod_category` varchar(100) DEFAULT NULL COMMENT 'Product category',
  `last_restocked` datetime DEFAULT NULL COMMENT 'Date of last restock',
  `supplier_id` varchar(200) DEFAULT NULL COMMENT 'Reference to supplier'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rpos_products`
--

INSERT INTO `rpos_products` (`prod_id`, `prod_code`, `prod_name`, `prod_img`, `prod_desc`, `prod_price`, `created_at`, `prod_quantity`, `prod_threshold`, `prod_category`, `last_restocked`, `supplier_id`) VALUES
('39443d8638', 'NMRV-7923', 'B1 - Regular Beef', 'B1.jpg', 'Filipino packed rice dish originating from Maguindanao, Mindanao, traditionally consisting of steamed rice wrapped in banana leaves with shredded chicken, fish, or beef.', 36.00, '2025-08-05 04:56:45.716248', 54, 20, 'Food', '2025-08-01 00:00:00', '1'),
('52b31af7f6', 'KCNI-7896', 'C1 - Regular Chicken', 'C1.jpg', 'Filipino packed rice dish originating from Maguindanao, Mindanao, traditionally consisting of steamed rice wrapped in banana leaves with shredded chicken, fish, or beef.', 21.00, '2025-08-05 04:57:41.377744', 25, 10, 'Food', '2025-08-05 00:00:00', '1'),
('ebd2468100', 'UYVB-7459', 'C2 - Spicy Chicken', 'C2.webp', 'Filipino packed rice dish originating from Maguindanao, Mindanao, traditionally consisting of steamed rice wrapped in banana leaves with shredded chicken, fish, or beef.', 21.00, '2025-07-31 16:09:26.642611', 9, 10, 'Food', '2025-08-01 00:07:15', NULL);

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
(1, 'PASTIL', '09122415127', 'pastil@gmail.com', 'Cainta, Rizal', '2025-07-31 18:39:12');

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
-- Indexes for table `rpos_feedback`
--
ALTER TABLE `rpos_feedback`
  ADD PRIMARY KEY (`feedback_id`);

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
-- Indexes for table `rpos_orders`
--
ALTER TABLE `rpos_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `CustomerOrder` (`customer_id`),
  ADD KEY `ProductOrder` (`prod_id`);

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
  ADD UNIQUE KEY `prod_name` (`prod_name`);

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `rpos_categories`
--
ALTER TABLE `rpos_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rpos_feedback`
--
ALTER TABLE `rpos_feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rpos_inventory_logs`
--
ALTER TABLE `rpos_inventory_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `rpos_pass_resets`
--
ALTER TABLE `rpos_pass_resets`
  MODIFY `reset_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rpos_staff`
--
ALTER TABLE `rpos_staff`
  MODIFY `staff_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `rpos_suppliers`
--
ALTER TABLE `rpos_suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
