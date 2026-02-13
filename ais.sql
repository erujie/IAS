-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 13, 2026 at 02:29 AM
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
-- Database: `ais`
--

-- --------------------------------------------------------

--
-- Table structure for table `menubar`
--

CREATE TABLE `menubar` (
  `id` int(11) NOT NULL,
  `menu` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menubar`
--

INSERT INTO `menubar` (`id`, `menu`) VALUES
(0, '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Simple Sidebar</title>\r\n    <style>\r\n        * {\r\n            margin: 0;\r\n            padding: 0;\r\n            box-sizing: border-box;\r\n        }\r\n\r\n        body {\r\n            font-family: Arial, sans-serif;\r\n        }\r\n\r\n        /* Hidden checkbox */\r\n        #menu-toggle {\r\n            display: none;\r\n        }\r\n\r\n        /* Sidebar */\r\n        .sidebar {\r\n            position: fixed;\r\n            left: 0;\r\n            top: 0;\r\n            width: 250px;\r\n            height: 100%;\r\n            background: #333;\r\n            padding: 20px;\r\n            transition: 0.3s;\r\n        }\r\n\r\n        /* Hide sidebar when checkbox checked */\r\n        #menu-toggle:checked ~ .sidebar {\r\n            left: -250px;\r\n        }\r\n\r\n        .sidebar ul {\r\n            list-style: none;\r\n            margin-top: 60px;\r\n        }\r\n\r\n        .sidebar li {\r\n            margin-bottom: 5px;\r\n        }\r\n\r\n        .sidebar a {\r\n            color: white;\r\n            padding: 12px 15px;\r\n            display: block;\r\n            text-decoration: none;\r\n            border-radius: 5px;\r\n        }\r\n\r\n        .sidebar a:hover {\r\n            background: #555;\r\n        }\r\n\r\n        .active a {\r\n            background: #007bff;\r\n        }\r\n\r\n        /* Toggle Button */\r\n        .toggle-btn {\r\n            position: fixed;\r\n            left: 10px;\r\n            top: 10px;\r\n            background: #333;\r\n            color: white;\r\n            padding: 10px 15px;\r\n            font-size: 20px;\r\n            cursor: pointer;\r\n            border-radius: 5px;\r\n            z-index: 1000;\r\n        }\r\n\r\n        .toggle-btn:hover {\r\n            background: #555;\r\n        }\r\n\r\n        /* Content */\r\n        .content {\r\n            margin-left: 250px;\r\n            padding: 20px;\r\n            transition: 0.3s;\r\n        }\r\n\r\n        #menu-toggle:checked ~ .content {\r\n            margin-left: 0;\r\n        }\r\n    </style>\r\n</head>\r\n<body>\r\n\r\n    <!-- Hidden checkbox -->\r\n    <input type=\"checkbox\" id=\"menu-toggle\">\r\n\r\n    <!-- Toggle button -->\r\n    <label for=\"menu-toggle\" class=\"toggle-btn\">☰</label>\r\n\r\n    <!-- Sidebar -->\r\n    <div class=\"sidebar\">\r\n        <ul>\r\n            <li class=\"active\"><a href=\"#\">Home</a></li>\r\n            <li><a href=\"#\">About</a></li>\r\n            <li><a href=\"#\">Services</a></li>\r\n            <li><a href=\"#\">Contact</a></li>\r\n        </ul>\r\n    </div>\r\n\r\n    <!-- Content -->\r\n    <div class=\"content\">\r\n        <h1>Home Page</h1>\r\n        <p>Welcome to the home page!</p>\r\n    </div>\r\n\r\n</body>\r\n</html>\r\n');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `menubar`
--
ALTER TABLE `menubar`
  ADD PRIMARY KEY (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
