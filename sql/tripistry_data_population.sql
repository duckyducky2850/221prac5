-- Method: we manually curated realistic data for our db

USE `tripistry`;

SET FOREIGN_KEY_CHECKS=0;


--  populating the Users table

INSERT INTO `user` (`user_id`, `email`, `password`, `role`, `created_at`) VALUES
(11,'amahle.dlamini@email.com','hashed_pwd_11','traveller','2026-01-15 08:30:00'),
(12,'pieter.vanzyl@email.com','hashed_pwd_12','traveller','2026-01-20 09:15:00'),
(13,'fatima.hassan@email.com','hashed_pwd_13','traveller','2026-02-01 10:00:00'),
(14,'james.okonkwo@email.com','hashed_pwd_14','traveller','2026-02-10 11:30:00'),
(15,'chen.wei@email.com','hashed_pwd_15','traveller','2026-02-14 14:00:00'),
(16,'priya.naidoo@email.com','hashed_pwd_16','traveller','2026-02-20 08:00:00'),
(17,'marco.rossini@email.com','hashed_pwd_17','traveller','2026-03-01 09:45:00'),
(18,'anna.kowalski@email.com','hashed_pwd_18','traveller','2026-03-05 13:00:00'),
(19,'david.mensah@email.com','hashed_pwd_19','traveller','2026-03-10 15:30:00'),
(20,'yuki.tanaka@email.com','hashed_pwd_20','traveller','2026-03-15 07:00:00'),
(21,'sofia.rodrigues@email.com','hashed_pwd_21','traveller','2026-03-20 10:30:00'),
(22,'global.adventures@agency.com','hashed_pwd_22','agency','2026-01-05 08:00:00'),
(23,'alpine.escapes@agency.com','hashed_pwd_23','agency','2026-01-10 09:00:00'),
(24,'africa.unlimited@agency.com','hashed_pwd_24','agency','2026-01-15 10:00:00'),
(25,'pacific.voyages@agency.com','hashed_pwd_25','agency','2026-01-20 11:00:00'),
(26,'mediterranean.dreams@agency.com','hashed_pwd_26','agency','2026-01-25 12:00:00');

--  populating the Travelers table

INSERT INTO `traveller` (`traveller_id`, `first_name`, `last_name`, `phone_number`, `home_address`, `id_number`, `passport_number`) VALUES
(11,'Amahle','Dlamini','+27 71 111 2233','15 Zulu Street, Durban, 4001','980203 0484 081','F67890123'),
(12,'Pieter','van Zyl','+27 82 222 3344','88 Voortrekker Road, Pretoria, 0001','870614 5084 092','G78901234'),
(13,'Fatima','Hassan','+27 73 333 4455','22 Rose Street, Cape Town, 7500','910925 0284 083','H89012345'),
(14,'James','Okonkwo','+27 84 444 5566','56 Nelson Mandela Ave, Johannesburg, 2000','860730 5084 094','I90123456'),
(15,'Chen','Wei','+27 61 555 6677','12 Oriental Road, Cape Town, 8001','930112 5084 085','J01234567'),
(16,'Priya','Naidoo','+27 72 666 7788','34 Gandhi Lane, Durban, 4050','950820 0484 086','K12345678'),
(17,'Marco','Rossini','+27 83 777 8899','78 Italia Avenue, Johannesburg, 2196','881205 5084 097','L23456789'),
(18,'Anna','Kowalski','+27 64 888 9900','45 Warsaw Street, Pretoria, 0181','920415 0284 088','M34567890'),
(19,'David','Mensah','+27 75 999 0011','90 Accra Road, Cape Town, 7441','870922 5084 099','N45678901'),
(20,'Yuki','Tanaka','+27 86 000 1122','23 Sakura Lane, Johannesburg, 2000','960308 5084 080','O56789012'),
(21,'Sofia','Rodrigues','+27 73 111 2233','67 Lisboa Street, Durban, 4001','910715 0284 091','P67890123');

--  populating the Travel Agency table

INSERT INTO `travel_agency` (`agency_id`, `company_name`, `contact_number`, `website`, `address`, `country`) VALUES
(22,'Global Adventures','+27 11 567 8901','www.globaladventures.co.za','456 Adventure Road, Cape Town, 8001','South Africa'),
(23,'Alpine Escapes','+41 44 123 4567','www.alpineescapes.ch','Bahnhofstrasse 12, Zurich, 8001','Switzerland'),
(24,'Africa Unlimited','+27 12 678 9012','www.africaunlimited.co.za','789 Safari Drive, Hoedspruit, 1380','South Africa'),
(25,'Pacific Voyages','+61 2 9876 5432','www.pacificvoyages.com.au','100 Harbour Street, Sydney, 2000','Australia'),
(26,'Mediterranean Dreams','+39 06 123 4567','www.meddreams.it','Via Roma 45, Rome, 00100','Italy');

--  populating the Destination table

INSERT INTO `destination` (`destination_id`, `city_name`, `country`, `description`, `popular_season`, `image_url`) VALUES
(9,'Rome','Italy','Eternal City with ancient history, art and world-class cuisine','Apr-Jun, Sep-Oct','/images/rome.jpg'),
(10,'Sydney','Australia','Iconic harbour city with beaches, culture and outdoor lifestyle','Sep-Feb','/images/sydney.jpg'),
(11,'New York','USA','The city that never sleeps, iconic skyline and diverse culture','Apr-Jun, Sep-Nov','/images/newyork.jpg'),
(12,'Bali','Indonesia','Tropical paradise with temples, rice terraces and surf beaches','Apr-Oct','/images/bali.jpg'),
(13,'Zanzibar','Tanzania','Spice island with white sand beaches and Swahili culture','Jun-Oct','/images/zanzibar.jpg'),
(14,'Zurich','Switzerland','Alpine city with stunning lake views and chocolate culture','Dec-Mar, Jun-Aug','/images/zurich.jpg'),
(15,'Marrakech','Morocco','Imperial city with vibrant souks, palaces and desert adventures','Mar-May, Sep-Nov','/images/marrakech.jpg'),
(16,'Dubai','UAE','Futuristic desert city with luxury shopping and iconic architecture','Nov-Mar','/images/dubai.jpg'),
(17,'Buenos Aires','Argentina','Paris of South America with tango, steak and vibrant nightlife','Mar-May, Sep-Nov','/images/buenosaires.jpg'),
(18,'Santorini','Greece','Stunning volcanic island with white-washed villages and sunsets','May-Oct','/images/santorini.jpg'),
(19,'Nairobi','Kenya','Gateway to East Africa safaris and vibrant urban culture','Jan-Feb, Jul-Oct','/images/nairobi.jpg'),
(20,'Amsterdam','Netherlands','Canal city with world-class museums, cycling and tulip fields','Apr-Aug','/images/amsterdam.jpg');

--  populating the Agency Staff table

INSERT INTO `agency_staff` (`agency_id`, `first_name`, `last_name`, `phone_number`, `email`, `role`) VALUES
(22,'Thabo','Mokoena','+27 82 123 9876','thabo@globaladventures.co.za','Senior Travel Consultant'),
(22,'Nandi','Zulu','+27 73 234 8765','nandi@globaladventures.co.za','Bookings Manager'),
(23,'Hans','Mueller','+41 79 345 7654','hans@alpineescapes.ch','Alpine Guide'),
(24,'Sipho','Ndlovu','+27 84 456 6543','sipho@africaunlimited.co.za','Safari Coordinator'),
(25,'Claire','Thompson','+61 411 567 5432','claire@pacificvoyages.com.au','Pacific Routes Manager'),
(26,'Giovanni','Ferrari','+39 335 678 4321','giovanni@meddreams.it','Mediterranean Specialist');

--  populating the Accomodation table

INSERT INTO `accommodation` (`accommodation_id`, `name`, `address`, `no_bedrooms`, `no_bathrooms`, `price_per_night`, `destination_id`, `agency_id`) VALUES
(9,'Colosseum View Hotel','Via Sacra 12, Rome',85,85,5200.00,9,26),
(10,'Hotel de Russie','Via del Babuino 9, Rome',120,120,9800.00,9,26),
(11,'Park Hyatt Sydney','7 Hickson Road, Sydney',155,155,6500.00,10,25),
(12,'The Langham Sydney','89 Kent Street, Sydney',98,98,4800.00,10,25),
(13,'The Plaza Hotel','768 Fifth Avenue, New York',282,282,12000.00,11,10),
(14,'1 Hotel Brooklyn Bridge','60 Furman Street, New York',194,194,7500.00,11,22),
(15,'Four Seasons Bali','Jimbaran Bay, Bali',147,147,8200.00,12,25),
(16,'Alaya Resort Ubud','Jalan Hanoman, Ubud, Bali',56,56,4500.00,12,25),
(17,'Baraza Resort Zanzibar','Bwejuu Beach, Zanzibar',30,30,6800.00,13,24),
(18,'The Baur au Lac','Talstrasse 1, Zurich',120,120,11000.00,14,23),
(19,'La Mamounia','Avenue Bab Jdid, Marrakech',209,209,7200.00,15,26),
(20,'Atlantis The Palm','Crescent Road, Dubai',1548,1548,9500.00,16,22),
(21,'Armani Hotel Dubai','Burj Khalifa, Dubai',160,160,14000.00,16,22),
(22,'Alvear Palace Hotel','Av. Alvear 1891, Buenos Aires',210,210,4200.00,17,25),
(23,'Katikies Hotel','Oia, Santorini',27,27,8900.00,18,26),
(24,'Hemingways Nairobi','Mbagathi Ridge, Karen, Nairobi',45,45,5500.00,19,24);


--  populating the Flight table

INSERT INTO `flight` (`flight_id`, `flight_number`, `airline`, `origin_destination_id`, `destination_id`, `departure_time`, `arrival_time`, `price`, `agency_id`) VALUES
(9,'QF481','Qantas',1,10,'2026-06-15 21:00:00','2026-06-16 17:30:00',14500.00,25),
(10,'EK763','Emirates',1,16,'2026-07-20 08:00:00','2026-07-20 17:30:00',8900.00,22),
(11,'MK045','Air Mauritius',1,12,'2026-08-01 10:00:00','2026-08-01 22:00:00',9800.00,25),
(12,'KQ101','Kenya Airways',1,19,'2026-07-05 06:00:00','2026-07-05 10:30:00',4500.00,24),
(13,'AZ610','ITA Airways',7,9,'2026-06-10 07:00:00','2026-06-10 10:30:00',3200.00,26),
(14,'LX318','Swiss Air',7,14,'2026-07-08 09:00:00','2026-07-08 11:30:00',4800.00,23),
(15,'AA100','American Airlines',11,7,'2026-08-12 22:00:00','2026-08-13 11:00:00',9500.00,22),
(16,'UA926','United Airlines',11,9,'2026-09-05 17:00:00','2026-09-06 08:30:00',7200.00,26),
(17,'SQ317','Singapore Airlines',8,12,'2026-09-15 00:05:00','2026-09-15 07:30:00',6800.00,25),
(18,'AT801','Royal Air Maroc',7,15,'2026-06-20 14:00:00','2026-06-20 17:00:00',2900.00,26),
(19,'AR1161','Aerolineas Argentinas',6,17,'2026-10-01 23:59:00','2026-10-02 18:00:00',11200.00,25),
(20,'A3601','Aegean Airlines',9,18,'2026-07-25 06:00:00','2026-07-25 09:30:00',2400.00,26),
(21,'KQ202','Kenya Airways',19,13,'2026-07-06 12:00:00','2026-07-06 14:00:00',2200.00,24),
(22,'FA201','FlySafair',1,3,'2026-06-01 07:00:00','2026-06-01 08:00:00',650.00,24),
(23,'SA789','South African Airways',2,16,'2026-08-10 09:00:00','2026-08-10 19:30:00',9200.00,22),
(24,'EK772','Emirates',16,8,'2026-09-11 02:30:00','2026-09-11 15:00:00',7800.00,22);


--  populating the Transport table

INSERT INTO `transport` (`transport_id`, `type`, `provider`, `origin_destination_id`, `destination_id`, `departure_time`, `price`, `agency_id`) VALUES
(8,'bus','Rome Airport Express',9,9,'2026-06-10 11:00:00',120.00,26),
(9,'train','Trenitalia',9,18,'2026-07-25 08:00:00',850.00,26),
(10,'shuttle','Sydney Airporter',10,10,'2026-06-16 18:00:00',85.00,25),
(11,'train','Sydney Trains',10,10,'2026-06-17 09:00:00',45.00,25),
(12,'shuttle','Dubai Metro',16,16,'2026-07-20 18:30:00',150.00,22),
(13,'car','Avis Rentals',16,16,'2026-07-21 09:00:00',950.00,22),
(14,'shuttle','Nairobi Shuttles',19,19,'2026-07-05 11:30:00',350.00,24),
(15,'bus','Marrakech Express',15,15,'2026-06-20 18:00:00',200.00,26),
(16,'boat','Zanzibar Ferry',13,13,'2026-07-07 09:00:00',180.00,24),
(17,'train','Thalys',20,20,'2026-08-15 10:00:00',320.00,23);


--  populating the Activity table

INSERT INTO `activity` (`activity_id`, `name`, `address`, `city`, `price`, `description`, `start_time`, `end_time`, `destination_id`, `agency_id`, `activity_type`) VALUES
(10,'Colosseum & Roman Forum Tour','Piazza del Colosseo 1','Rome',450.00,'Skip-the-line guided tour of the Colosseum','2026-06-11 09:00:00','2026-06-11 13:00:00',9,26,'Historical Site'),
(11,'Vatican Museums & Sistine Chapel','Viale Vaticano','Rome',380.00,'Guided tour of Vatican Museums','2026-06-12 08:00:00','2026-06-12 12:00:00',9,26,'Museum'),
(12,'Da Enzo al 29','Via dei Vascellari 29','Rome',1200.00,'Authentic Roman trattoria in Trastevere','2026-06-12 19:30:00','2026-06-12 22:00:00',9,26,'Restaurant'),
(13,'Sydney Opera House Tour','Bennelong Point','Sydney',280.00,'Behind-the-scenes tour of the Opera House','2026-06-17 10:00:00','2026-06-17 12:00:00',10,25,'Landmark'),
(14,'Bondi to Coogee Coastal Walk','Bondi Beach','Sydney',0.00,'Scenic 6km coastal walk','2026-06-18 07:00:00','2026-06-18 11:00:00',10,25,'Nature Tour'),
(15,'Quay Restaurant','Upper Level, Overseas Passenger Terminal','Sydney',2800.00,'Four-time Best Restaurant in Australia','2026-06-18 19:00:00','2026-06-18 22:00:00',10,25,'Restaurant'),
(16,'Statue of Liberty Tour','Liberty Island','New York',350.00,'Ferry and guided tour of the Statue of Liberty','2026-08-13 09:00:00','2026-08-13 14:00:00',11,22,'Landmark'),
(17,'Metropolitan Museum of Art','1000 Fifth Ave','New York',250.00,'World-class art museum tour','2026-08-14 10:00:00','2026-08-14 15:00:00',11,22,'Museum'),
(18,'Eleven Madison Park','11 Madison Ave','New York',4500.00,'Three-Michelin-star plant-based dining','2026-08-14 19:00:00','2026-08-14 22:30:00',11,22,'Restaurant'),
(19,'Uluwatu Temple Sunset','Uluwatu, Pecatu','Bali',150.00,'Sunset Kecak fire dance at clifftop temple','2026-08-02 17:00:00','2026-08-02 20:00:00',12,25,'Historical Site'),
(20,'Tegallalang Rice Terrace','Tegallalang, Ubud','Bali',80.00,'Walk through stunning rice terraces','2026-08-03 08:00:00','2026-08-03 11:00:00',12,25,'Nature Tour'),
(21,'Spice Islands Tour','Stone Town, Zanzibar','Zanzibar',350.00,'Historic Stone Town walking tour','2026-07-07 09:00:00','2026-07-07 13:00:00',13,24,'Historical Site'),
(22,'Maasai Mara Safari','Maasai Mara, Nairobi','Nairobi',4500.00,'Full day safari in Maasai Mara','2026-07-06 05:30:00','2026-07-06 18:00:00',19,24,'Wildlife Safari'),
(23,'Burj Khalifa At The Top','1 Sheikh Mohammed Blvd','Dubai',650.00,'Visit the worlds tallest building','2026-07-21 10:00:00','2026-07-21 13:00:00',16,22,'Landmark'),
(24,'Dubai Desert Safari','Al Qudra Desert','Dubai',1200.00,'Dune bashing, camel riding and BBQ dinner','2026-07-22 15:00:00','2026-07-22 22:00:00',16,22,'Nature Tour'),
(25,'Jardin Majorelle','Rue Yves Saint Laurent','Marrakech',180.00,'Stunning botanical garden and Berber museum','2026-06-21 09:00:00','2026-06-21 12:00:00',15,26,'Nature Tour'),
(26,'Jemaa el-Fna Food Tour','Jemaa el-Fna Square','Marrakech',650.00,'Evening street food tour of the main square','2026-06-21 18:00:00','2026-06-21 22:00:00',15,26,'Restaurant'),
(27,'Oia Sunset Walk','Oia Village','Santorini',0.00,'Walk to the famous Oia sunset viewpoint','2026-07-26 18:00:00','2026-07-26 21:00:00',18,26,'Nature Tour'),
(28,'Santorini Wine Tasting','Santo Wines, Pyrgos','Santorini',850.00,'Volcanic wine tasting with caldera views','2026-07-27 16:00:00','2026-07-27 19:00:00',18,26,'Restaurant'),
(29,'Rijksmuseum Tour','Museumstraat 1','Amsterdam',220.00,'Dutch Golden Age art and history','2026-08-16 10:00:00','2026-08-16 14:00:00',20,23,'Museum');


--  populating the Travel Package table

INSERT INTO `travel_package` (`package_id`, `agency_id`, `destination_id`, `name`, `description`, `base_price`, `duration_days`, `start_date`, `end_date`, `image_url`, `avg_rating`, `created_at`) VALUES
(9,26,9,'Rome & Vatican Explorer','5 days exploring ancient Rome, Vatican City and authentic Italian cuisine',16500.00,5,'2026-06-10','2026-06-15','/images/packages/rome_explorer.jpg',0.00,'2026-05-01 10:00:00'),
(10,25,10,'Sydney Harbour Experience','6 days discovering Sydney Opera House, Bondi Beach and Blue Mountains',19800.00,6,'2026-06-15','2026-06-21','/images/packages/sydney_harbour.jpg',0.00,'2026-05-01 10:00:00'),
(11,22,11,'New York City Highlights','5 days in the Big Apple including Broadway show and iconic landmarks',24500.00,5,'2026-08-12','2026-08-17','/images/packages/nyc_highlights.jpg',0.00,'2026-05-01 10:00:00'),
(12,25,12,'Bali Spiritual Retreat','7 days of temples, rice terraces and wellness in Bali',15200.00,7,'2026-08-01','2026-08-08','/images/packages/bali_retreat.jpg',0.00,'2026-05-01 10:00:00'),
(13,24,13,'Zanzibar Beach Paradise','6 days on pristine beaches with spice tours and Swahili culture',18900.00,6,'2026-07-06','2026-07-12','/images/packages/zanzibar_paradise.jpg',0.00,'2026-05-01 10:00:00'),
(14,23,14,'Swiss Alps Adventure','5 days of alpine scenery, chocolate tasting and mountain trains',28000.00,5,'2026-07-08','2026-07-13','/images/packages/swiss_alps.jpg',0.00,'2026-05-01 10:00:00'),
(15,26,15,'Marrakech Magic','4 days of souks, palaces, hammams and Sahara desert sunsets',11500.00,4,'2026-06-20','2026-06-24','/images/packages/marrakech_magic.jpg',0.00,'2026-05-01 10:00:00'),
(16,22,16,'Dubai Luxury Escape','5 days of desert luxury, Burj Khalifa and world-class shopping',32000.00,5,'2026-07-20','2026-07-25','/images/packages/dubai_luxury.jpg',0.00,'2026-05-01 10:00:00'),
(17,25,17,'Buenos Aires & Tango','6 days of Argentine steak, tango shows and Patagonian wine',21000.00,6,'2026-10-01','2026-10-07','/images/packages/buenosaires_tango.jpg',0.00,'2026-05-01 10:00:00'),
(18,26,18,'Santorini Sunset Romance','5 days of Aegean sunsets, volcanic beaches and Greek wine',23500.00,5,'2026-07-25','2026-07-30','/images/packages/santorini_romance.jpg',0.00,'2026-05-01 10:00:00'),
(19,24,19,'Kenya Safari & Nairobi','7 days Maasai Mara game drives and Nairobi city experience',26500.00,7,'2026-07-05','2026-07-12','/images/packages/kenya_safari.jpg',0.00,'2026-05-01 10:00:00'),
(20,23,20,'Amsterdam Canal Escape','4 days of museums, cycling, tulips and Dutch cuisine',13200.00,4,'2026-08-15','2026-08-19','/images/packages/amsterdam_canals.jpg',0.00,'2026-05-01 10:00:00'),
(21,24,3,'Kruger & Panorama Route','6 days combining Big Five safari with Blyde River Canyon',22000.00,6,'2026-08-01','2026-08-07','/images/packages/kruger_panorama.jpg',0.00,'2026-05-01 10:00:00'),
(22,22,16,'Dubai Desert & City','7 days combining desert adventures with Dubai city highlights',28500.00,7,'2026-11-01','2026-11-08','/images/packages/dubai_desert_city.jpg',0.00,'2026-05-01 10:00:00'),
(23,26,9,'Rome to Santorini Mediterranean','8 days island hopping from Rome through the Mediterranean',38000.00,8,'2026-07-10','2026-07-18','/images/packages/rome_santorini.jpg',0.00,'2026-05-01 10:00:00'),
(24,25,12,'Bali & Sydney Twin Centre','10 days combining tropical Bali with cosmopolitan Sydney',34500.00,10,'2026-09-15','2026-09-25','/images/packages/bali_sydney.jpg',0.00,'2026-05-01 10:00:00');

--  populating the Travel Package table

INSERT INTO `package_component` (`package_id`, `component_type`, `component_id`) VALUES
(9,'flight',13),(9,'accommodation',9),(9,'activity',10),(9,'activity',11),(9,'activity',12),(9,'transport',8),
(10,'flight',9),(10,'accommodation',11),(10,'activity',13),(10,'activity',14),(10,'activity',15),(10,'transport',10),
(11,'flight',15),(11,'accommodation',14),(11,'activity',16),(11,'activity',17),(11,'activity',18),
(12,'flight',11),(12,'accommodation',16),(12,'activity',19),(12,'activity',20),
(13,'flight',12),(13,'accommodation',17),(13,'activity',21),(13,'transport',16),
(14,'flight',14),(14,'accommodation',18),(14,'transport',17),
(15,'flight',18),(15,'accommodation',19),(15,'activity',25),(15,'activity',26),(15,'transport',15),
(16,'flight',10),(16,'accommodation',21),(16,'activity',23),(16,'activity',24),(16,'transport',12),
(17,'flight',19),(17,'accommodation',22),
(18,'flight',20),(18,'accommodation',23),(18,'activity',27),(18,'activity',28),
(19,'flight',12),(19,'accommodation',24),(19,'activity',22),(19,'transport',14),
(20,'accommodation',20),(20,'activity',29),(20,'transport',17),
(21,'flight',22),(21,'accommodation',3),(21,'transport',2),(21,'activity',3),
(22,'flight',23),(22,'accommodation',20),(22,'activity',23),(22,'activity',24),(22,'transport',13),
(23,'flight',13),(23,'accommodation',9),(23,'accommodation',23),(23,'activity',10),(23,'activity',27),
(24,'flight',17),(24,'accommodation',15),(24,'accommodation',12),(24,'activity',14),(24,'activity',19);


--  populating the Group Trip table

INSERT INTO `group_trip` (`group_trip_id`, `package_id`, `agency_id`, `max_members`, `current_members`, `start_date`, `end_date`, `status`) VALUES
(4,12,25,10,4,'2026-08-01','2026-08-08','open'),
(5,19,24,8,8,'2026-07-05','2026-07-12','full'),
(6,15,26,12,7,'2026-06-20','2026-06-24','open'),
(7,14,23,6,2,'2026-07-08','2026-07-13','open'),
(8,16,22,10,6,'2026-07-20','2026-07-25','open');


--  populating the Booking table

INSERT INTO `booking` (`booking_id`, `traveller_id`, `package_id`, `group_trip_id`, `booking_date`, `total_price`, `status`) VALUES
(7,11,9,NULL,'2026-04-01 09:00:00',16500.00,'confirmed'),
(8,12,14,7,'2026-04-05 10:30:00',28000.00,'confirmed'),
(9,13,15,6,'2026-04-10 11:00:00',11500.00,'confirmed'),
(10,14,19,5,'2026-04-12 14:00:00',26500.00,'confirmed'),
(11,15,12,4,'2026-04-15 09:30:00',15200.00,'confirmed'),
(12,16,16,8,'2026-04-18 10:00:00',32000.00,'confirmed'),
(13,17,9,NULL,'2026-04-20 11:30:00',16500.00,'confirmed'),
(14,18,10,NULL,'2026-04-22 13:00:00',19800.00,'pending'),
(15,19,13,NULL,'2026-04-25 08:30:00',18900.00,'confirmed'),
(16,20,8,NULL,'2026-04-28 15:00:00',22000.00,'confirmed'),
(17,21,18,NULL,'2026-05-01 09:00:00',23500.00,'pending'),
(18,11,16,8,'2026-05-02 10:00:00',32000.00,'confirmed'),
(19,13,19,5,'2026-05-03 11:00:00',26500.00,'confirmed'),
(20,15,15,6,'2026-05-04 12:00:00',11500.00,'confirmed');


--  populating the Receipt table

INSERT INTO `receipt` (`booking_id`, `amount`, `payment_date`, `payment_method`, `receipt_number`) VALUES
(7,16500.00,'2026-04-01 09:05:00','credit_card','RCP-2026-00007'),
(8,28000.00,'2026-04-05 10:35:00','bank_transfer','RCP-2026-00008'),
(9,11500.00,'2026-04-10 11:05:00','paypal','RCP-2026-00009'),
(10,26500.00,'2026-04-12 14:05:00','credit_card','RCP-2026-00010'),
(11,15200.00,'2026-04-15 09:35:00','debit_card','RCP-2026-00011'),
(12,32000.00,'2026-04-18 10:05:00','credit_card','RCP-2026-00012'),
(13,16500.00,'2026-04-20 11:35:00','bank_transfer','RCP-2026-00013'),
(15,18900.00,'2026-04-25 08:35:00','credit_card','RCP-2026-00015'),
(16,22000.00,'2026-04-28 15:05:00','debit_card','RCP-2026-00016'),
(18,32000.00,'2026-05-02 10:05:00','credit_card','RCP-2026-00018'),
(19,26500.00,'2026-05-03 11:05:00','bank_transfer','RCP-2026-00019'),
(20,11500.00,'2026-05-04 12:05:00','paypal','RCP-2026-00020');


--  populating the Review table

INSERT INTO `review` (`traveller_id`, `agency_id`, `package_id`, `rating`, `comment`, `created_date`) VALUES
(11,26,9,5,'Rome was absolutely breathtaking! The Vatican tour was perfectly organised.','2026-04-20 10:00:00'),
(12,23,14,5,'Swiss Alps exceeded all expectations. Hans was an incredible guide!','2026-04-25 11:00:00'),
(13,26,15,4,'Marrakech was magical, very well organised. Souk tour was a highlight.','2026-04-30 09:00:00'),
(14,24,19,5,'Best experience of my life. Seeing lions at sunrise in the Mara was surreal.','2026-05-01 08:00:00'),
(15,25,12,5,'Bali was pure paradise. The rice terraces at sunrise were unforgettable.','2026-05-02 10:00:00'),
(16,22,16,4,'Dubai was spectacular. The desert safari was the highlight of the trip.','2026-05-03 11:00:00'),
(17,26,9,4,'Great itinerary for Rome. The skip-the-line Colosseum access was worth it.','2026-05-05 09:00:00'),
(19,24,13,5,'Zanzibar is paradise on earth. Baraza Resort was absolutely stunning.','2026-05-06 10:00:00'),
(20,9,8,5,'Tokyo Discovery was phenomenal. Asia Discovery really know their stuff.','2026-05-07 11:00:00'),
(21,26,18,5,'Santorini was everything I dreamed of. Watching the sunset from Oia was magical.','2026-05-08 09:00:00'),
(1,22,11,4,'NYC package was great value. Broadway show was a lovely touch.','2026-05-09 10:00:00'),
(2,25,10,5,'Sydney was incredible. The coastal walk was the highlight of our trip.','2026-05-10 11:00:00'),
(3,24,21,4,'Kruger and Panorama Route combo was brilliant. Saw all Big Five!','2026-05-11 09:00:00'),
(4,22,22,3,'Dubai was amazing but the package felt rushed. Would have liked more time.','2026-05-12 10:00:00'),
(5,23,14,5,'Swiss Alps was worth every cent. Most beautiful scenery I have ever seen.','2026-05-13 11:00:00'),
(11,NULL,16,5,'Dubai Luxury Escape was flawless from start to finish. Will definitely book again.','2026-05-14 09:00:00'),
(13,NULL,15,4,'Marrakech exceeded expectations. The food tour at Jemaa el-Fna was incredible.','2026-05-15 10:00:00'),
(15,NULL,12,5,'Bali retreat was transformative. Every activity was thoughtfully selected.','2026-05-16 11:00:00'),
(19,NULL,13,5,'Zanzibar beach paradise is the perfect description. Absolutely loved it.','2026-05-17 09:00:00'),
(20,NULL,8,4,'Tokyo Discovery gave us a perfect mix of modern and traditional Japan.','2026-05-18 10:00:00');


-- updating the avg_rating based on reviews

UPDATE `travel_package` tp
SET `avg_rating` = (
  SELECT ROUND(AVG(r.rating), 2)
  FROM `review` r
  WHERE r.package_id = tp.package_id
)
WHERE EXISTS (
  SELECT 1 FROM `review` r WHERE r.package_id = tp.package_id
);

SET FOREIGN_KEY_CHECKS=1;