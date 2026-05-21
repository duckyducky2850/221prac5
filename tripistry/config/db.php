<?php
/**
 * config/db.php

 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'tripistry');
define('DB_USER', 'root');        
define('DB_PASS', '');            
define('DB_CHARSET', 'utf8mb4');

function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST
             . ';dbname=' . DB_NAME
             . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,  // real prepared statements
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) 
        {
            // Shows a friendly error
            die('<div style="font-family:monospace;padding:2rem;color:red;">
                 <strong>Database connection failed.</strong><br>
                 Check your credentials in <code>config/db.php</code>.<br>
                 Error: ' . htmlspecialchars($e->getMessage()) . '
                 </div>');
        }
    }
    
    return $pdo;
}
