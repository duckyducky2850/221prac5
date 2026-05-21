<?php
/*
ACTION ITEMS

Database -> ALTER TABLE 'review' ADD COLUMN 'sentiment' VARCHAR(20) DEFAULT NULL;
Database -> add sentiments to all users so it works from now on

*/

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config/db.php';

use nlpcloud\NlpCloudClient;

$NLP_CLOUD_API_KEY = '804149c76719205c1e48abc933a75a9affc96a82';

/*
Analyse sentiment of a review using AI
*/
function analyseSentiment($reviewText) {
    global $NLP_CLOUD_API_KEY;
    
    // Return neutral for empty comments
    if (empty($reviewText)) {
        return 'neutral';
    }
    
    try {
        $client = new NlpCloudClient(
            $NLP_CLOUD_API_KEY,
            'distilbert-base-uncased-finetuned-sst-2-english'
        );
        
        $result = $client->sentiment($reviewText);
        
        // The API returns labels like 'POSITIVE' or 'NEGATIVE'
        $label = $result['scored_labels'][0]['label'];
        
        if ($label == 'POSITIVE') {
            return 'positive';
        } elseif ($label == 'NEGATIVE') {
            return 'negative';
        } else {
            return 'neutral';
        }
        
    } catch (Exception $e) {
        error_log("Sentiment analysis failed: " . $e->getMessage());
        return 'neutral';
    }
}

/*
Get sentiment badge HTML
*/
function getSentimentBadge($sentiment) {
    switch ($sentiment) {
        case 'positive':
            return '<span class="sentiment-badge positive">👍 Positive</span>';
        case 'negative':
            return '<span class="sentiment-badge negative">👎 Negative</span>';
        case 'neutral':
            return '<span class="sentiment-badge neutral">😐 Neutral</span>';
        default:
            return '<span class="sentiment-badge unknown">❓ Not analysed</span>';
    }
}

/*
Save review with sentiment analysis
*/
function saveReviewWithSentiment($travellerId, $agencyId, $packageId, $rating, $comment) {
    $db = get_db();
    
    $sentiment = analyseSentiment($comment);
    
    $sql = "INSERT INTO review (traveller_id, agency_id, package_id, rating, comment, sentiment, created_date) 
            VALUES (:traveller_id, :agency_id, :package_id, :rating, :comment, :sentiment, NOW())";
    
    $stmt = $db->prepare($sql);
    $result = $stmt->execute([
        ':traveller_id' => $travellerId,
        ':agency_id' => $agencyId,
        ':package_id' => $packageId,
        ':rating' => $rating,
        ':comment' => $comment,
        ':sentiment' => $sentiment
    ]);
    
    if ($result) {
        return ['success' => true, 'sentiment' => $sentiment];
    } else {
        return ['success' => false, 'error' => $stmt->errorInfo()];
    }
}

/*
Get reviews with sentiment badges for a package
*/
function getReviewsWithSentiment($packageId) {
    $db = get_db();
    
    $sql = "SELECT r.*, t.first_name, t.last_name 
            FROM review r
            JOIN traveller t ON r.traveller_id = t.traveller_id
            WHERE r.package_id = ?
            ORDER BY r.created_date DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$packageId]);
    
    $reviews = $stmt->fetchAll();
    
    // Add the sentiment badge HTML to each review
    foreach ($reviews as &$review) {
        $review['sentiment_badge'] = getSentimentBadge($review['sentiment'] ?? null);
    }
    
    return $reviews;
}