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
    //  surnames
    'Grayson', 'Bennett', 'Haeberle', 'Walker', 'Wilkins', 'Slott',
    //  surnames
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
    $lastName  = $lastNames[array_rand($lastNames)];

    // Unique email using ID to avoid duplicates
    $email     = strtolower($firstName . '.' . $lastName . $id . '@' . $domains[array_rand($domains)]);
    $password  = 'hashed_pwd_' . $id;
    $createdAt = date('Y-m-d H:i:s', strtotime('-' . rand(1, 500) . ' days'));

    // Insert user
    $pdo->prepare("INSERT IGNORE INTO `user` 
                   (user_id, email, password, role, created_at) 
                   VALUES (?, ?, ?, 'traveller', ?)")
        ->execute([$id, $email, $password, $createdAt]);

    // Generating the cap data
    $phone     = '+27 ' . rand(60, 89) . ' ' . rand(100, 999) . ' ' . rand(1000, 9999);
    $city      = $cities[array_rand($cities)];
    $address   = rand(1, 250) . ' ' . ['Ramen Street', 'Sharingan Ave', 'Omni-Man Road', 
                  'Chakra Lane', 'Invincible Blvd', 'Main Road', 'Oak Street', 
                  'Sunset Drive', 'Rasengan Road'][rand(0,8)] . ', ' . $city;
    $idNumber  = rand(800101, 991231) . ' ' . rand(1000, 9999) . ' 0' . rand(80, 99);
    $passport  = chr(rand(65, 90)) . rand(10000000, 99999999);

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
    [21, 'Tokyo',        'Japan',          'This is where we anime noobs ninja run',          'Mar-May, Oct-Nov', 'https://images.unsplash.com/photo-1540959733332-eab4deabeeaf?w=800'],
    [22, 'Paris',        'France',         'I lost my wife to a baguette in this city, L place',          'Apr-Jun, Sep-Oct', 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=800'],
    [23, 'Cape Town',    'South Africa',   'Like most crime in the world, but nice views',     'Nov-Mar',          'https://images.unsplash.com/photo-1580060839134-75a5edca2e99?w=800'],
    [24, 'Barcelona',    'Spain',          'People think the goat is from here',     'May-Jun, Sep-Oct', 'https://images.unsplash.com/photo-1539037116277-4db20889f2d4?w=800'],
    [25, 'Maldives',     'Maldives',       'Do not get the hype shout out Lilly and Dian fr!',   'Nov-Apr',          'https://images.unsplash.com/photo-1573843981267-be1999ff37cd?w=800'],
    [26, 'Machu Picchu', 'Peru',           'lost to the spanish', 'May-Oct',          'https://images.unsplash.com/photo-1526392060635-9d6019884377?w=800'],
    [27, 'Kyoto',        'Japan',          'point mountains amazing view',   'Mar-May, Oct-Nov', 'https://images.unsplash.com/photo-1545569341-9eb8b30979d9?w=800'],
    [28, 'Lisbon',       'Portugal',       'This is really where the goat is from SUI',     'Mar-May, Sep-Oct', 'https://images.unsplash.com/photo-1555881400-74d7acaacd8b?w=800'],
    [29, 'Serengeti',    'Tanzania',       'Wildlife so intense even Mark Grayson was impressed',           'Jun-Oct',          'https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?w=800'],
    [30, 'Prague',       'Czech Republic', 'Fairy tale city — Shikamaru called it too troublesome to leave','Apr-May, Sep-Oct', 'https://images.unsplash.com/photo-1519677100203-a0e668c92439?w=800'],
    [31, 'Phuket',       'Thailand',       'Beach paradise — even Gaara took his sand shoes off here',      'Nov-Apr',          'https://images.unsplash.com/photo-1508009603885-50cf7c579365?w=800'],
    [32, 'Vienna',       'Austria',        'We dont talk about what happened here, great people though',           'Apr-May, Sep-Oct', 'https://images.unsplash.com/photo-1516550893923-42d28e5677af?w=800'],
    [33, 'Cairo',        'Egypt',          'Ancient pyramids older than even the Sage of Six Paths',        'Oct-Apr',          'https://images.unsplash.com/photo-1572252009286-268acec5ca0a?w=800'],
    [34, 'Vancouver',    'Canada',         'Six god City Iceman!!! great to see the boy',   'Jun-Sep',          'https://images.unsplash.com/photo-1560814304-4f05b62af116?w=800'],
    [35, 'Istanbul',     'Turkey',         'City bridging two worlds — like Obito bridging two dimensions', 'Apr-May, Sep-Oct', 'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?w=800'],
    [36, 'Havana',       'Cuba',           'amaxzin beahces', 'Dec-Apr',          'https://images.unsplash.com/photo-1500759285222-a95626b934cb?w=800'],
    [37, 'Queenstown',   'New Zealand',    'Adventure capital — Invincible goes here on days off',          'Dec-Feb, Jun-Aug', 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800'],
    [38, 'Florence',     'Italy',          'Renaissance art capital — Ino said the flowers were divine',    'Apr-Jun, Sep-Oct', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=800'],
    [39, 'Petra',        'Jordan',         'Rose-red city carved in rock — Yamato was jealous',             'Mar-May, Sep-Nov', 'https://images.unsplash.com/photo-1548786811-dd6e453ccca7?w=800'],
    [40, 'Reykjavik',    'Iceland',        'Northern Lights so bright even the Sharingan cannot copy them', 'Jun-Aug, Dec-Feb', 'https://images.unsplash.com/photo-1529963183134-61a90db47eaf?w=800'],
    [40, 'ItBuilding',    'UniPain',        'I really love comp sci so much', 'Jun-Aug, Dec-Feb', 'https://tenor.com/search/funny-meme-gifs'],
];  

foreach ($destinations as $dest) {
    [$id, $city, $country, $description, $season, $image] = $dest;
    $pdo->prepare("INSERT IGNORE INTO `destination` 
                   (destination_id, city_name, country, description, popular_season, image_url) 
                   VALUES (?, ?, ?, ?, ?, ?)")
        ->execute([$id, $city, $country, $description, $season, $image]);
}

echo "✓ " . count($destinations) . " destinations seeded\n";

// ── Seed New Agencies ──
echo "\nSeeding agencies...\n";

// Dump has 6-10, population adds 22-26, so we start at 27
$agencies = [
    [27, 'Konoha Travel Co',       'konoha.travel@agency.com',     '+81 3 1234 5678',  'www.konohatravel.jp',        '1 Hokage Rock Road, Konoha',           'Japan'],
    [28, 'Atlas City Adventures',  'atlascity@agency.com',         '+1 312 456 7890',  'www.atlascityadventures.com','55 Cecil Stedman Ave, Chicago',         'USA'],
    [29, 'Nordic Escapes',         'nordicescapes@agency.com',     '+47 22 123 456',   'www.nordicescapes.no',       'Karl Johans gate 1, Oslo 0154',         'Norway'],
    [30, 'Arabian Adventures',     'arabianadventures@agency.com', '+971 4 567 8901',  'www.arabianadventures.ae',   'Sheikh Zayed Road, Dubai',              'UAE'],
    [31, 'Cape and Safari Co',     'capesafari@agency.com',        '+27 21 987 6543',  'www.capesafari.co.za',       '12 Long Street, Cape Town 8001',        'South Africa'],
    [32, 'Hidden Leaf Getaways',   'hiddenleaf@agency.com',        '+49 30 123 4567',  'www.hiddenleafgetaways.com', 'Ramen Street 7, Berlin 10117',          'Germany'],
    [33, 'Omni-Tours International','omnitours@agency.com',        '+61 2 8765 4321',  'www.omnitours.com.au',       '200 Viltrum Street, Sydney 2000',       'Australia'],
    [34, 'African Horizons',       'africanhorizons@agency.com',   '+27 11 234 5678',  'www.africanhorizons.co.za',  '56 Nelson Mandela Square, Sandton',     'South Africa'],
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

// ── Seed some funny reviews ──
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
    [1, 6, null, 5], [2, null, 3, 5], [3, 8, null, 4],
    [4, null, 5, 5], [5, 6, null, 3], [1, null, 2, 4],
    [2, 7, null, 5], [3, null, 4, 4], [4, 9, null, 5],
    [5, null, 1, 4],
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

echo "\n All done! Tripistry database seeded successfully.\n";
echo " 300 travellers | 21 destinations | 8 agencies | funny reviews\n";