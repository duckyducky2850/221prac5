<?php
/*Browse, filter, sort, and compare travel packages.
All inputs sanitised through PDO prepared statements / integer casting.*/
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$db = get_db();

// ── Filter inputs – sanitised ────────────────────────────────
$search = trim($_GET['search'] ?? '');
$destination = (int)($_GET['destination'] ?? 0);
$agency_id = (int)($_GET['agency_id'] ?? 0);
$min_price = (float)($_GET['min_price'] ?? 0);
$max_price = (float)($_GET['max_price'] ?? 0);
$duration = (int)($_GET['duration'] ?? 0);
$sort = $_GET['sort'] ?? 'rating';
$compare_ids = array_filter(array_map('intval', explode(',', $_GET['compare'] ?? '')));

// Allowed sort values (whitelist to prevent SQL injection in ORDER BY)
$sort_map = [
    'rating' => 'avg_rating DESC',
    'price_asc' => 'tp.base_price ASC',
    'price_desc'=> 'tp.base_price DESC',
    'newest' => 'tp.created_at DESC',
    'duration'  => 'tp.duration_days ASC',
];
$order_by = $sort_map[$sort] ?? 'avg_rating DESC';

// ── Build dynamic WHERE clause ────────────────────────────────
$where  = ['1=1'];
$params = [];

if ($search) {
    $where[] = "(tp.name LIKE ? OR tp.description LIKE ? OR ta.company_name LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}
if ($destination) {
    $where[] = "d.destination_id = ?";
    $params[] = $destination;
}
if ($agency_id) {
    $where[] = "tp.agency_id = ?";
    $params[] = $agency_id;
}
if ($min_price > 0) {
    $where[] = "tp.base_price >= ?";
    $params[] = $min_price;
}
if ($max_price > 0) {
    $where[] = "tp.base_price <= ?";
    
    $params[] = $max_price;
}
if ($duration > 0) {
    $where[] = "tp.duration_days <= ?";
    $params[] = $duration;
}

$where_sql = implode(' AND ', $where);

/*$sql = "
    SELECT tp.package_id, tp.name, tp.base_price, tp.duration_days, tp.description,
           ta.company_name, ta.agency_id,
           d.city_name, d.country, d.image_url,
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
    ORDER BY $order_by";*/

//Optimized query
$sql = "
    SELECT tp.package_id, tp.name, tp.base_price, tp.duration_days,
       ta.company_name,
       d.city_name, d.country, d.image_url,
       ROUND(AVG(r.rating), 1) AS avg_rating,
       COUNT(DISTINCT r.review_id) AS review_count
    FROM (
        SELECT package_id, name, base_price, duration_days, agency_id
        FROM travel_package
        WHERE base_price BETWEEN 10000 AND 30000
    ) tp
    JOIN travel_agency ta ON ta.agency_id = tp.agency_id
    LEFT JOIN (
        SELECT package_id, component_id
        FROM package_component
        WHERE component_type = 'accommodation'
    ) pc ON pc.package_id = tp.package_id
    LEFT JOIN (
        SELECT accommodation_id, destination_id
        FROM accommodation
    ) a ON a.accommodation_id = pc.component_id
    LEFT JOIN (
        SELECT destination_id, city_name, country, image_url
        FROM destination
    ) d ON d.destination_id = a.destination_id
    LEFT JOIN review r ON r.package_id = tp.package_id
    GROUP BY tp.package_id, d.destination_id
    ORDER BY avg_rating DESC;";
$stmt = $db->prepare($sql);
$stmt->execute($params);
$packages = $stmt->fetchAll();

// Dropdown data
$destinations = $db->query("SELECT destination_id, city_name, country FROM destination ORDER BY city_name")->fetchAll();
$agencies = $db->query("SELECT agency_id, company_name FROM travel_agency ORDER BY company_name")->fetchAll();

// Compare mode, show side by side table
$compare_packages = [];
if (!empty($compare_ids)) {
    $placeholders = implode(',', array_fill(0, count($compare_ids), '?'));
    $cstmt = $db->prepare("
        SELECT tp.*, ta.company_name,
               ROUND(AVG(r.rating),1) AS avg_rating,
               COUNT(DISTINCT r.review_id) AS review_count
        FROM travel_package tp
        JOIN travel_agency ta ON ta.agency_id = tp.agency_id
        LEFT JOIN review r ON r.package_id = tp.package_id
        WHERE tp.package_id IN ($placeholders)
        GROUP BY tp.package_id");
    $cstmt->execute($compare_ids);
    $compare_packages = $cstmt->fetchAll();
}

$page_title = 'Browse Packages';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>Travel Packages</h1>
    <p>Browse and compare packages from our agency partners.</p>
</div>

<!--Compare table-->
<?php if (!empty($compare_packages)): ?>
<div class="card mb-3" style="overflow:hidden">
    <div style="padding:1rem 1.25rem;background:var(--clr-primary-light);border-bottom:1px solid var(--clr-border)">
        <strong>Package Comparison</strong>
        <a href="<?= BASE_URL ?>/traveller/packages.php" class="btn btn-sm btn-outline" style="float:right">✕ Clear</a>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr>
                <th>Feature</th>
                <?php foreach ($compare_packages as $cp): ?>
                    <th><?= e($cp['name']) ?></th>
                <?php endforeach; ?>
            </tr></thead>
            <tbody>
            <?php $rows = ['company_name'=>'Agency','base_price'=>'Base Price','duration_days'=>'Duration','avg_rating'=>'Avg Rating','review_count'=>'Reviews'];
            foreach ($rows as $key => $label): ?>
            <tr>
                <td><strong><?= $label ?></strong></td>
                <?php foreach ($compare_packages as $cp): ?>
                <td>
                    <?php if ($key === 'base_price'): ?>R<?= number_format($cp[$key], 2) ?>
                    <?php elseif ($key === 'duration_days'): ?><?= $cp[$key] ? $cp[$key].' days' : '–' ?>
                    <?php elseif ($key === 'avg_rating'): ?>
                        <span class="stars"><?= $cp[$key] ? str_repeat('★', round($cp[$key])) : '–' ?></span>
                    <?php else: ?><?= e((string)($cp[$key] ?? '–')) ?>
                    <?php endif; ?>
                </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td><strong>Book</strong></td>
                <?php foreach ($compare_packages as $cp): ?>
                <td><a href="<?= BASE_URL ?>/traveller/book.php?package_id=<?= $cp['package_id'] ?>" class="btn btn-primary btn-sm">Book</a></td>
                <?php endforeach; ?>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!--Filter bar-->
<form id="package-filter-form" method="GET" action="<?= BASE_URL ?>/traveller/packages.php">
<div class="filter-bar">
    <div class="form-group">
        <label for="search">Search</label>
        <input type="text" id="search" name="search" class="form-control"
               value="<?= e($search) ?>" placeholder="Package name or agency...">
    </div>
    <div class="form-group">
        <label for="destination">Destination</label>
        <select id="destination" name="destination" class="form-control">
            <option value="">All Destinations</option>
            <?php foreach ($destinations as $dest): ?>
                <option value="<?= $dest['destination_id'] ?>"
                    <?= $destination === (int)$dest['destination_id'] ? 'selected' : '' ?>>
                    <?= e($dest['city_name']) ?>, <?= e($dest['country']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="agency_id">Agency</label>
        <select id="agency_id" name="agency_id" class="form-control">
            <option value="">All Agencies</option>
            <?php foreach ($agencies as $ag): ?>
                <option value="<?= $ag['agency_id'] ?>"
                    <?= $agency_id === (int)$ag['agency_id'] ? 'selected' : '' ?>>
                    <?= e($ag['company_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="form-group">
        <label for="min_price">Min Price (R)</label>
        <input type="number" id="min_price" name="min_price" class="form-control"
               value="<?= $min_price ?: '' ?>" min="0" placeholder="0">
    </div>
    <div class="form-group">
        <label for="max_price">Max Price (R)</label>
        <input type="number" id="max_price" name="max_price" class="form-control"
               value="<?= $max_price ?: '' ?>" min="0" placeholder="Any">
    </div>
    <div class="form-group">
        <label for="duration">Max Days</label>
        <input type="number" id="duration" name="duration" class="form-control"
               value="<?= $duration ?: '' ?>" min="1" placeholder="Any">
    </div>
    <div class="form-group">
        <label for="sort">Sort By</label>
        <select id="sort" name="sort" class="form-control">
            <option value="rating"     <?= $sort==='rating'     ?'selected':'' ?>>Top Rated</option>
            <option value="price_asc"  <?= $sort==='price_asc'  ?'selected':'' ?>>Price: Low to High</option>
            <option value="price_desc" <?= $sort==='price_desc' ?'selected':'' ?>>Price: High to Low</option>
            <option value="newest"     <?= $sort==='newest'     ?'selected':'' ?>>Newest</option>
            <option value="duration"   <?= $sort==='duration'   ?'selected':'' ?>>Shortest Duration</option>
        </select>
    </div>
    <div class="form-group" style="flex:0">
        <label>&nbsp;</label>
        <button type="submit" class="btn btn-primary">Filter</button>
    </div>
</div>
</form>

<!--Results-->
<div id="package-results">
<div class="flex-between mb-2">
    <p class="text-muted"><?= count($packages) ?> package(s) found</p>
    <?php if (is_logged_in()): ?>
        <small class="text-muted">Tick the ☐ checkbox on cards to compare (max 3)</small>
    <?php endif; ?>
</div>

<?php if (empty($packages)): ?>
    <div class="empty-state"><div class="empty-icon">🔍</div><p>No packages match your filters.</p></div>
<?php else: ?>
<div class="grid-3">
    <?php foreach ($packages as $p): ?>
    <div class="card">
        <?php if ($p['image_url']): ?>
            <div class="card-img-placeholder" style="background-image:url('<?= e($p['image_url']) ?>');background-size:cover">
        <?php else: ?>
            <div class="card-img-placeholder">
        <?php endif; ?>
            <!-- Compare checkbox overlay -->
            <div style="position:absolute;top:8px;right:8px" onclick="event.stopPropagation()">
                <label style="background:rgba(255,255,255,.9);padding:.2rem .5rem;border-radius:4px;font-size:.8rem;cursor:pointer">
                    <input type="checkbox" class="compare-check" value="<?= $p['package_id'] ?>"
                        <?= in_array($p['package_id'], $compare_ids) ? 'checked' : '' ?>>
                    Compare
                </label>
            </div>
        </div>

        <div class="card-body">
            <div class="card-title"><?= e($p['name']) ?></div>
            <div class="card-meta">
                <?php if ($p['city_name']): ?>📍 <?= e($p['city_name']) ?>, <?= e($p['country']) ?> &nbsp;·&nbsp;<?php endif; ?>
                🏢 <?= e($p['company_name']) ?>
            </div>
            <?php if ($p['description']): ?>
                <p style="font-size:.87rem;color:var(--clr-text-muted);margin-bottom:.75rem">
                    <?= e(mb_strimwidth($p['description'], 0, 90, '…')) ?>
                </p>
            <?php endif; ?>
            <div class="flex-between">
                <span class="price-badge">R<?= number_format($p['base_price'], 2) ?></span>
                <div style="text-align:right">
                    <?php if ($p['avg_rating']): ?>
                        <div class="stars"><?= str_repeat('★', round($p['avg_rating'])) ?><?= str_repeat('☆', 5-round($p['avg_rating'])) ?></div>
                        <small class="text-muted"><?= $p['review_count'] ?> review(s)</small>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($p['duration_days']): ?>
                <p class="text-muted mt-1" style="font-size:.82rem">⏱ <?= (int)$p['duration_days'] ?> days</p>
            <?php endif; ?>
            <div style="margin-top:.9rem;display:flex;gap:.5rem">
                <a href="<?= BASE_URL ?>/traveller/package_detail.php?id=<?= $p['package_id'] ?>" class="btn btn-outline btn-sm">Details</a>
                <?php if (is_logged_in()): ?>
                <a href="<?= BASE_URL ?>/traveller/book.php?package_id=<?= $p['package_id'] ?>" class="btn btn-primary btn-sm">Book Now</a>
                <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary btn-sm">Login to Book</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
</div><!-- #package-results -->

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
