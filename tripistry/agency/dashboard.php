<?php
/*agency/dashboard.php  –  Agency home page*/
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('agency');

$db = get_db();
$agency_id = (int)$_SESSION['agency_id'];

// Stats
$stats = $db->prepare("
    SELECT
        (SELECT COUNT(*) FROM travel_package WHERE agency_id=?) AS pkg_count,
        (SELECT COUNT(*) FROM group_trip WHERE agency_id=? AND status='open') AS open_trips,
        (SELECT COUNT(*) FROM booking b JOIN travel_package tp ON tp.package_id=b.package_id WHERE tp.agency_id=?) AS total_bookings,
        (SELECT ROUND(AVG(r.rating),1) FROM review r WHERE r.agency_id=?) AS avg_rating
");
$stats->execute([$agency_id, $agency_id, $agency_id, $agency_id]);
$s = $stats->fetch();

// Recent bookings for this agency's packages
$stmt = $db->prepare("
    SELECT b.booking_id, b.booking_date, b.total_price, b.status,
           tp.name AS package_name,
           t.first_name, t.last_name
    FROM booking b
    JOIN travel_package tp ON tp.package_id = b.package_id
    JOIN traveller t ON t.traveller_id = b.traveller_id
    WHERE tp.agency_id = ?
    ORDER BY b.booking_date DESC
    LIMIT 8
");
$stmt->execute([$agency_id]);
$bookings = $stmt->fetchAll();

// Recent reviews
$stmt = $db->prepare("
    SELECT r.*, tp.name AS package_name, t.first_name, t.last_name
    FROM review r
    LEFT JOIN travel_package tp ON tp.package_id = r.package_id
    JOIN traveller t ON t.traveller_id = r.traveller_id
    WHERE r.agency_id = ? OR r.package_id IN (SELECT package_id FROM travel_package WHERE agency_id=?)
    ORDER BY r.created_date DESC
    LIMIT 4
");
$stmt->execute([$agency_id, $agency_id]);
$reviews = $stmt->fetchAll();

$display_name = $_SESSION['display_name'] ?? 'Agency';
$page_title = 'Agency Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Welcome, <?= e($display_name) ?> <img src="../assets/building.PNG" width = "40" height="40" alt="destinations"></h1>
    <p>Manage your packages, group trips and monitor bookings.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"> <img src="../assets/box.PNG" width = "40" height="40" alt="destinations"></div>
        <div class="stat-value"><?= $s['pkg_count'] ?></div>
        <div class="stat-label">Packages</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"> <img src="../assets/group.PNG" width = "40" height="40" alt="destinations"></div>
        <div class="stat-value"><?= $s['open_trips'] ?></div>
        <div class="stat-label">Open Group Trips</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"> <img src="../assets/ticket.PNG" width = "40" height="40"></div>
        <div class="stat-value"><?= $s['total_bookings'] ?></div>
        <div class="stat-label">Total Bookings</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"> <img src="../assets/star.PNG" width = "40" height="40" alt="destinations"></div>
        <div class="stat-value"><?= $s['avg_rating'] ?? '–' ?></div>
        <div class="stat-label">Avg Rating</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:2rem;align-items:start">

<!-- Bookings -->
<div>
    <div class="section-heading">
        <h2>Recent Bookings</h2>
    </div>
    <?php if (empty($bookings)): ?>
        <div class="empty-state"><p>No bookings yet.</p></div>
    <?php else: ?>
    <div class="table-wrap card">
    <table class="data-table">
        <thead><tr><th>Traveller</th><th>Package</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($bookings as $b): ?>
        <tr>
            <td><?= e($b['first_name'].' '.$b['last_name']) ?></td>
            <td><?= e($b['package_name']) ?></td>
            <td><?= date('d M Y', strtotime($b['booking_date'])) ?></td>
            <td>R<?= number_format($b['total_price'],2) ?></td>
            <td><span class="badge badge-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>

    <div style="margin-top:1rem;display:flex;gap:.75rem;flex-wrap:wrap">
        <a href="<?= BASE_URL ?>/agency/packages.php" class="btn btn-primary">Manage Packages</a>
        <a href="<?= BASE_URL ?>/agency/group_trips.php" class="btn btn-outline">Group Trips</a>
        <a href="<?= BASE_URL ?>/agency/manage_content.php" class="btn btn-outline">Manage Content</a>
    </div>
</div>

<!-- Reviews sidebar -->
<div>
    <div class="section-heading"><h2>Recent Reviews</h2></div>
    <?php if (empty($reviews)): ?>
        <p class="text-muted">No reviews yet.</p>
    <?php else: ?>
        <?php foreach ($reviews as $rev): ?>
        <div class="review-card">
            <div class="review-card-header">
                <span class="reviewer-name"><?= e($rev['first_name'].' '.($rev['last_name'][0]??'').'.') ?></span>
                <span class="stars"><?= str_repeat('★',$rev['rating']) ?><?= str_repeat('☆',5-$rev['rating']) ?></span>
            </div>
            <?php if ($rev['package_name']): ?><div style="font-size:.8rem;color:var(--clr-text-muted)">re: <?= e($rev['package_name']) ?></div><?php endif; ?>
            <?php if ($rev['comment']): ?><p class="review-body"><?= e(mb_strimwidth($rev['comment'],0,80,'…')) ?></p><?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
