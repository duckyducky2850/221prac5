<?php
/*Returns the package cards HTML fragment for AJAX filtering when typing in the search box, uses same filter logic as packages.php
Called by js/main.js when filter form changes.
All inputs sanitised via PDO prepared statements and integer casting.*/
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: text/html; charset=utf-8');

$db = get_db();

$search = trim($_GET['search']      ?? '');
$destination = (int)($_GET['destination'] ?? 0);
$agency_id = (int)($_GET['agency_id']  ?? 0);
$min_price = (float)($_GET['min_price'] ?? 0);
$max_price = (float)($_GET['max_price'] ?? 0);
$duration = (int)($_GET['duration']   ?? 0);
$sort = $_GET['sort'] ?? 'rating';

$sort_map = [
    'rating' => 'avg_rating DESC',
    'price_asc' => 'tp.base_price ASC',
    'price_desc'=> 'tp.base_price DESC',
    'newest' => 'tp.created_at DESC',
    'duration' => 'tp.duration_days ASC',
];
$order_by = $sort_map[$sort] ?? 'avg_rating DESC';

$where = ['1=1']; $params = [];
if ($search)
{
    $where[] = "(tp.name LIKE ? OR ta.company_name LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%";
}
if ($destination)
{
    $where[] = "d.destination_id = ?"; $params[] = $destination;
}
if ($agency_id)
{
    $where[] = "tp.agency_id = ?"; $params[] = $agency_id;
}
if ($min_price)
{
    $where[] = "tp.base_price >= ?"; $params[] = $min_price;
}
if ($max_price)
{
    $where[] = "tp.base_price <= ?"; $params[] = $max_price;
}
if ($duration)
{
    $where[] = "tp.duration_days <= ?"; $params[] = $duration;
}

$where_sql = implode(' AND ', $where);

$stmt = $db->prepare("
    SELECT tp.package_id, tp.name, tp.base_price, tp.duration_days, tp.description,
           ta.company_name,
           d.city_name, d.country, COALESCE(d.image_url, tp.image_url) AS image_url,
           ROUND(AVG(r.rating), 1) AS avg_rating,
           COUNT(DISTINCT r.review_id) AS review_count
    FROM travel_package tp
    JOIN travel_agency ta ON ta.agency_id = tp.agency_id
    LEFT JOIN package_component pc ON pc.package_id = tp.package_id AND pc.component_type = 'accommodation'
    LEFT JOIN accommodation a ON a.accommodation_id = pc.component_id
    LEFT JOIN destination d ON d.destination_id = a.destination_id
    LEFT JOIN review r ON r.package_id = tp.package_id
    WHERE $where_sql
    GROUP BY tp.package_id, d.destination_id
    ORDER BY $order_by
");
$stmt->execute($params);
$packages = $stmt->fetchAll();

if (empty($packages)): ?>
    <div class="empty-state">
        <div class="empty-icon">
            <img src="<?= BASE_URL ?>/assets/search.PNG" width = "40" height="40" alt="Search">
        </div>
        <p>No packages match your filters.</p>
    </div>
<?php else: ?>
<div class="grid-3">
    <?php foreach ($packages as $p): ?>
    <div class="card">
        <div class="card-img-placeholder" style="position:relative;<?= !empty($p['image_url']) ? "background-image:url('".e($p['image_url'])."');background-size:cover;background-position:center;" : '' ?>">
            <?php if (empty($p['image_url'])): ?>
                <img src="<?= BASE_URL ?>/assets/globe.PNG" width="40" height="40" alt="Package image">
            <?php endif; ?>
        </div>

        <div class="card-body">
            <div class="card-title"><?= e($p['name']) ?></div>
            <div class="card-meta">
                <?php if ($p['city_name']): ?><img src="<?= BASE_URL ?>/assets/pin.PNG" width="16" height="16" alt="Location"> <?= e($p['city_name']) ?>, <?= e($p['country']) ?> &nbsp;·&nbsp;<?php endif; ?>
                <img src="<?= BASE_URL ?>/assets/building.PNG" width="16" height="16" alt="Agency"> <?= e($p['company_name']) ?>
            </div>

            <div class="flex-between">
                <span class="price-badge">R<?= number_format($p['base_price'], 2) ?></span>
                <?php if ($p['avg_rating']): ?>
                    <span class="stars"><?= str_repeat('★', round($p['avg_rating'])) ?></span>
                <?php endif; ?>
            </div>
            <?php if ($p['duration_days']): ?><p class="text-muted mt-1" style="font-size:.82rem"><img src="<?= BASE_URL ?>/assets/clock.PNG" width = "40" height="40" alt="Duration"><?= $p['duration_days'] ?> days</p><?php endif; ?>
            <div style="margin-top:.9rem;display:flex;gap:.5rem">
                <a href="<?= BASE_URL ?>/traveller/package_detail.php?id=<?= $p['package_id'] ?>" class="btn btn-outline btn-sm">Details</a>
                <?php if (is_logged_in()): ?>
                <a href="<?= BASE_URL ?>/traveller/book.php?package_id=<?= $p['package_id'] ?>" class="btn btn-primary btn-sm">Book Now</a>
                <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-sm">Login to Book</a>
                <?php endif; ?>
            </div>
            <!-- added to work with the main.js -->
             <div style="margin-top:.5rem">
                <label style="font-size:.8rem;color:var(--clr-text-muted);cursor:pointer">
                    <input type="checkbox" class="compare-check" value="<?= $p['package_id'] ?>"> <!-- compareList is a JS var  -->
                Compare
            </label>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<p class="text-muted mt-1" style="font-size:.85rem"><?= count($packages) ?> package(s) found</p>
<?php endif;
