<?php
/**
 * register.php  –  Registration for travellers and agencies
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    header('Location: ' . BASE_URL . '/index.php'); exit;
}

$errors = [];
$form   = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $form = [
            'role'         => $_POST['role'] ?? '',
            'email'        => trim($_POST['email'] ?? ''),
            'password'     => $_POST['password'] ?? '',
            'password2'    => $_POST['password_confirm'] ?? '',
            // traveller
            'first_name'   => trim($_POST['first_name'] ?? ''),
            'last_name'    => trim($_POST['last_name'] ?? ''),
            'phone_number' => trim($_POST['phone_number'] ?? ''),
            'home_address' => trim($_POST['home_address'] ?? ''),
            // agency
            'company_name' => trim($_POST['company_name'] ?? ''),
            'contact_num'  => trim($_POST['contact_number'] ?? ''),
            'website'      => trim($_POST['website'] ?? ''),
            'address'      => trim($_POST['address'] ?? ''),
            'country'      => trim($_POST['country'] ?? ''),
        ];

        // Validation
        if (!in_array($form['role'], ['traveller', 'agency'])) $errors[] = 'Choose a role.';
        if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL))  $errors[] = 'Enter a valid email.';
        if (strlen($form['password']) < 6)                       $errors[] = 'Password must be at least 6 characters.';
        if ($form['password'] !== $form['password2'])            $errors[] = 'Passwords do not match.';

        if ($form['role'] === 'traveller') {
            if (!$form['first_name']) $errors[] = 'First name is required.';
            if (!$form['last_name'])  $errors[] = 'Last name is required.';
        } else {
            if (!$form['company_name']) $errors[] = 'Company name is required.';
        }

        if (empty($errors)) {
            $db = get_db();
            // Check email unique
            $chk = $db->prepare("SELECT user_id FROM user WHERE email = ?");
            $chk->execute([$form['email']]);
            if ($chk->fetch()) {
                $errors[] = 'That email is already registered.';
            }
        }

        if (empty($errors)) {
            $db = get_db();
            $hash = password_hash($form['password'], PASSWORD_BCRYPT);

            $db->beginTransaction();
            try {
                // Insert user
                $stmt = $db->prepare("INSERT INTO user (email, password, role) VALUES (?, ?, ?)");
                $stmt->execute([$form['email'], $hash, $form['role']]);
                $uid = (int)$db->lastInsertId();

                if ($form['role'] === 'traveller') {
                    $stmt2 = $db->prepare("INSERT INTO traveller
                        (traveller_id, first_name, last_name, phone_number, home_address)
                        VALUES (?, ?, ?, ?, ?)");
                    $stmt2->execute([$uid, $form['first_name'], $form['last_name'],
                        $form['phone_number'] ?: null, $form['home_address'] ?: null]);
                } else {
                    $stmt2 = $db->prepare("INSERT INTO travel_agency
                        (agency_id, company_name, contact_number, website, address, country)
                        VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt2->execute([$uid, $form['company_name'],
                        $form['contact_num'] ?: null, $form['website'] ?: null,
                        $form['address'] ?: null,     $form['country'] ?: null]);
                }

                $db->commit();
                set_flash('success', 'Account created! Please log in.');
                header('Location: ' . BASE_URL . '/login.php'); exit;
            } catch (Exception $ex) {
                $db->rollBack();
                $errors[] = 'Registration failed: ' . $ex->getMessage();
            }
        }
    }
}

$page_title = 'Register';
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-card" style="max-width:560px;margin-top:2rem">
    <h2>Create Account ✈</h2>

    <?php foreach ($errors as $err): ?>
        <div class="flash flash--error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="<?= BASE_URL ?>/register.php" data-validate>
        <?= csrf_field() ?>

        <!-- Role picker -->
        <div class="form-group">
            <label>I am a…</label>
            <div style="display:flex;gap:1rem;margin-top:.3rem">
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer">
                    <input type="radio" name="role" value="traveller" id="role_traveller"
                        <?= ($form['role'] ?? 'traveller') === 'traveller' ? 'checked' : '' ?>>
                    <span>Traveller</span>
                </label>
                <label style="display:flex;align-items:center;gap:.4rem;cursor:pointer">
                    <input type="radio" name="role" value="agency" id="role_agency"
                        <?= ($form['role'] ?? '') === 'agency' ? 'checked' : '' ?>>
                    <span>Travel Agency</span>
                </label>
            </div>
        </div>

        <!-- Common fields -->
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" class="form-control"
                   value="<?= e($form['email'] ?? '') ?>" data-required>
            <div class="form-error">Valid email required.</div>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" data-required>
            <div class="form-error">Password is required.</div>
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirm Password</label>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control" data-required>
            <div class="form-error">Passwords must match.</div>
        </div>

        <!-- Traveller fields (shown/hidden by JS) -->
        <div id="traveller-fields">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" class="form-control"
                       value="<?= e($form['first_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" class="form-control"
                       value="<?= e($form['last_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="phone_number">Phone Number <span class="text-muted">(optional)</span></label>
                <input type="text" id="phone_number" name="phone_number" class="form-control"
                       value="<?= e($form['phone_number'] ?? '') ?>">
            </div>
        </div>

        <!-- Agency fields -->
        <div id="agency-fields" style="display:none">
            <div class="form-group">
                <label for="company_name">Company Name</label>
                <input type="text" id="company_name" name="company_name" class="form-control"
                       value="<?= e($form['company_name'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="contact_number">Contact Number</label>
                <input type="text" id="contact_number" name="contact_number" class="form-control"
                       value="<?= e($form['contact_num'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="website">Website</label>
                <input type="text" id="website" name="website" class="form-control"
                       value="<?= e($form['website'] ?? '') ?>" placeholder="https://...">
            </div>
            <div class="form-group">
                <label for="country">Country</label>
                <input type="text" id="country" name="country" class="form-control"
                       value="<?= e($form['country'] ?? '') ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top:1.25rem">Create Account</button>
    </form>

    <hr class="divider">
    <p class="text-center text-muted" style="font-size:.9rem">
        Already have an account? <a href="<?= BASE_URL ?>/login.php">Log in here</a>
    </p>
</div>

<script>
// Toggle traveller/agency fields
const radios = document.querySelectorAll('input[name="role"]');
const tFields = document.getElementById('traveller-fields');
const aFields = document.getElementById('agency-fields');
function toggleFields() {
    const role = document.querySelector('input[name="role"]:checked')?.value;
    tFields.style.display = role === 'traveller' ? '' : 'none';
    aFields.style.display = role === 'agency'    ? '' : 'none';
}
radios.forEach(r => r.addEventListener('change', toggleFields));
toggleFields();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
