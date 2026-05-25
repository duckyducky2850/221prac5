# Tripistry - COS221 Practical 5
**Team32**

## Members
Lillian Muller u25253990
Stephen Molife u25368037
Dian le Roux u25147065
Jay Macaskill u25198387
Marko de Swardt u24658562
-----

## Overview
Tripistry is a  travel booking web application built with PHP, MariaDB, HTML5, CSS3 and JavaScript. It allows travellers to browse destinations, compare and book travel packages, and leave reviews, Travel agencies can manage packages, group trips, flights, accommodation, transport and activities through a dedicated portal. The application also features a dark mode toggle and an AI chatbot (Sir Jarvis the First) using the Google Gemini API.
-----

## Prerequisites
- PHP 8.0+ with PDO, PDO_MySQL, and cURL extensions enabled
- MariaDB 10.x or MySQL 8.x
- XAMPP, WAMP, Laragon, or the PHP built-in server
-----

## Setup

### 1. Clone the repository
```
git clone https://github.com/duckyducky2850/221prac5.git
cd 221prac5
```

### 2. Create the database
Open phpMyAdmin or a MariaDB terminal and run:

```sql
CREATE DATABASE tripistry CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then import the provided dump:

```
mysql -u root -p tripistry < sql/tripistry_dump.sql
```

or use phpMyAdmin: select the `tripistry` database, then Import, then choose `tripistry_dump.sql` then Go.

### 3. Configure database credentials
Open `tripistry/config/db.php`,
edit the following:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tripistry');
define('DB_USER', 'root'); // your MariaDB username here
define('DB_PASS', ''); // your MariaDB password here 
```

### 4. Configure the AI chatbot (optional)
The chatbot requires a free Google Gemini API key. Get one [here](https://aistudio.google.com/apikey)

Create the file `tripistry/config/secrets.php` (this file is gitignored and must be created manually on each machine):

```php
<?php
define('GEMINI_API_KEY', 'your_api_key_here');
```

### 5. Enable cURL in XAMPP
Open `C:/xampp/php/php.ini` **(Windows)** or `/Applications/XAMPP/xamppfiles/etc/php.ini` **(Mac)**

Find and uncomment:

```
extension=curl
```

Restart Apache in the XAMPP Control Panel.

### 6. Serve the application
**XAMPP/WAMP:** Place the `tripistry/` folder inside `htdocs/` and visit `http://localhost/tripistry/`

**PHP built-in server:**

```bash
cd tripistry/
php -S localhost:8000
```

Then visit `http://localhost:8000`

-----

## Sample Login Credentials - for testing/defaults 

These credentials come from the seed data in `tripistry_dump.sql`.
- **Traveller:**
name: Biggy Smith
email: bingbong@gmail.com
password: BingTestPass1

- **Agency:**
name:Demo
email:demo.agency@tripistry.com
password:Password123!

note: these are plain text passwords but our db uses bcrypt hashing

-----

## Customising the Styling

All of our colours, fonts and spacing are defined as CSS custom properties at the top of `css/style.css`. Edit only the `:root` block if you want to restyle the site. Note that if you change these colours it may clash with our custom icons that adhere to the current palette. Here is our current root block:

```css
:root {
    --clr-primary:       #1a6b8a;   /* main brand colour */
    --clr-accent:        #f5a623;   /* buttons, star ratings */
    --clr-bg:            #f7f9fc;   /* page background */
    --font-body:         'Inter', system-ui, sans-serif;
    --font-display:      'Playfair Display', Georgia, serif;
}
```

Dark mode colours are in the `body.dark-mode` block directly below.

-----

## Features

### Traveller

- Register and log in with a dedicated traveller interface
- Browse destinations with tabbed views for flights, stays, transport, attractions and restaurants
- Filter and sort packages by price, destination, duration, rating and agency
- Compare up to 3 packages side-by-side
- Book packages with simulated payment and automatic receipt generation
- Join agency-organised group trips
- Write star ratings and text reviews for packages and agencies
- View booking history and receipts

### Agency

- Register and log in with a dedicated agency interface
- Create, edit and delete travel packages with a dynamic component builder
- Organise and manage group trips with live member tracking
- Add and remove flights, accommodation, transport and activities
- View booking statistics and customer reviews

### General and Extra Functionality

- Dark mode toggle with localStorage persistence across pages + custom dark and light mode backgrounds
- AI travel assistant chatbot (powered by Google Gemini) grounded in live database data
- AJAX live package filtering without page reloads
- Fully responsive layout

-----

## Security

### SQL injection prevention
PDO prepared statements with `?` placeholders on every query. ORDER BY uses a server-side whitelist map. User input never reaches SQL directly. `PDO::ATTR_EMULATE_PREPARES => false` forces native prepared statements.

### XSS prevention
`e()` helper wraps `htmlspecialchars(ENT_QUOTES)`. Applied to every echoed variable throughout the application.  

### CSRF protection
`csrf_token()` generates a 64-character random session token. `csrf_field()` embeds it in every form. `verify_csrf()` validates it on every POST using `hash_equals()`

### Session fixation
`session_regenerate_id(true)` called on every successful login. 

### Password hashing and verification
`password_hash($password, PASSWORD_BCRYPT)` on registration. `password_verify()` on login.

### API key protection
Gemini API key stored in `config/secrets.php` which is listed in `.gitignore` and never committed to version control.

-----

## Git Workflow
Includes branches merged onto main and explanatory commit messages.

*COS221 Practical 5 - Team32 - 2026 - :)*