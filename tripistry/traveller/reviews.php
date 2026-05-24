<?php
/*Write and view my reviews*/
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../review_functions.php';

require_role('traveller');

$db  = get_db();
$uid = current_user_id();

// Prefill from URL param
$pre_package = (int)($_GET['package_id'] ?? 0);
$pre_agency = (int)($_GET['agency_id'] ?? 0);

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { $errors[] = 'Invalid request.'; }
    else {
        $rating = (int)($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        $package_id = (int)($_POST['package_id'] ?? 0) ?: null;
        $agency_id = (int)($_POST['agency_id']  ?? 0) ?: null;
        $sentiment = analyseSentiment($comment);

        if ($rating < 1 || $rating > 5)
            $errors[] = 'Please select a star rating (1–5).';
        if (!$package_id && !$agency_id)
            $errors[] = 'Select a package or agency to review.';

        if (empty($errors)) 
        {
                $stmt = $db->prepare("INSERT INTO review (traveller_id, agency_id, package_id, rating, comment, sentiment) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$uid, $agency_id, $package_id, $rating, $comment ?: null, $sentiment]);

                // updates the avg_rating if a package was reviewed
                if ($package_id) 
                {
                    $upd = $db->prepare("UPDATE travel_package SET avg_rating = (SELECT ROUND(AVG(rating), 2) FROM review WHERE package_id = ?) WHERE package_id = ?");
                    $upd->execute([$package_id, $package_id]);
                }

                set_flash('success', 'Review submitted. Thank you!');
                header('Location: ' . BASE_URL . '/traveller/reviews.php'); exit;
            }
    }
}

// My past reviews
$stmt = $db->prepare("
    SELECT r.*, tp.name AS package_name, ta.company_name AS agency_name
    FROM review r
    LEFT JOIN travel_package tp ON tp.package_id = r.package_id
    LEFT JOIN travel_agency ta  ON ta.agency_id  = r.agency_id
    WHERE r.traveller_id = ?
    ORDER BY r.created_date DESC
");
$stmt->execute([$uid]);
$my_reviews = $stmt->fetchAll();

// Dropdown data
$packages = $db->query("SELECT tp.package_id, tp.name, ta.company_name FROM travel_package tp JOIN travel_agency ta ON ta.agency_id=tp.agency_id ORDER BY tp.name")->fetchAll();
$agencies = $db->query("SELECT agency_id, company_name FROM travel_agency ORDER BY company_name")->fetchAll();

$page_title = 'My Reviews';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Reviews</h1>
    <p>Share your travel experiences with the community.</p>
</div>

<div style="display:grid;grid-template-columns:380px 1fr;gap:2rem;align-items:start">

<!-- Write review form -->
<div class="form-card" style="max-width:100%;margin:0">
    <h2 style="font-size:1.3rem">Write a Review</h2>
    <?php foreach ($errors as $err): ?><div class="flash flash--error"><?= e($err) ?></div><?php endforeach; ?>

    <form method="POST" action="<?= BASE_URL ?>/traveller/reviews.php" data-validate>
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="package_id">Package (optional)</label>
            <select id="package_id" name="package_id" class="form-control">
                <option value="">— None —</option>
                <?php foreach ($packages as $p): ?>
                <option value="<?= $p['package_id'] ?>" <?= $pre_package===$p['package_id']?'selected':'' ?>>
                    <?= e($p['name']) ?> – <?= e($p['company_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="agency_id">Agency (optional)</label>
            <select id="agency_id" name="agency_id" class="form-control">
                <option value="">— None —</option>
                <?php foreach ($agencies as $a): ?>
                <option value="<?= $a['agency_id'] ?>" <?= $pre_agency===$a['agency_id']?'selected':'' ?>>
                    <?= e($a['company_name']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <div style="font-size:.8rem;color:var(--clr-text-muted);margin-top:.25rem">At least one of package or agency must be selected.</div>
        </div>

        <div class="form-group">
            <label>Rating</label>
            <div class="stars-input">
                <?php for ($i=5;$i>=1;$i--): ?>
                <input type="radio" id="star<?=$i?>" name="rating" value="<?=$i?>">
                <label for="star<?=$i?>">★</label>
                <?php endfor; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="comment">Comment</label>
            <textarea id="comment" name="comment" class="form-control" rows="4"
                      placeholder="Describe your experience…"><?= e($_POST['comment'] ?? '') ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Submit Review</button>
    </form>
</div>

<!-- My past reviews -->
<div>
    <h2 style="font-family:var(--font-display);font-size:1.4rem;margin-bottom:1rem">My Reviews</h2>
    <?php if (empty($my_reviews)): ?>
        <div class="empty-state"><div class="empty-icon"><img src="../assets/star.PNG" width = "40" height="40"></div></div><p>You haven't written any reviews yet.</p></div>
    <?php else: ?>
        <?php foreach ($my_reviews as $rev): ?>
        <div class="review-card">
            <div class="review-card-header">
                <div>
                    <?php if ($rev['package_name']): ?><img src="../assets/box.PNG" width = "40" height="40"></div> <strong><?= e($rev['package_name']) ?></strong><?php endif; ?>
                    <?php if ($rev['package_name'] && $rev['agency_name']): ?> &nbsp;·&nbsp; <?php endif; ?>
                    <?php if ($rev['agency_name']): ?><img src="../assets/building.PNG" width = "40" height="40"></div> <?= e($rev['agency_name']) ?><?php endif; ?>
                </div>
                <div>
                    <span class="stars"><?= str_repeat('★', $rev['rating']) ?><?= str_repeat('☆', 5-$rev['rating']) ?></span>
                    <span class="review-date"><?= date('d M Y', strtotime($rev['created_date'])) ?></span>
                    <?= getSentimentBadge($rev['sentiment'] ?? null) ?>
                </div>
            </div>
            <?php if ($rev['comment']): ?><p class="review-body"><?= e($rev['comment']) ?></p><?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
