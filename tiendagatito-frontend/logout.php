<?php
session_start();
if (isset($_SESSION['loginStatus']) && $_SESSION['loginStatus']) {
    $toast['title'] = "Logged out";
    $toast['description'] = "You've been logged out successfully";
}
session_destroy();
session_start();
if (isset($toast)) {
    $_SESSION['toast'][] = $toast;
}
header("location: /");
