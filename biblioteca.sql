-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 16, 2025 at 08:45 AM
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
-- Database: `biblioteca`
--

-- --------------------------------------------------------

--
-- Table structure for table `carti`
--

CREATE TABLE `carti` (
  `id` int(11) NOT NULL,
  `cod_bare` varchar(50) NOT NULL,
  `titlu` varchar(255) NOT NULL,
  `autor` varchar(255) DEFAULT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `cota` varchar(50) DEFAULT NULL,
  `raft` varchar(10) DEFAULT NULL,
  `nivel` varchar(10) DEFAULT NULL,
  `pozitie` varchar(10) DEFAULT NULL,
  `locatie_completa` varchar(100) GENERATED ALWAYS AS (concat('Raft ',`raft`,' - Nivel ',`nivel`,' - Poziția ',`pozitie`)) STORED,
  `sectiune` varchar(50) DEFAULT NULL,
  `observatii_locatie` text DEFAULT NULL,
  `data_adaugare` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;

--
-- Dumping data for table `carti`
--

INSERT INTO `carti` (`id`, `cod_bare`, `titlu`, `autor`, `isbn`, `cota`, `raft`, `nivel`, `pozitie`, `sectiune`, `observatii_locatie`, `data_adaugare`) VALUES
(1, 'BOOK001', 'Amintiri din copilărie', 'Ion Creangă', '9789734640539', '821.135.1 CRE a', 'A', '1', '01', 'Literatură română', NULL, '2025-11-15 09:26:52'),
(2, 'BOOK002', 'Maitreyi000', 'Mircea Eliade', '9789734640546', '821.135.1 ELI m', 'A', '1', '02', 'Literatură română', '', '2025-11-15 09:26:52'),
(3, 'BOOK003', 'Pădurea spânzuraților', 'Liviu Rebreanu', '9789734640553', '821.135.1 REB p', 'A', '1', '03', 'Literatură română', NULL, '2025-11-15 09:26:52'),
(4, 'BOOK004', 'Enigma Otiliei99', 'George Călinescu', '9789734640560', '821.135.1 CAL e', 'A', '1', '04', 'Literatură română', '', '2025-11-15 09:26:52'),
(5, 'BOOK005', 'Moromeții', 'Marin Preda', '9789734640577', '821.135.1 PRE m', 'A', '1', '05', 'Literatură română', NULL, '2025-11-15 09:26:52'),
(6, 'BOOK006', 'Bebe', 'Autor Bebe', '235456565', 'SL455', 'P', '1', '05', 'Filosofie', '', '2025-11-15 09:36:17'),
(9, 'BOOK009', 'Ultima noapte de dragoste', 'Camil Petrescu', '9789734640607', '821.135.1 PET u', 'A', '2', '03', 'Literatură română', NULL, '2025-11-15 15:12:05'),
(10, 'BOOK010', 'Luminița', 'Mihai Eminescu', '9789734640614', '821.135.1 EMI l', 'A', '2', '04', 'Poezie', NULL, '2025-11-15 15:12:05'),
(11, 'BOOK011', 'Luceafărul', 'Mihai Eminescu', '9789734640621', '821.135.1 EMI lu', 'A', '2', '05', 'Poezie', NULL, '2025-11-15 15:12:05'),
(12, 'BOOK012', 'Moara cu noroc', 'Ioan Slavici', '9789734640638', '821.135.1 SLA m', 'B', '1', '01', 'Literatură română', NULL, '2025-11-15 15:12:05'),
(13, 'BOOK013', 'O scrisoare pierdută', 'I.L. Caragiale', '9789734640645', '821.135.1 CAR o', 'B', '1', '02', 'Teatru', NULL, '2025-11-15 15:12:05'),
(14, 'BOOK014', 'Citadela sfărâmată666666', 'Tudor Arghezi', '9789734640652', '821.135.1 ARG c', 'B', '1', '03', '', '', '2025-11-15 15:12:05'),
(15, 'BOOK015', 'Groapa', 'Eugen Barbu', '9789734640669', '821.135.1 BAR g', 'B', '1', '04', 'Literatură română', NULL, '2025-11-15 15:12:05'),
(17, 'BOOK0040', 'Yoga', 'Henri stahl', '', '', '', '', '', '', '', '2025-11-15 23:09:21');

-- --------------------------------------------------------

--
-- Table structure for table `cititori`
--

CREATE TABLE `cititori` (
  `id` int(11) NOT NULL,
  `cod_bare` varchar(50) NOT NULL,
  `nume` varchar(100) NOT NULL,
  `prenume` varchar(100) NOT NULL,
  `telefon` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `data_inregistrare` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;

--
-- Dumping data for table `cititori`
--

INSERT INTO `cititori` (`id`, `cod_bare`, `nume`, `prenume`, `telefon`, `email`, `data_inregistrare`) VALUES
(1, 'USER001', 'Popescuffff', 'Ion', '0721123456', 'ion.popescu@email.ro', '2025-11-15 09:26:52'),
(2, 'USER002', 'Ionescu', 'Maria', '0722234567', 'maria.ionescu@email.ro', '2025-11-15 09:26:52'),
(3, 'USER003', 'Dumitrescu', 'Andrei', '0723345678', 'andrei.dumitrescu@email.ro', '2025-11-15 09:26:52'),
(4, 'USER004', 'Gheorghe', 'Elena', '0724456789', 'elena.gheorghe@email.ro', '2025-11-15 15:11:57'),
(5, 'USER005', 'Radu', 'Mihai', '0725567890', 'mihai.radu@email.ro', '2025-11-15 15:11:57'),
(6, 'USER006', 'Stan', 'Alexandra', '0726678901', 'alexandra.stan@email.ro', '2025-11-15 15:11:57'),
(8, 'USER008', 'Popa', 'Diana', '0728890123', 'diana.popa@email.ro', '2025-11-15 15:11:57'),
(14, 'USER0070', 'Sebi', 'ionut', '0766334566', 'sebi@yahoo.com', '2025-11-15 23:10:16');

-- --------------------------------------------------------

--
-- Table structure for table `imprumuturi`
--

CREATE TABLE `imprumuturi` (
  `id` int(11) NOT NULL,
  `cod_cititor` varchar(50) NOT NULL,
  `cod_carte` varchar(50) NOT NULL,
  `data_imprumut` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_returnare` timestamp NULL DEFAULT NULL,
  `status` enum('activ','returnat') DEFAULT 'activ'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;

--
-- Dumping data for table `imprumuturi`
--

INSERT INTO `imprumuturi` (`id`, `cod_cititor`, `cod_carte`, `data_imprumut`, `data_returnare`, `status`) VALUES
(1, 'USER001', 'BOOK001', '2025-11-10 08:30:00', '2025-11-15 17:00:00', 'returnat'),
(2, 'USER002', 'BOOK003', '2025-11-12 12:15:00', '2025-11-15 16:43:31', 'returnat'),
(3, 'USER003', 'BOOK005', '2025-11-14 07:45:00', '2025-11-15 17:02:00', 'returnat'),
(4, 'USER001', 'BOOK006', '2025-11-15 09:20:00', '2025-11-15 16:57:00', 'returnat'),
(5, 'USER002', 'BOOK002', '2025-11-01 06:30:00', '2025-11-08 14:45:00', 'returnat'),
(6, 'USER003', 'BOOK004', '2025-11-03 11:20:00', '2025-11-10 08:15:00', 'returnat'),
(7, 'USER001', 'BOOK002', '2025-11-05 13:00:00', '2025-11-12 10:30:00', 'returnat'),
(8, 'USER002', 'BOOK001', '2025-10-28 07:00:00', '2025-11-05 12:20:00', 'returnat'),
(9, 'USER001', 'BOOK006', '2025-11-15 13:40:00', NULL, 'returnat'),
(19, 'USER003', 'BOOK006', '2025-11-10 14:10:34', '2025-11-15 16:10:34', 'returnat'),
(32, 'USER001', 'BOOK006', '2025-11-15 13:42:00', NULL, 'returnat'),
(35, 'USER004', 'BOOK009', '2025-11-15 08:12:00', '2025-11-15 16:58:00', 'returnat'),
(36, 'USER005', 'BOOK010', '2025-11-15 06:12:00', '2025-11-15 16:59:00', 'returnat'),
(37, 'USER006', 'BOOK011', '2025-11-15 04:12:00', '2025-11-15 17:01:00', 'returnat'),
(39, 'USER008', 'BOOK013', '2025-11-13 14:12:00', '2025-11-15 17:02:00', 'returnat'),
(40, 'USER001', 'BOOK014', '2025-11-12 14:12:00', '2025-11-02 22:31:00', 'returnat'),
(41, 'USER002', 'BOOK015', '2025-11-11 14:12:00', '2025-11-15 17:02:00', 'returnat'),
(42, 'USER003', 'BOOK006', '2025-11-10 14:12:59', '2025-11-15 16:12:59', 'returnat'),
(45, 'USER006', 'BOOK009', '2025-11-07 14:12:59', '2025-11-13 14:12:59', 'returnat'),
(47, 'USER008', 'BOOK011', '2025-11-05 14:12:59', '2025-11-11 14:12:59', 'returnat'),
(48, 'USER001', 'BOOK012', '2025-10-31 14:12:59', '2025-11-05 14:12:59', 'returnat'),
(49, 'USER002', 'BOOK013', '2025-10-26 14:12:59', '2025-10-31 14:12:59', 'returnat'),
(50, 'USER003', 'BOOK014', '2025-10-21 13:12:59', '2025-10-26 14:12:59', 'returnat'),
(51, 'USER004', 'BOOK015', '2025-10-16 13:12:59', '2025-10-21 13:12:59', 'returnat'),
(52, 'USER005', 'BOOK001', '2025-10-11 13:12:00', '2025-11-15 17:01:00', 'returnat'),
(53, 'USER006', 'BOOK002', '2025-10-06 13:12:00', '2025-11-15 17:02:00', 'returnat'),
(55, 'USER001', 'BOOK001', '2025-11-15 21:58:00', '2025-11-03 22:31:00', 'returnat'),
(56, 'USER001', 'BOOK002', '2025-11-15 21:58:41', NULL, 'activ'),
(57, 'USER002', 'BOOK003', '2025-11-15 22:12:12', NULL, 'activ'),
(58, 'USER002', 'BOOK006', '2025-11-15 22:12:25', NULL, 'activ'),
(59, 'USER002', 'BOOK005', '2025-11-15 22:12:00', '2025-11-05 22:31:00', 'returnat'),
(60, 'USER002', 'BOOK004', '2025-11-15 22:36:16', NULL, 'activ'),
(61, 'USER002', 'BOOK005', '2025-11-15 22:36:25', NULL, 'activ'),
(62, 'USER002', 'BOOK001', '2025-11-15 22:37:08', NULL, 'activ');

-- --------------------------------------------------------

--
-- Table structure for table `istoric_locatii`
--

CREATE TABLE `istoric_locatii` (
  `id` int(11) NOT NULL,
  `cod_carte` varchar(50) NOT NULL,
  `raft_vechi` varchar(10) DEFAULT NULL,
  `nivel_vechi` varchar(10) DEFAULT NULL,
  `pozitie_veche` varchar(10) DEFAULT NULL,
  `raft_nou` varchar(10) DEFAULT NULL,
  `nivel_nou` varchar(10) DEFAULT NULL,
  `pozitie_noua` varchar(10) DEFAULT NULL,
  `data_mutare` timestamp NOT NULL DEFAULT current_timestamp(),
  `utilizator` varchar(100) DEFAULT NULL,
  `motiv` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_romanian_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carti`
--
ALTER TABLE `carti`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cod_bare` (`cod_bare`),
  ADD KEY `idx_cod_bare` (`cod_bare`),
  ADD KEY `idx_locatie` (`raft`,`nivel`,`pozitie`),
  ADD KEY `idx_cota` (`cota`);

--
-- Indexes for table `cititori`
--
ALTER TABLE `cititori`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cod_bare` (`cod_bare`),
  ADD KEY `idx_cod_bare` (`cod_bare`);

--
-- Indexes for table `imprumuturi`
--
ALTER TABLE `imprumuturi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cod_carte` (`cod_carte`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_cititor` (`cod_cititor`);

--
-- Indexes for table `istoric_locatii`
--
ALTER TABLE `istoric_locatii`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cod_carte` (`cod_carte`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carti`
--
ALTER TABLE `carti`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `cititori`
--
ALTER TABLE `cititori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `imprumuturi`
--
ALTER TABLE `imprumuturi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `istoric_locatii`
--
ALTER TABLE `istoric_locatii`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `imprumuturi`
--
ALTER TABLE `imprumuturi`
  ADD CONSTRAINT `imprumuturi_ibfk_1` FOREIGN KEY (`cod_cititor`) REFERENCES `cititori` (`cod_bare`),
  ADD CONSTRAINT `imprumuturi_ibfk_2` FOREIGN KEY (`cod_carte`) REFERENCES `carti` (`cod_bare`);

--
-- Constraints for table `istoric_locatii`
--
ALTER TABLE `istoric_locatii`
  ADD CONSTRAINT `istoric_locatii_ibfk_1` FOREIGN KEY (`cod_carte`) REFERENCES `carti` (`cod_bare`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
