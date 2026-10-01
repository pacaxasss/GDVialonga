<?php
require_once '../config.php';
require_once '../includes/auth.php';
requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	exit;
}

verifyCsrfToken();
doLogout();
header('Location: login.php');
exit;
