-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 27 юни 2017 в 14:10
-- Версия на сървъра: 10.1.22-MariaDB
-- PHP Version: 7.1.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ticket_system`
--

-- --------------------------------------------------------

--
-- Структура на таблица `priority`
--

CREATE TABLE `priority` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Схема на данните от таблица `priority`
--

INSERT INTO `priority` (`id`, `name`, `value`) VALUES
(3, 'Low', 1),
(4, 'Medium', 2),
(5, 'High', 3);

-- --------------------------------------------------------

--
-- Структура на таблица `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `days_left_design` date NOT NULL,
  `days_to_finish` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Схема на данните от таблица `projects`
--

INSERT INTO `projects` (`id`, `name`, `description`, `days_left_design`, `days_to_finish`) VALUES
(1, 'Ticket System', 'Best ticket system developed by Aleks Moskovski', '2017-06-21', '2017-06-30'),
(2, 'Movie Catalog', 'Best Movie Catalog ever ', '2017-06-29', '2017-08-31'),
(3, 'Bike app', 'Best Bike app', '2017-06-28', '2017-06-29'),
(4, 'Hottel app', 'Best hottel apppp eveeer ', '2017-06-28', '2017-06-30');

-- --------------------------------------------------------

--
-- Структура на таблица `status`
--

CREATE TABLE `status` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Схема на данните от таблица `status`
--

INSERT INTO `status` (`id`, `name`, `value`) VALUES
(1, 'Todo', 1),
(3, 'Doing', 2),
(4, 'Done', 3);

-- --------------------------------------------------------

--
-- Структура на таблица `ticket`
--

CREATE TABLE `ticket` (
  `id` int(130) NOT NULL,
  `name` varchar(70) NOT NULL,
  `description` varchar(500) NOT NULL,
  `priority_id` int(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `userAssing` int(11) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Схема на данните от таблица `ticket`
--

INSERT INTO `ticket` (`id`, `name`, `description`, `priority_id`, `start_date`, `end_date`, `userAssing`, `status_id`) VALUES
(0, 'Need Beer', 'Zagorka', 5, '2017-06-12', '2017-06-23', 2, 1),
(0, 'Need Beer', 'Zagorka', 5, '2017-06-12', '2017-06-23', 2, 1),
(0, 'Vafli', 'borovec', 3, '2017-06-19', '2017-06-04', 24, 2),
(0, 'Vafli', 'borovec', 3, '2017-06-19', '2017-06-04', 24, 2),
(0, 'Kola', 'Koka Kola', 4, '2017-06-05', '2017-06-22', 12, 3),
(0, 'bira trqbva Velko', 'Zagorka studena ', 3, '2017-06-16', '2017-06-22', 3, 1),
(0, 'Food kfs', 'petuk kupon', 3, '2017-06-30', '2017-06-30', 3, 1);

-- --------------------------------------------------------

--
-- Структура на таблица `user`
--

CREATE TABLE `user` (
  `id` varchar(130) NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `second_name` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `user_ip` varchar(15) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Схема на данните от таблица `user`
--

INSERT INTO `user` (`id`, `username`, `first_name`, `second_name`, `password`, `user_ip`, `date`) VALUES
('abv', 'abv', 'abv', 'abv', 'abv', '201.06.16', '2017-06-16'),
('admin@admin.com', 'admin', 'Gosho', 'Peshev', '12345', '', '2017-06-16'),
('aleks4o_94@abv.bg', 'aleks4o_94', 'Mitkov', 'Mitko', '1244124', '201.06.16', '2017-06-16'),
('asd', 'asd', 'asd', 'asd', 'ads', '201.06.16', '2017-06-16'),
('asdasd', 'asdasd', 'asdads', 'asdasd', 'asdasd', '201.06.16', '2017-06-16'),
('asdfghjk', 'asdfasf', 'asdasdasdas', 'asdasdasf', 'asfasf', '201.06.16', '2017-06-16'),
('doncho', 'doncho', 'doncho', 'doncho', 'doncho', '201.06.16', '2017-06-16'),
('mitko', 'mitko', 'mitko ', 'mitko ', 'mitko', '201.06.16', '2017-06-16'),
('mitko@mitkov.com', 'mitko', 'mitashki', 'mitkov', '123', '201.06.16', '2017-06-16'),
('petko@petko.com', 'admina', 'Petkov', 'Gosho', '123456', '201.06.16', '2017-06-16'),
('qwertyui', 'qwertyu', 'qwerty', 'qwerty', 'qwertyu', '201.06.16', '2017-06-16');

-- --------------------------------------------------------

--
-- Структура на таблица `users_details`
--

CREATE TABLE `users_details` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `gender` int(11) NOT NULL,
  `birthday` date NOT NULL,
  `img` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `priority`
--
ALTER TABLE `priority`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ticket`
--
ALTER TABLE `ticket`
  ADD KEY `priority_id` (`priority_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users_details`
--
ALTER TABLE `users_details`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `priority`
--
ALTER TABLE `priority`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `users_details`
--
ALTER TABLE `users_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
