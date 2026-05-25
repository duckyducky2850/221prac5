<?php
define('BASE_URL', '/tripistry');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'tripistry');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=tripistry;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
    echo "Connected successfully\n";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

echo " Seeding Tripistry database...\n\n";
// Name
$firstNames = [
    'Naruto', 'Sasuke', 'Sakura', 'Kakashi', 'Itachi', 'Hinata', 'Shikamaru', 'Temari',
    'Gaara', 'Rock', 'Neji', 'Tenten', 'Ino', 'Choji', 'Kiba', 'Shino',
    'Tsunade', 'Jiraiya', 'Orochimaru', 'Minato', 'Kushina', 'Obito', 'Nagato', 'Konan',
    'Mark', 'Amber', 'Nolan', 'Debbie', 'William', 'Eve', 'Rex', 'Atom',
    'Cecil', 'Robot', 'Dupli', 'Bulletproof', 'Shrinking', 'Darkwing', 'Omni',
    'Amahle', 'Pieter', 'Fatima', 'James', 'Chen', 'Priya', 'Marco', 'Anna',
    'David', 'Yuki', 'Sofia', 'Liam', 'Emma', 'Noah', 'Olivia', 'Ethan',
    'Sipho', 'Beast', 'Thabo', 'Lerato', 'Kagiso', 'Zanele', 'Bongani', 'Nomsa',
    'Mohammed', 'Aisha', 'KhanGul', 'Layla', 'Hassan', 'Sara', 'Ali', 'Nour',
    'Carlos', 'Maria', 'Luis', 'Ana', 'Pedro', 'Julia', 'Rafael', 'Camila',
    'Pierre', 'Marie', 'Jean', 'Claire', 'Antoine', 'Sophie', 'Francois', 'Isabelle'
];

$lastNames = [
    // surnames
    'Uzumaki', 'Uchiha', 'Haruno', 'Hatake', 'Hyuga', 'Nara', 'Akimichi', 'Yamanaka',
    'Inuzuka', 'Aburame', 'Senju', 'Namikaze', 'Sabaku', 'Lee', 'Maito',
    'Grayson', 'Bennett', 'Haeberle', 'Walker', 'Wilkins', 'Slott',
    'Dlamini', 'van Zyl', 'Hassan', 'Okonkwo', 'Wei', 'Naidoo', 'Rossini', 'Kowalski',
    'Mensah', 'Tanaka', 'Rodrigues', 'Smith', 'Johnson', 'Williams', 'Brown', 'Jones',
    'Mokoena', 'Zulu', 'Ndlovu', 'Khumalo', 'Mthembu', 'Nkosi', 'Sithole', 'Mabaso',
    'Patel', 'Sharma', 'Singh', 'Kumar', 'Gupta', 'Mueller', 'Schmidt', 'Fischer',
    'Dubois', 'Martin', 'Bernard', 'Ferrari', 'Russo', 'Esposito', 'Romano',
    'Thompson', 'White', 'Harris', 'Clark', 'Lewis', 'Robinson', 'Walker',
    'Yamamoto', 'Suzuki', 'Watanabe', 'Ito', 'Kobayashi', 'Nakamura'
];

$domains = ['gmail.com', 'yahoo.com', 'outlook.com', 'hotmail.com', 'icloud.com', 'konoha.com', 'invincible.net'];

$cities = [
    'Cape Town', 'Johannesburg', 'Durban', 'Pretoria', 'London', 'Paris', 'Tokyo',
    'Sydney', 'New York', 'Dubai', 'Konoha Village', 'Suna Village', 'Chicago',
    'Nairobi', 'São Paulo', 'Berlin', 'Atlas City', 'Rome', 'Bangkok'
];

//  Seed Users and Travellers 
echo "Seeding users and travellers...\n";

// Dump IDS users from ID start
$startId = 26;
$count = 300;

for ($i = 0; $i < $count; $i++) {
    $id = $startId + $i;
    $firstName = $firstNames[array_rand($firstNames)];
    $lastName = $lastNames[array_rand($lastNames)];

    // Unique email using ID to avoid duplicates
    $email = strtolower($firstName . '.' . $lastName . $id . '@' . $domains[array_rand($domains)]);
    $password = 'hashed_pwd_' . $id;
    $createdAt = date('Y-m-d H:i:s', strtotime('-' . rand(1, 500) . ' days'));

    // Insert user
    $pdo->prepare("INSERT IGNORE INTO `user` 
                   (user_id, email, password, role, created_at) 
                   VALUES (?, ?, ?, 'traveller', ?)")
        ->execute([$id, $email, $password, $createdAt]);

    // Generating the cap data
    $phone = '+27 ' . rand(60, 89) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999);
    $city = $cities[array_rand($cities)];
    $address = rand(1, 250) . ' ' . ['Ramen Street', 'Sharingan Ave', 'Omni-Man Road', 
                  'Chakra Lane', 'Invincible Blvd', 'Main Road', 'Oak Street', 
                  'Sunset Drive', 'Rasengan Road'][rand(0,8)] . ', ' . $city;
    $idNumber = rand(800101, 991231) . ' ' . rand(1000, 9999) . ' 0' . rand(80, 99);
    $passport = chr(rand(65, 90)) . rand(10000000, 99999999);

    // Insert traveller
    $pdo->prepare("INSERT IGNORE INTO `traveller` 
                   (traveller_id, first_name, last_name, phone_number, home_address, id_number, passport_number) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)")
        ->execute([$id, $firstName, $lastName, $phone, $address, $idNumber, $passport]);
}

echo "✓ $count users and travellers seeded (IDs $startId to " . ($startId + $count - 1) . ")\n";

// Seed Destinations 
echo "\nSeeding destinations...\n";

// 
$destinations = [
    [21, 'Tokyo', 'Japan', 'This is where we anime noobs ninja run', 'Mar-May, Oct-Nov', 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=800'],
    [22, 'Paris', 'France', 'I lost my wife to a baguette in this city, L place', 'Apr-Jun, Sep-Oct', 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800'],
    [23, 'Cape Town', 'South Africa', 'Like most crime in the world, but nice views', 'Nov-Mar', 'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?w=800'],
    [24, 'Barcelona', 'Spain', 'People think the goat is from here', 'May-Jun, Sep-Oct', 'https://images.unsplash.com/photo-1539037116277-4db20889f2d4?w=800'],
    [25, 'Maldives', 'Maldives', 'Do not get the hype shout out Lilly and Dian fr!', 'Nov-Apr', 'https://images.unsplash.com/photo-1573843981267-be1999ff37cd?w=800'],
    [26, 'Machu Picchu', 'Peru', 'lost to the spanish', 'May-Oct', 'https://images.unsplash.com/photo-1526392060635-9d6019884377?w=800'],
    [27, 'Kyoto', 'Japan', 'point mountains amazing view', 'Mar-May, Oct-Nov', 'https://images.unsplash.com/photo-1545569341-9eb8b30979d9?w=800'],
    [28, 'Lisbon', 'Portugal', 'This is really where the goat is from SUI', 'Mar-May, Sep-Oct', 'https://images.unsplash.com/photo-1555881400-74d7acaacd8b?w=800'],
    [29, 'Serengeti', 'Tanzania', 'Wildlife so intense even Mark Grayson was impressed', 'Jun-Oct', 'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?w=800'],
    [30, 'Prague', 'Czech Republic', 'Fairy tale city — Shikamaru called it too troublesome to leave','Apr-May, Sep-Oct', 'https://images.unsplash.com/photo-1519677100203-a0e668c92439?w=800'],
    [31, 'Phuket', 'Thailand', 'Beach paradise — even Gaara took his sand shoes off here', 'Nov-Apr', 'https://images.unsplash.com/photo-1508009603885-50cf7c579365?w=800'],
    [32, 'Vienna', 'Austria', 'We dont talk about what happened here, great people though', 'Apr-May, Sep-Oct', 'https://images.unsplash.com/photo-1516550893923-42d28e5677af?w=800'],
    [33, 'Cairo', 'Egypt', 'Ancient pyramids older than even the Sage of Six Paths', 'Oct-Apr', 'https://images.unsplash.com/photo-1572252009286-268acec5ca0a?w=800'],
    [34, 'Vancouver', 'Canada', 'Six god City Iceman!!! great to see the boy', 'Jun-Sep', 'https://images.unsplash.com/photo-1560814304-4f05b62af116?w=800'],
    [35, 'Istanbul', 'Turkey', 'City bridging two worlds — like Obito bridging two dimensions', 'Apr-May, Sep-Oct', 'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?w=800'],
    [36, 'Havana', 'Cuba', 'amaxzin beahces', 'Dec-Apr', 'https://images.unsplash.com/photo-1500759285222-a95626b934cb?w=800'],
    [37, 'Queenstown', 'New Zealand', 'Adventure capital — Invincible goes here on days off', 'Dec-Feb, Jun-Aug', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800'],
    [38, 'Florence', 'Italy', 'Renaissance art capital — Ino said the flowers were divine', 'Apr-Jun, Sep-Oct', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=800'],
    [39, 'Petra', 'Jordan', 'Rose-red city carved in rock — Yamato was jealous', 'Mar-May, Sep-Nov', 'https://images.unsplash.com/photo-1548786811-dd6e453ccca7?w=800'],
    [40, 'Reykjavik', 'Iceland', 'Northern Lights so bright even the Sharingan cannot copy them', 'Jun-Aug, Dec-Feb', 'https://images.unsplash.com/photo-1529963183134-61a90db47eaf?w=800'],
    [40, 'ItBuilding', 'UniPain', 'I really love comp sci so much', 'Jun-Aug, Dec-Feb', 'https://tenor.com/search/funny-meme-gifs'],
];  

foreach ($destinations as $dest) {
    [$id, $city, $country, $description, $season, $image] = $dest;
    $pdo->prepare("INSERT IGNORE INTO `destination` 
                   (destination_id, city_name, country, description, popular_season, image_url) 
                   VALUES (?, ?, ?, ?, ?, ?)")
        ->execute([$id, $city, $country, $description, $season, $image]);
}

echo "✓ " . count($destinations) . " destinations seeded\n";

//Seed New Agencies
echo "\nSeeding agencies...\n";

// Dump has 6-10, population adds 22-26, so we start at 27
$agencies = [
    [27, 'Konoha Travel Co', 'konoha.travel@agency.com', '+81 3 1234 5678', 'www.konohatravel.jp', '1 Hokage Rock Road, Konoha', 'Japan'],
    [28, 'Atlas City Adventures', 'atlascity@agency.com', '+1 312 456 7890', 'www.atlascityadventures.com','55 Cecil Stedman Ave, Chicago', 'USA'],
    [29, 'Nordic Escapes', 'nordicescapes@agency.com', '+47 22 123 456', 'www.nordicescapes.no', 'Karl Johans gate 1, Oslo 0154', 'Norway'],
    [30, 'Arabian Adventures', 'arabianadventures@agency.com', '+971 4 567 8901', 'www.arabianadventures.ae', 'Sheikh Zayed Road, Dubai', 'UAE'],
    [31, 'Cape and Safari Co', 'capesafari@agency.com', '+27 21 987 6543', 'www.capesafari.co.za', '12 Long Street, Cape Town 8001', 'South Africa'],
    [32, 'Hidden Leaf Getaways', 'hiddenleaf@agency.com', '+49 30 123 4567', 'www.hiddenleafgetaways.com', 'Ramen Street 7, Berlin 10117', 'Germany'],
    [33, 'Omni-Tours International','omnitours@agency.com', '+61 2 8765 4321', 'www.omnitours.com.au', '200 Viltrum Street, Sydney 2000', 'Australia'],
    [34, 'African Horizons', 'africanhorizons@agency.com', '+27 11 234 5678', 'www.africanhorizons.co.za', '56 Nelson Mandela Square, Sandton', 'South Africa'],
];

foreach ($agencies as $agency) {
    [$id, $name, $email, $phone, $website, $address, $country] = $agency;

    $pdo->prepare("INSERT IGNORE INTO `user` 
                   (user_id, email, password, role, created_at) 
                   VALUES (?, ?, ?, 'agency', NOW())")
        ->execute([$id, $email, 'hashed_pwd_' . $id]);

    $pdo->prepare("INSERT IGNORE INTO `travel_agency` 
                   (agency_id, company_name, contact_number, website, address, country) 
                   VALUES (?, ?, ?, ?, ?, ?)")
        ->execute([$id, $name, $phone, $website, $address, $country]);
}

echo "✓ " . count($agencies) . " agencies seeded\n";

//Seed some funny reviews
echo "\nSeeding reviews...\n";

$funnyReviews = [
    "I wish I could leave my kids here.",
    "Will I ever find my long lost airpod, but amazing place.",
    "Shikamaru said it was too troublesome to leave. High praise.",
    "I cant believe team32 made it here .",
    "Mark Grayson crash landed here on accident. Stayed for a week.",
    "Jiraiya called it research. We call it paradise.",
    "Rock Lee trained on the beach without ninjutsu. Still impressive.",
    "Kakashi read the whole Icha Icha series here undisturbed. Perfect.",
    "Tsunade won big at the casino then lost it all. Typical.",
    "I really love anime btw!",
    "Cecil booked the whole hotel out for a mission. Loved it anyway.",
    "Eve's force fields kept the mosquitoes away. 5 stars.",
    "Itachi visited once. Left no trace. As expected.",
    "Temari fanned away the clouds for a perfect sunny day somehow.",
    "Choji said the buffet was almost as good as chips. Legendary praise.",
];

// Add reviews from existing travellers (IDs 1-5) to existing packages (IDs 1-8)
$reviewId = 7; // dump already has reviews 1-6
$combinations = [
    [1, 6, null, 5], [2, null, 3, 5], [3, 8, null, 4], [4, null, 5, 5], [5, 6, null, 3], [1, null, 2, 4],
    [2, 7, null, 5], [3, null, 4, 4], [4, 9, null, 5], [5, null, 1, 4],
];

foreach ($combinations as $combo) {
    [$travellerId, $agencyId, $packageId, $rating] = $combo;
    $comment = $funnyReviews[array_rand($funnyReviews)];
    $date = date('Y-m-d H:i:s', strtotime('-' . rand(1, 60) . ' days'));

    try {
        $pdo->prepare("INSERT IGNORE INTO `review` 
                       (traveller_id, agency_id, package_id, rating, comment, created_date) 
                       VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$travellerId, $agencyId, $packageId, $rating, $comment, $date]);
        $reviewId++;
    } catch (Exception $e) {
        // Skip duplicates silently
    }
}

echo "✓ Reviews seeded\n";

//Seed Flights for all destinations
echo "\nSeeding flights...\n";

$flights = [
    ['AA201','American Airlines',1,6,'2026-08-01 10:00:00','2026-08-01 22:00:00',11500.00,10],
    ['UA301','United Airlines',1,11,'2026-08-12 09:00:00','2026-08-13 05:00:00',13200.00,22],
    ['KL101','KLM',1,20,'2026-08-15 08:00:00','2026-08-15 18:00:00',8900.00,23],
    ['NH201','ANA',1,21,'2026-09-10 11:00:00','2026-09-11 08:00:00',14500.00,27],
    ['AF101','Air France',1,22,'2026-06-10 07:00:00','2026-06-10 17:00:00',8500.00,26],
    ['SA201','South African Airways',1,23,'2026-07-01 08:00:00','2026-07-01 10:00:00',2500.00,31],
    ['VY301','Vueling',9,24,'2026-07-15 06:00:00','2026-07-15 09:00:00',3200.00,26],
    ['MH201','Malaysia Airlines',1,25,'2026-07-20 22:00:00','2026-07-21 16:00:00',15000.00,25],
    ['LA301','LATAM Airlines',1,26,'2026-09-01 20:00:00','2026-09-02 18:00:00',16500.00,25],
    ['JL301','Japan Airlines',8,27,'2026-05-15 09:00:00','2026-05-15 11:00:00',4500.00,27],
    ['TP201','TAP Air Portugal',7,28,'2026-06-20 07:00:00','2026-06-20 10:00:00',4200.00,26],
    ['KQ301','Kenya Airways',19,29,'2026-07-10 08:00:00','2026-07-10 11:00:00',3500.00,24],
    ['OK201','Czech Airlines',7,30,'2026-06-15 09:00:00','2026-06-15 11:00:00',3800.00,32],
    ['TG201','Thai Airways',1,31,'2026-08-01 23:00:00','2026-08-02 12:00:00',9800.00,25],
    ['OS201','Austrian Airlines',7,32,'2026-07-08 08:00:00','2026-07-08 10:00:00',3500.00,23],
    ['MS201','EgyptAir',7,33,'2026-06-25 06:00:00','2026-06-25 11:00:00',4800.00,30],
    ['AC201','Air Canada',11,34,'2026-07-20 08:00:00','2026-07-20 13:00:00',6500.00,28],
    ['TK201','Turkish Airlines',1,35,'2026-07-15 07:00:00','2026-07-15 15:00:00',7200.00,30],
    ['CU201','Cubana Airlines',6,36,'2026-12-01 10:00:00','2026-12-01 13:00:00',5500.00,28],
    ['NZ201','Air New Zealand',10,37,'2026-12-15 22:00:00','2026-12-16 10:00:00',12000.00,33],
    ['AZ201','ITA Airways',9,38,'2026-06-10 11:30:00','2026-06-10 12:30:00',2800.00,26],
    ['RJ201','Royal Jordanian',7,39,'2026-09-15 08:00:00','2026-09-15 13:00:00',5200.00,30],
    ['FI201','Icelandair',7,40,'2026-12-01 09:00:00','2026-12-01 13:00:00',6800.00,29],
];

foreach ($flights as $f) {
    [$number, $airline, $origin, $dest, $dep, $arr, $price, $agency] = $f;
    $pdo->prepare("INSERT IGNORE INTO `flight` 
                   (flight_number, airline, origin_destination_id, destination_id, departure_time, arrival_time, price, agency_id) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([$number, $airline, $origin, $dest, $dep, $arr, $price, $agency]);
}
echo "✓ " . count($flights) . " flights seeded\n";

//Seed Accommodation for all destinations
echo "\nSeeding accommodation...\n";

$accommodations = [
    ['Saxon Hotel Villas','36 Saxon Road, Sandhurst, Johannesburg',84,84,4500.00,2,6],
    ['The Dylan Amsterdam','Keizersgracht 384, Amsterdam',40,40,5800.00,20,23],
    ['Aman Tokyo','The Otemachi Tower, Tokyo',84,84,12000.00,21,27],
    ['Hotel Le Bristol Paris','112 Rue du Faubourg Saint-Honore, Paris',188,188,9500.00,22,26],
    ['The Silo Hotel','Silo Square, V&A Waterfront, Cape Town',28,28,8500.00,23,31],
    ['Hotel Arts Barcelona','Carrer de la Marina 19, Barcelona',483,483,7200.00,24,26],
    ['Gili Lankanfushi','North Male Atoll, Maldives',45,45,18000.00,25,30],
    ['Inkaterra Machu Picchu','Aguas Calientes, Machu Picchu',85,85,6500.00,26,25],
    ['Tawaraya Ryokan','Fuyacho Oike, Nakagyo-ku, Kyoto',18,18,9800.00,27,27],
    ['Bairro Alto Hotel','Praca Luis de Camoes 2, Lisbon',55,55,4800.00,28,26],
    ['Four Seasons Safari Lodge','Serengeti National Park, Tanzania',77,77,14000.00,29,24],
    ['Augustine Hotel Prague','Letenska 12, Prague',101,101,5200.00,30,32],
    ['Trisara Resort','60/1 Moo 6, Srisoonthorn Road, Phuket',39,39,7800.00,31,25],
    ['Hotel Sacher Wien','Philharmoniker Strasse 4, Vienna',149,149,6800.00,32,23],
    ['Four Seasons Cairo','1089 Corniche El Nil, Cairo',269,269,5500.00,33,30],
    ['Fairmont Pacific Rim','1038 Canada Place, Vancouver',377,377,6200.00,34,28],
    ['Ciragan Palace Kempinski','Ciragan Caddesi 32, Istanbul',313,313,7500.00,35,30],
    ['Hotel Nacional de Cuba','Calle 21 y O, Vedado, Havana',426,426,2800.00,36,28],
    ['Eichardt Private Hotel','Marine Parade, Queenstown',11,11,5500.00,37,33],
    ['Portrait Firenze','Lungarno degli Acciaiuoli 4, Florence',36,36,7200.00,38,26],
    ['Petra Marriott Hotel','Queen of Sheba Street, Petra',90,90,4200.00,39,30],
    ['Hotel Borg','Posthusstraeti 11, Reykjavik',99,99,5800.00,40,29],
];

foreach ($accommodations as $a) {
    [$name, $address, $bedrooms, $bathrooms, $price, $dest, $agency] = $a;
    $pdo->prepare("INSERT IGNORE INTO `accommodation` 
                   (name, address, no_bedrooms, no_bathrooms, price_per_night, destination_id, agency_id) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)")
        ->execute([$name, $address, $bedrooms, $bathrooms, $price, $dest, $agency]);
}
echo "✓ " . count($accommodations) . " accommodations seeded\n";

//Seed Transport for all destinations
echo "\nSeeding transport...\n";

$transports = [
    ['shuttle','Gautrain Airport Shuttle',2,2,'2026-08-01 09:00:00',350.00,6],
    ['car','Avis Kruger',3,3,'2026-07-15 06:00:00',850.00,7],
    ['train','Elizabeth Line',7,7,'2026-09-01 08:00:00',180.00,8],
    ['shuttle','NYC Airlink',11,11,'2026-08-12 10:00:00',450.00,22],
    ['shuttle','Bali Airport Taxi',12,12,'2026-08-01 11:00:00',250.00,25],
    ['train','Swiss Rail',14,14,'2026-07-08 09:00:00',380.00,23],
    ['taxi','Buenos Aires Remis',17,17,'2026-10-01 12:00:00',280.00,25],
    ['train','Tokyo Metro',21,21,'2026-09-10 09:00:00',220.00,27],
    ['train','Paris Metro',22,22,'2026-06-10 08:00:00',180.00,26],
    ['shuttle','Cape Town MyCiti Bus',23,23,'2026-07-01 09:00:00',120.00,31],
    ['bus','Barcelona Aerobus',24,24,'2026-07-15 07:00:00',160.00,26],
    ['boat','Maldives Speedboat',25,25,'2026-07-20 10:00:00',1200.00,30],
    ['bus','Peru Hop',26,26,'2026-09-01 07:00:00',450.00,25],
    ['train','Kyoto City Bus',27,27,'2026-05-15 08:00:00',150.00,27],
    ['train','Lisbon Metro',28,28,'2026-06-20 09:00:00',140.00,26],
    ['car','Serengeti Safari Jeep',29,29,'2026-07-10 06:00:00',1800.00,24],
    ['train','Prague Metro',30,30,'2026-06-15 08:00:00',120.00,32],
    ['shuttle','Phuket Limousine',31,31,'2026-08-01 10:00:00',650.00,25],
    ['train','Vienna U-Bahn',32,32,'2026-07-08 09:00:00',160.00,23],
    ['bus','Cairo Airport Shuttle',33,33,'2026-06-25 08:00:00',200.00,30],
    ['train','Canada Line',34,34,'2026-07-20 09:00:00',280.00,28],
    ['train','Istanbul Metro',35,35,'2026-07-15 08:00:00',180.00,30],
    ['taxi','Havana Classic Car',36,36,'2026-12-01 09:00:00',350.00,28],
    ['shuttle','Queenstown Airport Transfer',37,37,'2026-12-15 11:00:00',280.00,33],
    ['train','Florence Tramvia',38,38,'2026-06-10 09:00:00',140.00,26],
    ['car','Petra Wadi Rum Jeep',39,39,'2026-09-15 07:00:00',950.00,30],
    ['bus','Reykjavik City Bus',40,40,'2026-12-01 10:00:00',280.00,29],
];

foreach ($transports as $t) {
    [$type, $provider, $origin, $dest, $dep, $price, $agency] = $t;
    $pdo->prepare("INSERT IGNORE INTO `transport` 
                   (type, provider, origin_destination_id, destination_id, departure_time, price, agency_id) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)")
        ->execute([$type, $provider, $origin, $dest, $dep, $price, $agency]);
}
echo "✓ " . count($transports) . " transport records seeded\n";

//Seed Activities for all destinations
echo "\nSeeding activities...\n";

$activities = [
    ['Apartheid Museum','Northern Parkway, Johannesburg','Johannesburg',280.00,'Moving tribute to South Africa struggle for freedom','2026-08-02 09:00:00','2026-08-02 13:00:00',2,6,'Museum'],
    ['Gold Reef City','Northern Parkway, Johannesburg','Johannesburg',350.00,'Theme park built on historic gold mine','2026-08-03 10:00:00','2026-08-03 17:00:00',2,6,'Historical Site'],
    ['Trumps Grill','Trumps Steakhouse, Johannesburg','Johannesburg',1200.00,'Best steakhouse in Johannesburg','2026-08-03 19:00:00','2026-08-03 22:00:00',2,6,'Restaurant'],
    ['Tower of London','Tower Hill, London','London',380.00,'Historic castle and home of Crown Jewels','2026-09-02 09:00:00','2026-09-02 13:00:00',7,8,'Historical Site'],
    ['British Museum','Great Russell Street, London','London',0.00,'World-class museum of human history','2026-09-03 10:00:00','2026-09-03 16:00:00',7,8,'Museum'],
    ['Dishoom','12 Upper St Martins Lane, London','London',850.00,'Legendary Bombay cafe in Covent Garden','2026-09-03 19:00:00','2026-09-03 22:00:00',7,8,'Restaurant'],
    ['Senso-ji Temple','2 Chome-3-1 Asakusa, Tokyo','Tokyo',0.00,'Tokyos oldest and most significant temple','2026-09-11 08:00:00','2026-09-11 12:00:00',8,9,'Historical Site'],
    ['teamLab Planets','6-1-16 Toyosu, Koto, Tokyo','Tokyo',950.00,'Immersive digital art museum','2026-09-12 10:00:00','2026-09-12 14:00:00',8,9,'Museum'],
    ['Sukiyabashi Jiro','4 Chome-2-15 Ginza, Tokyo','Tokyo',18000.00,'World famous sushi restaurant','2026-09-12 19:00:00','2026-09-12 21:00:00',8,9,'Restaurant'],
    ['Lake Zurich Cruise','Burkliplatz, Zurich','Zurich',280.00,'Scenic boat cruise on Lake Zurich','2026-07-09 10:00:00','2026-07-09 12:00:00',14,23,'Nature Tour'],
    ['Swiss National Museum','Museumstrasse 2, Zurich','Zurich',180.00,'Largest cultural history museum in Switzerland','2026-07-10 09:00:00','2026-07-10 13:00:00',14,23,'Museum'],
    ['Kronenhalle','Ramistrasse 4, Zurich','Zurich',2200.00,'Historic restaurant with original Picasso artworks','2026-07-10 19:00:00','2026-07-10 22:00:00',14,23,'Restaurant'],
    ['La Boca Neighbourhood Tour','La Boca, Buenos Aires','Buenos Aires',320.00,'Colourful tango district walking tour','2026-10-02 10:00:00','2026-10-02 13:00:00',17,25,'Historical Site'],
    ['Tango Show at Cafe Tortoni','Av. de Mayo 825, Buenos Aires','Buenos Aires',1800.00,'World famous cafe with nightly tango shows','2026-10-02 20:00:00','2026-10-02 23:00:00',17,25,'Restaurant'],
    ['Senso-ji Temple Kyoto','Higashiyama, Kyoto','Kyoto',0.00,'Ancient Buddhist temple in Kyoto','2026-09-11 08:00:00','2026-09-11 12:00:00',21,27,'Historical Site'],
    ['Arashiyama Bamboo Grove','Sagatenryuji, Kyoto','Kyoto',0.00,'Famous bamboo forest walk','2026-09-12 07:00:00','2026-09-12 10:00:00',21,27,'Nature Tour'],
    ['Kikunoi Honten','459 Shimokawara-cho, Kyoto','Kyoto',4500.00,'Three Michelin star kaiseki restaurant','2026-09-12 19:00:00','2026-09-12 22:00:00',21,27,'Restaurant'],
    ['Louvre Museum','Rue de Rivoli, Paris','Paris',380.00,'Worlds largest art museum','2026-06-11 09:00:00','2026-06-11 15:00:00',22,26,'Museum'],
    ['Versailles Palace Tour','Place d Armes, Versailles','Paris',450.00,'Magnificent royal palace and gardens','2026-06-12 09:00:00','2026-06-12 16:00:00',22,26,'Historical Site'],
    ['Le Jules Verne','Eiffel Tower 2nd Floor, Paris','Paris',4800.00,'Michelin star restaurant inside Eiffel Tower','2026-06-12 19:30:00','2026-06-12 22:00:00',22,26,'Restaurant'],
    ['Cape Point Nature Reserve','Cape Point, Cape Town','Cape Town',280.00,'Stunning clifftop reserve at tip of Cape Peninsula','2026-07-02 09:00:00','2026-07-02 14:00:00',23,31,'Nature Tour'],
    ['District Six Museum','25A Buitenkant Street, Cape Town','Cape Town',180.00,'Moving museum about forced removals','2026-07-03 10:00:00','2026-07-03 13:00:00',23,31,'Historical Site'],
    ['La Colombe','Silvermist Wine Estate, Cape Town','Cape Town',2800.00,'Award winning fine dining with mountain views','2026-07-03 19:00:00','2026-07-03 22:00:00',23,31,'Restaurant'],
    ['Sagrada Familia','Carrer de Mallorca 401, Barcelona','Barcelona',380.00,'Gaudis iconic unfinished masterpiece','2026-07-16 09:00:00','2026-07-16 13:00:00',24,26,'Landmark'],
    ['Park Guell','08024 Barcelona','Barcelona',280.00,'Gaudis colourful mosaic park','2026-07-17 09:00:00','2026-07-17 12:00:00',24,26,'Nature Tour'],
    ['El Celler de Can Roca','Can Sunyer 48, Girona','Barcelona',6500.00,'Three Michelin star restaurant near Barcelona','2026-07-17 20:00:00','2026-07-17 23:00:00',24,26,'Restaurant'],
    ['Maldives Snorkelling','North Male Atoll, Maldives','Maldives',850.00,'Snorkel with manta rays and whale sharks','2026-07-21 09:00:00','2026-07-21 13:00:00',25,30,'Nature Tour'],
    ['Underwater Restaurant','5.8 Undersea Restaurant, Maldives','Maldives',4500.00,'Worlds largest underwater restaurant','2026-07-21 19:00:00','2026-07-21 21:00:00',25,30,'Restaurant'],
    ['Machu Picchu Citadel','Machu Picchu, Aguas Calientes','Machu Picchu',650.00,'Ancient Incan citadel in the clouds','2026-09-02 06:00:00','2026-09-02 14:00:00',26,25,'Historical Site'],
    ['Rainbow Mountain Hike','Vinicunca, Cusco Region','Machu Picchu',850.00,'Stunning hike to colourful Vinicunca mountain','2026-09-03 05:00:00','2026-09-03 15:00:00',26,25,'Nature Tour'],
    ['Fushimi Inari Shrine','68 Fukakusa Yabunouchicho, Kyoto','Kyoto',0.00,'Thousands of torii gates up Mount Inari','2026-05-16 07:00:00','2026-05-16 11:00:00',27,27,'Historical Site'],
    ['Nishiki Market','Nishiki Market, Kyoto','Kyoto',0.00,'Famous food market in central Kyoto','2026-05-16 12:00:00','2026-05-16 15:00:00',27,27,'Nature Tour'],
    ['Mizai Restaurant','Saginoyu Nakagyo-ku, Kyoto','Kyoto',5500.00,'Exclusive kaiseki cuisine with garden view','2026-05-16 19:00:00','2026-05-16 22:00:00',27,27,'Restaurant'],
    ['Belem Tower','Av. Brasilia, Lisbon','Lisbon',120.00,'16th century fortress on the Tagus River','2026-06-21 09:00:00','2026-06-21 12:00:00',28,26,'Historical Site'],
    ['Alfama District Walk','Alfama, Lisbon','Lisbon',280.00,'Guided walk through oldest neighbourhood','2026-06-22 10:00:00','2026-06-22 13:00:00',28,26,'Historical Site'],
    ['Belcanto','Largo de Sao Carlos 10, Lisbon','Lisbon',3200.00,'Two Michelin star Portuguese cuisine','2026-06-22 19:30:00','2026-06-22 22:30:00',28,26,'Restaurant'],
    ['Serengeti Game Drive','Serengeti National Park','Serengeti',4500.00,'Full day big five game drive','2026-07-11 06:00:00','2026-07-11 18:00:00',29,24,'Wildlife Safari'],
    ['Hot Air Balloon Safari','Serengeti National Park','Serengeti',8500.00,'Sunrise balloon ride over the Serengeti','2026-07-12 05:30:00','2026-07-12 09:00:00',29,24,'Nature Tour'],
    ['Prague Castle','Hradcany, Prague','Prague',280.00,'Largest ancient castle complex in the world','2026-06-16 09:00:00','2026-06-16 13:00:00',30,32,'Historical Site'],
    ['Charles Bridge Walk','Mostecka, Prague','Prague',0.00,'Iconic medieval stone bridge with statues','2026-06-17 08:00:00','2026-06-17 10:00:00',30,32,'Historical Site'],
    ['Cafe Savoy','Vitezna 124/5, Prague','Prague',850.00,'Historic neo-renaissance cafe in Prague','2026-06-17 19:00:00','2026-06-17 22:00:00',30,32,'Restaurant'],
    ['Phi Phi Island Tour','Chalong Pier, Phuket','Phuket',1200.00,'Full day speedboat tour to Phi Phi Islands','2026-08-02 08:00:00','2026-08-02 17:00:00',31,25,'Nature Tour'],
    ['Big Buddha','Maret, Ko Samui','Phuket',0.00,'45 metre white marble Buddha statue','2026-08-03 09:00:00','2026-08-03 12:00:00',31,25,'Landmark'],
    ['Suay Restaurant','Coconut Village Resort, Phuket','Phuket',1800.00,'Award winning Thai fusion cuisine','2026-08-03 19:00:00','2026-08-03 22:00:00',31,25,'Restaurant'],
    ['Schonbrunn Palace','Schonbrunner Schlossstrasse, Vienna','Vienna',280.00,'Imperial Habsburg palace with 1441 rooms','2026-07-09 09:00:00','2026-07-09 13:00:00',32,23,'Historical Site'],
    ['Vienna State Opera Tour','Opernring 2, Vienna','Vienna',180.00,'Behind scenes tour of world famous opera house','2026-07-10 10:00:00','2026-07-10 12:00:00',32,23,'Landmark'],
    ['Steirereck im Stadtpark','Am Heumarkt 2A, Vienna','Vienna',3500.00,'Two Michelin star Austrian fine dining','2026-07-10 19:00:00','2026-07-10 22:00:00',32,23,'Restaurant'],
    ['Pyramids of Giza','Al Haram, Giza','Cairo',350.00,'Visit the last surviving ancient wonder','2026-06-26 07:00:00','2026-06-26 13:00:00',33,30,'Historical Site'],
    ['Egyptian Museum','Tahrir Square, Cairo','Cairo',180.00,'Worlds greatest collection of ancient Egyptian art','2026-06-27 09:00:00','2026-06-27 14:00:00',33,30,'Museum'],
    ['Koshary El Tahrir','Tahrir Square, Cairo','Cairo',150.00,'Famous Egyptian street food restaurant','2026-06-27 13:00:00','2026-06-27 14:00:00',33,30,'Restaurant'],
    ['Capilano Suspension Bridge','3735 Capilano Road, Vancouver','Vancouver',380.00,'Famous suspension bridge in temperate rainforest','2026-07-21 09:00:00','2026-07-21 13:00:00',34,28,'Nature Tour'],
    ['Museum of Anthropology','6393 NW Marine Drive, Vancouver','Vancouver',220.00,'World class collection of Northwest Coast art','2026-07-22 10:00:00','2026-07-22 14:00:00',34,28,'Museum'],
    ['Hawksworth Restaurant','801 W Georgia Street, Vancouver','Vancouver',1800.00,'Award winning contemporary Canadian cuisine','2026-07-22 19:00:00','2026-07-22 22:00:00',34,28,'Restaurant'],
    ['Hagia Sophia','Sultan Ahmet, Istanbul','Istanbul',0.00,'Magnificent Byzantine cathedral turned mosque','2026-07-16 09:00:00','2026-07-16 13:00:00',35,30,'Historical Site'],
    ['Grand Bazaar','Beyazit, Istanbul','Istanbul',0.00,'One of worlds oldest and largest covered markets','2026-07-17 10:00:00','2026-07-17 15:00:00',35,30,'Historical Site'],
    ['Mikla Restaurant','Marmara Pera Hotel, Istanbul','Istanbul',2800.00,'Rooftop restaurant with stunning Bosphorus views','2026-07-17 19:00:00','2026-07-17 22:00:00',35,30,'Restaurant'],
    ['Old Havana Walking Tour','Habana Vieja, Havana','Havana',280.00,'UNESCO heritage walking tour of colonial Havana','2026-12-02 09:00:00','2026-12-02 13:00:00',36,28,'Historical Site'],
    ['Classic Car Tour','Malecon, Havana','Havana',650.00,'Tour Havana in a vintage 1950s American car','2026-12-03 10:00:00','2026-12-03 13:00:00',36,28,'Historical Site'],
    ['La Guarida','Concordia 418, Havana','Havana',1200.00,'Famous paladar restaurant in a crumbling mansion','2026-12-03 19:00:00','2026-12-03 22:00:00',36,28,'Restaurant'],
    ['Milford Sound Cruise','Milford Sound, Queenstown','Queenstown',1200.00,'Scenic cruise through dramatic fiord','2026-12-16 09:00:00','2026-12-16 14:00:00',37,33,'Nature Tour'],
    ['Bungee Jumping','Kawarau Bridge, Queenstown','Queenstown',850.00,'World first commercial bungee jump site','2026-12-17 10:00:00','2026-12-17 12:00:00',37,33,'Nature Tour'],
    ['Rata Restaurant','43 Ballarat Street, Queenstown','Queenstown',1800.00,'Award winning New Zealand cuisine','2026-12-17 19:00:00','2026-12-17 22:00:00',37,33,'Restaurant'],
    ['Uffizi Gallery','Piazzale degli Uffizi, Florence','Florence',280.00,'World class Renaissance art gallery','2026-06-11 09:00:00','2026-06-11 14:00:00',38,26,'Museum'],
    ['Ponte Vecchio','Ponte Vecchio, Florence','Florence',0.00,'Medieval bridge lined with jewellery shops','2026-06-12 10:00:00','2026-06-12 12:00:00',38,26,'Landmark'],
    ['Enoteca Pinchiorri','Via Ghibellina 87, Florence','Florence',4500.00,'Three Michelin star Tuscan fine dining','2026-06-12 19:30:00','2026-06-12 22:30:00',38,26,'Restaurant'],
    ['Petra Treasury','Wadi Musa, Petra','Petra',450.00,'Iconic rock-carved facade of Al Khazneh','2026-09-16 07:00:00','2026-09-16 13:00:00',39,30,'Historical Site'],
    ['Petra by Night','Wadi Musa, Petra','Petra',650.00,'Magical candlelit walk to the Treasury','2026-09-16 20:00:00','2026-09-16 22:30:00',39,30,'Historical Site'],
    ['The Basin Restaurant','Wadi Musa, Petra','Petra',850.00,'Traditional Jordanian cuisine with Petra views','2026-09-17 19:00:00','2026-09-17 22:00:00',39,30,'Restaurant'],
    ['Northern Lights Tour','Reykjavik Outskirts','Reykjavik',1800.00,'Evening hunt for the Aurora Borealis','2026-12-02 21:00:00','2026-12-03 01:00:00',40,29,'Nature Tour'],
    ['Blue Lagoon Geothermal Spa','Nordurvegur 9, Grindavik','Reykjavik',1200.00,'World famous geothermal spa','2026-12-03 10:00:00','2026-12-03 14:00:00',40,29,'Nature Tour'],
    ['Dill Restaurant','Hverfisgata 12, Reykjavik','Reykjavik',3200.00,'New Nordic cuisine with Icelandic ingredients','2026-12-03 19:00:00','2026-12-03 22:00:00',40,29,'Restaurant'],
];

foreach ($activities as $a) {
    [$name, $address, $city, $price, $desc, $start, $end, $dest, $agency, $type] = $a;
    $pdo->prepare("INSERT IGNORE INTO `activity` 
                   (name, address, city, price, description, start_time, end_time, destination_id, agency_id, activity_type) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([$name, $address, $city, $price, $desc, $start, $end, $dest, $agency, $type]);
}
echo "✓ " . count($activities) . " activities seeded\n";

echo "\n All done! Tripistry database seeded successfully.\n";
echo " 300 travellers | 40 destinations | 8 agencies | 47 flights | 46 accommodations | 44 transport | 100 activities | funny reviews\n";