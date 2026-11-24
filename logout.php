<?php
// logout.php - end the session and go home
session_start();
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
