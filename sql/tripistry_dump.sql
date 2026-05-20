
-- MariaDB dump 10.19-12.2.2-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: tripistry
-- ------------------------------------------------------
-- Server version	12.2.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `accommodation`
--

DROP TABLE IF EXISTS `accommodation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `accommodation` (
  `accommodation_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `no_bedrooms` int(11) DEFAULT NULL,
  `no_bathrooms` int(11) DEFAULT NULL,
  `price_per_night` decimal(10,2) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  PRIMARY KEY (`accommodation_id`),
  KEY `destination_id` (`destination_id`),
  KEY `agency_id` (`agency_id`),
  CONSTRAINT `fk_accommodation_destination` FOREIGN KEY (`destination_id`) REFERENCES `destination` (`destination_id`),
  CONSTRAINT `fk_accommodation_agency` FOREIGN KEY (`agency_id`) REFERENCES `travel_agency` (`agency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accommodation`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `accommodation` WRITE;
/*!40000 ALTER TABLE `accommodation` DISABLE KEYS */;
INSERT INTO `accommodation` VALUES
(1,'The V&A Waterfront Hotel','1 Dock Road, Cape Town',250,250,2500.00,1,6),
(2,'Camps Bay Retreat','32 Camps Bay Drive, Cape Town',15,15,3800.00,1,6),
(3,'Sabi Sands Game Lodge','Sabi Sands Reserve, Kruger',20,20,5500.00,3,7),
(4,'Four Seasons Hotel George V','31 Avenue George V, Paris',80,80,8500.00,4,8),
(5,'Mandarin Oriental','48 Oriental Avenue, Bangkok',120,120,4200.00,5,9),
(6,'Fontainebleau Miami Beach','4441 Collins Ave, Miami Beach',150,150,4800.00,6,10),
(7,'The Ritz London','150 Piccadilly, London',100,100,7500.00,7,8),
(8,'Park Hyatt Tokyo','3-7-1-2 Nishi Shinjuku, Tokyo',90,90,6800.00,8,9);
/*!40000 ALTER TABLE `accommodation` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `activity`
--

DROP TABLE IF EXISTS `activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity` (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `destination_id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `activity_type` varchar(30) NOT NULL,
  PRIMARY KEY (`activity_id`),
  KEY `destination_id` (`destination_id`),
  KEY `agency_id` (`agency_id`),
  CONSTRAINT `fk_activity_destination` FOREIGN KEY (`destination_id`) REFERENCES `destination` (`destination_id`),
  CONSTRAINT `fk_activity_agency` FOREIGN KEY (`agency_id`) REFERENCES `travel_agency` (`agency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `activity` WRITE;
/*!40000 ALTER TABLE `activity` DISABLE KEYS */;
INSERT INTO `activity` VALUES
(1,'Table Mountain Cableway','Tafelberg Road, Table Mountain','Cape Town',380.00,'Cable car to the top of Table Mountain','2026-06-02 09:00:00','2026-06-02 17:00:00',1,6,'Natural Landmark'),
(2,'Robben Island Tour','V&A Waterfront','Cape Town',400.00,'Historical tour of Robben Island','2026-06-03 10:00:00','2026-06-03 14:00:00',1,6,'Historical Site'),
(3,'Kruger Safari Drive','Skukuza Camp','Kruger',1850.00,'Morning safari game drive','2026-07-15 05:30:00','2026-07-15 11:30:00',3,7,'Wildlife Safari'),
(4,'Eiffel Tower Visit','Champ de Mars','Paris',320.00,'Access to 2nd floor of Eiffel Tower','2026-06-05 10:00:00','2026-06-05 20:00:00',4,8,'Landmark'),
(5,'Grand Palace Tour','Na Phra Lan Rd','Bangkok',500.00,'Visit to the Grand Palace','2026-07-12 08:30:00','2026-07-12 15:30:00',5,9,'Palace'),
(6,'Everglades Airboat Tour','Miami Everglades','Miami',650.00,'Airboat ride through Everglades','2026-08-08 10:00:00','2026-08-08 13:00:00',6,10,'Nature Tour'),
(7,'Le Bernadin Fine Dining','155 W 51st St','New York',2500.00,'Three-Michelin-star restaurant','2026-08-10 19:00:00','2026-08-10 22:00:00',6,10,'Restaurant'),
(8,'Gaggan Anand','68 Sukhumvit 31','Bangkok',1800.00,'Progressive Indian cuisine','2026-07-13 18:30:00','2026-07-13 21:30:00',5,9,'Restaurant'),
(9,'The Test Kitchen','The Old Biscuit Mill','Cape Town',1450.00,'Fine dining experience','2026-06-04 19:00:00','2026-06-04 22:00:00',1,6,'Restaurant');
/*!40000 ALTER TABLE `activity` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `agency_staff`
--

DROP TABLE IF EXISTS `agency_staff`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `agency_staff` (
  `staff_id` int(11) NOT NULL AUTO_INCREMENT,
  `agency_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `role` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`staff_id`),
  KEY `agency_id` (`agency_id`),
  CONSTRAINT `fk_staff_agency` FOREIGN KEY (`agency_id`) REFERENCES `travel_agency` (`agency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agency_staff`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `agency_staff` WRITE;
/*!40000 ALTER TABLE `agency_staff` DISABLE KEYS */;
INSERT INTO `agency_staff` VALUES
(1,6,'Peter','Van der Merwe','+27 82 111 2222','peter@wanderlust.co.za','Travel Consultant'),
(2,6,'Lisa','Smith','+27 83 333 4444','lisa@wanderlust.co.za','Operations Manager'),
(3,7,'Jaco','Kruger','+27 82 555 6666','jaco@safariexperts.co.za','Safari Guide'),
(4,8,'Sophie','Martin','+33 6 12 34 56 78','sophie@eurotours.com','Tour Coordinator'),
(5,9,'Somchai','Thailand','+66 81 234 5678','somchai@asiadiscovery.com','Local Guide'),
(6,10,'Maria','Garcia','+1 305 555 7890','maria@beachholidays.com','Customer Service');
/*!40000 ALTER TABLE `agency_staff` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `booking`
--

DROP TABLE IF EXISTS `booking`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `booking` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `traveller_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `group_trip_id` int(11) DEFAULT NULL,
  `booking_date` timestamp NULL DEFAULT current_timestamp(),
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  PRIMARY KEY (`booking_id`),
  KEY `traveller_id` (`traveller_id`),
  KEY `package_id` (`package_id`),
  KEY `group_trip_id` (`group_trip_id`),
  CONSTRAINT `fk_booking_traveller` FOREIGN KEY (`traveller_id`) REFERENCES `traveller` (`traveller_id`),
  CONSTRAINT `fk_booking_package` FOREIGN KEY (`package_id`) REFERENCES `travel_package` (`package_id`),
  CONSTRAINT `fk_booking_group` FOREIGN KEY (`group_trip_id`) REFERENCES `group_trip` (`group_trip_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `booking`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `booking` WRITE;
/*!40000 ALTER TABLE `booking` DISABLE KEYS */;
INSERT INTO `booking` VALUES
(1,1,1,NULL,'2026-05-12 16:15:20',8500.00,'confirmed'),
(2,2,3,1,'2026-05-12 16:15:20',18500.00,'confirmed'),
(3,3,4,2,'2026-05-12 16:15:20',15000.00,'confirmed'),
(4,4,5,3,'2026-05-12 16:15:20',12000.00,'pending'),
(5,5,1,NULL,'2026-05-12 16:15:20',8500.00,'cancelled'),
(6,1,4,NULL,'2026-05-12 16:15:20',15000.00,'pending');
/*!40000 ALTER TABLE `booking` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `destination`
--

DROP TABLE IF EXISTS `destination`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `destination` (
  `destination_id` int(11) NOT NULL AUTO_INCREMENT,
  `city_name` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `popular_season` varchar(100) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`destination_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `destination`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `destination` WRITE;
/*!40000 ALTER TABLE `destination` DISABLE KEYS */;
INSERT INTO `destination` VALUES
(1,'Cape Town','South Africa','Mother City with Table Mountain and beautiful beaches','Oct-Mar','/images/capetown.jpg'),
(2,'Johannesburg','South Africa','City of Gold, vibrant culture and history','Sep-Nov','/images/johannesburg.jpg'),
(3,'Kruger National Park','South Africa','Famous safari destination with Big Five','May-Sep','/images/kruger.jpg'),
(4,'Paris','France','City of Love, Eiffel Tower and gourmet cuisine','Apr-Oct','/images/paris.jpg'),
(5,'Bangkok','Thailand','Street food paradise with ornate temples','Nov-Feb','/images/bangkok.jpg'),
(6,'Miami','USA','Sunny beaches and vibrant nightlife','Dec-Apr','/images/miami.jpg'),
(7,'London','United Kingdom','Historic landmarks and royal palaces','May-Sep','/images/london.jpg'),
(8,'Tokyo','Japan','Futuristic city with traditional temples','Mar-May, Sep-Nov','/images/tokyo.jpg');
/*!40000 ALTER TABLE `destination` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `flight`
--

DROP TABLE IF EXISTS `flight`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `flight` (
  `flight_id` int(11) NOT NULL AUTO_INCREMENT,
  `flight_number` varchar(20) NOT NULL,
  `airline` varchar(100) NOT NULL,
  `origin_destination_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `departure_time` datetime NOT NULL,
  `arrival_time` datetime NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `agency_id` int(11) NOT NULL,
  PRIMARY KEY (`flight_id`),
  KEY `origin_destination_id` (`origin_destination_id`),
  KEY `destination_id` (`destination_id`),
  KEY `agency_id` (`agency_id`),
  CONSTRAINT `fk_flight_origin` FOREIGN KEY (`origin_destination_id`) REFERENCES `destination` (`destination_id`),
  CONSTRAINT `fk_flight_destination` FOREIGN KEY (`destination_id`) REFERENCES `destination` (`destination_id`),
  CONSTRAINT `fk_flight_agency` FOREIGN KEY (`agency_id`) REFERENCES `travel_agency` (`agency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flight`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `flight` WRITE;
/*!40000 ALTER TABLE `flight` DISABLE KEYS */;
INSERT INTO `flight` VALUES
(1,'SA123','South African Airways',1,4,'2026-06-01 20:00:00','2026-06-02 08:30:00',8500.00,6),
(2,'SA456','South African Airways',4,1,'2026-06-10 21:00:00','2026-06-11 07:45:00',8500.00,6),
(3,'EK789','Emirates',1,5,'2026-07-01 13:00:00','2026-07-02 06:30:00',12000.00,9),
(4,'BA234','British Airways',7,4,'2026-05-15 10:00:00','2026-05-15 13:30:00',3500.00,8),
(5,'AA567','American Airlines',6,7,'2026-08-01 18:00:00','2026-08-02 09:00:00',9500.00,10),
(6,'JL890','Japan Airlines',5,8,'2026-09-10 23:00:00','2026-09-11 09:00:00',15000.00,9),
(7,'FA101','FlySafair',1,2,'2026-05-20 08:00:00','2026-05-20 09:30:00',850.00,6),
(8,'FA102','FlySafair',2,1,'2026-05-25 17:00:00','2026-05-25 18:30:00',850.00,6);
/*!40000 ALTER TABLE `flight` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `group_trip`
--

DROP TABLE IF EXISTS `group_trip`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_trip` (
  `group_trip_id` int(11) NOT NULL AUTO_INCREMENT,
  `package_id` int(11) NOT NULL,
  `agency_id` int(11) NOT NULL,
  `max_members` int(11) NOT NULL,
  `current_members` int(11) DEFAULT 1,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('open','full','cancelled','completed') DEFAULT 'open',
  PRIMARY KEY (`group_trip_id`),
  KEY `package_id` (`package_id`),
  KEY `agency_id` (`agency_id`),
  CONSTRAINT `fk_grouptrip_package` FOREIGN KEY (`package_id`) REFERENCES `travel_package` (`package_id`),
  CONSTRAINT `fk_grouptrip_agency` FOREIGN KEY (`agency_id`) REFERENCES `travel_agency` (`agency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_trip`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `group_trip` WRITE;
/*!40000 ALTER TABLE `group_trip` DISABLE KEYS */;
INSERT INTO `group_trip` VALUES
(1,3,7,12,5,'2026-07-15','2026-07-19','open'),
(2,4,8,8,8,'2026-06-05','2026-06-10','full'),
(3,5,9,15,3,'2026-07-12','2026-07-16','open');
/*!40000 ALTER TABLE `group_trip` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `package_component`
--

DROP TABLE IF EXISTS `package_component`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `package_component` (
  `package_component_id` int(11) NOT NULL AUTO_INCREMENT,
  `package_id` int(11) NOT NULL,
  `component_type` enum('flight','accommodation','transport','activity') NOT NULL,
  `component_id` int(11) NOT NULL,
  PRIMARY KEY (`package_component_id`),
  KEY `package_id` (`package_id`),
  CONSTRAINT `fk_pkgcomponent_package` FOREIGN KEY (`package_id`) REFERENCES `travel_package` (`package_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `package_component`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `package_component` WRITE;
/*!40000 ALTER TABLE `package_component` DISABLE KEYS */;
INSERT INTO `package_component` VALUES
(1,1,'flight',1),
(2,1,'accommodation',1),
(3,1,'activity',1),
(4,1,'activity',9),
(5,3,'flight',7),
(6,3,'accommodation',3),
(7,3,'transport',2),
(8,3,'activity',3),
(9,4,'flight',4),
(10,4,'accommodation',4),
(11,4,'transport',3),
(12,4,'activity',4),
(13,5,'flight',3),
(14,5,'accommodation',5),
(15,5,'transport',5),
(16,5,'activity',5),
(17,5,'activity',8);
/*!40000 ALTER TABLE `package_component` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `receipt`
--

DROP TABLE IF EXISTS `receipt`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `receipt` (
  `receipt_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` timestamp NULL DEFAULT current_timestamp(),
  `payment_method` enum('credit_card','debit_card','paypal','bank_transfer') NOT NULL,
  `receipt_number` varchar(100) NOT NULL,
  PRIMARY KEY (`receipt_id`),
  UNIQUE KEY `receipt_number` (`receipt_number`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `fk_receipt_booking` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`booking_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `receipt`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `receipt` WRITE;
/*!40000 ALTER TABLE `receipt` DISABLE KEYS */;
INSERT INTO `receipt` VALUES
(1,1,8500.00,'2026-05-12 16:15:29','credit_card','RCP-2026-00001'),
(2,2,18500.00,'2026-05-12 16:15:29','debit_card','RCP-2026-00002'),
(3,3,15000.00,'2026-05-12 16:15:29','paypal','RCP-2026-00003'),
(4,4,12000.00,'2026-05-12 16:15:29','credit_card','RCP-2026-00004'),
(5,6,15000.00,'2026-05-12 16:15:29','bank_transfer','RCP-2026-00006');
/*!40000 ALTER TABLE `receipt` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `restaurant`
--
DROP TABLE IF EXISTS `restaurant`;
--
-- Table structure for table `review`
--

DROP TABLE IF EXISTS `review`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `review` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `traveller_id` int(11) NOT NULL,
  `agency_id` int(11) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `created_date` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`review_id`),
  KEY `traveller_id` (`traveller_id`),
  KEY `agency_id` (`agency_id`),
  KEY `package_id` (`package_id`),
  CONSTRAINT `fk_review_traveller` FOREIGN KEY (`traveller_id`) REFERENCES `traveller` (`traveller_id`),
  CONSTRAINT `fk_review_agency` FOREIGN KEY (`agency_id`) REFERENCES `travel_agency` (`agency_id`),
  CONSTRAINT `fk_review_package` FOREIGN KEY (`package_id`) REFERENCES `travel_package` (`package_id`),
  CONSTRAINT `CONSTRAINT_1` CHECK (`agency_id` is not null or `package_id` is not null)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `review`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `review` WRITE;
/*!40000 ALTER TABLE `review` DISABLE KEYS */;
INSERT INTO `review` VALUES
(1,1,6,NULL,5,'Great experience with Wanderlust! Very organized.','2026-05-12 16:15:41'),
(2,2,7,3,5,'Best safari ever! Saw all Big Five.','2026-05-12 16:15:41'),
(3,3,8,4,4,'Paris was lovely, hotel was excellent.','2026-05-12 16:15:41'),
(4,4,9,5,5,'Amazing food tour in Bangkok!','2026-05-12 16:15:41'),
(5,5,6,1,3,'Decent but overpriced for what we got.','2026-05-12 16:15:41'),
(6,1,NULL,4,5,'The Paris package was a dream come true!','2026-05-12 16:15:41');
/*!40000 ALTER TABLE `review` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `tourist_attraction`
--

DROP TABLE IF EXISTS `tourist_attraction`;

--
-- Table structure for table `transport`
--

DROP TABLE IF EXISTS `transport`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `transport` (
  `transport_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('car','taxi','bus','train','shuttle','boat','other') NOT NULL,
  `provider` varchar(100) DEFAULT NULL,
  `origin_destination_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `departure_time` datetime NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `agency_id` int(11) NOT NULL,
  PRIMARY KEY (`transport_id`),
  KEY `origin_destination_id` (`origin_destination_id`),
  KEY `destination_id` (`destination_id`),
  KEY `agency_id` (`agency_id`),
  CONSTRAINT `fk_transport_origin` FOREIGN KEY (`origin_destination_id`) REFERENCES `destination` (`destination_id`),
  CONSTRAINT `fk_transport_destination` FOREIGN KEY (`destination_id`) REFERENCES `destination` (`destination_id`),
  CONSTRAINT `fk_transport_agency` FOREIGN KEY (`agency_id`) REFERENCES `travel_agency` (`agency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transport`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `transport` WRITE;
/*!40000 ALTER TABLE `transport` DISABLE KEYS */;
INSERT INTO `transport` VALUES
(1,'shuttle','Cape Town Shuttles',1,1,'2026-06-01 09:00:00',350.00,6),
(2,'bus','Intercape',2,1,'2026-05-20 19:00:00',550.00,6),
(3,'taxi','Uber',4,4,'2026-06-02 10:00:00',45.00,8),
(4,'train','Eurostar',7,4,'2026-05-15 07:00:00',1200.00,8),
(5,'boat','Chao Phraya Express',5,5,'2026-07-10 14:00:00',25.00,9),
(6,'car','Hertz Rental',6,6,'2026-08-05 09:00:00',650.00,10),
(7,'shuttle','Narita Express',8,8,'2026-09-12 08:00:00',180.00,9);
/*!40000 ALTER TABLE `transport` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `travel_agency`
--

DROP TABLE IF EXISTS `travel_agency`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `travel_agency` (
  `agency_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`agency_id`),
  CONSTRAINT `fk_travelagency_user` FOREIGN KEY (`agency_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `travel_agency`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `travel_agency` WRITE;
/*!40000 ALTER TABLE `travel_agency` DISABLE KEYS */;
INSERT INTO `travel_agency` VALUES
(6,'Wanderlust Travel','+27 11 345 6789','www.wanderlust.co.za','123 Main Street, Sandton, 2196','South Africa'),
(7,'Safari Experts SA','+27 21 456 7890','www.safariexperts.co.za','45 Wildlife Avenue, Hoedspruit, 1380','South Africa'),
(8,'EuroTours International','+44 20 7946 0123','www.eurotours.com','221 Baker Street, London, NW1 6XE','United Kingdom'),
(9,'Asia Discovery','+66 2 123 4567','www.asiadiscovery.com','89 Sukhumvit Road, Bangkok, 10110','Thailand'),
(10,'Beach Holidays','+1 305 555 0123','www.beachholidays.com','1000 Ocean Drive, Miami Beach, FL 33139','USA');
/*!40000 ALTER TABLE `travel_agency` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `travel_package`
--

DROP TABLE IF EXISTS `travel_package`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `travel_package` (
  `package_id` int(11) NOT NULL AUTO_INCREMENT,
  `agency_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `duration_days` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `avg_rating` decimal(3,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`package_id`),
  KEY `agency_id` (`agency_id`),
  KEY `destination_id` (`destination_id`),
  CONSTRAINT `fk_package_agency` FOREIGN KEY (`agency_id`) REFERENCES `travel_agency` (`agency_id`),
  CONSTRAINT `fk_package_destination` FOREIGN KEY (`destination_id`) REFERENCES `destination` (`destination_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `travel_package`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `travel_package` WRITE;
/*!40000 ALTER TABLE `travel_package` DISABLE KEYS */;
INSERT INTO `travel_package` VALUES
(1,6,1,'Cape Town Explorer','5 days in beautiful Cape Town including Table Mountain',8500.00,5,'2026-06-01','2026-06-05','/images/packages/capetown_explorer.jpg',3.00,'2026-05-12 16:12:32'),
(2,6,1,'Garden Route Adventure','7 day road trip along South Africa\'s stunning coastline',12500.00,7,'2026-07-01','2026-07-07','/images/packages/garden_route.jpg',0.00,'2026-05-12 16:12:32'),
(3,7,3,'Kruger Big 5 Safari','4 day safari in Kruger National Park',18500.00,4,'2026-07-15','2026-07-19','/images/packages/kruger_safari.jpg',5.00,'2026-05-12 16:12:32'),
(4,8,4,'Paris Romance','5 days in Paris including Eiffel Tower and Seine cruise',15000.00,5,'2026-06-05','2026-06-10','/images/packages/paris_romance.jpg',5.00,'2026-05-12 16:12:32'),
(5,9,5,'Bangkok Foodie Tour','4 days of Thai street food and temples',12000.00,4,'2026-07-12','2026-07-16','/images/packages/bangkok_foodie.jpg',5.00,'2026-05-12 16:12:32'),
(6,10,6,'Miami Beach Escape','5 days of sun, sand and nightlife',13500.00,5,'2026-08-05','2026-08-10','/images/packages/miami_escape.jpg',0.00,'2026-05-12 16:12:32'),
(7,8,7,'London Heritage','4 days exploring British history and royal sites',14200.00,4,'2026-09-01','2026-09-05','/images/packages/london_heritage.jpg',0.00,'2026-05-12 16:12:32'),
(8,9,8,'Tokyo Discovery','6 days of futuristic Tokyo and traditional culture',22000.00,6,'2026-09-10','2026-09-16','/images/packages/tokyo_discovery.jpg',0.00,'2026-05-12 16:12:32');
/*!40000 ALTER TABLE `travel_package` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `traveller`
--

DROP TABLE IF EXISTS `traveller`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `traveller` (
  `traveller_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `home_address` varchar(255) DEFAULT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `passport_number` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`traveller_id`),
  CONSTRAINT `fk_traveller_user` FOREIGN KEY (`traveller_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `traveller`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `traveller` WRITE;
/*!40000 ALTER TABLE `traveller` DISABLE KEYS */;
INSERT INTO `traveller` VALUES
(1,'John','Doe','+27 82 123 4567','12 Oak Street, Cape Town, 8001','850101 5084 089','A12345678'),
(2,'Sarah','Wilson','+27 83 234 5678','45 Beach Road, Durban, 4001','920505 5084 123','B23456789'),
(3,'Michael','Brown','+27 71 345 6789','78 Mountain View, Johannesburg, 2196','880312 5084 456','C34567890'),
(4,'Emma','Davis','+27 72 456 7890','23 Sunset Blvd, Pretoria, 0081','950728 5084 789','D45678901'),
(5,'Lucas','Martin','+27 73 567 8901','56 Garden Route, George, 6529','900415 5084 234','E56789012');
/*!40000 ALTER TABLE `traveller` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('traveller','agency') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES
(1,'john.doe@email.com','hashed_pwd_1','traveller','2026-05-12 16:09:28'),
(2,'sarah.wilson@email.com','hashed_pwd_2','traveller','2026-05-12 16:09:28'),
(3,'michael.brown@email.com','hashed_pwd_3','traveller','2026-05-12 16:09:28'),
(4,'emma.davis@email.com','hashed_pwd_4','traveller','2026-05-12 16:09:28'),
(5,'lucas.martin@email.com','hashed_pwd_5','traveller','2026-05-12 16:09:28'),
(6,'wanderlust.travel@agency.com','hashed_pwd_6','agency','2026-05-12 16:09:28'),
(7,'safari.experts@agency.com','hashed_pwd_7','agency','2026-05-12 16:09:28'),
(8,'euro.tours@agency.com','hashed_pwd_8','agency','2026-05-12 16:09:28'),
(9,'asia.discovery@agency.com','hashed_pwd_9','agency','2026-05-12 16:09:28'),
(10,'beach.holidays@agency.com','hashed_pwd_10','agency','2026-05-12 16:09:28');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-05-12 18:23:53
