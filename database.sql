-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2025 at 10:39 AM
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
-- Database: `airport`
--

-- --------------------------------------------------------

--
-- Table structure for table `aircraft`
--

CREATE TABLE `aircraft` (
  `AircraftID` int(11) NOT NULL,
  `AircraftType` varchar(50) DEFAULT NULL,
  `RegistrationNumber` varchar(50) DEFAULT NULL,
  `Capacity` int(11) DEFAULT NULL,
  `AirlineCode` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aircraft`
--

INSERT INTO `aircraft` (`AircraftID`, `AircraftType`, `RegistrationNumber`, `Capacity`, `AirlineCode`) VALUES
(1, 'Boeing 737-800', 'PK-GMI', 162, 'GA'),
(2, 'Airbus A350-900', '9V-SMF', 325, 'SQ'),
(3, 'Boeing 777-200', '9M-MRL', 314, 'MH'),
(4, 'Boeing 787-9', 'JA861J', 296, 'JL'),
(5, 'Airbus A320-200', 'PK-AXC', 180, 'QZ');

-- --------------------------------------------------------

--
-- Table structure for table `airline`
--

CREATE TABLE `airline` (
  `AirlineCode` varchar(2) NOT NULL,
  `AirlineName` varchar(100) DEFAULT NULL,
  `ContactNumber` varchar(20) DEFAULT NULL,
  `OperatingRegion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `airline`
--

INSERT INTO `airline` (`AirlineCode`, `AirlineName`, `ContactNumber`, `OperatingRegion`) VALUES
('GA', 'Garuda Indonesia', '+62 21 2351 9999', 'Asia, Australia'),
('JL', 'Japan Airlines', '+81 3 5460 3121', 'Asia, North America'),
('MH', 'Malaysia Airlines', '+60 3 7843 3000', 'Asia, Middle East'),
('QZ', 'Indonesia AirAsia', '+62 21 2927 0999', 'Asia'),
('SQ', 'Singapore Airlines', '+65 6223 8888', 'Asia, Europe');

-- --------------------------------------------------------

--
-- Table structure for table `airport`
--

CREATE TABLE `airport` (
  `AirportCode` varchar(3) NOT NULL,
  `AirportName` varchar(100) DEFAULT NULL,
  `City` varchar(30) DEFAULT NULL,
  `Country` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `airport`
--

INSERT INTO `airport` (`AirportCode`, `AirportName`, `City`, `Country`) VALUES
('AMS', 'Amsterdam Airport', 'Amsterdam', 'Netherlands'),
('CBR', 'Canberra Airport', 'Canberra', 'Australia'),
('CDG', 'Charles de Gaulle Airport', 'Paris', 'France'),
('CGK', 'Soekarno-Hatta International Airport', 'Jakarta', 'Indonesia'),
('DEL', 'Indira Gandhi International Airport', 'New Delhi', 'India'),
('DME', 'Domodedovo International Airport', 'Moscow', 'Russia'),
('DPS', 'Ngurah Rai International Airport', 'Denpasar', 'Indonesia'),
('DXB', 'Dubai International Airport', 'Dubai', 'United Arab Emirates'),
('HND', 'Tokyo Haneda Airport', 'Tokyo', 'Japan'),
('ICN', 'Incheon International Airport', 'Seoul', 'South Korea'),
('KUL', 'Kuala Lumpur International Airport', 'Kuala Lumpur', 'Malaysia'),
('LCY', 'London City Airport', 'London', 'England'),
('PEK', 'Beijing Capital International Airport', 'Beijing', 'China'),
('SIN', 'Changi International Airport', 'Singapore', 'Singapore');

-- --------------------------------------------------------

--
-- Table structure for table `baggage`
--

CREATE TABLE `baggage` (
  `BaggageID` int(11) NOT NULL,
  `BookingID` int(11) DEFAULT NULL,
  `BaggageType` enum('Checked','Carry-on') DEFAULT 'Checked',
  `Weight` decimal(5,2) DEFAULT NULL,
  `Status` enum('Checked In','Onboard','In Transit','Lost') DEFAULT 'Checked In'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `baggage`
--

INSERT INTO `baggage` (`BaggageID`, `BookingID`, `BaggageType`, `Weight`, `Status`) VALUES
(1, 1, 'Checked', 20.50, 'Checked In'),
(2, 2, 'Carry-on', 7.00, 'Onboard'),
(3, 3, 'Checked', 23.40, 'In Transit'),
(4, 4, 'Carry-on', 0.00, 'Checked In'),
(5, 5, 'Checked', 18.75, 'Lost');

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `BookingID` int(11) NOT NULL,
  `FlightID` int(11) DEFAULT NULL,
  `PassengerID` int(11) DEFAULT NULL,
  `BookingDate` datetime DEFAULT current_timestamp(),
  `PaymentStatus` enum('Pending','Paid','Cancelled','Rescheduled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking`
--

INSERT INTO `booking` (`BookingID`, `FlightID`, `PassengerID`, `BookingDate`, `PaymentStatus`) VALUES
(1, 1, 1, '2025-05-01 10:00:00', 'Paid'),
(2, 2, 2, '2025-05-02 14:30:00', 'Pending'),
(3, 3, 3, '2025-05-03 09:00:00', 'Paid'),
(4, 4, 4, '2025-05-04 16:45:00', 'Cancelled'),
(5, 5, 5, '2025-05-05 12:00:00', 'Paid');

-- --------------------------------------------------------

--
-- Table structure for table `flight`
--

CREATE TABLE `flight` (
  `FlightID` int(11) NOT NULL,
  `FlightNumber` varchar(20) DEFAULT NULL,
  `AirlineCode` varchar(2) DEFAULT NULL,
  `DepartureDateTime` datetime DEFAULT NULL,
  `ArrivalDateTime` datetime DEFAULT NULL,
  `OriginAirportCode` varchar(3) DEFAULT NULL,
  `DestinationAirportCode` varchar(3) DEFAULT NULL,
  `AvailableSeats` int(11) DEFAULT NULL CHECK (`AvailableSeats` >= 0),
  `Status` enum('Terjadwal','Ditunda','Dibatalkan') DEFAULT 'Terjadwal'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `flight`
--

INSERT INTO `flight` (`FlightID`, `FlightNumber`, `AirlineCode`, `DepartureDateTime`, `ArrivalDateTime`, `OriginAirportCode`, `DestinationAirportCode`, `AvailableSeats`, `Status`) VALUES
(1, 'GA710', 'GA', '2025-05-10 08:00:00', '2025-05-10 12:00:00', 'CGK', 'DPS', 50, 'Terjadwal'),
(2, 'SQ952', 'SQ', '2025-05-10 09:30:00', '2025-05-10 12:30:00', 'SIN', 'CGK', 40, 'Terjadwal'),
(3, 'MH721', 'MH', '2025-05-11 07:00:00', '2025-05-11 10:00:00', 'KUL', 'CGK', 60, 'Terjadwal'),
(4, 'JL729', 'JL', '2025-05-12 22:00:00', '2025-05-13 05:30:00', 'HND', 'CGK', 30, 'Terjadwal'),
(5, 'QZ751', 'QZ', '2025-05-13 15:00:00', '2025-05-13 18:00:00', 'DPS', 'SIN', 70, 'Terjadwal');

-- --------------------------------------------------------

--
-- Table structure for table `passenger`
--

CREATE TABLE `passenger` (
  `PassengerID` int(11) NOT NULL,
  `FirstName` varchar(30) DEFAULT NULL,
  `LastName` varchar(30) DEFAULT NULL,
  `Email` varchar(50) DEFAULT NULL,
  `PassportNumber` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passenger`
--

INSERT INTO `passenger` (`PassengerID`, `FirstName`, `LastName`, `Email`, `PassportNumber`) VALUES
(1, 'Candra', 'Wijaya', 'andi.wijaya@email.com', 'A12345678'),
(2, 'Rina', 'Putri', 'rina.putri@email.com', 'B98765432'),
(3, 'James', 'Tan', 'james.tan@email.com', 'C45678901'),
(4, 'Sakura', 'Yamamoto', 'sakura.yamamoto@email.com', 'D11223344'),
(5, 'Suchi', 'Hartini', 'ahmad.fahmi@email.com', 'E99887766');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `PaymentID` int(11) NOT NULL,
  `BookingID` int(11) DEFAULT NULL,
  `PaymentMethod` varchar(50) DEFAULT NULL,
  `Amount` decimal(10,2) DEFAULT NULL,
  `TransactionDateTime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`PaymentID`, `BookingID`, `PaymentMethod`, `Amount`, `TransactionDateTime`) VALUES
(1, 1, 'Credit Card', 1500000.00, '2025-05-21 22:20:10'),
(2, 3, 'Bank Transfer', 2500000.00, '2025-05-03 09:10:00'),
(3, 5, 'E-Wallet', 1200000.00, '2025-05-05 12:05:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aircraft`
--
ALTER TABLE `aircraft`
  ADD PRIMARY KEY (`AircraftID`),
  ADD KEY `AirlineCode` (`AirlineCode`);

--
-- Indexes for table `airline`
--
ALTER TABLE `airline`
  ADD PRIMARY KEY (`AirlineCode`);

--
-- Indexes for table `airport`
--
ALTER TABLE `airport`
  ADD PRIMARY KEY (`AirportCode`);

--
-- Indexes for table `baggage`
--
ALTER TABLE `baggage`
  ADD PRIMARY KEY (`BaggageID`),
  ADD KEY `BookingID` (`BookingID`);

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`BookingID`),
  ADD KEY `FlightID` (`FlightID`),
  ADD KEY `PassengerID` (`PassengerID`);

--
-- Indexes for table `flight`
--
ALTER TABLE `flight`
  ADD PRIMARY KEY (`FlightID`),
  ADD UNIQUE KEY `FlightNumber` (`FlightNumber`),
  ADD KEY `AirlineCode` (`AirlineCode`),
  ADD KEY `OriginAirportCode` (`OriginAirportCode`),
  ADD KEY `DestinationAirportCode` (`DestinationAirportCode`);

--
-- Indexes for table `passenger`
--
ALTER TABLE `passenger`
  ADD PRIMARY KEY (`PassengerID`),
  ADD UNIQUE KEY `PassportNumber` (`PassportNumber`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`PaymentID`),
  ADD UNIQUE KEY `BookingID` (`BookingID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `baggage`
--
ALTER TABLE `baggage`
  MODIFY `BaggageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `BookingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `flight`
--
ALTER TABLE `flight`
  MODIFY `FlightID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `passenger`
--
ALTER TABLE `passenger`
  MODIFY `PassengerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `PaymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aircraft`
--
ALTER TABLE `aircraft`
  ADD CONSTRAINT `aircraft_ibfk_1` FOREIGN KEY (`AirlineCode`) REFERENCES `airline` (`AirlineCode`);

--
-- Constraints for table `baggage`
--
ALTER TABLE `baggage`
  ADD CONSTRAINT `baggage_ibfk_1` FOREIGN KEY (`BookingID`) REFERENCES `booking` (`BookingID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`FlightID`) REFERENCES `flight` (`FlightID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`PassengerID`) REFERENCES `passenger` (`PassengerID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `flight`
--
ALTER TABLE `flight`
  ADD CONSTRAINT `flight_ibfk_1` FOREIGN KEY (`AirlineCode`) REFERENCES `airline` (`AirlineCode`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `flight_ibfk_2` FOREIGN KEY (`OriginAirportCode`) REFERENCES `airport` (`AirportCode`) ON UPDATE CASCADE,
  ADD CONSTRAINT `flight_ibfk_3` FOREIGN KEY (`DestinationAirportCode`) REFERENCES `airport` (`AirportCode`) ON UPDATE CASCADE;

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`BookingID`) REFERENCES `booking` (`BookingID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
