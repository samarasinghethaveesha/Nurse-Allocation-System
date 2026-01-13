<?php
session_start();

// Clear all session data
$_SESSION = [];

// Destroy session
session_destroy();

// Redirect to customer login by default
header("Location: index.php");
exit;
?>