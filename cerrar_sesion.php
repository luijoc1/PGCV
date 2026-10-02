<?php
	session_start();
	require_once __DIR__ . '/includes/csrf.php';
	requireValidCSRFRequest();
	session_destroy();

	header('location: index.php');
?>
