<?php
/* This is our AI travel assistant powered by Google Gemini, the free tier
 we got our free API key at: https://aistudio.google.com/apikey
 it receives a user message, fetches relevant DB context, sends it to the to Gemini API, and returns the response as Json
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// CSRF check
if (!verify_csrf()) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid request.']);
    exit;
}

// *** PASTE GEMINI API KEY HERE ***

require_once __DIR__ . '/../config/secrets.php';
$api_key = GEMINI_API_KEY;

$input   = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');

if (!$message) {
    echo json_encode(['error' => 'No message provided.']);
    exit;
}

// ── Fetch live DB context to ground the AI ───────────────────
$db = get_db();

$packages = $db->query("
    SELECT tp.name, tp.base_price, tp.duration_days, tp.description,
           ta.company_name,
           ROUND(AVG(r.rating), 1) AS avg_rating
    FROM travel_package tp
    JOIN travel_agency ta ON ta.agency_id = tp.agency_id
    LEFT JOIN review r ON r.package_id = tp.package_id
    GROUP BY tp.package_id
    ORDER BY avg_rating DESC
    LIMIT 10
")->fetchAll();

$destinations = $db->query("
    SELECT city_name, country, popular_season, description
    FROM destination
    LIMIT 10
")->fetchAll();

// Build plain-text summary for the AI
$pkg_text = "Available travel packages on Tripistry:\n";
foreach ($packages as $p) {
    $pkg_text .= "- \"{$p['name']}\" by {$p['company_name']}: R{$p['base_price']}";
    if ($p['duration_days']) $pkg_text .= ", {$p['duration_days']} days";
    if ($p['avg_rating'])    $pkg_text .= ", rated {$p['avg_rating']}/5";
    if ($p['description'])   $pkg_text .= ". " . mb_strimwidth($p['description'], 0, 80, '...');
    $pkg_text .= "\n";
}

$dest_text = "Available destinations:\n";
foreach ($destinations as $d) {
    $dest_text .= "- {$d['city_name']}, {$d['country']}";
    if ($d['popular_season']) $dest_text .= " (best season: {$d['popular_season']})";
    $dest_text .= "\n";
}

// ── Build the prompt ─────────────────────────────────────────
$system_context = "You are a friendly and helpful travel assistant for Tripistry, an online travel booking platform based in South Africa.
Your job is to help users choose travel packages and destinations.
Keep all responses short and helpful — 2 to 4 sentences maximum.
Only recommend packages and destinations that exist in the data provided below.
If asked something unrelated to travel or Tripistry, politely redirect the conversation back to travel.
Always be warm, enthusiastic, and encouraging about travel.

$pkg_text

$dest_text";

// Gemini expects the system instruction merged into the first user turn
$full_prompt = $system_context . "\n\nUser question: " . $message;

// ── Call Google Gemini API ───────────────────────────────────
// Using gemini-1.5-flash — fast and free tier available
$gemini_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=" . urlencode($api_key);

$payload = json_encode([
    'contents' => [
        [
            'parts' => [
                ['text' => $full_prompt]
            ]
        ]
    ],
    'generationConfig' => [
        'maxOutputTokens' => 300,
        'temperature'     => 0.7,
    ]
]);

$ch = curl_init($gemini_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
    ],
    CURLOPT_TIMEOUT => 15,
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err = curl_error($ch);
curl_close($ch);

// ------------------------- Handling possible errors 
if ($curl_err) 
    {
    echo json_encode(['error' => 'Connection failed. Is curl enabled in php.ini?']);
    exit;
}

if ($http_code !== 200) 
{
    $err_data = json_decode($response, true);
    $err_msg  = $err_data['error']['message'] ?? 'AI service unavailable. Check your API key.';
    echo json_encode(['error' => $err_msg]);
    exit;
}

// -------------------- Extracting a reply mssg
$data = json_decode($response, true);
$reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not generate a response. Please try again.';

// Cleaning up any markdown formatting Gemini might add
$reply = str_replace(['**', '*', '##', '#'], '', $reply);
$reply = trim($reply);

echo json_encode(['reply' => $reply]);