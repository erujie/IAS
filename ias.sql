-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 07, 2026 at 02:53 AM
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
-- Database: `ias`
--

-- --------------------------------------------------------

--
-- Table structure for table `forms`
--

CREATE TABLE `forms` (
  `id` int(11) NOT NULL,
  `form_name` varchar(50) NOT NULL,
  `form_html` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forms`
--

INSERT INTO `forms` (`id`, `form_name`, `form_html`) VALUES
(1, 'login', '<div class=\"form-group\">\r\n    <label for=\"username\">Username</label>\r\n    <input type=\"text\" id=\"username\" name=\"username\" class=\"form-input\"\r\n           placeholder=\"Enter your username\" autocomplete=\"username\">\r\n</div>\r\n<div class=\"form-group\">\r\n    <label for=\"password\">Password</label>\r\n    <div class=\"pw-wrap\">\r\n        <input type=\"password\" id=\"password\" name=\"password\" class=\"form-input\"\r\n               placeholder=\"Enter your password\" autocomplete=\"current-password\">\r\n        <button type=\"button\" class=\"toggle-pw\" onclick=\"togglePassword()\">👁</button>\r\n    </div>\r\n</div>\r\n<div class=\"form-group\">\r\n    <label>Security Check</label>\r\n    <div class=\"captcha-row\">\r\n        <img src=\"captcha.php\" id=\"captcha-img\" alt=\"CAPTCHA\" class=\"captcha-img\">\r\n        <button type=\"button\" class=\"captcha-refresh\" onclick=\"refreshCaptcha()\" title=\"Refresh\">↻</button>\r\n    </div>\r\n    <input type=\"text\" name=\"captcha_input\" class=\"form-input captcha-input\"\r\n           placeholder=\"Type the code above\" autocomplete=\"off\" maxlength=\"5\">\r\n</div>\r\n<button type=\"submit\" name=\"login_submit\" class=\"btn-login\">Sign In &rarr;</button>\r\n<div class=\"form-footer\">\r\n    Don&apos;t have an account? <a href=\"register.php\">Create one</a>\r\n</div>'),
(2, 'register', '<div class=\"form-group\">\r\n    <label for=\"username\">Username</label>\r\n    <input type=\"text\" id=\"username\" name=\"username\" class=\"form-input\"\r\n           placeholder=\"Choose a username\" autocomplete=\"username\">\r\n</div>\r\n<div class=\"form-group\">\r\n    <label for=\"email\">Email Address</label>\r\n    <input type=\"email\" id=\"email\" name=\"email\" class=\"form-input\"\r\n           placeholder=\"you@example.com\" autocomplete=\"email\">\r\n</div>\r\n<div class=\"form-group\">\r\n    <label for=\"password\">Password</label>\r\n    <div class=\"pw-wrap\">\r\n        <input type=\"password\" id=\"password\" name=\"password\" class=\"form-input\"\r\n               placeholder=\"Create a password\" autocomplete=\"new-password\">\r\n        <button type=\"button\" class=\"toggle-pw\" onclick=\"togglePassword()\">👁</button>\r\n    </div>\r\n    <p class=\"hint\">Minimum 6 characters</p>\r\n</div>\r\n<div class=\"form-group\">\r\n    <label for=\"confirm_password\">Confirm Password</label>\r\n    <input type=\"password\" id=\"confirm_password\" name=\"confirm_password\" class=\"form-input\"\r\n           placeholder=\"Repeat your password\" autocomplete=\"new-password\">\r\n</div>\r\n<button type=\"submit\" name=\"register_submit\" class=\"btn-login\">Create Account &rarr;</button>\r\n<div class=\"form-footer\">\r\n    Already have an account? <a href=\"login.php\">Sign in</a>\r\n</div>');

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
(0, '<!DOCTYPE html>\r\n<html>\r\n<head>\r\n    <title>Simple Sidebar</title>\r\n    <style>\r\n        * {\r\n            margin: 0;\r\n            padding: 0;\r\n            box-sizing: border-box;\r\n        }\r\n\r\n        body {\r\n            font-family: Arial, sans-serif;\r\n        }\r\n\r\n        /* Hidden checkbox */\r\n        #menu-toggle {\r\n            display: none;\r\n        }\r\n\r\n        /* Sidebar */\r\n        .sidebar {\r\n            position: fixed;\r\n            left: 0;\r\n            top: 0;\r\n            width: 250px;\r\n            height: 100%;\r\n            background: #333;\r\n            padding: 20px;\r\n            transition: 0.3s;\r\n        }\r\n\r\n        /* Hide sidebar when checkbox checked */\r\n        #menu-toggle:checked ~ .sidebar {\r\n            left: -250px;\r\n        }\r\n\r\n        .sidebar ul {\r\n            list-style: none;\r\n            margin-top: 60px;\r\n        }\r\n\r\n        .sidebar li {\r\n            margin-bottom: 5px;\r\n        }\r\n\r\n        .sidebar a {\r\n            color: white;\r\n            padding: 12px 15px;\r\n            display: block;\r\n            text-decoration: none;\r\n            border-radius: 5px;\r\n        }\r\n\r\n        .sidebar a:hover {\r\n            background: #555;\r\n        }\r\n\r\n        .active a {\r\n            background: #007bff;\r\n        }\r\n\r\n        /* Logout link */\r\n        .logout a {\r\n            color: #ff6b6b;\r\n        }\r\n\r\n        .logout a:hover {\r\n            background: #555;\r\n        }\r\n\r\n        /* Toggle Button */\r\n        .toggle-btn {\r\n            position: fixed;\r\n            left: 10px;\r\n            top: 10px;\r\n            background: #333;\r\n            color: white;\r\n            padding: 10px 15px;\r\n            font-size: 20px;\r\n            cursor: pointer;\r\n            border-radius: 5px;\r\n            z-index: 1000;\r\n        }\r\n\r\n        .toggle-btn:hover {\r\n            background: #555;\r\n        }\r\n\r\n        /* Content */\r\n        .content {\r\n            margin-left: 250px;\r\n            padding: 20px;\r\n            transition: 0.3s;\r\n        }\r\n\r\n        #menu-toggle:checked ~ .content {\r\n            margin-left: 0;\r\n        }\r\n    </style>\r\n</head>\r\n<body>\r\n\r\n    <input type=\"checkbox\" id=\"menu-toggle\">\r\n    <label for=\"menu-toggle\" class=\"toggle-btn\">☰</label>\r\n\r\n    <div class=\"sidebar\">\r\n        <ul>\r\n            <li class=\"active\"><a href=\"#\">Home</a></li>\r\n            <li><a href=\"#\">About</a></li>\r\n            <li><a href=\"#\">Services</a></li>\r\n            <li><a href=\"#\">Contact</a></li>\r\n            <li class=\"logout\"><a href=\"logout.php\">Logout</a></li>\r\n        </ul>\r\n    </div>\r\n\r\n    <div class=\"content\">\r\n        <h1>Home Page</h1>\r\n        <p>Welcome to the home page!</p>\r\n    </div>\r\n\r\n</body>\r\n</html>');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varbinary(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 0x99f5f4350b50ee869f36a21cb7eb2345, 'erjay@gmail.com', '$2y$10$Uhm0tDYB4FvAmGYDhDaB7Ocpp3nJCbEOwuRP.GugcYhF7yDH3W67y', '2026-03-07 01:34:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `forms`
--
ALTER TABLE `forms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `form_name` (`form_name`);

--
-- Indexes for table `menubar`
--
ALTER TABLE `menubar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `forms`
--
ALTER TABLE `forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
