-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 04, 2025 at 08:15 AM
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vehicle_rental_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `complete_address` varchar(255) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `dropoff_location` varchar(255) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `start_date_and_time` datetime DEFAULT NULL,
  `end_date_and_time` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `number_of_day` int(255) NOT NULL,
  `total_cost` decimal(10,2) NOT NULL,
  `proof_payment` varchar(255) DEFAULT NULL,
  `payment_method` varchar(250) DEFAULT NULL,
  `code` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `vehicle_id`, `username`, `fullname`, `complete_address`, `mobile`, `pickup_location`, `dropoff_location`, `status`, `start_date_and_time`, `end_date_and_time`, `created_at`, `number_of_day`, `total_cost`, `proof_payment`, `payment_method`, `code`) VALUES
(94, 3, 'LUCEL', 'LUCEL CANO.PONSO', 'brgy. Tinubdan Palompon leyte', '09659186523', 'Palompon', 'Palompon', 'confirmed', '2024-11-21 13:49:00', '2024-11-22 13:49:00', '2024-11-21 05:50:59', 0, '9600.00', '168.jpeg', NULL, NULL),
(97, 13, 'roel', 'Mary grace benecario', 'sitio bay-ang barangay salvacion ormoc city, leyte', '9661383820', 'villaba park', 'saint peter an paul parish church ormoc city', 'confirmed', '2024-12-05 05:20:00', '2024-12-05 12:01:00', '2024-12-04 06:22:11', 0, '2000.00', 'pay.jpg', NULL, NULL),
(106, 14, 'roel', 'Mary grace benecario', 'sitio bay-ang barangay salvacion ormoc city, leyte', '9661383820', 'villaba park', 'saint peter and paul parish church ormoc city', 'confirmed', '2024-12-21 13:53:00', '2024-12-22 13:59:00', '2024-12-18 15:52:31', 0, '1800.00', '168.jpeg', NULL, NULL),
(113, 1, 'grace', 'mary grace S. Benecario', 'purok Orchids Brgy. Suba Villaba, Leyte', '09304694542', 'Pob. Del Norte Villaba, Leyte, Villaba Park', 'Ormoc city superdome ', 'pending', '2024-12-20 09:00:00', '2024-12-21 09:00:00', '2024-12-19 01:00:31', 0, '2500.00', '168.jpeg', NULL, NULL),
(114, 4, 'SHEILA LUBIANO TORON', 'SHEILA LUBIANO TORON', 'Sitio Bay-Ang Barangay Salvacion Ormoc City, Leyte', '09380373278', 'Pob. Del Norte Villaba, Leyte, Villaba Park', 'Ormoc city superdome ', 'confirmed', '2024-12-21 10:00:00', '2024-12-22 11:00:00', '2024-12-19 02:04:40', 0, '2500.00', '51cea376a53b108b448290d54b6f2ffb.jpeg', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contact_us`
--

CREATE TABLE `contact_us` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `contact_us`
--

INSERT INTO `contact_us` (`id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'marygrace benecario', 'benext19@gmail.com', 'can you make hurry verify my account so that i can login', '2024-10-24 04:22:57'),
(2, 'marygrace benecario', 'benext19@gmail.com', 'can you make hurry verify my account so that i can login', '2024-10-24 04:25:12'),
(3, 'Josie', 'loretonowls19@gmail.com', 'i just want login', '2024-10-24 06:16:04');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `price_per_hour` decimal(10,2) NOT NULL,
  `number_of_hours` int(11) NOT NULL,
  `total_payment` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `promotion_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `discount` decimal(5,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `name`, `message`, `created_at`) VALUES
(1, 'Noel Pepito Loreto', 'booking here is fast and accurate ', '2024-10-23 06:33:27'),
(2, 'marygrace benecario', 'thanks a lot for successful booking ', '2024-10-23 15:52:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `complete_address` varchar(255) NOT NULL,
  `valid_id_front` varchar(250) NOT NULL,
  `valid_id_back` varchar(250) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`username`, `email`, `password`, `role`, `fullname`, `age`, `contact_number`, `complete_address`, `valid_id_front`, `valid_id_back`, `created_at`, `verified`) VALUES
('Aizel', 'aisumayo18@gmail.com', '$2y$10$IPLstqAdLQ40j1Z8Oc5Ez.p2l8pk.3Bm365Gt2gwRla/LEz4R4oXW', 'user', 'Aizel Sumayo', 18, '09538743679', 'Brgy. Mabini Palompon Leyte', '', '', '2024-11-21 08:59:19', 1),
('Annapretty', 'pitad21olacaoanna@gmail.com', '$2y$10$cyUuysf/CYwkbpu5QY3lQ.BsJWMlhZu.PUx0vdt/Gty6olDCISWGG', 'admin', 'Anna Toron Olacao', 21, '09287014264', 'Sitio Libu Brgy. Mabini Palompon Leyte', '', '', '2024-11-20 06:47:05', 1),
('Brenda', 'rojasbrenda143@gmail.com', '$2y$10$bjhVGQ4iCZXgytLSaZG4.e7X1rGLQyk7cX.18pyaaqZm7nRSEFqp2', 'user', 'Brenda T. Rojas', 37, '09510347806', 'San Miguel Palompon Leyte', '', '', '2024-11-21 05:59:46', 1),
('christine bardaje', 'bardajechristine25@gmail.com', '$2y$10$OnlmRn9.cDCT2XCKtqGAKuBv8esWoS8HeEj/39CLDNDOAiLPUgQAm', 'user', 'Ma. christine bardaje', 24, '09564334943', 'BRGY.MAZAWALO,PALOMPON,LEYTE', '', '', '2024-11-21 06:41:19', 1),
('grace', 'graciasutchesa@gmail.com', '$2y$10$KO1f/4b50gm6vFW9Rr.nGeqOy2FdvYnWaUGDsAYLielvjqjXuk7Mm', 'user', 'mary grace benecario', 26, '09304694542', 'suba villaba, leyte', '', '', '2024-12-19 00:54:45', 1),
('Joan', 'petilunajoan@gmail.com', '$2y$10$vysl21aaxQRusq0OKpBhAOIX93bd7FlvPJ9feG/ehSBNkY47WXxwm', 'user', 'Joan P. Monares', 21, '09362739957', 'Brgy. Mabini Palompon Leyte', '', '', '2024-11-21 08:50:44', 1),
('julie', 'pahulayanannjulie@gmail.com', '$2y$10$5rQrZEfS.DdJVHK7fDc//eUK.GK2UHgp8lgj97JVUoNq.5.k..l9e', 'user', 'julie ann pahulayan', 24, '09260368669', 'brgy mabini palompon leyte', '', '', '2024-11-23 10:49:29', 1),
('LUCEL', 'lucelcanoponso131995@gmail.com', '$2y$10$LPXdCGwhC86mSLs5go3aBuw19h/DgZwqciudApuHWkvzPWeQPze/G', 'user', 'LUCEL CANO.PONSO', 29, '09659186523', 'brgy. Tinubdan Palompon leyte', '', '', '2024-11-21 05:46:35', 1),
('noelLoreto', 'loretonowls19@gmail.com', '$2y$10$YscrAu.PjPwhPIjXmhaWK.FPFDGplWZZahSQt86A5Ks6MLnB4tJFK', 'admin', 'Noel Pepito Loreto', 23, '09661383820', 'Sitio Bay-Ang Barangay Salvacion Ormoc City, Leyte', '', '', '2024-11-20 06:44:43', 1),
('roel', 'loretonowls19@gmail.com', '$2y$10$Xd3f1q4pCdHgGr.x1Y2.mOt90nwSDS.ujwfYGPA/A9YVyk3A/T9jO', 'user', 'Noel Pepito Loreto', 23, '09661383820', 'Sitio Bay-Ang Barangay Salvacion Ormoc City, Leyte', '', '', '2024-11-20 04:49:38', 1),
('SHEILA LUBIANO TORON', 'pitad21.toronsheila@gmail.com', '$2y$10$r1RJBY748jCfxQLWtiqneuHlnqbDUZAheDYMgj37zOxRbP3SI59E6', 'user', 'SHEILA LUBIANO TORON', 20, '09161574369', 'BRGY. MABINI PALOMPON LEYTE', '', '', '2024-12-19 01:29:31', 1),
('Susan Itang', 'susan.itang21@yahoo.com', '$2y$10$/IU/8akufnwcp7Tg63rr4eJ52OOt0cUb.hq1Hn4B03imj7YpKruPG', 'user', 'Susan Itang', 23, '09055253826', 'BRGY.MAZAWALO,PALOMPON,LEYTE', '', '', '2024-11-21 07:01:31', 1);

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `vehicle_id` int(11) NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `brand` varchar(50) DEFAULT NULL,
  `model` varchar(50) NOT NULL,
  `year` int(11) NOT NULL,
  `status` enum('available','unavailable') NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `driver_name` varchar(50) NOT NULL,
  `vehicle_image` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `purpose` enum('TOYOTA','FORD','NISSAN','HYUNDAI','MITSUBISHI') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `plate_number`, `brand`, `model`, `year`, `status`, `price_per_day`, `owner`, `driver_name`, `vehicle_image`, `description`, `purpose`) VALUES
(1, '6538', 'Toyota', 'Fortuner', 2021, 'unavailable', '2500.00', 'Noel Loreto', 'mark', 'uploads/toyota fortuner2023.jpeg', '7 seaters capacity \r\nLeyte area only', 'TOYOTA'),
(2, '29682', 'Nissan', 'Urvan', 2017, 'available', '180.00', 'Sheila Toron ', 'sheila Toron', 'uploads/received_1960848614360013.jpeg', 'Nissan Urvan Standard Plus 18-Seater is an 18 seats Minivans,\r\n', 'NISSAN'),
(4, '67329', 'Toyota', 'Land Cruiser Armored ', 2022, 'unavailable', '2500.00', 'Anna Olacao', 'Richard Anderson ', 'uploads/received_2309224969421723.jpeg', '6 seaters', 'TOYOTA'),
(9, '566299', 'Nissan', 'Terra', 2019, 'available', '150.00', 'Sheila Toron ', 'kyle ymas', 'uploads/received_3894528094094980.jpeg', '7 seaters capacity\r\n\r\nLeyte area only', 'NISSAN'),
(10, '45623', 'Hyundai ', 'Santa fe', 2020, 'available', '1800.00', 'Mary grace Benecario', 'mark', 'uploads/received_3049098511896999.jpeg', '7 seaters capacity\r\nLeyte and Samar area', 'HYUNDAI'),
(13, '6538', 'Toyota', 'Rush', 2019, 'available', '2000.00', 'Mary grace Benecario', 'Richard Anderson ', 'uploads/received_504396392516578.jpeg', '\r\n7 seaters', 'TOYOTA'),
(14, '65593', 'Toyota', 'Fortuner2', 2021, 'available', '1800.00', 'Anna Olacao', 'carl', 'uploads/received_1913012045861703.jpeg', '\r\n5 seaters', 'TOYOTA'),
(17, '5623923', 'Toyota ', 'suv', 2021, 'available', '3200.00', 'Noel Loreto', 'carl', 'uploads/received_1176547503426648.jpeg', '\r\n12seaters', 'TOYOTA'),
(19, '6595', 'Ford', 'Everest', 2021, 'available', '2000.00', 'Noel Loreto', 'JAKE', 'uploads/ford everest.jpeg', '6 seaters, best for any occasions  leyte and samar area', 'FORD'),
(20, '67435', 'Ford', 'Ranger', 2022, 'available', '2000.00', 'Sheila Toron ', 'July', 'uploads/ford ranger.jpeg', '4 seaters  leyte area only', 'FORD'),
(21, '43278', 'Mitsubishi ', 'Montero Sport', 2021, 'available', '2400.00', 'Anna Olacao', 'Jamieson ', 'uploads/Mitsubishi Montero Sport.jpeg', '7seaters', 'MITSUBISHI'),
(22, '47824', 'Toyota', 'Hilux', 2017, 'available', '1800.00', 'Noel Loreto', 'carl', 'uploads/Toyota Hilux.jpeg', '\r\ncan cater passenger 5 to 7\r\narea region 8 only', 'TOYOTA'),
(24, '67435', 'Ford', 'Mustang', 2022, 'available', '2000.00', 'Anna Olacao', 'james', 'uploads/mustang.webp', '3 to 4 passengers\r\nleyte area only', 'FORD'),
(25, '87543', 'Ford', 'Raptor black', 2021, 'available', '1800.00', 'Noel Loreto', 'carl', 'uploads/raptor black.webp', 'available pickup vehicle leyte and samar area only', 'FORD'),
(26, '743520', 'Ford', 'Raptor red', 2021, 'available', '1800.00', 'Mary grace Benecario', 'mark', 'uploads/raptor red.webp', '\r\nford raptor red pick-up vehicle ', 'FORD'),
(27, '986524', 'Nissan ', 'Patrol', 2023, 'available', '2500.00', 'Mary grace Benecario', 'Richard Anderson ', 'uploads/Patrol2.jpg', '\r\n6 seaters', 'NISSAN'),
(28, '6539624', 'Nissan ', 'Terra', 2024, 'available', '2000.00', 'Noel Loreto', 'kyle ymas', 'uploads/terra2.jpg', '\r\n6 seaters leyte and samar area only', 'NISSAN'),
(29, '5349821', 'Nissan ', 'Almera', 2021, 'available', '1800.00', 'Sheila Toron ', 'sheila Toron', 'uploads/almera2.jpg', '\r\n5 seaters', 'NISSAN'),
(30, '231324', 'Nissan ', 'Livina', 2024, 'available', '2000.00', 'Mary grace Benecario', 'carl', 'uploads/livina2.jpg', ' day6 seaters', 'NISSAN'),
(31, '75649', 'Hyundai ', 'i10', 2023, 'available', '1800.00', 'Anna Olacao', 'mark', 'uploads/i10 blue.jpg', 'good for travel region 8 only\r\n5 seaters', 'HYUNDAI'),
(32, '436243', 'Hyundai ', 'Sonata', 2023, 'available', '1800.00', 'Sheila Toron ', 'CHERRY mae', 'uploads/sonata2.jpg', '\r\n4 seaters', 'HYUNDAI'),
(33, '99643', 'Hyundai ', 'Tucson', 2023, 'available', '1800.00', 'Noel Loreto', 'Jamie ', 'uploads/tucson2.jpg', '\r\n5 seaters', 'HYUNDAI'),
(34, '543362', 'Hyundai ', 'Kona', 2023, 'available', '2000.00', 'Mary grace Benecario', 'jayson', 'uploads/kona 3.jpg', '\r\n6 seaters', 'HYUNDAI'),
(35, '8664323', 'Hyundai ', 'Palisade ', 2021, 'available', '1800.00', 'Mary grace Benecario', 'James ', 'uploads/Palisade.jpg', '\r\n7 seaters', 'HYUNDAI'),
(36, '775469', 'Ford', 'Territory', 2023, 'available', '2000.00', 'Anna Olacao', 'jay', 'uploads/ford-territory.webp', 'leyte area only\r\n6 seaters', 'FORD'),
(37, '549832', 'Mitsubishi ', 'Montero Sport', 2023, 'available', '1800.00', 'Sheila Toron ', 'crismark', 'uploads/2025-mitsubishi-montero-sport.webp', '\r\n6 seaters', 'MITSUBISHI'),
(38, '733289', 'Mitsubishi ', 'Xpander cross', 2023, 'available', '2022.00', 'Noel Loreto', 'cris', 'uploads/2023-xpander-cross.webp', '\r\n6 seaters', 'MITSUBISHI'),
(39, '432876', 'Mitsubishi ', 'Xpander', 2022, 'available', '1800.00', 'Sheila Toron ', 'Justine', 'uploads/2022-mitsubishi-xpander.webp', '\r\n5seaters', 'MITSUBISHI'),
(40, '938547', 'Mitsubishi ', 'Mirage G4', 2021, 'available', '2000.00', 'Anna Olacao', 'carl john', 'uploads/2021-mitsubishi-mirage-g4-.webp', '\r\n5seaters', 'MITSUBISHI'),
(41, '8665231', 'Mitsubishi ', 'Triton', 2023, 'available', '1800.00', 'Anna Olacao', 'Hance', 'uploads/mitsubishi-triton.webp', '\r\n5 seaters pickup truck', 'MITSUBISHI');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`);

--
-- Indexes for table `contact_us`
--
ALTER TABLE `contact_us`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`,`token`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`promotion_id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`username`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`vehicle_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=115;

--
-- AUTO_INCREMENT for table `contact_us`
--
ALTER TABLE `contact_us`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `promotion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
