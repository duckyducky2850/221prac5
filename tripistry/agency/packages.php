<?php
/* agency/packages.php,  List + delete packages
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('agency');

$db        = get_db();
$agency_id = (int)$_SESSION['agency_id'];

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') 
{
    if (!verify_csrf()) { 
        set_flash('error','Invalid request.'); 
    }
    else {
        $pid = (int)$_POST['package_id'];
        // Only delete own packages
        $stmt = $db->prepare("DELETE FROM travel_package WHERE package_id=? AND agency_id=?");
        $stmt->execute([$pid, $agency_id]);
        set_flash('success', 'Package deleted.');
    }
    header('Location: ' . BASE_URL . '/agency/packages.php'); exit;
}

// Fetch packages with booking + review stats
$stmt = $db->prepare("
    SELECT tp.*,
           COUNT(DISTINCT b.booking_id)  AS booking_count,
           ROUND(AVG(r.rating),1)        AS avg_rating,
           COUNT(DISTINCT r.review_id)   AS review_count
    FROM travel_package tp
    LEFT JOIN booking b ON b.package_id = tp.package_id
    LEFT JOIN review  r ON r.package_id = tp.package_id
    WHERE tp.agency_id = ?
    GROUP BY tp.package_id
    ORDER BY tp.created_at DESC
");
$stmt->execute([$agency_id]);
$packages = $stmt->fetchAll();

$page_title = 'My Packages';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="flex-between page-header">
    <div>
        <h1>My Packages</h1>
        <p>Create and manage the travel packages you offer.</p>
    </div>
    <a href="<?= BASE_URL ?>/agency/package_form.php" class="btn btn-primary">+ New Package</a>
</div>

<?php if (empty($packages)): ?>
    <div class="empty-state">
        <div class="empty-icon">📦</div>
        <p>You haven't created any packages yet.</p>
        <a href="<?= BASE_URL ?>/agency/package_form.php" class="btn btn-primary mt-2">Create Your First Package</a>
    </div>
<?php else: ?>
<div class="table-wrap card">
<table class="data-table">
    <thead><tr><th>Package Name</th><th>Price</th><th>Duration</th><th>Bookings</th><th>Rating</th><th>Created</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($packages as $p): ?>
    <tr>
        <td><a href="<?= BASE_URL ?>/traveller/package_detail.php?id=<?= $p['package_id'] ?>"><?= e($p['name']) ?></a></td>
        <td>R<?= number_format($p['base_price'], 2) ?></td>
        <td><?= $p['duration_days'] ? $p['duration_days'].' days' : '–' ?></td>
        <td><?= $p['booking_count'] ?></td>
        <td>
            <?php if ($p['avg_rating']): ?>
                <span class="stars"><?= str_repeat('★', round($p['avg_rating'])) ?></span>
                <small class="text-muted">(<?= $p['review_count'] ?>)</small>
            <?php else: ?><span class="text-muted">–</span>
            <?php endif; ?>
        </td>
        <td><?= date('d M Y', strtotime($p['created_at'])) ?></td>
        <td>
            <div style="display:flex;gap:.4rem">
                <a href="<?= BASE_URL ?>/agency/package_form.php?id=<?= $p['package_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                <form method="POST" style="display:inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="package_id" value="<?= $p['package_id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm"
                            data-confirm="Delete '<?= e(addslashes($p['name'])) ?>'? This cannot be undone.">Delete</button>
                </form>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
