<?php
/*Shared page header
$page_title should be set before including this file.*/
require_once __DIR__ . '/../config/db.php'; // added bsc BASE_URL is only defined in db.php
require_once __DIR__ . '/auth.php';

$page_title = $page_title ?? 'Tripistry';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> – Tripistry</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
</head>
<body>

<!-- Apply dark mode BEFORE anything renders to prevent flash -->
<script>
(function() {
    if (localStorage.getItem('darkMode') === '1') {
        document.body.classList.add('dark-mode');
    }
})();
</script>

<nav class="navbar">
    <div class="nav-inner">
        <a href="<?= BASE_URL ?>/index.php" class="nav-brand">✈ Tripistry</a>

        <ul class="nav-links">
            <?php if (!is_logged_in()): ?>
                <li><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                <li><a href="<?= BASE_URL ?>/login.php" class="btn btn-outline">Log In</a></li>
                <li><a href="<?= BASE_URL ?>/register.php" class="btn btn-primary">Register</a></li>

            <?php elseif (current_role() === 'traveller'): ?>
                <li><a href="<?= BASE_URL ?>/traveller/dashboard.php">Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/traveller/destinations.php">Destinations</a></li>
                <li><a href="<?= BASE_URL ?>/traveller/packages.php">Packages</a></li>
                <li><a href="<?= BASE_URL ?>/traveller/bookings.php">My Bookings</a></li>
                <li><a href="<?= BASE_URL ?>/traveller/reviews.php">Reviews</a></li>
                <li><a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline">Log Out</a></li>

            <?php elseif (current_role() === 'agency'): ?>
                <li><a href="<?= BASE_URL ?>/agency/dashboard.php">Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/agency/packages.php">Packages</a></li>
                <li><a href="<?= BASE_URL ?>/agency/group_trips.php">Group Trips</a></li>
                <li><a href="<?= BASE_URL ?>/agency/manage_content.php">Manage Content</a></li>
                <li><a href="<?= BASE_URL ?>/logout.php" class="btn btn-outline">Log Out</a></li>
            <?php endif; ?>

            <!-- Dark mode toggle -->
            <li>
                <button class="dark-toggle" onclick="toggleDarkMode()" id="dark-btn"><img src="../assets/moon.PNG" width = "40" height="40"></div> Dark</button>
            </li>
        </ul>
    </div>
</nav>

<main class="main-content">

<?php if ($flash): ?>
    <div class="flash flash--<?= e($flash['type']) ?>">
        <?= e($flash['msg']) ?>
    </div>
<?php endif; ?>

<!-- ── AI Chatbot Widget ── -->
<div id="chat-bubble" onclick="toggleChat()"
     style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:999;
            width:56px;height:56px;border-radius:50%;
            background:var(--clr-primary);color:#fff;
            display:flex;align-items:center;justify-content:center;
            font-size:1.5rem;cursor:pointer;box-shadow:var(--shadow-lg);
            transition:transform 0.2s;">
    <img src="../assets/chat.PNG" width="30" height="30">
</div>


<div id="chat-window"
     style="display:none;position:fixed;bottom:5rem;right:1.5rem;
            z-index:998;width:320px;height:420px;
            background:var(--clr-surface);border:1px solid var(--clr-border);
            border-radius:var(--radius-lg);box-shadow:var(--shadow-lg);
            flex-direction:column;overflow:hidden;">

    <div style="background:var(--clr-primary);color:#fff;
                padding:.75rem 1rem;font-weight:600;font-size:.95rem;
                display:flex;justify-content:space-between;align-items:center;">
        <span>✈ Tripistry Assistant</span>
        <button onclick="toggleChat()"
                style="background:none;border:none;color:#fff;
                       font-size:1.2rem;cursor:pointer">✕</button>
    </div>

    <div id="chat-messages"
         style="flex:1;overflow-y:auto;padding:.75rem;
                display:flex;flex-direction:column;gap:.5rem;font-size:.88rem;">
        <div style="background:var(--clr-primary-light);
                    padding:.6rem .85rem;border-radius:12px;
                    border-bottom-left-radius:2px;max-width:85%">
            Hi! I'm your Tripistry travel assistant. Ask me about packages, destinations, or pricing! <img src="../assets/globe.PNG" width="40" height="40">
 </div>
    </div>

    <div style="padding:.6rem;border-top:1px solid var(--clr-border);
                display:flex;gap:.4rem;">
        <input type="text" id="chat-input" placeholder="Ask about packages..."
               style="flex:1;padding:.5rem .75rem;border:1.5px solid var(--clr-border);
                      border-radius:var(--radius-sm);font-size:.88rem;
                      font-family:var(--font-body);background:var(--clr-bg);
                      color:var(--clr-text);"
               onkeydown="if(event.key==='Enter') sendChat()">
        <button onclick="sendChat()"
                style="background:var(--clr-primary);color:#fff;border:none;
                       border-radius:var(--radius-sm);padding:.5rem .85rem;
                       cursor:pointer;font-size:.88rem;font-weight:600;">
            Send
        </button>
    </div>
</div>

<script>
const CSRF_TOKEN = '<?= csrf_token() ?>';

// ── Dark mode ────────────────────────────────────────────────
function toggleDarkMode() {
    document.body.classList.toggle('dark-mode');
    const isDark = document.body.classList.contains('dark-mode');
    localStorage.setItem('darkMode', isDark ? '1' : '0');
    
    const btn = document.getElementById('dark-btn');
    if (isDark) {
        btn.innerHTML = '<img src="../assets/sun.PNG" width="40" height="40"> Light';
    } else {
        btn.innerHTML = '<img src="../assets/moon.PNG" width="40" height="40"> Dark';
    }
}

// Set button label to match current state on load
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('dark-btn');
    if (localStorage.getItem('darkMode') === '1') {
        btn.innerHTML = '<img src="../assets/sun.PNG" width="40" height="40"> Light';
    } else {
        btn.innerHTML = '<img src="../assets/moon.PNG" width="40" height="40"> Dark';
    }
});


// ── Chatbot ──────────────────────────────────────────────────
function toggleChat() {
    const win = document.getElementById('chat-window');
    const isHidden = win.style.display === 'none' || win.style.display === '';
    win.style.display = isHidden ? 'flex' : 'none';
    if (isHidden) document.getElementById('chat-input').focus();
}

async function sendChat() {
    const input    = document.getElementById('chat-input');
    const messages = document.getElementById('chat-messages');
    const text     = input.value.trim();
    if (!text) return;

    // Show user message
    messages.innerHTML += `
        <div style="background:var(--clr-primary);color:#fff;
                    padding:.6rem .85rem;border-radius:12px;
                    border-bottom-right-radius:2px;
                    max-width:85%;align-self:flex-end;">
            ${text.replace(/</g,'&lt;')}
        </div>`;
    input.value = '';
    messages.scrollTop = messages.scrollHeight;

    // Typing indicator
    const typing = document.createElement('div');
    typing.id = 'typing';
    typing.style.cssText = 'background:var(--clr-bg);padding:.6rem .85rem;border-radius:12px;max-width:85%;color:var(--clr-text-muted)';
    typing.textContent = 'Typing…';
    messages.appendChild(typing);
    messages.scrollTop = messages.scrollHeight;

    try {
        const res = await fetch('<?= BASE_URL ?>/api/chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text, csrf_token: CSRF_TOKEN })
        });
        const data = await res.json();
        typing.remove();

        messages.innerHTML += `
            <div style="background:var(--clr-primary-light);
                        padding:.6rem .85rem;border-radius:12px;
                        border-bottom-left-radius:2px;max-width:85%;">
                ${(data.reply || data.error || 'No response').replace(/</g,'&lt;')}
            </div>`;
    } catch {
        typing.remove();
        messages.innerHTML += `<div style="color:var(--clr-danger);font-size:.82rem;">Connection error.</div>`;
    }
    messages.scrollTop = messages.scrollHeight;
}
</script>