-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2026 at 05:20 AM
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
-- Database: `entrypulse_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `ep_admin_audit`
--

CREATE TABLE `ep_admin_audit` (
  `audit_id` varchar(50) NOT NULL,
  `actor_user_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `action` varchar(50) NOT NULL,
  `details` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ep_event_catalog`
--

CREATE TABLE `ep_event_catalog` (
  `event_id` varchar(50) NOT NULL,
  `event_artist` varchar(100) NOT NULL,
  `event_title` varchar(150) NOT NULL,
  `venue_name` varchar(150) NOT NULL,
  `category` varchar(50) NOT NULL,
  `vip_price` decimal(10,2) NOT NULL,
  `lower_price` decimal(10,2) NOT NULL,
  `upper_price` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ;

--
-- Dumping data for table `ep_event_catalog`
--

INSERT INTO `ep_event_catalog` (`event_id`, `event_artist`, `event_title`, `venue_name`, `category`, `vip_price`, `lower_price`, `upper_price`, `is_active`) VALUES
('BD-PPOP', 'Babydolls', 'P-Pop Showcase', 'Skydome, SM North EDSA', 'P-Pop & OPM', 4500.00, 3000.00, 1200.00, 1),
('BINI-GB', 'BINI', 'Grand BINIverse', 'Smart Araneta Coliseum', 'P-Pop & OPM', 11195.00, 7000.00, 2000.00, 1),
('BM-MNL', 'Bruno Mars', 'Live in Manila', 'Philippine Arena (Bulacan)', 'International', 23850.00, 15000.00, 5000.00, 1),
('BP-BP', 'BLACKPINK', 'BORN PINK World Tour', 'Philippine Arena (Bulacan)', 'International', 19400.00, 14500.00, 6000.00, 1),
('BSB-DNA', 'Backstreet Boys', 'DNA World Tour', 'SM Mall of Asia Arena', 'International', 18350.00, 14000.00, 5000.00, 1),
('BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'International', 25000.00, 16000.00, 7500.00, 1),
('EN-FATE', 'ENHYPEN', 'FATE World Tour', 'New Clark City Stadium / MOA', 'International', 17000.00, 12500.00, 6500.00, 1),
('F4-MG', 'F4 (Meteor Garden)', 'Reunion Concert (Custom)', 'SM Mall of Asia Arena', 'International', 50000.00, 20000.00, 6000.00, 1),
('JMC-XMAS', 'Jose Mari Chan', 'Christmas Special Live', 'Newport Performing Arts Theater', 'P-Pop & OPM', 8500.00, 5500.00, 2200.00, 1),
('KAIA-CS', 'KAIA', 'P-Pop Concert Series', 'Music Museum (Greenhills)', 'P-Pop & OPM', 5500.00, 3500.00, 1500.00, 1),
('RD-SOLO', 'Rob Deniel', 'Solo Live Concert', 'New Frontier Theater', 'P-Pop & OPM', 6000.00, 4000.00, 2000.00, 1),
('SB19-PAG', 'SB19', 'Pagtatag / Simula at Wakas', 'Smart Araneta Coliseum', 'P-Pop & OPM', 15000.00, 8000.00, 2500.00, 1),
('TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'International', 50000.00, 22000.00, 6500.00, 1),
('TW-AH', 'The Weeknd', 'After Hours Tour (Custom)', 'Philippine Arena (Bulacan)', 'International', 50000.00, 18000.00, 7000.00, 1),
('WL-WD', 'Westlife', 'The Wild Dreams Tour', 'SM Mall of Asia Arena', 'International', 18000.00, 13500.00, 4500.00, 1),
('YC-BB', 'Yeng Constantino', 'Biyaheng Bente', 'Smart Araneta Coliseum', 'P-Pop & OPM', 8600.00, 5000.00, 2900.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `ep_gate_scans`
--

CREATE TABLE `ep_gate_scans` (
  `scan_id` varchar(50) NOT NULL,
  `ticket_serial` varchar(100) NOT NULL,
  `user_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `gate_id` varchar(30) NOT NULL,
  `scanned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ep_orders`
--

CREATE TABLE `ep_orders` (
  `order_id` varchar(50) NOT NULL,
  `user_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `event_id` varchar(50) NOT NULL,
  `event_artist` varchar(100) NOT NULL,
  `event_title` varchar(150) NOT NULL,
  `venue_name` varchar(150) NOT NULL,
  `seat_id` varchar(100) NOT NULL,
  `seat_label` varchar(150) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(30) NOT NULL,
  `ticket_serial` varchar(100) NOT NULL,
  `order_status` varchar(20) NOT NULL DEFAULT 'ACTIVE',
  `purchased_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `ep_orders`
--

INSERT INTO `ep_orders` (`order_id`, `user_id`, `event_id`, `event_artist`, `event_title`, `venue_name`, `seat_id`, `seat_label`, `total_amount`, `payment_method`, `ticket_serial`, `order_status`, `purchased_at`) VALUES
('ord_3d8ab43506e496e7a432b1a4', 'usr_101', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'BTS-WT-LB-2-R1-S5', 'Lower Box 2 - Row 1, Seat 5', 13600.00, 'CARD', 'EP-6801C69BAF5F319A', 'ACTIVE', '2026-10-14 10:13:04'),
('ord_a6a84b3e7489ab46a01acb6b', 'usr_102', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'BTS-WT-GA-R1-S2', 'Gen Ad - Row 1, Seat 2', 7500.00, 'MAYA', 'EP-7AEAAD4EC7F3AB10', 'ACTIVE', '2026-10-14 10:09:35');

-- --------------------------------------------------------

--
-- Table structure for table `ep_refunds`
--

CREATE TABLE `ep_refunds` (
  `refund_id` varchar(50) NOT NULL,
  `order_id` varchar(50) NOT NULL,
  `user_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(200) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'PROCESSED',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Table structure for table `ep_seat_inventory`
--

CREATE TABLE `ep_seat_inventory` (
  `seat_id` varchar(100) NOT NULL,
  `event_id` varchar(50) NOT NULL,
  `event_artist` varchar(100) NOT NULL,
  `event_title` varchar(150) NOT NULL,
  `venue_name` varchar(150) NOT NULL,
  `section_id` varchar(30) NOT NULL,
  `section_name` varchar(80) NOT NULL,
  `seat_label` varchar(150) NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'AVAILABLE',
  `locked_by_user_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `lock_expires_at` datetime DEFAULT NULL,
  `ticket_serial` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `ep_seat_inventory`
--

INSERT INTO `ep_seat_inventory` (`seat_id`, `event_id`, `event_artist`, `event_title`, `venue_name`, `section_id`, `section_name`, `seat_label`, `base_price`, `status`, `locked_by_user_id`, `lock_expires_at`, `ticket_serial`, `updated_at`) VALUES
('BINI-GB-GA-R1-S1', 'BINI-GB', 'BINI', 'Grand BINIverse', 'Smart Araneta Coliseum', 'GA', 'Gen Ad', 'Gen Ad - Row 1, Seat 1', 2000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-15 02:26:58'),
('BTS-WT-GA-R1-S1', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 1, Seat 1', 7500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-GA-R1-S2', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 1, Seat 2', 7500.00, 'SOLD', NULL, NULL, 'EP-7AEAAD4EC7F3AB10', '2026-10-14 10:09:35'),
('BTS-WT-GA-R1-S3', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 1, Seat 3', 7500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-GA-R1-S4', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 1, Seat 4', 7500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 10:11:29'),
('BTS-WT-GA-R1-S5', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 1, Seat 5', 7500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-GA-R1-S6', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 1, Seat 6', 7500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-GA-R2-S1', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 2, Seat 1', 7500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-GA-R2-S2', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 2, Seat 2', 7500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-GA-R2-S3', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 2, Seat 3', 7500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-GA-R2-S4', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 2, Seat 4', 7500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-GA-R2-S5', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 2, Seat 5', 7500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-GA-R2-S6', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 2, Seat 6', 7500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-1-R1-S1', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 1, Seat 1', 16000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-1-R1-S2', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 1, Seat 2', 16000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-1-R1-S3', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 1, Seat 3', 16000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-1-R1-S4', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 1, Seat 4', 16000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-1-R1-S5', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 1, Seat 5', 16000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-1-R1-S6', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 1, Seat 6', 16000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-1-R2-S1', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 2, Seat 1', 16000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-1-R2-S2', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 2, Seat 2', 16000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-1-R2-S3', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 2, Seat 3', 16000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-1-R2-S4', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 2, Seat 4', 16000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-1-R2-S5', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 2, Seat 5', 16000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-1-R2-S6', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 2, Seat 6', 16000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-2-R1-S1', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 1, Seat 1', 13600.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-2-R1-S2', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 1, Seat 2', 13600.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-2-R1-S3', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 1, Seat 3', 13600.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-2-R1-S4', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 1, Seat 4', 13600.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-2-R1-S5', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 1, Seat 5', 13600.00, 'SOLD', NULL, NULL, 'EP-6801C69BAF5F319A', '2026-10-14 10:13:04'),
('BTS-WT-LB-2-R1-S6', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 1, Seat 6', 13600.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-2-R2-S1', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 2, Seat 1', 13600.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-2-R2-S2', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 2, Seat 2', 13600.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-2-R2-S3', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 2, Seat 3', 13600.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-2-R2-S4', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 2, Seat 4', 13600.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-2-R2-S5', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 2, Seat 5', 13600.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-LB-2-R2-S6', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 2, Seat 6', 13600.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-UB-R1-S1', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 1, Seat 1', 11250.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-UB-R1-S2', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 1, Seat 2', 11250.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-UB-R1-S3', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 1, Seat 3', 11250.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-UB-R1-S4', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 1, Seat 4', 11250.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-UB-R1-S5', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 1, Seat 5', 11250.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-UB-R1-S6', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 1, Seat 6', 11250.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-UB-R2-S1', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 2, Seat 1', 11250.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-UB-R2-S2', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 2, Seat 2', 11250.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-UB-R2-S3', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 2, Seat 3', 11250.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-UB-R2-S4', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 2, Seat 4', 11250.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-UB-R2-S5', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 2, Seat 5', 11250.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-UB-R2-S6', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 2, Seat 6', 11250.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-A-R1-S1', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 1, Seat 1', 25000.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-A-R1-S2', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 1, Seat 2', 25000.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-A-R1-S3', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 1, Seat 3', 25000.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-A-R1-S4', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 1, Seat 4', 25000.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-A-R1-S5', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 1, Seat 5', 25000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-A-R1-S6', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 1, Seat 6', 25000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-A-R2-S1', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 2, Seat 1', 25000.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-A-R2-S2', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 2, Seat 2', 25000.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-A-R2-S3', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 2, Seat 3', 25000.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-A-R2-S4', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 2, Seat 4', 25000.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-A-R2-S5', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 2, Seat 5', 25000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-A-R2-S6', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 2, Seat 6', 25000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-B-R1-S1', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 1, Seat 1', 25000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-B-R1-S2', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 1, Seat 2', 25000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-B-R1-S3', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 1, Seat 3', 25000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-B-R1-S4', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 1, Seat 4', 25000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-B-R1-S5', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 1, Seat 5', 25000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-B-R1-S6', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 1, Seat 6', 25000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-B-R2-S1', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 2, Seat 1', 25000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-B-R2-S2', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 2, Seat 2', 25000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-B-R2-S3', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 2, Seat 3', 25000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-B-R2-S4', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 2, Seat 4', 25000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-B-R2-S5', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 2, Seat 5', 25000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('BTS-WT-VIP-B-R2-S6', 'BTS-WT', 'BTS', 'World Tour', 'Philippine Arena (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 2, Seat 6', 25000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:46:24'),
('TS-ERAS-GA-R1-S1', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 1, Seat 1', 6500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-GA-R1-S2', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 1, Seat 2', 6500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-GA-R1-S3', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 1, Seat 3', 6500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-GA-R1-S4', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 1, Seat 4', 6500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-GA-R1-S5', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 1, Seat 5', 6500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-GA-R1-S6', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 1, Seat 6', 6500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-GA-R2-S1', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 2, Seat 1', 6500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-GA-R2-S2', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 2, Seat 2', 6500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-GA-R2-S3', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 2, Seat 3', 6500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-GA-R2-S4', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 2, Seat 4', 6500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-GA-R2-S5', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 2, Seat 5', 6500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-GA-R2-S6', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'GA', 'Gen Ad', 'Gen Ad - Row 2, Seat 6', 6500.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-1-R1-S1', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 1, Seat 1', 22000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-1-R1-S2', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 1, Seat 2', 22000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-1-R1-S3', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 1, Seat 3', 22000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-1-R1-S4', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 1, Seat 4', 22000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-1-R1-S5', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 1, Seat 5', 22000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-1-R1-S6', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 1, Seat 6', 22000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-1-R2-S1', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 2, Seat 1', 22000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-1-R2-S2', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 2, Seat 2', 22000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-1-R2-S3', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 2, Seat 3', 22000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-1-R2-S4', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 2, Seat 4', 22000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-1-R2-S5', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 2, Seat 5', 22000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-1-R2-S6', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-1', 'Lower Box 1', 'Lower Box 1 - Row 2, Seat 6', 22000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-2-R1-S1', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 1, Seat 1', 18700.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-2-R1-S2', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 1, Seat 2', 18700.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-2-R1-S3', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 1, Seat 3', 18700.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-2-R1-S4', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 1, Seat 4', 18700.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-2-R1-S5', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 1, Seat 5', 18700.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-2-R1-S6', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 1, Seat 6', 18700.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-2-R2-S1', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 2, Seat 1', 18700.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-2-R2-S2', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 2, Seat 2', 18700.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-2-R2-S3', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 2, Seat 3', 18700.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-2-R2-S4', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 2, Seat 4', 18700.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-2-R2-S5', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 2, Seat 5', 18700.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-LB-2-R2-S6', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'LB-2', 'Lower Box 2', 'Lower Box 2 - Row 2, Seat 6', 18700.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-UB-R1-S1', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 1, Seat 1', 9750.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-UB-R1-S2', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 1, Seat 2', 9750.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-UB-R1-S3', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 1, Seat 3', 9750.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-UB-R1-S4', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 1, Seat 4', 9750.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-UB-R1-S5', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 1, Seat 5', 9750.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-UB-R1-S6', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 1, Seat 6', 9750.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-UB-R2-S1', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 2, Seat 1', 9750.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-UB-R2-S2', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 2, Seat 2', 9750.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-UB-R2-S3', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 2, Seat 3', 9750.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-UB-R2-S4', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 2, Seat 4', 9750.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-UB-R2-S5', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 2, Seat 5', 9750.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-UB-R2-S6', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'UB', 'Upper Box', 'Upper Box - Row 2, Seat 6', 9750.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-A-R1-S1', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 1, Seat 1', 50000.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-A-R1-S2', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 1, Seat 2', 50000.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-A-R1-S3', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 1, Seat 3', 50000.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-A-R1-S4', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 1, Seat 4', 50000.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-A-R1-S5', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 1, Seat 5', 50000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-A-R1-S6', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 1, Seat 6', 50000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-A-R2-S1', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 2, Seat 1', 50000.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-A-R2-S2', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 2, Seat 2', 50000.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-A-R2-S3', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 2, Seat 3', 50000.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-A-R2-S4', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 2, Seat 4', 50000.00, 'SOLD', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-A-R2-S5', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 2, Seat 5', 50000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-A-R2-S6', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-A', 'VIP A', 'VIP A - Row 2, Seat 6', 50000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-B-R1-S1', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 1, Seat 1', 50000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-B-R1-S2', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 1, Seat 2', 50000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-B-R1-S3', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 1, Seat 3', 50000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-B-R1-S4', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 1, Seat 4', 50000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-B-R1-S5', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 1, Seat 5', 50000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-B-R1-S6', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 1, Seat 6', 50000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-B-R2-S1', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 2, Seat 1', 50000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-B-R2-S2', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 2, Seat 2', 50000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-B-R2-S3', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 2, Seat 3', 50000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-B-R2-S4', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 2, Seat 4', 50000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-B-R2-S5', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 2, Seat 5', 50000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38'),
('TS-ERAS-VIP-B-R2-S6', 'TS-ERAS', 'Taylor Swift', 'The Eras Tour (Custom)', 'Philippine Stadium (Bulacan)', 'VIP-B', 'VIP B', 'VIP B - Row 2, Seat 6', 50000.00, 'AVAILABLE', NULL, NULL, NULL, '2026-10-14 06:45:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'CUSTOMER',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `role`, `created_at`) VALUES
('usr_101', 'admin', 'admin@entrypulse.com', '$2y$10$KNqjCON6PaFcaU.ba1.RZeA02HGlgXfuY8dVTzuTLnQPrtY5f58Ee', 'ADMIN', '2026-10-14 06:52:18'),
('usr_102', 'dwight', 'dwight@entrypulse.com', '$2y$10$suPu7e2qH9N3jVn8YSTgh.1JIEI8ZRSZEsrPsiBlOSXKGeQ8BM56K', 'CUSTOMER', '2026-10-14 06:52:18'),
('usr_103', 'ruellyn', 'ruellyn@entrypulse.com', '$2y$10$DMsW28QSgxaYLyO02lQ3w.OIDNRRnmaK4XGj/WnXO.05JNCcqF9vO', 'CUSTOMER', '2026-10-14 06:52:18'),
('usr_40b70867c6f16d4b8d914372', 'betauser', 'beta@example.com', '$2y$10$ZPP4DkSxT7ce4CjUfMGcnOLTZ2evixENE/h.aRrmmK64B/Stim80y', 'CUSTOMER', '2026-10-15 02:26:58'),
('usr_5f810f961f064a4d074264a8', 'alicedemo', 'alice@example.com', '$2y$10$h94/ebHUevtrKB15ZbO8SOTvncTughhxFx.L3avcpbKIFUMoCwPx.', 'CUSTOMER', '2026-10-15 02:26:37'),
('usr_75f0f836c6c2b7a8a4c2f176', 'formuser', 'formuser@example.com', '$2y$10$RJRzQspe9diLGB/fOuXUzOZCzVmiBJgdDiRIp/DCwNTCCvZLQQeH.', 'CUSTOMER', '2026-10-15 02:28:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ep_admin_audit`
--
ALTER TABLE `ep_admin_audit`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `actor_audit_log` (`actor_user_id`,`created_at`);

--
-- Indexes for table `ep_event_catalog`
--
ALTER TABLE `ep_event_catalog`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `ep_gate_scans`
--
ALTER TABLE `ep_gate_scans`
  ADD PRIMARY KEY (`scan_id`),
  ADD UNIQUE KEY `ticket_serial` (`ticket_serial`),
  ADD KEY `user_scans` (`user_id`,`scanned_at`);

--
-- Indexes for table `ep_orders`
--
ALTER TABLE `ep_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `ticket_serial` (`ticket_serial`),
  ADD KEY `user_orders` (`user_id`,`purchased_at`),
  ADD KEY `ep_orders_seat_fk` (`seat_id`),
  ADD KEY `orders_purchased_at` (`purchased_at`),
  ADD KEY `ep_orders_event_fk` (`event_id`);

--
-- Indexes for table `ep_refunds`
--
ALTER TABLE `ep_refunds`
  ADD PRIMARY KEY (`refund_id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `user_refunds` (`user_id`,`created_at`);

--
-- Indexes for table `ep_seat_inventory`
--
ALTER TABLE `ep_seat_inventory`
  ADD PRIMARY KEY (`seat_id`),
  ADD UNIQUE KEY `ticket_serial` (`ticket_serial`),
  ADD KEY `event_status` (`event_id`,`status`),
  ADD KEY `lock_expiry` (`lock_expires_at`),
  ADD KEY `ep_seat_lock_user_fk` (`locked_by_user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ep_admin_audit`
--
ALTER TABLE `ep_admin_audit`
  ADD CONSTRAINT `ep_admin_audit_user_fk` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `ep_gate_scans`
--
ALTER TABLE `ep_gate_scans`
  ADD CONSTRAINT `ep_gate_scans_ticket_fk` FOREIGN KEY (`ticket_serial`) REFERENCES `ep_orders` (`ticket_serial`),
  ADD CONSTRAINT `ep_gate_scans_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `ep_orders`
--
ALTER TABLE `ep_orders`
  ADD CONSTRAINT `ep_orders_event_fk` FOREIGN KEY (`event_id`) REFERENCES `ep_event_catalog` (`event_id`),
  ADD CONSTRAINT `ep_orders_seat_fk` FOREIGN KEY (`seat_id`) REFERENCES `ep_seat_inventory` (`seat_id`),
  ADD CONSTRAINT `ep_orders_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `ep_refunds`
--
ALTER TABLE `ep_refunds`
  ADD CONSTRAINT `ep_refunds_order_fk` FOREIGN KEY (`order_id`) REFERENCES `ep_orders` (`order_id`),
  ADD CONSTRAINT `ep_refunds_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `ep_seat_inventory`
--
ALTER TABLE `ep_seat_inventory`
  ADD CONSTRAINT `ep_seat_lock_user_fk` FOREIGN KEY (`locked_by_user_id`) REFERENCES `users` (`user_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
