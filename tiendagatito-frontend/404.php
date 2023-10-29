<?php
session_start();
$toast['title'] = "Page Not Found";
$toast['description'] = "The Requested page was not found or is unavailable";
$toast['type'] = "warning";

$_SESSION['toast'][] = $toast;

header('Location: /');