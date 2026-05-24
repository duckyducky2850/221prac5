<?php
/*Shows full package info: components, reviews, group trips, agency info.*/
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$db = get_db();
$package_id = (int)($_GET['id'] ?? 0);

if (!$package_id) {
    header('Location: ' . BASE_URL . '/traveller/packages.php');
    exit;
}

// Package + agency
$stmt = $db->prepare("
    SELECT tp.*, ta.company_name, ta.contact_number, ta.website, ta.address, ta.country,
           ROUND(AVG(r.rating),1) AS avg_rating,
           COUNT(DISTINCT r.review_id) AS review_count
    FROM travel_package tp
    JOIN travel_agency ta ON ta.agency_id = tp.agency_id
    LEFT JOIN review r ON r.package_id = tp.package_id
    WHERE tp.package_id = ?
    GROUP BY tp.package_id
");
$stmt->execute([$package_id]);
$pkg = $stmt->fetch();
if (!$pkg) {
    header('Location: ' . BASE_URL . '/traveller/packages.php');
    exit;
}

// Components, grouped by type
$comp_stmt = $db->prepare("
    SELECT pc.component_type, pc.component_id,
           CASE pc.component_type
               WHEN 'flight' THEN CONCAT(f.flight_number, ' – ', f.airline)
               WHEN 'accommodation' THEN a.name
               WHEN 'transport' THEN CONCAT(tr.type, ' (', COALESCE(tr.provider,''), ')')
               WHEN 'activity' THEN act.name
           END AS component_name,
           CASE pc.component_type
               WHEN 'flight' THEN f.price
               WHEN 'accommodation' THEN a.price_per_night
               WHEN 'transport' THEN tr.price
               WHEN 'activity' THEN act.price
           END AS price
    FROM package_component pc
    LEFT JOIN flight f ON pc.component_type='flight' AND f.flight_id=pc.component_id
    LEFT JOIN accommodation a ON pc.component_type='accommodation' AND a.accommodation_id=pc.component_id
    LEFT JOIN transport tr ON pc.component_type='transport' AND tr.transport_id=pc.component_id
    LEFT JOIN activity act ON pc.component_type='activity' AND act.activity_id=pc.component_id
    WHERE pc.package_id = ?
    ORDER BY FIELD(pc.component_type,'flight','accommodation','transport','activity')");
$comp_stmt->execute([$package_id]);
$components = $comp_stmt->fetchAll();
$components_by_type = [];
foreach ($components as $c) $components_by_type[$c['component_type']][] = $c;

// Reviews
$rev_stmt = $db->prepare("
    SELECT r.*, t.first_name, t.last_name
    FROM review r
    JOIN traveller t ON t.traveller_id = r.traveller_id
    WHERE r.package_id = ?
    ORDER BY r.created_date DESC");
$rev_stmt->execute([$package_id]);
$reviews = $rev_stmt->fetchAll();

// Open group trips for this package
$gt_stmt = $db->prepare("
    SELECT gt.*, (gt.max_members - gt.current_members) AS spots_left
    FROM group_trip gt
    WHERE gt.package_id = ? AND gt.status = 'open'
    ORDER BY gt.start_date ASC");
$gt_stmt->execute([$package_id]);
$group_trips = $gt_stmt->fetchAll();

$page_title = $pkg['name'];
require_once __DIR__ . '/../includes/header.php';
?>

<div style="margin-bottom:.75rem">
    <a href="<?= BASE_URL ?>/traveller/packages.php" class="text-muted" style="font-size:.9rem">← Back to Packages</a>
</div>

<div class="package-detail">

<!--Left: Main content-->
<div>
    <div class="card-img-placeholder" style="height:280px;border-radius:var(--radius-md);margin-bottom:1.5rem;font-size:4rem"><div class="card-img-placeholder" style="height:120px"><img src="../assets/globe.PNG" width = "40" height="40"></div></div>

    <h1 style="font-family:var(--font-display);font-size:2rem;color:var(--clr-primary);margin-bottom:.4rem"><?= e($pkg['name']) ?></h1>
    <p class="text-muted mb-2">By <?= e($pkg['company_name']) ?></p>

    <?php if ($pkg['avg_rating']): ?>
    <div class="flex mb-2" style="gap:.5rem">
        <span class="stars"><?= str_repeat('★', round($pkg['avg_rating'])) ?><?= str_repeat('☆', 5-round($pkg['avg_rating'])) ?></span>
        <span class="text-muted" style="font-size:.9rem"><?= $pkg['avg_rating'] ?> / 5 (<?= $pkg['review_count'] ?> review<?= $pkg['review_count'] != 1 ? 's' : '' ?>)</span>
    </div>
    <?php endif; ?>

    <?php if ($pkg['description']): ?>
    <p style="margin-bottom:1.5rem"><?= nl2br(e($pkg['description'])) ?></p>
    <?php endif; ?>

    <!--Tabs: Itinerary or Group Trips or Reviews-->
    <div class="tabs">
        <button class="tab-btn active" data-tab="itinerary"><div class="card-img-placeholder" style="height:120px"><img src="../assets/map.PNG" width = "40" height="40"></div> Itinerary</button>
        <button class="tab-btn" data-tab="group_trips"><div class="card-img-placeholder" style="height:120px"><img src="../assets/group.PNG" width = "40" height="40"></div> Group Trips (<?= count($group_trips) ?>)</button>
        <button class="tab-btn" data-tab="reviews"><div class="card-img-placeholder" style="height:120px"><img src="../assets/star.PNG" width = "40" height="40"></div> Reviews (<?= count($reviews) ?>)</button>
    </div>

    <!-- Itinerary panel -->
<div class="tab-panel active" data-panel="itinerary">
    <?php $icons = [
        'flight' => '../assets/plane.PNG',
        'accommodation' =>'../assets/stays.PNG',
        'transport'=> '../assets/transport.PNG',
        'activity'=> '../assets/attractions.PNG',
    ];
    $labels = ['flight'=>'Flights','accommodation'=>'Accommodation','transport'=>'Transport','activity'=>'Activities'];
    foreach ($icons as $type => $icon): ?>
        <?php if (!empty($components_by_type[$type])): ?>
        <div style="margin-bottom:1.25rem">
            <h3 style="font-size:1rem;margin-bottom:.6rem">
                <img src="<?= $icon ?>" alt="<?= $labels[$type] ?>" width="20" height="20" style="vertical-align:middle;margin-right:.35rem">
                <?= $labels[$type] ?>
            </h3>
            <?php foreach ($components_by_type[$type] as $c): ?>
            <div style="display:flex;justify-content:space-between;padding:.5rem .75rem;background:var(--clr-bg);border-radius:var(--radius-sm);margin-bottom:.4rem;font-size:.9rem">
                <span><?= e($c['component_name'] ?? '—') ?></span>
                <?php if ($c['price']): ?><span class="text-muted">R<?= number_format($c['price'], 2) ?></span><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if (empty($components)): ?><p class="text-muted">No itinerary details available yet.</p><?php endif; ?>
</div>

    <!-- Group Trips panel -->
    <div class="tab-panel" data-panel="group_trips">
        <?php if (empty($group_trips)): ?>
            <p class="text-muted">No open group trips for this package at the moment.</p>
        <?php else: ?>
            <?php foreach ($group_trips as $gt): ?>
            <div class="card mb-2" style="box-shadow:none">
                <div class="card-body">
                    <div class="flex-between">
                        <div>
                            <strong><?= date('d M Y', strtotime($gt['start_date'])) ?></strong> –
                            <?= date('d M Y', strtotime($gt['end_date'])) ?>
                            <span class="badge badge-<?= e($gt['status']) ?>" style="margin-left:.5rem"><?= e($gt['status']) ?></span>
                        </div>
                        <div class="text-muted" style="font-size:.88rem">
                            <?= $gt['spots_left'] ?> spot<?= $gt['spots_left']!=1?'s':'' ?> left of <?= $gt['max_members'] ?>
                        </div>
                    </div>
                    <?php if (is_logged_in()): ?>
                    <a href="<?= BASE_URL ?>/traveller/book.php?package_id=<?= $package_id ?>&group_trip_id=<?= $gt['group_trip_id'] ?>"
                       class="btn btn-primary btn-sm mt-1">Join Group Trip</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Reviews panel -->
    <div class="tab-panel" data-panel="reviews">
        <?php if (is_logged_in() && current_role() === 'traveller'): ?>
        <div class="card mb-3" style="box-shadow:none;border:1px dashed var(--clr-border)">
            <div class="card-body">
                <h4 style="margin-bottom:1rem">Write a Review</h4>
                <form method="POST" action="<?= BASE_URL ?>/traveller/reviews.php">
                    <?= csrf_field() ?>
                    <input type="hidden" name="package_id" value="<?= $package_id ?>">
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
                        <label>Comment (optional)</label>
                        <textarea name="comment" class="form-control" rows="3" placeholder="Share your experience…"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Submit Review</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <?php if (empty($reviews)): ?>
            <p class="text-muted">No reviews yet. Be the first to review this package!</p>
        <?php else: ?>
            <?php foreach ($reviews as $rev): ?>
            <div class="review-card">
                <div class="review-card-header">
                    <span class="reviewer-name"><?= e($rev['first_name']) ?> <?= e($rev['last_name'][0]) ?>.</span>
                    <div>
                        <span class="stars"><?= str_repeat('★', $rev['rating']) ?><?= str_repeat('☆', 5-$rev['rating']) ?></span>
                        <span class="review-date"><?= date('d M Y', strtotime($rev['created_date'])) ?></span>
                    </div>
                </div>
                <?php if ($rev['comment']): ?>
                <p class="review-body"><?= e($rev['comment']) ?></p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!--Right: Sidebar-->
<div class="detail-sidebar">

    <!-- Price and book -->
    <div class="sidebar-card" style="border-color:var(--clr-primary)">
        <div style="font-size:1.8rem;font-weight:700;color:var(--clr-primary);margin-bottom:.25rem">
            R<?= number_format($pkg['base_price'], 2) ?>
        </div>
        <p class="text-muted mb-2">per person · <?= $pkg['duration_days'] ? $pkg['duration_days'].' days' : '' ?></p>
        <?php if (is_logged_in() && current_role() === 'traveller'): ?>
            <a href="<?= BASE_URL ?>/traveller/book.php?package_id=<?= $package_id ?>" class="btn btn-primary btn-block">Book This Package</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-block">Log In to Book</a>
        <?php endif; ?>
    </div>

    <!-- Agency info -->
    <div class="sidebar-card">
        <h4 style="margin-bottom:.75rem"><div class="card-img-placeholder" style="height:120px"><img src="../assets/building.PNG" width = "40" height="40"></div>Agency</h4>
        <p><strong><?= e($pkg['company_name']) ?></strong></p>
        <?php if ($pkg['contact_number']): ?><p class="text-muted" style="font-size:.88rem"><div class="card-img-placeholder" style="height:120px"><img src="../assets/phone.PNG" width = "40" height="40"></div> <?= e($pkg['contact_number']) ?></p><?php endif; ?>
        <?php if ($pkg['website']): ?><p style="font-size:.88rem"><a href="<?= e($pkg['website']) ?>" target="_blank"><div class="card-img-placeholder" style="height:120px"><img src="../website/map.PNG" width = "40" height="40"></div> Website</a></p><?php endif; ?>
        <?php if ($pkg['country']): ?><p class="text-muted" style="font-size:.88rem"><div class="card-img-placeholder" style="height:120px"><img src="../assets/pin.PNG" width = "40" height="40"></div> <?= e($pkg['country']) ?></p><?php endif; ?>
        <a href="<?= BASE_URL ?>/traveller/packages.php?agency_id=<?= $pkg['agency_id'] ?>" class="btn btn-outline btn-sm mt-2">More from this agency</a>
    </div>

    <!-- Quick stats -->
    <div class="sidebar-card">
        <h4 style="margin-bottom:.75rem"><div class="card-img-placeholder" style="height:120px"><img src="../assets/map.PNG" width = "40" height="40"></div> Package Info</h4>
        <div style="font-size:.9rem;display:flex;flex-direction:column;gap:.4rem">
            <div class="flex-between"><span>Components</span><strong><?= count($components) ?></strong></div>
            <div class="flex-between"><span>Group trips</span><strong><?= count($group_trips) ?></strong></div>
            <div class="flex-between"><span>Reviews</span><strong><?= count($reviews) ?></strong></div>
            <div class="flex-between"><span>Created</span><strong><?= date('M Y', strtotime($pkg['created_at'])) ?></strong></div>
        </div>
    </div>

</div>
</div><!-- .package-detail -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
