<?php
/**
 * traveller/book.php  –  Book a package
 */
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('traveller');

$db         = get_db();
$uid        = current_user_id();
$package_id = (int)($_GET['package_id'] ?? $_POST['package_id'] ?? 0);
$gt_id      = (int)($_GET['group_trip_id'] ?? $_POST['group_trip_id'] ?? 0) ?: null;

if (!$package_id) { header('Location: ' . BASE_URL . '/traveller/packages.php'); exit; }

// Load package
$stmt = $db->prepare("SELECT tp.*, ta.company_name FROM travel_package tp JOIN travel_agency ta ON ta.agency_id=tp.agency_id WHERE tp.package_id=?");
$stmt->execute([$package_id]);
$pkg = $stmt->fetch();
if (!$pkg) { header('Location: ' . BASE_URL . '/traveller/packages.php'); exit; }

// Load group trip if given
$gt = null;
if ($gt_id) {
    $s = $db->prepare("SELECT * FROM group_trip WHERE group_trip_id=? AND package_id=? AND status='open'");
    $s->execute([$gt_id, $package_id]);
    $gt = $s->fetch();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { $errors[] = 'Invalid request.'; }
    else {
        $payment_method = $_POST['payment_method'] ?? '';
        $valid_methods  = ['credit_card','debit_card','paypal','bank_transfer'];
        if (!in_array($payment_method, $valid_methods)) $errors[] = 'Select a payment method.';

        if (empty($errors)) {
            $db->beginTransaction();
            try {
                // Insert booking
                $stmt = $db->prepare("INSERT INTO booking (traveller_id, package_id, group_trip_id, total_price, status) VALUES (?,?,?,?,'confirmed')");
                $stmt->execute([$uid, $package_id, $gt_id, $pkg['base_price']]);
                $booking_id = (int)$db->lastInsertId();

                // Insert receipt
                $receipt_num = 'RCP-' . date('Y') . '-' . str_pad($booking_id, 5, '0', STR_PAD_LEFT);
                $stmt2 = $db->prepare("INSERT INTO receipt (booking_id, amount, payment_method, receipt_number) VALUES (?,?,?,?)");
                $stmt2->execute([$booking_id, $pkg['base_price'], $payment_method, $receipt_num]);

                // Increment group_trip members if applicable
                if ($gt_id) {
                    $db->prepare("UPDATE group_trip SET current_members = current_members + 1 WHERE group_trip_id = ?")->execute([$gt_id]);
                    // Auto-close if full
                    $db->prepare("UPDATE group_trip SET status='full' WHERE group_trip_id=? AND current_members >= max_members")->execute([$gt_id]);
                }

                $db->commit();
                set_flash('success', "Booking confirmed! Receipt: $receipt_num");
                header('Location: ' . BASE_URL . '/traveller/bookings.php'); exit;
            } catch (Exception $e) {
                $db->rollBack();
                $errors[] = 'Booking failed: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Book: ' . $pkg['name'];
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width:600px;margin:0 auto">
    <a href="<?= BASE_URL ?>/traveller/package_detail.php?id=<?= $package_id ?>" class="text-muted" style="font-size:.9rem">← Back to Package</a>

    <div class="form-card" style="max-width:100%;margin-top:1.25rem">
        <h2>Confirm Booking</h2>

        <?php foreach ($errors as $err): ?><div class="flash flash--error"><?= e($err) ?></div><?php endforeach; ?>

        <!-- Summary -->
        <div style="background:var(--clr-bg);border-radius:var(--radius-sm);padding:1rem;margin-bottom:1.5rem">
            <h4 style="margin-bottom:.5rem"><?= e($pkg['name']) ?></h4>
            <p class="text-muted" style="font-size:.88rem">🏢 <?= e($pkg['company_name']) ?></p>
            <?php if ($gt): ?>
                <p style="font-size:.88rem">👥 Group trip: <?= date('d M Y', strtotime($gt['start_date'])) ?> – <?= date('d M Y', strtotime($gt['end_date'])) ?></p>
            <?php endif; ?>
            <?php if ($pkg['duration_days']): ?><p style="font-size:.88rem">⏱ <?= $pkg['duration_days'] ?> days</p><?php endif; ?>
            <hr class="divider">
            <div class="flex-between"><strong>Total</strong><strong class="price-badge">R<?= number_format($pkg['base_price'], 2) ?></strong></div>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/traveller/book.php" data-validate>
            <?= csrf_field() ?>
            <input type="hidden" name="package_id" value="<?= $package_id ?>">
            <?php if ($gt_id): ?><input type="hidden" name="group_trip_id" value="<?= $gt_id ?>"><?php endif; ?>

            <div class="form-group">
                <label for="payment_method">Payment Method</label>
                <select id="payment_method" name="payment_method" class="form-control" data-required>
                    <option value="">— Select —</option>
                    <option value="credit_card">💳 Credit Card</option>
                    <option value="debit_card">💳 Debit Card</option>
                    <option value="paypal">🅿 PayPal</option>
                    <option value="bank_transfer">🏦 Bank Transfer</option>
                </select>
                <div class="form-error">Please select a payment method.</div>
            </div>

            <!-- NOTE: Actual payment processing is NOT implemented here.
                 In production integrate a payment gateway (e.g. PayFast, Stripe).
                 The booking is recorded as 'confirmed' for demo purposes. -->
            <div class="flash flash--info" style="font-size:.85rem">
                ℹ️ Payment processing is simulated for demo purposes. No real transaction occurs.
            </div>

            <button type="submit" class="btn btn-primary btn-block mt-2">Confirm & Pay R<?= number_format($pkg['base_price'], 2) ?></button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
