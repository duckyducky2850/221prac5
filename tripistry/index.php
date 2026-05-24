<?php
/*Public landing page*/
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect logged in users to their dashboard
if (is_logged_in()) {
    $dest = current_role() === 'agency' ? BASE_URL . '/agency/dashboard.php' : BASE_URL . '/traveller/dashboard.php';
    header("Location: $dest"); exit;
}

$db = get_db();

// Fetch 6 featured packages for the homepage
$packages = $db->query("
    SELECT tp.package_id, tp.name, tp.base_price, tp.duration_days,
           ta.company_name AS agency_name,
           d.city_name, d.country, d.image_url,
           ROUND(AVG(r.rating), 1) AS avg_rating,
           COUNT(DISTINCT r.review_id) AS review_count
    FROM travel_package tp
    JOIN travel_agency ta ON ta.agency_id = tp.agency_id
    LEFT JOIN package_component pc ON pc.package_id = tp.package_id AND pc.component_type = 'accommodation'
    LEFT JOIN accommodation a ON a.accommodation_id = pc.component_id
    LEFT JOIN destination d ON d.destination_id = a.destination_id
    LEFT JOIN review r ON r.package_id = tp.package_id
    GROUP BY tp.package_id, d.destination_id
    ORDER BY avg_rating DESC, tp.created_at DESC
    LIMIT 6
")->fetchAll();

// Popular destinations
$destinations = $db->query("
    SELECT d.destination_id, d.city_name, d.country, d.image_url, d.popular_season,
           COUNT(DISTINCT a.accommodation_id) AS accom_count
    FROM destination d
    LEFT JOIN accommodation a ON a.destination_id = d.destination_id
    GROUP BY d.destination_id
    ORDER BY accom_count DESC
    LIMIT 8")->fetchAll();

$page_title = 'Discover the World';
require_once __DIR__ . '/includes/header.php';
?>

<!--Hero-->
<section class="hero">
    <h1>Your Adventure Starts Here</h1>
    <p>Compare travel packages from top agencies, discover amazing destinations, and book your dream holiday — all in one place.</p>
    <div class="hero-btns">
        <a href="<?= BASE_URL ?>/traveller/packages.php" class="btn btn-accent btn-lg">Browse Packages</a>
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-outline btn-lg" style="color:#fff;border-color:#fff">Sign Up Free</a>
    </div>
</section>

<!--Stats strip-->
<div class="stats-grid mb-3" style="max-width:800px;margin-left:auto;margin-right:auto">
    <div class="stat-card">
        <div class="stat-icon"><img src="../assets/plane.PNG" width = "40" height="40"></div>️</div>
        <div class="stat-value"><?= $db->query("SELECT COUNT(*) FROM travel_package")->fetchColumn() ?></div>
        <div class="stat-label">Travel Packages</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><img src="../assets/globe.PNG" width = "40" height="40"></div></div>
        <div class="stat-value"><?= $db->query("SELECT COUNT(*) FROM destination")->fetchColumn() ?></div>
        <div class="stat-label">Destinations</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><img src="../assets/building.PNG" width = "40" height="40"></div></div>
        <div class="stat-value"><?= $db->query("SELECT COUNT(*) FROM travel_agency")->fetchColumn() ?></div>
        <div class="stat-label">Travel Agencies</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><img src="../assets/star.PNG" width = "40" height="40"></div></div>
        <div class="stat-value"><?= $db->query("SELECT COUNT(*) FROM review")->fetchColumn() ?></div>
        <div class="stat-label">Reviews</div>
    </div>
</div>

<!--Featured packages-->
<div class="section-heading">
    <h2>Featured Packages</h2>
    <a href="<?= BASE_URL ?>/traveller/packages.php" class="btn btn-outline btn-sm">View All →</a>
</div>

<?php if (empty($packages)): ?>
    <div class="empty-state"><div class="empty-icon"><img src="../assets/box.PNG" width = "40" height="40"></div></div><p>No packages yet.</p></div>
<?php else: ?>
<div class="grid-3 mb-3">
    <?php foreach ($packages as $p): ?>
    <a href="<?= BASE_URL ?>/traveller/package_detail.php?id=<?= $p['package_id'] ?>" style="text-decoration:none;color:inherit">
    <div class="card">
        <?php if ($p['image_url']): ?>
            <div class="card-img-placeholder" style="background-image:url('<?= e($p['image_url']) ?>');background-size:cover;background-position:center"></div>
        <?php else: ?>
            <div class="card-img-placeholder"><img src="../assets/globe.PNG" width = "40" height="40"></div></div>
        <?php endif; ?>
        <div class="card-body">
            <div class="card-title"><?= e($p['name']) ?></div>
            <div class="card-meta">
                <?php if ($p['city_name']): ?><img src="../assets/pin.PNG" width = "40" height="40"></div> <?= e($p['city_name']) ?>, <?= e($p['country']) ?> &nbsp;·&nbsp;<?php endif; ?>
                <img src="../assets/building.PNG" width = "40" height="40"></div> <?= e($p['agency_name']) ?>
            </div>
            <div class="flex-between">
                <span class="price-badge">R<?= number_format($p['base_price'], 2) ?></span>
                <?php if ($p['avg_rating']): ?>
                    <span class="stars"><?= str_repeat('★', round($p['avg_rating'])) ?><?= str_repeat('☆', 5 - round($p['avg_rating'])) ?></span>
                <?php endif; ?>
            </div>
            <?php if ($p['duration_days']): ?>
                <p class="text-muted mt-1" style="font-size:.82rem"><img src="../assets/clock.PNG" width = "40" height="40"></div> <?= (int)$p['duration_days'] ?> days</p>
            <?php endif; ?>
        </div>
    </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!--Popular destinations-->
<div class="section-heading mt-3">
    <h2>Popular Destinations</h2>
    <a href="<?= BASE_URL ?>/traveller/destinations.php" class="btn btn-outline btn-sm">All Destinations →</a>
</div>
<div class="grid-4">
    <?php foreach ($destinations as $d): ?>
    <a href="<?= BASE_URL ?>/traveller/destinations.php?id=<?= $d['destination_id'] ?>" style="text-decoration:none;color:inherit">
    <div class="card">
        <div class="card-img-placeholder" style="height:130px"><img src="../assets/HolidayPlaceholder.PNG" width = "130"></div></div>
        <div class="card-body" style="padding:.9rem">
            <div class="card-title" style="font-size:1rem"><?= e($d['city_name']) ?></div>
            <div class="card-meta"><img src="../assets/pin.PNG" width = "40" height="40"></div><?= e($d['country']) ?></div>
            <?php if ($d['popular_season']): ?>
                <small class="text-muted">Best: <?= e($d['popular_season']) ?></small>
            <?php endif; ?>
        </div>
    </div>
    </a>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
