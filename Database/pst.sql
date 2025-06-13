-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 13, 2025 at 03:34 PM
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
('9ad012jksad01sakxncasu12e3sad', 'System Admin', 'pastilsatabi.pagasa@gmail.com', '$2y$10$.39zVClTEfQddJb2Ize54eIOQlFT08hWs5rIvSANvYUOAZJhu.Ut2');

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

--
-- Dumping data for table `rpos_customers`
--

INSERT INTO `rpos_customers` (`customer_id`, `customer_name`, `customer_phoneno`, `customer_email`, `customer_password`, `created_at`, `verification_token`, `is_verified`) VALUES
('CUS6849a6c26354d6082', 'Jeffersson Divina', '09754315633', 'jefferssond.03@gmail.com', 'adcd7048512e64b48da55b027577886ee5a36350', '2025-06-11 15:55:11.556916', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `rpos_inventory`
--

CREATE TABLE `rpos_inventory` (
  `inv_id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(20) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `reorder_level` decimal(10,2) DEFAULT 0.00,
  `location` varchar(100) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rpos_orders`
--

CREATE TABLE `rpos_orders` (
  `order_id` varchar(200) NOT NULL,
  `order_code` varchar(200) NOT NULL,
  `customer_id` varchar(200) NOT NULL,
  `customer_name` varchar(200) NOT NULL,
  `prod_id` varchar(200) NOT NULL,
  `prod_name` varchar(200) NOT NULL,
  `prod_price` varchar(200) NOT NULL,
  `prod_qty` varchar(200) NOT NULL,
  `order_status` varchar(200) NOT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rpos_orders`
--

INSERT INTO `rpos_orders` (`order_id`, `order_code`, `customer_id`, `customer_name`, `prod_id`, `prod_name`, `prod_price`, `prod_qty`, `order_status`, `created_at`) VALUES
('4ad66e196a', 'PXNJ-4081', 'CUS6849a6c26354d6082', 'Jeffersson Divina', 'c915e331cd', 'B1 - Beef Pastil', '36', '2', 'Paid', '2025-06-12 05:56:54.701534'),
('529bb823c5', 'XZWT-4589', 'CUS6849a6c26354d6082', 'Jeffersson Divina', 'e98d3a7921', 'C1 - Chicken Pastil', '21', '1', 'Paid', '2025-06-12 04:18:47.287345'),
('f01139322e', 'VWXY-3516', 'CUS6849a6c26354d6082', 'Jeffersson Divina', 'e98d3a7921', 'C1 - Regular Chicken', '21', '3', '', '2025-06-12 06:53:52.212761');

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

--
-- Dumping data for table `rpos_payments`
--

INSERT INTO `rpos_payments` (`pay_id`, `pay_code`, `order_code`, `customer_id`, `pay_amt`, `pay_method`, `created_at`) VALUES
('770bd3', '4THGL9MVFS', 'PXNJ-4081', 'CUS6849a6c26354d6082', '72', 'Cash', '2025-06-12 05:56:54.701228'),
('c4b279', '2OAMUXVGC9', 'XZWT-4589', 'CUS6849a6c26354d6082', '21', 'Cash', '2025-06-12 04:18:47.287066');

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
  `prod_price` varchar(200) NOT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `rpos_products`
--

INSERT INTO `rpos_products` (`prod_id`, `prod_code`, `prod_name`, `prod_img`, `prod_desc`, `prod_price`, `created_at`) VALUES
('c915e331cd', 'PJMC-6850', 'B1 - Regular Beef', 'B1.jpg', 'A Filipino dish made with steamed rice wrapped in banana leaves with dry shredded beef.', '36', '2025-06-12 05:29:05.799116'),
('e98d3a7921', 'RLBP-7254', 'C1 - Regular Chicken', 'C1.jpg', 'A Filipino dish made with steamed rice wrapped in banana leaves with dry shredded chicken.', '21', '2025-06-12 05:29:21.427038');

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
(1, 'Gabriel Cabututan', 'WOVQ-8652', 'gabrielcabututan@gmail.com', 'adcd7048512e64b48da55b027577886ee5a36350', '2025-06-13 13:29:14.206285', 'cashier,inventory');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `rpos_admin`
--
ALTER TABLE `rpos_admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `rpos_customers`
--
ALTER TABLE `rpos_customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `rpos_inventory`
--
ALTER TABLE `rpos_inventory`
  ADD PRIMARY KEY (`inv_id`),
  ADD UNIQUE KEY `product_code` (`product_code`),
  ADD KEY `category` (`category`),
  ADD KEY `supplier_id` (`supplier_id`);

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
  ADD PRIMARY KEY (`prod_id`);

--
-- Indexes for table `rpos_staff`
--
ALTER TABLE `rpos_staff`
  ADD PRIMARY KEY (`staff_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `rpos_inventory`
--
ALTER TABLE `rpos_inventory`
  MODIFY `inv_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rpos_pass_resets`
--
ALTER TABLE `rpos_pass_resets`
  MODIFY `reset_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rpos_staff`
--
ALTER TABLE `rpos_staff`
  MODIFY `staff_id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `rpos_orders`
--
ALTER TABLE `rpos_orders`
  ADD CONSTRAINT `CustomerOrder` FOREIGN KEY (`customer_id`) REFERENCES `rpos_customers` (`customer_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ProductOrder` FOREIGN KEY (`prod_id`) REFERENCES `rpos_products` (`prod_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
