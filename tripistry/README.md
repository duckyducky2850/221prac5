# Tripistry – COS221 Practical 5

**Team32** | Lillian Muller | Stephen Molife | Dian le Roux | Jay Macaskill | Marko de Swardt

---

## Quick Setup

### 1. Prerequisites
- PHP 8.0+ with PDO and PDO_MySQL enabled
- MariaDB 10.x / MySQL 8.x
- A local server (XAMPP, WAMP, Laragon, or built-in `php -S`)

### 2. Database
```sql
CREATE DATABASE tripistry CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
Then import the provided dump:
```bash
mysql -u root -p tripistry < tripistry_dump.sql
```

### 3. Configure DB credentials
Open **`config/db.php`** and set your credentials:
```php
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

### 4. Serve the app
**Option A – PHP built-in server (easiest):**
```bash
cd tripistry/
php -S localhost:8000
```
Then visit `http://localhost:8000`

**Option B – XAMPP/WAMP:**
Place the `tripistry/` folder in `htdocs/` and visit `http://localhost/tripistry/`

---

## File Structure

```
tripistry/
├── config/
│   └── db.php               ← *** EDIT THIS: DB credentials ***
├── includes/
│   ├── auth.php             ← Session management, CSRF, role guards
│   ├── header.php           ← Shared HTML head + navbar
│   └── footer.php           ← Shared footer + JS include
├── css/
│   └── style.css            ← All styles (edit CSS variables at top to restyle)
├── js/
│   └── main.js              ← Client-side validation, tabs, AJAX filter, compare
├── index.php                ← Public landing page
├── login.php                ← Login (traveller + agency)
├── register.php             ← Register (traveller + agency)
├── logout.php               ← Destroy session
├── traveller/
│   ├── dashboard.php        ← Traveller home + recent bookings + recommendations
│   ├── destinations.php     ← Browse destinations, flights, stays, activities
│   ├── packages.php         ← Browse + filter + sort + compare packages
│   ├── package_detail.php   ← Full package view with itinerary, reviews, group trips
│   ├── book.php             ← Booking confirmation form
│   ├── bookings.php         ← My bookings + receipt detail + cancel
│   └── reviews.php          ← Write + view my reviews
├── agency/
│   ├── dashboard.php        ← Agency stats + recent bookings + reviews
│   ├── packages.php         ← List + delete packages
│   ├── package_form.php     ← Create + edit packages + manage components
│   ├── group_trips.php      ← Create, update status, delete group trips
│   └── manage_content.php   ← Add/delete flights, accommodation, transport, activities
└── api/
    └── ajax.php             ← JSON/HTML endpoint for live package filtering
```

---

## Customising the Style

All design tokens are CSS custom properties in `css/style.css` at the top:

```css
:root {
    --clr-primary:       #1a6b8a;   /* ← main brand colour */
    --clr-accent:        #f5a623;   /* ← buttons, stars    */
    --font-body:         'Inter';
    --font-display:      'Playfair Display';
    /* ... etc */
}
```
Change these values to completely restyle the site without touching any PHP.

---

## Sample Login Credentials (from seed data)

| Role      | Email                              | Password      |
|-----------|------------------------------------|---------------|
| Traveller | john.doe@email.com                 | hashed_pwd_1  |
| Traveller | sarah.wilson@email.com             | hashed_pwd_2  |
| Agency    | wanderlust.travel@agency.com       | hashed_pwd_6  |
| Agency    | safari.experts@agency.com          | hashed_pwd_7  |

> **Note:** The seed data uses plain-text passwords for convenience during development.
> New registrations use bcrypt (`password_hash`). Before submission, re-hash the
> seed passwords or use the registration page to create fresh accounts.

---

## Security Implementation

| Mechanism          | Where                             |
|--------------------|-----------------------------------|
| SQL Injection      | PDO prepared statements everywhere – no string interpolation in SQL |
| XSS prevention     | `htmlspecialchars()` via `e()` helper on all output |
| CSRF protection    | `csrf_token()` / `verify_csrf()` on every POST form |
| Session fixation   | `session_regenerate_id(true)` on login |
| Password hashing   | `password_hash(…, PASSWORD_BCRYPT)` on registration |
| Role-based access  | `require_role('traveller'/'agency')` guard at top of each protected page |

---

## What Requires a Running DB

The following functionality only works with a live MariaDB connection:
- Login / Register
- All traveller and agency pages
- AJAX filter (falls back gracefully to full-page form submit)

Static pages that work without DB: `index.php` layout (will show error on DB fetch), CSS/JS.

---

## Task 5 Checklist

| Requirement | File(s) |
|---|---|
| Two distinct user types | `login.php`, `register.php`, `includes/auth.php` |
| Browse destinations, flights, accommodation, attractions, restaurants | `traveller/destinations.php` |
| Compare travel packages | `traveller/packages.php` (compare bar + comparison table) |
| Book a package | `traveller/book.php`, `traveller/bookings.php` |
| Leave reviews and ratings | `traveller/reviews.php`, `traveller/package_detail.php` |
| Agency: create/edit/delete packages | `agency/packages.php`, `agency/package_form.php` |
| Agency: manage group trips | `agency/group_trips.php` |
| Agency: manage content (flights/accommodation/transport/activities) | `agency/manage_content.php` |
| Sort and filter packages | `traveller/packages.php` filter bar |
| Detailed package view | `traveller/package_detail.php` |
| SQL injection prevention | PDO prepared statements in all DB queries |
