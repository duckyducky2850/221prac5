<?php
/*manages group trips*/
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('agency');

$db = get_db();
$agency_id = (int)$_SESSION['agency_id'];

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!verify_csrf())
    {
        set_flash('error','Invalid request.');
    }
    else {
        $gid = (int)$_POST['group_trip_id'];
        $db->prepare("DELETE FROM group_trip WHERE group_trip_id=? AND agency_id=?")->execute([$gid, $agency_id]);
        set_flash('success','Group trip deleted.');
    }
    header('Location: ' . BASE_URL . '/agency/group_trips.php'); exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status') {
    if (!verify_csrf())
    {
        set_flash('error','Invalid request.');
    }
    else {
        $gid    = (int)$_POST['group_trip_id'];
        $status = $_POST['status'] ?? '';
        $valid  = ['open','full','cancelled','completed'];
        if (in_array($status, $valid)) {
            $db->prepare("UPDATE group_trip SET status=? WHERE group_trip_id=? AND agency_id=?")->execute([$status, $gid, $agency_id]);
            set_flash('success','Status updated.');
        }
    }
    header('Location: ' . BASE_URL . '/agency/group_trips.php'); exit;
}

// Handle create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    if (!verify_csrf())
    {
        $errors[] = 'Invalid request.';
    }
    else {
        $pkg_id = (int)($_POST['package_id'] ?? 0);
        $max = (int)($_POST['max_members'] ?? 0);
        $start = $_POST['start_date'] ?? '';
        $end = $_POST['end_date'] ?? '';

        if (!$pkg_id || !$max || !$start || !$end) {
            set_flash('error','All fields are required.');
        } else {
            // Verify package belongs to this agency
            $s = $db->prepare("SELECT package_id FROM travel_package WHERE package_id=? AND agency_id=?");
            $s->execute([$pkg_id, $agency_id]);
            if ($s->fetch()) {
                $stmt = $db->prepare("INSERT INTO group_trip (package_id, agency_id, max_members, start_date, end_date, status) VALUES (?,?,?,?,?,'open')");
                $stmt->execute([$pkg_id, $agency_id, $max, $start, $end]);
                set_flash('success','Group trip created.');
            } else {
                set_flash('error','Invalid package.');
            }
        }
        header('Location: ' . BASE_URL . '/agency/group_trips.php'); exit;
    }
}

// Fetch trips
$stmt = $db->prepare("
    SELECT gt.*, tp.name AS package_name,
           (gt.max_members - gt.current_members) AS spots_left
    FROM group_trip gt
    JOIN travel_package tp ON tp.package_id = gt.package_id
    WHERE gt.agency_id = ?
    ORDER BY gt.start_date DESC
");
$stmt->execute([$agency_id]);
$trips = $stmt->fetchAll();

$my_packages = $db->prepare("SELECT package_id, name FROM travel_package WHERE agency_id=? ORDER BY name");
$my_packages->execute([$agency_id]);
$packages = $my_packages->fetchAll();

$page_title = 'Group Trips';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="flex-between page-header">
    <div><h1>Group Trips</h1><p>Organise and manage group travel offerings.</p></div>
</div>

<!-- Create form -->
<div class="card mb-3">
<div class="card-body">
    <h3 style="margin-bottom:1rem">Create New Group Trip</h3>
    <form method="POST" action="<?= BASE_URL ?>/agency/group_trips.php" data-validate style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">

        <div class="form-group" style="flex:2;min-width:200px;margin-bottom:0">
            <label>Package</label>
            <select name="package_id" class="form-control" data-required>
                <option value="">— Select —</option>
                <?php foreach ($packages as $p): ?>
                <option value="<?= $p['package_id'] ?>"><?= e($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" style="flex:1;min-width:120px;margin-bottom:0">
            <label>Max Members</label>
            <input type="number" name="max_members" class="form-control" min="2" data-required placeholder="12">
        </div>
        <div class="form-group" style="flex:1;min-width:150px;margin-bottom:0">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control" data-required>
        </div>
        <div class="form-group" style="flex:1;min-width:150px;margin-bottom:0">
            <label>End Date</label>
            <input type="date" name="end_date" class="form-control" data-required>
        </div>
        <button type="submit" class="btn btn-primary">Create Trip</button>
    </form>
</div>
</div>

<!-- List -->
<?php if (empty($trips)): ?>
    <div class="empty-state"><div class="empty-icon"><img src="../assets/group.PNG" width = "40" height="40"></div></div><p>No group trips yet.</p></div>
<?php else: ?>
<div class="table-wrap card">
<table class="data-table">
    <thead><tr><th>Package</th><th>Dates</th><th>Members</th><th>Spots Left</th><th>Status</th><th>Update Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($trips as $t): ?>
    <tr>
        <td><?= e($t['package_name']) ?></td>
        <td><?= date('d M Y', strtotime($t['start_date'])) ?> – <?= date('d M Y', strtotime($t['end_date'])) ?></td>
        <td><?= $t['current_members'] ?> / <?= $t['max_members'] ?></td>
        <td><?= max(0, $t['spots_left']) ?></td>
        <td><span class="badge badge-<?= e($t['status']) ?>"><?= e($t['status']) ?></span></td>
        <td>
            <form method="POST" style="display:flex;gap:.3rem">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="group_trip_id" value="<?= $t['group_trip_id'] ?>">
                <select name="status" class="form-control" style="padding:.3rem .5rem;font-size:.85rem">
                    <?php foreach (['open','full','cancelled','completed'] as $st): ?>
                    <option value="<?=$st?>" <?= $t['status']===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-outline btn-sm">Save</button>
            </form>
        </td>
        <td>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="group_trip_id" value="<?= $t['group_trip_id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm" data-confirm="Delete this group trip?">Delete</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
