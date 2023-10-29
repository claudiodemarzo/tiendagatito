<?php
include('resources/db.php');
if (!isset($_POST['userInfo'])) {
    $_SESSION['data'] = null;
    $_SESSION['loginStatus'] = false;
    $toast['title'] = "Login Error";
    $toast['delay'] = 4000;
    $toast['type'] = 'error';
    $toast['description'] = "There was an error during the login process";
    $_SESSION['toast'][] = $toast;
    header("Location: /");
    die();
} else {
    $_SESSION['data'] = json_decode($_POST['userInfo']);
    $_SESSION['loginStatus'] = true;
    $userID = $_SESSION['data']->id;
    $query = "select * from users where twitchID = '$userID'";
    $result = mysqli_query($link, $query);
    if (mysqli_num_rows($result) == 0) {    //show new user toast
        $query = "insert into users (twitchID) values ('$userID')";
        $result = mysqli_query($link, $query);
        $toast['title'] = "Logged in";
        $toast['delay'] = 4000;
        $toast['description'] = "Welcome to tiendagatito.com, " . $_SESSION['data']->display_name;
    } else {    //show welcome back toast
        $toast['title'] = "Logged in";
        $toast['delay'] = 4000;
        $toast['description'] = "Welcome back, " . $_SESSION['data']->display_name;
    }
    $_SESSION['toast'][] = $toast;
}
if (isset($_SESSION['redirect'])) {
    $url = $_SESSION['redirect'];
    unset($_SESSION['redirect']);
    header("Location: $url");
    die();
} else {
    if (isset($_SESSION['currentURI']) && !empty($_SESSION['currentURI'])) {
        header("Location: ".$_SESSION['currentURI']);
        die();
    } else {
        header("Location: /");
        die();
    }
}