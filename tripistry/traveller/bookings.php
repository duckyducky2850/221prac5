<?php
/*View and manage my bookings*/
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('traveller');

$db  = get_db();
$uid = current_user_id();

// Handle cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    if (!verify_csrf()) {
        set_flash('error', 'Invalid request.');
    }
    else {
        $bid = (int)($_POST['booking_id'] ?? 0);
        $stmt = $db->prepare("UPDATE booking SET status='cancelled' WHERE booking_id=? AND traveller_id=? AND status IN ('pending','confirmed')");
        $stmt->execute([$bid, $uid]);
        set_flash('success', 'Booking cancelled.');
    }
    header('Location: ' . BASE_URL . '/traveller/bookings.php'); exit;
}

// Load bookings
$stmt = $db->prepare("
    SELECT b.booking_id, b.booking_date, b.total_price, b.status, b.group_trip_id,
           tp.name AS package_name, tp.package_id, tp.duration_days,
           ta.company_name,
           r.receipt_number, r.payment_method, r.payment_date,
           gt.start_date, gt.end_date
    FROM booking b
    JOIN travel_package tp ON tp.package_id = b.package_id
    JOIN travel_agency ta ON ta.agency_id = tp.agency_id
    LEFT JOIN receipt r ON r.booking_id = b.booking_id
    LEFT JOIN group_trip gt ON gt.group_trip_id = b.group_trip_id
    WHERE b.traveller_id = ?
    ORDER BY b.booking_date DESC");
$stmt->execute([$uid]);
$bookings = $stmt->fetchAll();

// Single booking detail view
$detail_id = (int)($_GET['id'] ?? 0);
$detail = null;
if ($detail_id) {
    foreach ($bookings as $b) {
        if ((int)$b['booking_id'] === $detail_id) {
            $detail = $b; break;
            }
    }
}

$page_title = 'My Bookings';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <h1>My Bookings</h1>
    <p>View your booking history and receipts.</p>
</div>

<?php if ($detail): ?>
<!--Booking detail-->
<div style="max-width:580px">
    <a href="<?= BASE_URL ?>/traveller/bookings.php" class="text-muted" style="font-size:.9rem">← All Bookings</a>
    <div class="card mt-2">
        <div class="card-body">
            <h2 style="margin-bottom:.25rem"><?= e($detail['package_name']) ?></h2>
            <p class="text-muted mb-2"><img src="<?= BASE_URL ?>/assets/building.PNG" width = "40" height="40"> <?= e($detail['company_name']) ?></p>
            <span class="badge badge-<?= e($detail['status']) ?>"><?= e($detail['status']) ?></span>

            <hr class="divider">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;font-size:.9rem">
                <div><span class="text-muted">Booking Date</span><br><strong><?= date('d M Y', strtotime($detail['booking_date'])) ?></strong></div>
                <div><span class="text-muted">Total Price</span><br><strong>R<?= number_format($detail['total_price'], 2) ?></strong></div>
                <?php if ($detail['duration_days']): ?>
                <div><span class="text-muted">Duration</span><br><strong><?= $detail['duration_days'] ?> days</strong></div>
                <?php endif; ?>
                <?php if ($detail['group_trip_id']): ?>
                <div><span class="text-muted">Trip Dates</span><br><strong><?= date('d M Y', strtotime($detail['start_date'])) ?> – <?= date('d M Y', strtotime($detail['end_date'])) ?></strong></div>
                <?php endif; ?>
            </div>

            <?php if ($detail['receipt_number']): ?>
            <hr class="divider">
            <h4 style="margin-bottom:.5rem">Receipt</h4>
            <div style="font-size:.9rem;display:flex;flex-direction:column;gap:.3rem">
                <div class="flex-between"><span class="text-muted">Receipt #</span><strong><?= e($detail['receipt_number']) ?></strong></div>
                <div class="flex-between"><span class="text-muted">Payment</span><strong><?= e(str_replace('_',' ',$detail['payment_method'])) ?></strong></div>
                <div class="flex-between"><span class="text-muted">Paid on</span><strong><?= date('d M Y H:i', strtotime($detail['payment_date'])) ?></strong></div>
                <div class="flex-between"><span class="text-muted">Amount</span><strong>R<?= number_format($detail['total_price'], 2) ?></strong></div>
            </div>
            <?php endif; ?>

            <?php if (in_array($detail['status'], ['pending', 'confirmed'])): ?>
            <hr class="divider">
            <form method="POST" action="<?= BASE_URL ?>/traveller/bookings.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="booking_id" value="<?= $detail['booking_id'] ?>">
                <button type="submit" class="btn btn-danger btn-sm"
                        data-confirm="Cancel this booking?">Cancel Booking</button>
            </form>
            <?php endif; ?>

            <hr class="divider">
            <a href="<?= BASE_URL ?>/traveller/reviews.php?package_id=<?= $detail['package_id'] ?>" class="btn btn-outline btn-sm">Write a Review</a>
        </div>
    </div>
</div>

<?php else: ?>
<!--Bookings list-->
<?php if (empty($bookings)): ?>
    <div class="empty-state">
        <div class="empty-icon"><img src="<?= BASE_URL ?>/assets/map.PNG" width = "40" height="40"></div>
        <p>No bookings yet.</p>
        <a href="<?= BASE_URL ?>/traveller/packages.php" class="btn btn-primary mt-2">Browse Packages</a>
    </div>
<?php else: ?>
<div class="table-wrap card">
<table class="data-table">
    <thead><tr><th>Package</th><th>Agency</th><th>Date</th><th>Price</th><th>Status</th><th>Receipt</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($bookings as $b): ?>
    <tr>
        <td><a href="<?= BASE_URL ?>/traveller/package_detail.php?id=<?= $b['package_id'] ?>"><?= e($b['package_name']) ?></a></td>
        <td><?= e($b['company_name']) ?></td>
        <td><?= date('d M Y', strtotime($b['booking_date'])) ?></td>
        <td>R<?= number_format($b['total_price'], 2) ?></td>
        <td><span class="badge badge-<?= e($b['status']) ?>"><?= e($b['status']) ?></span></td>
        <td><?= $b['receipt_number'] ? e($b['receipt_number']) : '–' ?></td>
        <td><a href="<?= BASE_URL ?>/traveller/bookings.php?id=<?= $b['booking_id'] ?>" class="btn btn-outline btn-sm">View</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php endif; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
