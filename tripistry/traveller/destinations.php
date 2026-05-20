<?php
/*Browse destinations, flights, accommodation, attractions, restaurants*/
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

$db = get_db();
$selected_id = (int)($_GET['id'] ?? 0);

// All destinations
$destinations = $db->query("SELECT * FROM destination ORDER BY city_name")->fetchAll();

// If a destination is selected, load its details
$dest = null;
$flights = $accommodations = $transports = $attractions = $restaurants = [];

if ($selected_id) {
    $s = $db->prepare("SELECT * FROM destination WHERE destination_id = ?");
    $s->execute([$selected_id]);
    $dest = $s->fetch();

    if ($dest) {
        $s = $db->prepare("SELECT f.*, d_orig.city_name AS origin_city FROM flight f JOIN destination d_orig ON d_orig.destination_id=f.origin_destination_id WHERE f.destination_id=? ORDER BY f.departure_time");
        $s->execute([$selected_id]); $flights = $s->fetchAll();

        $s = $db->prepare("SELECT * FROM accommodation WHERE destination_id=? ORDER BY price_per_night");
        $s->execute([$selected_id]); $accommodations = $s->fetchAll();

        $s = $db->prepare("SELECT t.*, d_orig.city_name AS origin_city FROM transport t JOIN destination d_orig ON d_orig.destination_id=t.origin_destination_id WHERE t.destination_id=? ORDER BY t.departure_time");
        $s->execute([$selected_id]); $transports = $s->fetchAll();

        $s = $db->prepare("SELECT a.*, ta.opening_hours, ta.category, ta.entry_fee FROM activity a JOIN tourist_attraction ta ON ta.attraction_id=a.activity_id WHERE a.destination_id=? ORDER BY a.name");
        $s->execute([$selected_id]); $attractions = $s->fetchAll();

        $s = $db->prepare("SELECT a.*, r.cuisine_type, r.price_range, r.opening_hours FROM activity a JOIN restaurant r ON r.restaurant_id=a.activity_id WHERE a.destination_id=? ORDER BY a.name");
        $s->execute([$selected_id]); $restaurants = $s->fetchAll();
    }
}

$page_title = $dest ? $dest['city_name'] : 'Destinations';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1><?= $dest ? e($dest['city_name'].', '.$dest['country']) : 'Explore Destinations' ?></h1>
    <?php if (!$dest): ?><p>Discover amazing places around the world.</p><?php endif; ?>
</div>

<?php if (!$dest): ?>
<!--Destinations grid-->
<div class="grid-3">
    <?php foreach ($destinations as $d): ?>
    <a href="<?= BASE_URL ?>/traveller/destinations.php?id=<?= $d['destination_id'] ?>" style="text-decoration:none;color:inherit">
    <div class="card">
        <div class="card-img-placeholder">🌆</div>
        <div class="card-body">
            <div class="card-title"><?= e($d['city_name']) ?></div>
            <div class="card-meta">📍 <?= e($d['country']) ?></div>
            <?php if ($d['description']): ?>
                <p style="font-size:.87rem;color:var(--clr-text-muted)"><?= e(mb_strimwidth($d['description'],0,80,'…')) ?></p>
            <?php endif; ?>
            <?php if ($d['popular_season']): ?>
                <p class="text-muted mt-1" style="font-size:.82rem">🌤 Best: <?= e($d['popular_season']) ?></p>
            <?php endif; ?>
        </div>
    </div>
    </a>
    <?php endforeach; ?>
</div>

<?php else: ?>
<!--Destination detail-->
<a href="<?= BASE_URL ?>/traveller/destinations.php" class="text-muted" style="font-size:.9rem">← All Destinations</a>

<?php if ($dest['description']): ?>
    <p style="margin:1rem 0"><?= nl2br(e($dest['description'])) ?></p>
<?php endif; ?>

<div class="tabs mt-2">
    <button class="tab-btn active" data-tab="flights">✈️ Flights (<?= count($flights) ?>)</button>
    <button class="tab-btn" data-tab="accommodation">🏨 Stays (<?= count($accommodations) ?>)</button>
    <button class="tab-btn" data-tab="transport">🚌 Transport (<?= count($transports) ?>)</button>
    <button class="tab-btn" data-tab="attractions">🎯 Attractions (<?= count($attractions) ?>)</button>
    <button class="tab-btn" data-tab="restaurants">🍽 Restaurants (<?= count($restaurants) ?>)</button>
</div>

<!-- Flights -->
<div class="tab-panel active" data-panel="flights">
    <?php if (empty($flights)): ?><p class="text-muted">No flights found.</p>
    <?php else: ?>
    <div class="table-wrap card"><table class="data-table">
        <thead><tr><th>Flight</th><th>Airline</th><th>From</th><th>Departure</th><th>Arrival</th><th>Price</th></tr></thead>
        <tbody>
        <?php foreach ($flights as $f): ?>
        <tr>
            <td><?= e($f['flight_number']) ?></td>
            <td><?= e($f['airline']) ?></td>
            <td><?= e($f['origin_city']) ?></td>
            <td><?= date('d M Y H:i', strtotime($f['departure_time'])) ?></td>
            <td><?= date('d M Y H:i', strtotime($f['arrival_time'])) ?></td>
            <td><span class="price-badge">R<?= number_format($f['price'], 2) ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div>

<!-- Accommodation -->
<div class="tab-panel" data-panel="accommodation">
    <?php if (empty($accommodations)): ?><p class="text-muted">No accommodation found.</p>
    <?php else: ?>
    <div class="grid-3">
        <?php foreach ($accommodations as $a): ?>
        <div class="card">
            <div class="card-img-placeholder" style="height:130px">🏨</div>
            <div class="card-body">
                <div class="card-title"><?= e($a['name']) ?></div>
                <?php if ($a['address']): ?><div class="card-meta">📍 <?= e($a['address']) ?></div><?php endif; ?>
                <div class="flex-between mt-1">
                    <span class="price-badge">R<?= number_format($a['price_per_night'], 2) ?>/night</span>
                    <span class="text-muted" style="font-size:.82rem"><?= $a['no_bedrooms'] ?>🛏 <?= $a['no_bathrooms'] ?>🚿</span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Transport -->
<div class="tab-panel" data-panel="transport">
    <?php if (empty($transports)): ?><p class="text-muted">No transport options found.</p>
    <?php else: ?>
    <div class="table-wrap card"><table class="data-table">
        <thead><tr><th>Type</th><th>Provider</th><th>From</th><th>Departure</th><th>Price</th></tr></thead>
        <tbody>
        <?php foreach ($transports as $t): ?>
        <tr>
            <td><?= e(ucfirst($t['type'])) ?></td>
            <td><?= e($t['provider'] ?? '–') ?></td>
            <td><?= e($t['origin_city']) ?></td>
            <td><?= date('d M Y H:i', strtotime($t['departure_time'])) ?></td>
            <td><span class="price-badge">R<?= number_format($t['price'], 2) ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div>

<!-- Attractions -->
<div class="tab-panel" data-panel="attractions">
    <?php if (empty($attractions)): ?><p class="text-muted">No attractions found.</p>
    <?php else: ?>
    <div class="grid-3">
        <?php foreach ($attractions as $a): ?>
        <div class="card">
            <div class="card-img-placeholder" style="height:120px">🎯</div>
            <div class="card-body">
                <div class="card-title"><?= e($a['name']) ?></div>
                <?php if ($a['category']): ?><span class="badge badge-open" style="margin-bottom:.5rem"><?= e($a['category']) ?></span><?php endif; ?>
                <?php if ($a['description']): ?><p style="font-size:.85rem;color:var(--clr-text-muted)"><?= e(mb_strimwidth($a['description'],0,80,'…')) ?></p><?php endif; ?>
                <div class="flex-between mt-1">
                    <?php if ($a['entry_fee']): ?><span class="price-badge" style="font-size:.85rem">R<?= number_format($a['entry_fee'], 2) ?></span><?php endif; ?>
                    <?php if ($a['opening_hours']): ?><span class="text-muted" style="font-size:.8rem">🕐 <?= e($a['opening_hours']) ?></span><?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Restaurants -->
<div class="tab-panel" data-panel="restaurants">
    <?php if (empty($restaurants)): ?><p class="text-muted">No restaurants found.</p>
    <?php else: ?>
    <div class="grid-3">
        <?php foreach ($restaurants as $r): ?>
        <div class="card">
            <div class="card-img-placeholder" style="height:120px">🍽</div>
            <div class="card-body">
                <div class="card-title"><?= e($r['name']) ?></div>
                <?php if ($r['cuisine_type']): ?><div class="card-meta"><?= e($r['cuisine_type']) ?></div><?php endif; ?>
                <div class="flex-between mt-1">
                    <?php if ($r['price_range']): ?><span class="price-range-badge"><?= e($r['price_range']) ?></span><?php endif; ?>
                    <?php if ($r['opening_hours']): ?><span class="text-muted" style="font-size:.8rem">🕐 <?= e($r['opening_hours']) ?></span><?php endif; ?>
                </div>
                <?php if ($r['price']): ?><p class="text-muted mt-1" style="font-size:.82rem">Avg spend: R<?= number_format($r['price'],2) ?></p><?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
