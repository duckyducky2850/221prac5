<?php
/*Destroy session and redirect*/
require_once __DIR__ . '/config/db.php'; // added confic/db.php because BASE_URL is used
require_once __DIR__ . '/includes/auth.php';
session_destroy();
header('Location: ' . BASE_URL . '/index.php'); exit;
