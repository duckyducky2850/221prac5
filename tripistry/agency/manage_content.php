<?php
/**
 * agency/manage_content.php
 * --------------------------
 * Agencies manage their flights, accommodation, transport and activities.
 * All CRUD in one tabbed page for convenience.
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('agency');

$db        = get_db();
$agency_id = (int)$_SESSION['agency_id'];

$errors = [];

// ── Generic POST handler ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { set_flash('error','Invalid request.'); header('Location: ' . BASE_URL . '/agency/manage_content.php'); exit; }

    $action = $_POST['action'] ?? '';
    $tab    = $_POST['tab']    ?? 'flights';

    // ── Flights ──
    if ($action === 'add_flight') {
        $stmt = $db->prepare("INSERT INTO flight (flight_number,airline,origin_destination_id,destination_id,departure_time,arrival_time,price,agency_id) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            trim($_POST['flight_number']), trim($_POST['airline']),
            (int)$_POST['origin_id'], (int)$_POST['destination_id'],
            $_POST['departure_time'], $_POST['arrival_time'],
            (float)$_POST['price'], $agency_id
        ]);
        set_flash('success','Flight added.');
    } elseif ($action === 'delete_flight') {
        $db->prepare("DELETE FROM flight WHERE flight_id=? AND agency_id=?")->execute([(int)$_POST['id'], $agency_id]);
        set_flash('success','Flight deleted.');
    }

    // ── Accommodation ──
    elseif ($action === 'add_accommodation') {
        $stmt = $db->prepare("INSERT INTO accommodation (name,address,no_bedrooms,no_bathrooms,price_per_night,destination_id,agency_id) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([
            trim($_POST['name']), trim($_POST['address']) ?: null,
            (int)$_POST['no_bedrooms'], (int)$_POST['no_bathrooms'],
            (float)$_POST['price_per_night'], (int)$_POST['destination_id'], $agency_id
        ]);
        set_flash('success','Accommodation added.');
    } elseif ($action === 'delete_accommodation') {
        $db->prepare("DELETE FROM accommodation WHERE accommodation_id=? AND agency_id=?")->execute([(int)$_POST['id'], $agency_id]);
        set_flash('success','Accommodation deleted.');
    }

    // ── Transport ──
    elseif ($action === 'add_transport') {
        $stmt = $db->prepare("INSERT INTO transport (type,provider,origin_destination_id,destination_id,departure_time,price,agency_id) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([
            $_POST['type'], trim($_POST['provider']) ?: null,
            (int)$_POST['origin_id'], (int)$_POST['destination_id'],
            $_POST['departure_time'], (float)$_POST['price'], $agency_id
        ]);
        set_flash('success','Transport added.');
    } elseif ($action === 'delete_transport') {
        $db->prepare("DELETE FROM transport WHERE transport_id=? AND agency_id=?")->execute([(int)$_POST['id'], $agency_id]);
        set_flash('success','Transport deleted.');
    }

    // ── Activity ──
    elseif ($action === 'add_activity') {
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO activity (name,address,city,price,description,start_time,end_time,destination_id,agency_id,activity_type) VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                trim($_POST['name']), trim($_POST['address']) ?: null,
                trim($_POST['city']) ?: null, (float)$_POST['price'] ?: null,
                trim($_POST['description']) ?: null,
                $_POST['start_time'] ?: null, $_POST['end_time'] ?: null,
                (int)$_POST['destination_id'], $agency_id, $_POST['activity_type']
            ]);
            $aid = (int)$db->lastInsertId();
            if ($_POST['activity_type'] === 'attraction') {
                $db->prepare("INSERT INTO tourist_attraction (attraction_id,entry_fee,opening_hours,category) VALUES (?,?,?,?)")
                   ->execute([$aid, (float)$_POST['entry_fee'] ?: null, trim($_POST['opening_hours']) ?: null, trim($_POST['category']) ?: null]);
            } else {
                $db->prepare("INSERT INTO restaurant (restaurant_id,cuisine_type,price_range,opening_hours) VALUES (?,?,?,?)")
                   ->execute([$aid, trim($_POST['cuisine_type']) ?: null, $_POST['price_range'] ?: null, trim($_POST['opening_hours']) ?: null]);
            }
            $db->commit();
            set_flash('success','Activity added.');
        } catch (Exception $e) { $db->rollBack(); set_flash('error','Failed: '.$e->getMessage()); }
    } elseif ($action === 'delete_activity') {
        $db->prepare("DELETE FROM activity WHERE activity_id=? AND agency_id=?")->execute([(int)$_POST['id'], $agency_id]);
        set_flash('success','Activity deleted.');
    }

    header('Location: ' . BASE_URL . '/agency/manage_content.php?tab=' . urlencode($tab)); exit;
}

$active_tab = $_GET['tab'] ?? 'flights';

// Load data
$destinations = $db->query("SELECT destination_id, city_name, country FROM destination ORDER BY city_name")->fetchAll();

$flights       = $db->prepare("SELECT f.*,d1.city_name AS origin_city,d2.city_name AS dest_city FROM flight f JOIN destination d1 ON d1.destination_id=f.origin_destination_id JOIN destination d2 ON d2.destination_id=f.destination_id WHERE f.agency_id=? ORDER BY f.departure_time");
$flights->execute([$agency_id]); $flights = $flights->fetchAll();

$accoms  = $db->prepare("SELECT a.*,d.city_name FROM accommodation a JOIN destination d ON d.destination_id=a.destination_id WHERE a.agency_id=? ORDER BY a.name");
$accoms->execute([$agency_id]); $accoms = $accoms->fetchAll();

$transports_q = $db->prepare("SELECT t.*,d1.city_name AS origin_city,d2.city_name AS dest_city FROM transport t JOIN destination d1 ON d1.destination_id=t.origin_destination_id JOIN destination d2 ON d2.destination_id=t.destination_id WHERE t.agency_id=? ORDER BY t.departure_time");
$transports_q->execute([$agency_id]); $transports_list = $transports_q->fetchAll();

$acts = $db->prepare("SELECT a.*,d.city_name FROM activity a JOIN destination d ON d.destination_id=a.destination_id WHERE a.agency_id=? ORDER BY a.name");
$acts->execute([$agency_id]); $activities_list = $acts->fetchAll();

$page_title = 'Manage Content';
require_once __DIR__ . '/../includes/header.php';

// Destination options helper
function dest_options(array $destinations, int $selected=0): string {
    $html = '<option value="">— Destination —</option>';
    foreach ($destinations as $d) {
        $sel = $selected===$d['destination_id'] ? 'selected' : '';
        $html .= "<option value=\"{$d['destination_id']}\" $sel>".htmlspecialchars($d['city_name'].', '.$d['country'])."</option>";
    }
    return $html;
}
?>

<div class="page-header">
    <h1>Manage Content</h1>
    <p>Add and manage flights, accommodation, transport and activities for your packages.</p>
</div>

<div class="tabs">
    <?php foreach (['flights'=>'✈️ Flights','accommodation'=>'🏨 Stays','transport'=>'🚌 Transport','activities'=>'🎯 Activities'] as $t => $l): ?>
    <button class="tab-btn <?= $active_tab===$t?'active':'' ?>" data-tab="<?= $t ?>"><?= $l ?></button>
    <?php endforeach; ?>
</div>

<!-- ═══ FLIGHTS ═══ -->
<div class="tab-panel <?= $active_tab==='flights'?'active':'' ?>" data-panel="flights">

    <div class="card mb-2"><div class="card-body">
        <h4 style="margin-bottom:1rem">Add Flight</h4>
        <form method="POST" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.75rem;align-items:end">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_flight">
            <input type="hidden" name="tab" value="flights">
            <div class="form-group" style="margin:0"><label>Flight No.</label><input type="text" name="flight_number" class="form-control" required placeholder="SA123"></div>
            <div class="form-group" style="margin:0"><label>Airline</label><input type="text" name="airline" class="form-control" required></div>
            <div class="form-group" style="margin:0"><label>Origin</label><select name="origin_id" class="form-control" required><?= dest_options($destinations) ?></select></div>
            <div class="form-group" style="margin:0"><label>Destination</label><select name="destination_id" class="form-control" required><?= dest_options($destinations) ?></select></div>
            <div class="form-group" style="margin:0"><label>Departure</label><input type="datetime-local" name="departure_time" class="form-control" required></div>
            <div class="form-group" style="margin:0"><label>Arrival</label><input type="datetime-local" name="arrival_time" class="form-control" required></div>
            <div class="form-group" style="margin:0"><label>Price (R)</label><input type="number" name="price" class="form-control" min="0" step="0.01" required></div>
            <div class="form-group" style="margin:0"><label>&nbsp;</label><button type="submit" class="btn btn-primary btn-block">Add</button></div>
        </form>
    </div></div>

    <?php if (!empty($flights)): ?>
    <div class="table-wrap card"><table class="data-table">
        <thead><tr><th>Flight</th><th>Airline</th><th>From</th><th>To</th><th>Departure</th><th>Price</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($flights as $f): ?>
        <tr>
            <td><?= e($f['flight_number']) ?></td>
            <td><?= e($f['airline']) ?></td>
            <td><?= e($f['origin_city']) ?></td>
            <td><?= e($f['dest_city']) ?></td>
            <td><?= date('d M Y H:i', strtotime($f['departure_time'])) ?></td>
            <td>R<?= number_format($f['price'],2) ?></td>
            <td><form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="delete_flight"><input type="hidden" name="tab" value="flights"><input type="hidden" name="id" value="<?= $f['flight_id'] ?>"><button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete flight?">Delete</button></form></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div>

<!-- ═══ ACCOMMODATION ═══ -->
<div class="tab-panel <?= $active_tab==='accommodation'?'active':'' ?>" data-panel="accommodation">

    <div class="card mb-2"><div class="card-body">
        <h4 style="margin-bottom:1rem">Add Accommodation</h4>
        <form method="POST" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.75rem;align-items:end">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_accommodation">
            <input type="hidden" name="tab" value="accommodation">
            <div class="form-group" style="margin:0"><label>Name</label><input type="text" name="name" class="form-control" required></div>
            <div class="form-group" style="margin:0"><label>Address</label><input type="text" name="address" class="form-control"></div>
            <div class="form-group" style="margin:0"><label>Destination</label><select name="destination_id" class="form-control" required><?= dest_options($destinations) ?></select></div>
            <div class="form-group" style="margin:0"><label>Bedrooms</label><input type="number" name="no_bedrooms" class="form-control" min="0"></div>
            <div class="form-group" style="margin:0"><label>Bathrooms</label><input type="number" name="no_bathrooms" class="form-control" min="0"></div>
            <div class="form-group" style="margin:0"><label>Price/Night (R)</label><input type="number" name="price_per_night" class="form-control" min="0" step="0.01" required></div>
            <div class="form-group" style="margin:0"><label>&nbsp;</label><button type="submit" class="btn btn-primary btn-block">Add</button></div>
        </form>
    </div></div>

    <?php if (!empty($accoms)): ?>
    <div class="table-wrap card"><table class="data-table">
        <thead><tr><th>Name</th><th>Destination</th><th>Beds</th><th>Baths</th><th>Price/Night</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($accoms as $a): ?>
        <tr>
            <td><?= e($a['name']) ?></td>
            <td><?= e($a['city_name']) ?></td>
            <td><?= $a['no_bedrooms'] ?? '–' ?></td>
            <td><?= $a['no_bathrooms'] ?? '–' ?></td>
            <td>R<?= number_format($a['price_per_night'],2) ?></td>
            <td><form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="delete_accommodation"><input type="hidden" name="tab" value="accommodation"><input type="hidden" name="id" value="<?= $a['accommodation_id'] ?>"><button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete?">Delete</button></form></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div>

<!-- ═══ TRANSPORT ═══ -->
<div class="tab-panel <?= $active_tab==='transport'?'active':'' ?>" data-panel="transport">

    <div class="card mb-2"><div class="card-body">
        <h4 style="margin-bottom:1rem">Add Transport</h4>
        <form method="POST" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.75rem;align-items:end">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_transport">
            <input type="hidden" name="tab" value="transport">
            <div class="form-group" style="margin:0"><label>Type</label>
                <select name="type" class="form-control" required>
                    <?php foreach (['car','taxi','bus','train','shuttle','boat','other'] as $t): ?>
                    <option value="<?=$t?>"><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin:0"><label>Provider</label><input type="text" name="provider" class="form-control"></div>
            <div class="form-group" style="margin:0"><label>Origin</label><select name="origin_id" class="form-control" required><?= dest_options($destinations) ?></select></div>
            <div class="form-group" style="margin:0"><label>Destination</label><select name="destination_id" class="form-control" required><?= dest_options($destinations) ?></select></div>
            <div class="form-group" style="margin:0"><label>Departure</label><input type="datetime-local" name="departure_time" class="form-control" required></div>
            <div class="form-group" style="margin:0"><label>Price (R)</label><input type="number" name="price" class="form-control" min="0" step="0.01" required></div>
            <div class="form-group" style="margin:0"><label>&nbsp;</label><button type="submit" class="btn btn-primary btn-block">Add</button></div>
        </form>
    </div></div>

    <?php if (!empty($transports_list)): ?>
    <div class="table-wrap card"><table class="data-table">
        <thead><tr><th>Type</th><th>Provider</th><th>From</th><th>To</th><th>Departure</th><th>Price</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($transports_list as $t): ?>
        <tr>
            <td><?= e(ucfirst($t['type'])) ?></td>
            <td><?= e($t['provider'] ?? '–') ?></td>
            <td><?= e($t['origin_city']) ?></td>
            <td><?= e($t['dest_city']) ?></td>
            <td><?= date('d M Y H:i', strtotime($t['departure_time'])) ?></td>
            <td>R<?= number_format($t['price'],2) ?></td>
            <td><form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="delete_transport"><input type="hidden" name="tab" value="transport"><input type="hidden" name="id" value="<?= $t['transport_id'] ?>"><button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete?">Delete</button></form></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div>

<!-- ═══ ACTIVITIES ═══ -->
<div class="tab-panel <?= $active_tab==='activities'?'active':'' ?>" data-panel="activities">

    <div class="card mb-2"><div class="card-body">
        <h4 style="margin-bottom:1rem">Add Activity</h4>
        <form method="POST" id="activity-form" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.75rem;align-items:end">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add_activity">
            <input type="hidden" name="tab" value="activities">
            <div class="form-group" style="margin:0"><label>Name</label><input type="text" name="name" class="form-control" required></div>
            <div class="form-group" style="margin:0"><label>Type</label>
                <select name="activity_type" id="activity_type" class="form-control" required onchange="toggleActivityFields()">
                    <option value="attraction">Tourist Attraction</option>
                    <option value="restaurant">Restaurant</option>
                </select>
            </div>
            <div class="form-group" style="margin:0"><label>Destination</label><select name="destination_id" class="form-control" required><?= dest_options($destinations) ?></select></div>
            <div class="form-group" style="margin:0"><label>City</label><input type="text" name="city" class="form-control"></div>
            <div class="form-group" style="margin:0"><label>Price (R)</label><input type="number" name="price" class="form-control" min="0" step="0.01"></div>
            <div class="form-group" style="margin:0"><label>Start Time</label><input type="datetime-local" name="start_time" class="form-control"></div>
            <div class="form-group" style="margin:0"><label>End Time</label><input type="datetime-local" name="end_time" class="form-control"></div>
            <div class="form-group" style="margin:0;grid-column:1/-1"><label>Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
            <!-- Attraction-specific -->
            <div class="form-group attr-field" style="margin:0"><label>Entry Fee (R)</label><input type="number" name="entry_fee" class="form-control" min="0" step="0.01"></div>
            <div class="form-group attr-field" style="margin:0"><label>Category</label><input type="text" name="category" class="form-control"></div>
            <!-- Restaurant-specific -->
            <div class="form-group rest-field" style="margin:0;display:none"><label>Cuisine Type</label><input type="text" name="cuisine_type" class="form-control"></div>
            <div class="form-group rest-field" style="margin:0;display:none"><label>Price Range</label>
                <select name="price_range" class="form-control">
                    <option value="">—</option>
                    <option value="$">$</option><option value="$$">$$</option>
                    <option value="$$$">$$$</option><option value="$$$$">$$$$</option>
                </select>
            </div>
            <!-- Shared -->
            <div class="form-group" style="margin:0"><label>Opening Hours</label><input type="text" name="opening_hours" class="form-control" placeholder="08:00-18:00"></div>
            <div class="form-group" style="margin:0"><label>&nbsp;</label><button type="submit" class="btn btn-primary btn-block">Add</button></div>
        </form>
    </div></div>

    <?php if (!empty($activities_list)): ?>
    <div class="table-wrap card"><table class="data-table">
        <thead><tr><th>Name</th><th>Type</th><th>Destination</th><th>Price</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($activities_list as $a): ?>
        <tr>
            <td><?= e($a['name']) ?></td>
            <td><?= e(ucfirst($a['activity_type'])) ?></td>
            <td><?= e($a['city_name']) ?></td>
            <td><?= $a['price'] ? 'R'.number_format($a['price'],2) : '–' ?></td>
            <td><form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="delete_activity"><input type="hidden" name="tab" value="activities"><input type="hidden" name="id" value="<?= $a['activity_id'] ?>"><button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete?">Delete</button></form></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php endif; ?>
</div>

<script>
function toggleActivityFields() {
    const type = document.getElementById('activity_type').value;
    document.querySelectorAll('.attr-field').forEach(el => el.style.display = type === 'attraction' ? '' : 'none');
    document.querySelectorAll('.rest-field').forEach(el => el.style.display = type === 'restaurant' ? '' : 'none');
}
// Activate the correct tab on page load (from URL)
const urlTab = new URLSearchParams(window.location.search).get('tab');
if (urlTab) {
    document.querySelectorAll('.tab-btn').forEach(b => { if (b.dataset.tab === urlTab) b.click(); });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
