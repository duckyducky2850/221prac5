<?php
/*Traveller home page*/
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('traveller');

$uid = current_user_id();
$db  = get_db();

// Stats
$stmt = $db->prepare("SELECT COUNT(*) FROM booking WHERE traveller_id = ?"); $stmt->execute([$uid]);
$booking_count = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM booking WHERE traveller_id = ? AND status = 'confirmed'"); $stmt->execute([$uid]);
$confirmed = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM review WHERE traveller_id = ?"); $stmt->execute([$uid]);
$review_count = $stmt->fetchColumn();

// Recent bookings
$stmt = $db->prepare("
    SELECT b.booking_id, b.booking_date, b.total_price, b.status,
           tp.name AS package_name, ta.company_name AS agency_name
    FROM booking b
    JOIN travel_package tp ON tp.package_id = b.package_id
    JOIN travel_agency ta ON ta.agency_id = tp.agency_id
    WHERE b.traveller_id = ?
    ORDER BY b.booking_date DESC
    LIMIT 5");
$stmt->execute([$uid]);
$recent_bookings = $stmt->fetchAll();

// Recommended packages (simpleway of recommending, same destination as past bookings or top-rated)
$stmt = $db->prepare("
    SELECT tp.package_id, tp.name, tp.base_price, tp.duration_days,
           ta.company_name,
           ROUND(AVG(r.rating),1) AS avg_rating
    FROM travel_package tp
    JOIN travel_agency ta ON ta.agency_id = tp.agency_id
    LEFT JOIN review r ON r.package_id = tp.package_id
    WHERE tp.package_id NOT IN (
        SELECT package_id FROM booking WHERE traveller_id = ?
    )
    GROUP BY tp.package_id
    ORDER BY avg_rating DESC, tp.base_price ASC
    LIMIT 4");
$stmt->execute([$uid]);
$recommended = $stmt->fetchAll();

$display_name = $_SESSION['display_name'] ?? 'Traveller';
$page_title = 'My Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Welcome back, <?= e($display_name) ?></h1>
    <p>Here's an overview of your travel activity.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><img src="<?= BASE_URL ?>/assets/building.PNG" width = "40" height="40" alt="Bookings icon"></div>
        <div class="stat-value"><?= $booking_count ?></div>
        <div class="stat-label">Total Bookings</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><img src="<?= BASE_URL ?>/assets/tick.PNG" width = "40" height="40" alt="Confirmed icon"></div>
        <div class="stat-value"><?= $confirmed ?></div>
        <div class="stat-label">Confirmed</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><img src="<?= BASE_URL ?>/assets/star.PNG" width = "40" height="40" alt="Reviews icon"></div>
        <div class="stat-value"><?= $review_count ?></div>
        <div class="stat-label">Reviews Written</div>
    </div>
</div>

<!-- Recent bookings -->
<div class="section-heading">
    <h2>Recent Bookings</h2>
    <a href="<?= BASE_URL ?>/traveller/bookings.php" class="btn btn-outline btn-sm">View All</a>
</div>

<?php if (empty($recent_bookings)): ?>
    <div class="empty-state">
        <div class="empty-icon"><img src="<?= BASE_URL ?>/assets/map.PNG" width = "40" height="40" alt="No bookings icon"></div>
        <p>You haven't made any bookings yet.</p>
        <a href="<?= BASE_URL ?>/traveller/packages.php" class="btn btn-primary mt-2">Browse Packages</a>
    </div>
<?php else: ?>
<div class="table-wrap card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Package</th>
                <th>Agency</th>
                <th>Date</th>
                <th>Price</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($recent_bookings as $b): ?>
        <tr>
            <td><?= e($b['package_name']) ?></td>
            <td><?= e($b['agency_name']) ?></td>
            <td><?= date('d M Y', strtotime($b['booking_date'])) ?></td>
            <td>R<?= number_format($b['total_price'], 2) ?></td>
            <td><span class="badge badge-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
            <td><a href="<?= BASE_URL ?>/traveller/bookings.php?id=<?= $b['booking_id'] ?>" class="btn btn-outline btn-sm">View</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<!-- Recommended packages -->
<?php if (!empty($recommended)): ?>
<div class="section-heading mt-3">
    <h2>Recommended for You</h2>
    <a href="<?= BASE_URL ?>/traveller/packages.php" class="btn btn-outline btn-sm">Browse All</a>
</div>
<div class="grid-4">
    <?php foreach ($recommended as $p): ?>
    <a href="<?= BASE_URL ?>/traveller/package_detail.php?id=<?= $p['package_id'] ?>" style="text-decoration:none;color:inherit">
    <div class="card">
        <div class="card-img-placeholder" style="height:120px"><img src="<?= BASE_URL ?>/assets/globe.PNG" width = "40" height="40"></div>
        <div class="card-body" style="padding:.9rem">
            <div class="card-title" style="font-size:.95rem"><?= e($p['name']) ?></div>
            <div class="card-meta"><?= e($p['company_name']) ?></div>
            <div class="flex-between">
                <span class="price-badge" style="font-size:.85rem">R<?= number_format($p['base_price'], 2) ?></span>
                <?php if ($p['avg_rating']): ?><span class="stars" style="font-size:.85rem"><?= str_repeat('★', round($p['avg_rating'])) ?></span><?php endif; ?>
            </div>
        </div>
    </div>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
