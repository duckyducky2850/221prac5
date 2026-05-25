<?php
/*Create or edit a travel package. it also manages the package_component relationships.*/
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('agency');

$db = get_db();
$agency_id = (int)$_SESSION['agency_id'];
$edit_id = (int)($_GET['id'] ?? 0);

// loading our existing package 
$pkg = null;
$existing_components = [];

if ($edit_id) 
{
    $s = $db->prepare("SELECT * FROM travel_package WHERE package_id=? AND agency_id=?");
    $s->execute([$edit_id, $agency_id]);
    $pkg = $s->fetch();
    if (!$pkg) { set_flash('error','Package not found.'); header('Location: ' . BASE_URL . '/agency/packages.php'); exit; }

    $s = $db->prepare("SELECT * FROM package_component WHERE package_id=? ORDER BY component_type");
    $s->execute([$edit_id]);
    $existing_components = $s->fetchAll();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    if (!verify_csrf()) { 
        $errors[] = 'Invalid request.'; 
    }
    else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description']  ?? '');
        $base_price = (float)($_POST['base_price'] ?? 0);
        $duration = (int)($_POST['duration_days'] ?? 0);

        // Component arrays
        $c_types = $_POST['component_type'] ?? [];
        $c_ids = $_POST['component_id'] ?? [];

        if (!$name) $errors[] = 'Package name is required.';
        if ($base_price <= 0) $errors[] = 'Base price must be greater than 0.';

        if (empty($errors)) 
        {
            $db->beginTransaction();
            try 
            {
                if ($edit_id) 
                {
                    $stmt = $db->prepare("UPDATE travel_package SET name=?,description=?,base_price=?,duration_days=? WHERE package_id=? AND agency_id=?");
                    $stmt->execute([$name, $description ?: null, $base_price, $duration ?: null, $edit_id, $agency_id]);
                    $pid = $edit_id;
                    // Clearing and re inserting components
                    $db->prepare("DELETE FROM package_component WHERE package_id=?")->execute([$pid]);
                } else {
                    $stmt = $db->prepare("INSERT INTO travel_package (agency_id,name,description,base_price,duration_days) VALUES (?,?,?,?,?)");
                    $stmt->execute([$agency_id, $name, $description ?: null, $base_price, $duration ?: null]);
                    $pid = (int)$db->lastInsertId();
                }

                // Insert components
                $comp_stmt = $db->prepare("INSERT INTO package_component (package_id, component_type, component_id) VALUES (?,?,?)");
                foreach ($c_types as $i => $type) 
                {
                    $cid = (int)($c_ids[$i] ?? 0);
                    $valid_types = ['flight','accommodation','transport','activity'];

                    if ($cid > 0 && in_array($type, $valid_types)) 
                    {
                        $comp_stmt->execute([$pid, $type, $cid]);
                    }
                }

                $db->commit();

                set_flash('success', $edit_id ? 'Package updated.' : 'Package created.');
                header('Location: ' . BASE_URL . '/agency/packages.php'); exit;

            } catch (Exception $e) 
            {
                $db->rollBack();
                $errors[] = 'Save failed: ' . $e->getMessage();
            }
        }
    }
}

// Destination dropdown
$dest_options = $db->query("SELECT destination_id, city_name, country FROM destination ORDER BY city_name")->fetchAll();

// Component options for our dropdowns
$flights = $db->prepare("SELECT flight_id AS id, CONCAT(flight_number,' – ',airline) AS label FROM flight WHERE agency_id=?"); $flights->execute([$agency_id]);
$accommodations = $db->prepare("SELECT accommodation_id AS id, name AS label FROM accommodation WHERE agency_id=?"); $accommodations->execute([$agency_id]);
$transports = $db->prepare("SELECT transport_id AS id, CONCAT(type,' (',COALESCE(provider,''),' )') AS label FROM transport WHERE agency_id=?"); $transports->execute([$agency_id]);
$activities = $db->prepare("SELECT activity_id AS id, name AS label FROM activity WHERE agency_id=?"); $activities->execute([$agency_id]);

$comp_options = [
    'flight' => $flights->fetchAll(),
    'accommodation' => $accommodations->fetchAll(),
    'transport' => $transports->fetchAll(),
    'activity' => $activities->fetchAll(),
];

$page_title = $edit_id ? 'Edit Package' : 'New Package';
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width:720px">
    <a href="<?= BASE_URL ?>/agency/packages.php" class="text-muted" style="font-size:.9rem">← My Packages</a>

    <div class="form-card" style="max-width:100%;margin-top:1rem">
        <h2><?= $edit_id ? 'Edit Package' : 'Create Package' ?></h2>

        <?php foreach ($errors as $err): ?><div class="flash flash--error"><?= e($err) ?></div><?php endforeach; ?>

        <form method="POST" action="<?= BASE_URL ?>/agency/package_form.php<?= $edit_id ? '?id='.$edit_id : '' ?>" data-validate>
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="name">Package Name *</label>
                <input type="text" id="name" name="name" class="form-control" data-required
                       value="<?= e($_POST['name'] ?? $pkg['name'] ?? '') ?>">
                <div class="form-error">Name is required.</div>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?= e($_POST['description'] ?? $pkg['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
    <label for="destination_id">Destination *</label>
    <select id="destination_id" name="destination_id" class="form-control" required>
        <option value="">— Select Destination —</option>
        <?php foreach ($dest_options as $dest): ?>
            <option value="<?= $dest['destination_id'] ?>"
                <?= (($_POST['destination_id'] ?? $pkg['destination_id'] ?? '') == $dest['destination_id']) ? 'selected' : '' ?>>
                <?= e($dest['city_name']) ?>, <?= e($dest['country']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                <div class="form-group">
                    <label for="base_price">Base Price (R) *</label>
                    <input type="number" id="base_price" name="base_price" class="form-control"
                           min="0" step="0.01" data-required
                           value="<?= e($_POST['base_price'] ?? $pkg['base_price'] ?? '') ?>">
                    <div class="form-error">Price required.</div>
                </div>
                <div class="form-group">
                    <label for="duration_days">Duration (days)</label>
                    <input type="number" id="duration_days" name="duration_days" class="form-control"
                           min="1" value="<?= e($_POST['duration_days'] ?? $pkg['duration_days'] ?? '') ?>">
                </div>
            </div>

            <!--Components section-->
            <hr class="divider">
            <div class="flex-between mb-2">
                <h3 style="font-size:1rem">Package Components</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="addComponentRow()">+ Add Component</button>
            </div>
            <p class="text-muted mb-2" style="font-size:.85rem">Add flights, accommodation, transport and activities to this package.</p>

            <div id="component-rows">
            <?php
            // Show existing or one blank row
            $rows_to_show = !empty($existing_components) ? $existing_components : [['component_type'=>'','component_id'=>'']];
            foreach ($rows_to_show as $row):
            ?>
            <div class="component-row" style="display:flex;gap:.75rem;margin-bottom:.75rem;align-items:center">
                <select name="component_type[]" class="form-control" style="flex:1" onchange="updateComponentSelect(this)">
                    <option value="">— Type —</option>
                    <?php foreach (['flight','accommodation','transport','activity'] as $t): ?>
                    <option value="<?=$t?>" <?= ($row['component_type']==$t)?'selected':'' ?>><?= ucfirst($t) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="component_id[]" class="form-control" style="flex:2">
                    <option value="">— Select component —</option>
                    <?php foreach ($comp_options[$row['component_type'] ?: 'flight'] ?? [] as $opt): ?>
                    <option value="<?= $opt['id'] ?>" <?= ($row['component_id']==$opt['id'])?'selected':'' ?>><?= e($opt['label']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">✕</button>
            </div>
            <?php endforeach; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block mt-3"><?= $edit_id ? 'Save Changes' : 'Create Package' ?></button>
        </form>
    </div>
</div>

<script>
// Component options passed from PHP
const COMP_OPTIONS = <?= json_encode($comp_options) ?>;

function addComponentRow() {
    const container = document.getElementById('component-rows');
    const row = document.createElement('div');
    row.className = 'component-row';
    row.style.cssText = 'display:flex;gap:.75rem;margin-bottom:.75rem;align-items:center';
    row.innerHTML = `
        <select name="component_type[]" class="form-control" style="flex:1" onchange="updateComponentSelect(this)">
            <option value="">— Type —</option>
            <option value="flight">Flight</option>
            <option value="accommodation">Accommodation</option>
            <option value="transport">Transport</option>
            <option value="activity">Activity</option>
        </select>
        <select name="component_id[]" class="form-control" style="flex:2">
            <option value="">— Select component —</option>
        </select>
        <button type="button" class="btn btn-danger btn-sm" onclick="this.parentElement.remove()">✕</button>
    `;
    container.appendChild(row);
}

function updateComponentSelect(typeSelect) {
    const type = typeSelect.value;
    const idSelect = typeSelect.nextElementSibling;
    idSelect.innerHTML = '<option value="">— Select component —</option>';
    const opts = COMP_OPTIONS[type] || [];
    opts.forEach(o => {
        const opt = document.createElement('option');
        opt.value = o.id;
        opt.textContent = o.label;
        idSelect.appendChild(opt);
    });
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
