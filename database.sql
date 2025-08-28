-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 25, 2025 at 02:05 PM
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
-- Database: `meat_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `batchretailer`
--

CREATE TABLE `batchretailer` (
  `BatchID` int(11) NOT NULL,
  `RetailerID` int(11) NOT NULL,
  `Date` date DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `Weight` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `batchretailer`
--

INSERT INTO `batchretailer` (`BatchID`, `RetailerID`, `Date`, `Quantity`, `Weight`) VALUES
(1, 1, '2025-04-20', 50, 600.00),
(2, 2, '2025-04-21', 40, 275.00),
(3, 3, '2025-04-22', 45, 142.50),
(4, 4, '2025-04-23', 30, 212.50),
(5, 5, '2025-04-24', 38, 237.50),
(6, 6, '2025-04-25', 35, 112.50),
(7, 7, '2025-04-26', 20, 70.00),
(8, 8, '2025-04-27', 15, 105.00),
(9, 9, '2025-04-28', 25, 95.00),
(10, 10, '2025-04-29', 23, 125.00);

-- --------------------------------------------------------

--
-- Table structure for table `batchwarehouse`
--

CREATE TABLE `batchwarehouse` (
  `BatchID` int(11) NOT NULL,
  `WarehouseID` int(11) NOT NULL,
  `Date` date DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `Weight` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `batchwarehouse`
--

INSERT INTO `batchwarehouse` (`BatchID`, `WarehouseID`, `Date`, `Quantity`, `Weight`) VALUES
(1, 1, '2025-04-10', 100, 1200.00),
(2, 2, '2025-04-11', 80, 550.00),
(3, 3, '2025-04-12', 90, 285.00),
(4, 4, '2025-04-13', 60, 425.00),
(5, 5, '2025-04-14', 75, 475.00),
(6, 6, '2025-04-15', 70, 225.00),
(7, 7, '2025-04-16', 40, 140.00),
(8, 8, '2025-04-17', 30, 210.00),
(9, 9, '2025-04-18', 50, 190.00),
(10, 10, '2025-04-19', 45, 250.00);

-- --------------------------------------------------------

--
-- Table structure for table `batchwholesaler`
--

CREATE TABLE `batchwholesaler` (
  `BatchID` int(11) NOT NULL,
  `WholesalerID` int(11) NOT NULL,
  `Date` date DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `Weight` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `batchwholesaler`
--

INSERT INTO `batchwholesaler` (`BatchID`, `WholesalerID`, `Date`, `Quantity`, `Weight`) VALUES
(1, 1, '2025-04-15', 100, 1200.00),
(2, 2, '2025-04-16', 80, 550.00),
(3, 3, '2025-04-17', 90, 285.00),
(4, 4, '2025-04-18', 60, 425.00),
(5, 5, '2025-04-19', 75, 475.00),
(6, 6, '2025-04-20', 70, 225.00),
(7, 7, '2025-04-21', 40, 140.00),
(8, 8, '2025-04-22', 30, 210.00),
(9, 9, '2025-04-23', 50, 190.00),
(10, 10, '2025-04-24', 45, 250.00);

-- --------------------------------------------------------

--
-- Table structure for table `coldstorage`
--

CREATE TABLE `coldstorage` (
  `StorageID` int(11) NOT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Capacity` int(11) DEFAULT NULL,
  `CurrentTemperature` decimal(5,2) DEFAULT NULL,
  `CurrentHumidity` decimal(5,2) DEFAULT NULL,
  `MonitoringSystemID` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coldstorage`
--

INSERT INTO `coldstorage` (`StorageID`, `ProductID`, `Name`, `Capacity`, `CurrentTemperature`, `CurrentHumidity`, `MonitoringSystemID`) VALUES
(1, 1, 'Beef Freezer A', 5000, -2.00, 85.00, 'MON-001'),
(2, 2, 'Pork Chiller B', 4000, 1.50, 80.00, 'MON-002'),
(3, 3, 'Poultry Room C', 4500, 2.00, 75.00, 'MON-003'),
(4, 4, 'Lamb Freezer D', 3500, -1.50, 82.00, 'MON-004'),
(5, 5, 'Turkey Chiller E', 3800, 1.00, 78.00, 'MON-005'),
(6, 6, 'Duck Freezer F', 3000, -2.50, 83.00, 'MON-006'),
(7, 7, 'Goat Chiller G', 3200, 0.50, 79.00, 'MON-007'),
(8, 8, 'Veal Freezer H', 2800, -3.00, 84.00, 'MON-008'),
(9, 9, 'Game Room I', 4200, 1.50, 77.00, 'MON-009'),
(10, 10, 'Lamb Freezer J', 3600, -2.00, 81.00, 'MON-010');

-- --------------------------------------------------------

--
-- Table structure for table `farmer`
--

CREATE TABLE `farmer` (
  `FarmerID` int(11) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Size` varchar(50) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `farmer`
--

INSERT INTO `farmer` (`FarmerID`, `Name`, `Size`, `Address`) VALUES
(1, 'Green Pastures Farm', 'Large', '123 Farm Road, Ruralville'),
(2, 'Sunny Acres Ranch', 'Medium', '456 Ranch Lane, Countryside'),
(3, 'Happy Cow Farm', 'Small', '789 Grazing Street, Pasturetown'),
(4, 'Organic Beef Co.', 'Large', '321 Grassland Avenue, Farmington'),
(5, 'Free Range Poultry', 'Medium', '654 Cluck Road, Henville'),
(6, 'Heritage Pork Farm', 'Small', '987 Oink Boulevard, Swineford'),
(7, 'Golden Fleece Sheep', 'Medium', '147 Wool Lane, Sheeptown'),
(8, 'Grass Fed Beef Co.', 'Large', '258 Meadow Street, Grassville'),
(9, 'Premium Poultry Farm', 'Medium', '369 Egg Road, Chickenton'),
(10, 'Natural Lamb Ranch', 'Small', '741 Lamb Street, Muttonville');

-- --------------------------------------------------------

--
-- Table structure for table `livestock`
--

CREATE TABLE `livestock` (
  `LivestockID` int(11) NOT NULL,
  `FarmerID` int(11) DEFAULT NULL,
  `AnimalType` varchar(50) DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `Date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `livestock`
--

INSERT INTO `livestock` (`LivestockID`, `FarmerID`, `AnimalType`, `Quantity`, `Date`) VALUES
(1, 1, 'Cattle', 120, '2025-01-15'),
(2, 2, 'Pigs', 80, '2025-02-20'),
(3, 3, 'Chickens', 500, '2025-03-10'),
(4, 4, 'Sheep', 60, '2025-01-25'),
(5, 5, 'Turkeys', 200, '2025-02-15'),
(6, 6, 'Ducks', 150, '2025-03-05'),
(7, 7, 'Goats', 40, '2025-01-30'),
(8, 8, 'Veal Calves', 30, '2025-02-10'),
(9, 9, 'Geese', 100, '2025-03-20'),
(10, 10, 'Lambs', 70, '2025-01-10');

-- --------------------------------------------------------

--
-- Table structure for table `meatproduct`
--

CREATE TABLE `meatproduct` (
  `ProductID` int(11) NOT NULL,
  `BatchID` int(11) DEFAULT NULL,
  `CutType` varchar(50) DEFAULT NULL,
  `StorageRequirements` varchar(255) DEFAULT NULL,
  `ShelfLife` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meatproduct`
--

INSERT INTO `meatproduct` (`ProductID`, `BatchID`, `CutType`, `StorageRequirements`, `ShelfLife`) VALUES
(1, 1, 'Ribeye Steak', 'Refrigerated at 34°F', 14),
(2, 2, 'Pork Chops', 'Refrigerated at 36°F', 10),
(3, 3, 'Whole Chicken', 'Refrigerated at 38°F', 7),
(4, 4, 'Lamb Leg', 'Refrigerated at 34°F', 12),
(5, 5, 'Turkey Breast', 'Refrigerated at 36°F', 8),
(6, 6, 'Duck Breast', 'Refrigerated at 34°F', 9),
(7, 7, 'Goat Shoulder', 'Refrigerated at 36°F', 11),
(8, 8, 'Veal Cutlets', 'Refrigerated at 34°F', 13),
(9, 9, 'Goose Legs', 'Refrigerated at 36°F', 10),
(10, 10, 'Lamb Chops', 'Refrigerated at 34°F', 12);

-- --------------------------------------------------------

--
-- Table structure for table `offcuts`
--

CREATE TABLE `offcuts` (
  `UniqueID` int(11) NOT NULL,
  `BatchID` int(11) DEFAULT NULL,
  `Type` varchar(50) DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `Weight` decimal(10,2) DEFAULT NULL,
  `Date` date DEFAULT NULL,
  `DispositionMethod` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offcuts`
--

INSERT INTO `offcuts` (`UniqueID`, `BatchID`, `Type`, `Quantity`, `Weight`, `Date`, `DispositionMethod`) VALUES
(1, 1, 'Fat Trimmings', 15, 75.00, '2025-04-01', 'Rendered'),
(2, 2, 'Bones', 20, 60.00, '2025-04-02', 'Stock Production'),
(3, 3, 'Giblets', 40, 20.00, '2025-04-03', 'Pet Food'),
(4, 4, 'Fat', 10, 30.00, '2025-04-04', 'Rendered'),
(5, 5, 'Neck', 15, 45.00, '2025-04-05', 'Stock Production'),
(6, 6, 'Wings', 30, 24.00, '2025-04-06', 'Pet Food'),
(7, 7, 'Bones', 12, 36.00, '2025-04-07', 'Stock Production'),
(8, 8, 'Fat', 8, 24.00, '2025-04-08', 'Rendered'),
(9, 9, 'Giblets', 25, 12.50, '2025-04-09', 'Pet Food'),
(10, 10, 'Trim', 18, 27.00, '2025-04-10', 'Ground Meat');

-- --------------------------------------------------------

--
-- Table structure for table `packagedbatch`
--

CREATE TABLE `packagedbatch` (
  `BatchID` int(11) NOT NULL,
  `StorageID` int(11) DEFAULT NULL,
  `FactoryID` int(11) DEFAULT NULL,
  `PackagingDate` date DEFAULT NULL,
  `ExpiryDate` date DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `Weight` decimal(10,2) DEFAULT NULL,
  `Barcode` varchar(50) DEFAULT NULL,
  `PackagingDetails` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packagedbatch`
--

INSERT INTO `packagedbatch` (`BatchID`, `StorageID`, `FactoryID`, `PackagingDate`, `ExpiryDate`, `Quantity`, `Weight`, `Barcode`, `PackagingDetails`) VALUES
(1, 1, 1, '2025-04-05', '2025-04-19', 200, 2400.00, 'BC-001', 'Vacuum sealed, 12oz portions'),
(2, 2, 2, '2025-04-06', '2025-04-20', 160, 1100.00, 'BC-002', 'Tray pack, 8oz chops'),
(3, 3, 3, '2025-04-07', '2025-04-21', 180, 570.00, 'BC-003', 'Whole bird, shrink wrapped'),
(4, 4, 4, '2025-04-08', '2025-04-22', 120, 850.00, 'BC-004', 'Cryovac, bone-in leg'),
(5, 5, 5, '2025-04-09', '2025-04-23', 150, 950.00, 'BC-005', 'Skinless, boneless breast'),
(6, 6, 6, '2025-04-10', '2025-04-24', 140, 450.00, 'BC-006', 'Vacuum sealed, 6oz portions'),
(7, 7, 7, '2025-04-11', '2025-04-25', 80, 280.00, 'BC-007', 'Cubed for stew, 1lb packs'),
(8, 8, 8, '2025-04-12', '2025-04-26', 60, 420.00, 'BC-008', 'Pounded cutlets, 4oz each'),
(9, 9, 9, '2025-04-13', '2025-04-27', 100, 380.00, 'BC-009', 'Leg quarters, tray pack'),
(10, 10, 10, '2025-04-14', '2025-04-28', 90, 500.00, 'BC-010', 'Frenched chops, 2 per pack');

-- --------------------------------------------------------

--
-- Table structure for table `packagingfactory`
--

CREATE TABLE `packagingfactory` (
  `FactoryID` int(11) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Certification` varchar(100) DEFAULT NULL,
  `Capacity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packagingfactory`
--

INSERT INTO `packagingfactory` (`FactoryID`, `Name`, `Address`, `Certification`, `Capacity`) VALUES
(1, 'Prime Pack Meats', '100 Packaging Parkway, Processville', 'USDA', 10000),
(2, 'Fresh Cut Packaging', '200 Seal Street, Wrapburg', 'HACCP', 8000),
(3, 'Quality Meat Packers', '300 Vacuum Road, Sealton', 'USDA', 12000),
(4, 'Premium Protein Pack', '400 Cryo Lane, Freezeford', 'HACCP', 9000),
(5, 'Butcher\'s Best Pack', '500 Seal Avenue, Packville', 'USDA', 7500),
(6, 'Farm Fresh Packaging', '600 Wrap Road, Sealburg', 'HACCP', 8500),
(7, 'Meat Masters Pack', '700 Vacuum Street, Packtown', 'USDA', 11000),
(8, 'Tender Cut Packers', '800 Cryo Parkway, Freezeton', 'HACCP', 9500),
(9, 'Prime Cut Packaging', '900 Seal Lane, Wrapville', 'USDA', 10500),
(10, 'Quality Seal Meats', '010 Vacuum Avenue, Packford', 'HACCP', 7000);

-- --------------------------------------------------------

--
-- Table structure for table `processedbatch`
--

CREATE TABLE `processedbatch` (
  `BatchID` int(11) NOT NULL,
  `LivestockID` int(11) DEFAULT NULL,
  `HouseID` int(11) DEFAULT NULL,
  `ProcessingDate` date DEFAULT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `Weight` decimal(10,2) DEFAULT NULL,
  `ExpiryDate` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `processedbatch`
--

INSERT INTO `processedbatch` (`BatchID`, `LivestockID`, `HouseID`, `ProcessingDate`, `Quantity`, `Weight`, `ExpiryDate`) VALUES
(1, 1, 1, '2025-04-01', 50, 2500.00, '2025-04-15'),
(2, 2, 2, '2025-04-02', 40, 1200.00, '2025-04-16'),
(3, 3, 3, '2025-04-03', 200, 600.00, '2025-04-17'),
(4, 4, 4, '2025-04-04', 30, 900.00, '2025-04-18'),
(5, 5, 5, '2025-04-05', 80, 1000.00, '2025-04-19'),
(6, 6, 6, '2025-04-06', 60, 480.00, '2025-04-20'),
(7, 7, 7, '2025-04-07', 20, 300.00, '2025-04-21'),
(8, 8, 8, '2025-04-08', 15, 450.00, '2025-04-22'),
(9, 9, 9, '2025-04-09', 50, 400.00, '2025-04-23'),
(10, 10, 10, '2025-04-10', 35, 525.00, '2025-04-24');

-- --------------------------------------------------------

--
-- Table structure for table `retailer`
--

CREATE TABLE `retailer` (
  `RetailerID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `retailer`
--

INSERT INTO `retailer` (`RetailerID`) VALUES
(1),
(2),
(3),
(4),
(5),
(6),
(7),
(8),
(9),
(10);

-- --------------------------------------------------------

--
-- Table structure for table `retailerstore`
--

CREATE TABLE `retailerstore` (
  `StoreID` int(11) NOT NULL,
  `RetailerID` int(11) DEFAULT NULL,
  `Location` varchar(100) DEFAULT NULL,
  `Rating` decimal(3,2) DEFAULT NULL,
  `Contact` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `retailerstore`
--

INSERT INTO `retailerstore` (`StoreID`, `RetailerID`, `Location`, `Rating`, `Contact`) VALUES
(1, 1, '100 Market Street, Groceryville', 4.50, 'store1@retailer.com'),
(2, 2, '200 Supermarket Lane, Foodtown', 4.20, 'store2@retailer.com'),
(3, 3, '300 Grocery Avenue, Marketburg', 4.70, 'store3@retailer.com'),
(4, 4, '400 Food Court, Shopville', 4.30, 'store4@retailer.com'),
(5, 5, '500 Market Plaza, Groceryburg', 4.60, 'store5@retailer.com'),
(6, 6, '600 Super Center Road, Foodford', 4.40, 'store6@retailer.com'),
(7, 7, '700 Grocery Parkway, Marketford', 4.80, 'store7@retailer.com'),
(8, 8, '800 Food Hall, Shopton', 4.25, 'store8@retailer.com'),
(9, 9, '900 Market Mall, Groceryton', 4.65, 'store9@retailer.com'),
(10, 10, '010 Super Store Lane, Foodville', 4.35, 'store10@retailer.com');

-- --------------------------------------------------------

--
-- Table structure for table `sensorreadings`
--

CREATE TABLE `sensorreadings` (
  `ReadingID` int(11) NOT NULL,
  `StorageID` int(11) DEFAULT NULL,
  `LocationType` varchar(50) DEFAULT NULL,
  `Temperature` decimal(5,2) DEFAULT NULL,
  `Humidity` decimal(5,2) DEFAULT NULL,
  `OxygenLevel` decimal(5,2) DEFAULT NULL,
  `ReadingDateTime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sensorreadings`
--

INSERT INTO `sensorreadings` (`ReadingID`, `StorageID`, `LocationType`, `Temperature`, `Humidity`, `OxygenLevel`, `ReadingDateTime`) VALUES
(1, 1, 'Freezer', -2.10, 84.50, 20.50, '2025-04-15 08:00:00'),
(2, 2, 'Chiller', 1.60, 79.80, 20.80, '2025-04-15 08:00:00'),
(3, 3, 'Room', 2.10, 74.70, 21.00, '2025-04-15 08:00:00'),
(4, 4, 'Freezer', -1.60, 81.50, 20.60, '2025-04-15 08:00:00'),
(5, 5, 'Chiller', 1.10, 77.60, 20.90, '2025-04-15 08:00:00'),
(6, 6, 'Freezer', -2.60, 82.40, 20.40, '2025-04-15 08:00:00'),
(7, 7, 'Chiller', 0.60, 78.50, 20.70, '2025-04-15 08:00:00'),
(8, 8, 'Freezer', -3.10, 83.20, 20.30, '2025-04-15 08:00:00'),
(9, 9, 'Room', 1.60, 76.30, 21.10, '2025-04-15 08:00:00'),
(10, 10, 'Freezer', -2.10, 80.80, 20.50, '2025-04-15 08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `slaughterhouse`
--

CREATE TABLE `slaughterhouse` (
  `HouseID` int(11) NOT NULL,
  `Capacity` int(11) DEFAULT NULL,
  `Address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `slaughterhouse`
--

INSERT INTO `slaughterhouse` (`HouseID`, `Capacity`, `Address`) VALUES
(1, 500, '101 Processing Avenue, Meatville'),
(2, 300, '202 Butcher Road, Chopstown'),
(3, 400, '303 Abattoir Lane, Slaughterton'),
(4, 250, '404 Meatpacking Street, Processburg'),
(5, 350, '505 Cutting Boulevard, Carveville'),
(6, 200, '606 Knife Road, Cleaverton'),
(7, 450, '707 Processing Lane, Butcherville'),
(8, 150, '808 Slaughter Street, Meatford'),
(9, 500, '909 Packing Avenue, Processville'),
(10, 300, '010 Cutting Road, Chopville');

-- --------------------------------------------------------

--
-- Table structure for table `warehouse`
--

CREATE TABLE `warehouse` (
  `WarehouseID` int(11) NOT NULL,
  `Address` varchar(255) DEFAULT NULL,
  `Capacity` int(11) DEFAULT NULL,
  `TemperatureZones` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warehouse`
--

INSERT INTO `warehouse` (`WarehouseID`, `Address`, `Capacity`, `TemperatureZones`) VALUES
(1, '100 Distribution Drive, Storageville', 50000, 'Frozen, Chilled'),
(2, '200 Logistics Lane, Depotville', 40000, 'Chilled, Ambient'),
(3, '300 Storage Street, Warehouse City', 45000, 'Frozen, Chilled, Dry'),
(4, '400 Depot Road, Storageburg', 35000, 'Chilled, Ambient'),
(5, '500 Fulfillment Avenue, Logisticsville', 38000, 'Frozen, Chilled'),
(6, '600 Cold Storage Lane, Freezeton', 30000, 'Frozen'),
(7, '700 Refrigerated Road, Chillville', 32000, 'Chilled'),
(8, '800 Distribution Parkway, Depotford', 28000, 'Frozen, Chilled'),
(9, '900 Storage Boulevard, Warehouseton', 42000, 'Frozen, Chilled, Dry'),
(10, '010 Logistics Street, Distributionburg', 36000, 'Chilled, Ambient');

-- --------------------------------------------------------

--
-- Table structure for table `wholesaler`
--

CREATE TABLE `wholesaler` (
  `WholesalerID` int(11) NOT NULL,
  `Name` varchar(100) DEFAULT NULL,
  `DistributionRegion` varchar(100) DEFAULT NULL,
  `ContactInfo` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wholesaler`
--

INSERT INTO `wholesaler` (`WholesalerID`, `Name`, `DistributionRegion`, `ContactInfo`) VALUES
(1, 'Meat Distributors Inc.', 'Northeast', 'contact@meatdist.com'),
(2, 'Premium Protein Wholesale', 'Midwest', 'sales@premiumprotein.com'),
(3, 'Quality Cuts Distributing', 'South', 'info@qualitycuts.com'),
(4, 'Farm Fresh Wholesale', 'West', 'orders@farmfresh.com'),
(5, 'Butcher\'s Choice Supply', 'Northwest', 'contact@butcherschoice.com'),
(6, 'Prime Meat Distributors', 'Southeast', 'sales@primemeat.com'),
(7, 'Gourmet Protein Supply', 'Southwest', 'info@gourmetprotein.com'),
(8, 'Heritage Meat Wholesale', 'Mid-Atlantic', 'orders@heritage.com'),
(9, 'Artisan Cuts Distributing', 'Great Lakes', 'contact@artisancuts.com'),
(10, 'Organic Protein Supply', 'National', 'sales@organicprotein.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `batchretailer`
--
ALTER TABLE `batchretailer`
  ADD PRIMARY KEY (`BatchID`,`RetailerID`),
  ADD KEY `RetailerID` (`RetailerID`);

--
-- Indexes for table `batchwarehouse`
--
ALTER TABLE `batchwarehouse`
  ADD PRIMARY KEY (`BatchID`,`WarehouseID`),
  ADD KEY `WarehouseID` (`WarehouseID`);

--
-- Indexes for table `batchwholesaler`
--
ALTER TABLE `batchwholesaler`
  ADD PRIMARY KEY (`BatchID`,`WholesalerID`),
  ADD KEY `WholesalerID` (`WholesalerID`);

--
-- Indexes for table `coldstorage`
--
ALTER TABLE `coldstorage`
  ADD PRIMARY KEY (`StorageID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- Indexes for table `farmer`
--
ALTER TABLE `farmer`
  ADD PRIMARY KEY (`FarmerID`);

--
-- Indexes for table `livestock`
--
ALTER TABLE `livestock`
  ADD PRIMARY KEY (`LivestockID`),
  ADD KEY `FarmerID` (`FarmerID`);

--
-- Indexes for table `meatproduct`
--
ALTER TABLE `meatproduct`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `BatchID` (`BatchID`);

--
-- Indexes for table `offcuts`
--
ALTER TABLE `offcuts`
  ADD PRIMARY KEY (`UniqueID`),
  ADD KEY `BatchID` (`BatchID`);

--
-- Indexes for table `packagedbatch`
--
ALTER TABLE `packagedbatch`
  ADD PRIMARY KEY (`BatchID`),
  ADD KEY `StorageID` (`StorageID`),
  ADD KEY `FactoryID` (`FactoryID`);

--
-- Indexes for table `packagingfactory`
--
ALTER TABLE `packagingfactory`
  ADD PRIMARY KEY (`FactoryID`);

--
-- Indexes for table `processedbatch`
--
ALTER TABLE `processedbatch`
  ADD PRIMARY KEY (`BatchID`),
  ADD KEY `LivestockID` (`LivestockID`),
  ADD KEY `HouseID` (`HouseID`);

--
-- Indexes for table `retailer`
--
ALTER TABLE `retailer`
  ADD PRIMARY KEY (`RetailerID`);

--
-- Indexes for table `retailerstore`
--
ALTER TABLE `retailerstore`
  ADD PRIMARY KEY (`StoreID`),
  ADD KEY `RetailerID` (`RetailerID`);

--
-- Indexes for table `sensorreadings`
--
ALTER TABLE `sensorreadings`
  ADD PRIMARY KEY (`ReadingID`),
  ADD KEY `StorageID` (`StorageID`);

--
-- Indexes for table `slaughterhouse`
--
ALTER TABLE `slaughterhouse`
  ADD PRIMARY KEY (`HouseID`);

--
-- Indexes for table `warehouse`
--
ALTER TABLE `warehouse`
  ADD PRIMARY KEY (`WarehouseID`);

--
-- Indexes for table `wholesaler`
--
ALTER TABLE `wholesaler`
  ADD PRIMARY KEY (`WholesalerID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `batchretailer`
--
ALTER TABLE `batchretailer`
  ADD CONSTRAINT `batchretailer_ibfk_1` FOREIGN KEY (`BatchID`) REFERENCES `packagedbatch` (`BatchID`),
  ADD CONSTRAINT `batchretailer_ibfk_2` FOREIGN KEY (`RetailerID`) REFERENCES `retailer` (`RetailerID`);

--
-- Constraints for table `batchwarehouse`
--
ALTER TABLE `batchwarehouse`
  ADD CONSTRAINT `batchwarehouse_ibfk_1` FOREIGN KEY (`BatchID`) REFERENCES `packagedbatch` (`BatchID`),
  ADD CONSTRAINT `batchwarehouse_ibfk_2` FOREIGN KEY (`WarehouseID`) REFERENCES `warehouse` (`WarehouseID`);

--
-- Constraints for table `batchwholesaler`
--
ALTER TABLE `batchwholesaler`
  ADD CONSTRAINT `batchwholesaler_ibfk_1` FOREIGN KEY (`BatchID`) REFERENCES `packagedbatch` (`BatchID`),
  ADD CONSTRAINT `batchwholesaler_ibfk_2` FOREIGN KEY (`WholesalerID`) REFERENCES `wholesaler` (`WholesalerID`);

--
-- Constraints for table `coldstorage`
--
ALTER TABLE `coldstorage`
  ADD CONSTRAINT `coldstorage_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `meatproduct` (`ProductID`);

--
-- Constraints for table `livestock`
--
ALTER TABLE `livestock`
  ADD CONSTRAINT `livestock_ibfk_1` FOREIGN KEY (`FarmerID`) REFERENCES `farmer` (`FarmerID`);

--
-- Constraints for table `meatproduct`
--
ALTER TABLE `meatproduct`
  ADD CONSTRAINT `meatproduct_ibfk_1` FOREIGN KEY (`BatchID`) REFERENCES `processedbatch` (`BatchID`);

--
-- Constraints for table `offcuts`
--
ALTER TABLE `offcuts`
  ADD CONSTRAINT `offcuts_ibfk_1` FOREIGN KEY (`BatchID`) REFERENCES `processedbatch` (`BatchID`);

--
-- Constraints for table `packagedbatch`
--
ALTER TABLE `packagedbatch`
  ADD CONSTRAINT `packagedbatch_ibfk_1` FOREIGN KEY (`StorageID`) REFERENCES `coldstorage` (`StorageID`),
  ADD CONSTRAINT `packagedbatch_ibfk_2` FOREIGN KEY (`FactoryID`) REFERENCES `packagingfactory` (`FactoryID`);

--
-- Constraints for table `processedbatch`
--
ALTER TABLE `processedbatch`
  ADD CONSTRAINT `processedbatch_ibfk_1` FOREIGN KEY (`LivestockID`) REFERENCES `livestock` (`LivestockID`),
  ADD CONSTRAINT `processedbatch_ibfk_2` FOREIGN KEY (`HouseID`) REFERENCES `slaughterhouse` (`HouseID`);

--
-- Constraints for table `retailerstore`
--
ALTER TABLE `retailerstore`
  ADD CONSTRAINT `retailerstore_ibfk_1` FOREIGN KEY (`RetailerID`) REFERENCES `retailer` (`RetailerID`);

--
-- Constraints for table `sensorreadings`
--
ALTER TABLE `sensorreadings`
  ADD CONSTRAINT `sensorreadings_ibfk_1` FOREIGN KEY (`StorageID`) REFERENCES `coldstorage` (`StorageID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
