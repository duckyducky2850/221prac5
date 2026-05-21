<?php
/*Login for both travellers and agencies
SQL injection prevention: all DB queries use PDO prepared statements.
CSRF protection: every POST verified against session token.*/
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) 
{
    header('Location: ' . (current_role() === 'agency' ? BASE_URL . '/agency/dashboard.php' : BASE_URL . '/traveller/dashboard.php'));
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    //CSRF check
    if (!verify_csrf()) 
    {
        $errors[] = 'Invalid request. Please try again.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) 
        {
            $errors[] = 'Email and password are required.';
        } else {
            $db = get_db();
            // Prepared statement
            $stmt = $db->prepare("SELECT user_id, email, password, role FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) 
            {
                $errors[] = 'Incorrect email or password.';
                $user = null;
            }

            if ($user && empty($errors)) 
            {
                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int)$user['user_id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];

                // Fetch display name
                if ($user['role'] === 'traveller') {
                    $s = $db->prepare("SELECT first_name, last_name FROM traveller WHERE traveller_id = ?");
                    $s->execute([$user['user_id']]);
                    $profile = $s->fetch();
                    $_SESSION['display_name'] = trim(($profile['first_name'] ?? '') . ' ' . ($profile['last_name'] ?? ''));
                } else {
                    $s = $db->prepare("SELECT company_name FROM travel_agency WHERE agency_id = ?");
                    $s->execute([$user['user_id']]);
                    $profile = $s->fetch();
                    $_SESSION['display_name'] = $profile['company_name'] ?? 'Agency';
                    $_SESSION['agency_id'] = (int)$user['user_id'];
                }

                $redirect = $_GET['redirect'] ?? ($user['role'] === 'agency' ? BASE_URL . '/agency/dashboard.php' : BASE_URL . '/traveller/dashboard.php');
                header('Location: ' . $redirect); exit;
            }
        }
    }
}

$page_title = 'Log In';
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-card" style="margin-top:2rem">
    <h2>Welcome Back ✈</h2>

    <?php foreach ($errors as $err): ?>
        <div class="flash flash--error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="POST" action="<?= BASE_URL ?>/login.php" data-validate>
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control"
                   value="<?= e($_POST['email'] ?? '') ?>"
                   data-required placeholder="you@example.com">
            <div class="form-error">Email is required.</div>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control"
                   data-required placeholder="••••••••">
            <div class="form-error">Password is required.</div>
        </div>

        <button type="submit" class="btn btn-primary btn-block" style="margin-top:1.25rem">Log In</button>
    </form>

    <hr class="divider">
    <p class="text-center text-muted" style="font-size:.9rem">
        Don't have an account? <a href="<?= BASE_URL ?>/register.php">Register here</a>
    </p>

    <!--Dev hint-->

    <!--This is a big security issue (plain text credentials in html), should either be removed later with the final product or we should use .env loader-->
    <details style="margin-top:1.5rem;font-size:.8rem;color:var(--clr-text-muted)">
        <summary>Dev: Sample login credentials</summary>
        <p style="margin-top:.5rem"><strong>Traveller:</strong> john.doe@email.com / hashed_pwd_1</p>
        <p><strong>Agency:</strong> wanderlust.travel@agency.com / hashed_pwd_6</p>
    </details>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
