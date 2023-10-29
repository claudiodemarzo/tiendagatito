<?php
include('../resources/db.php');
$response = array();
header("Content-Type: application/json");

if (isset($_SESSION['data']->id)) {
    $id = mysqli_real_escape_string($link, $_SESSION['data']->id);
    $fullName = mysqli_real_escape_string($link, $_POST['fullName']);
    $email = mysqli_real_escape_string($link, $_POST['email']);
    $phoneNumber = mysqli_real_escape_string($link, $_POST['phoneNumber']);
    $addressOne = mysqli_real_escape_string($link, $_POST['addressOne']);
    $addressTwo = mysqli_real_escape_string($link, $_POST['addressTwo']);
    $city = mysqli_real_escape_string($link, $_POST['city']);
    $state = mysqli_real_escape_string($link, $_POST['state']);
    $postalCode = mysqli_real_escape_string($link, $_POST['postalCode']);
    $country = mysqli_real_escape_string($link, $_POST['country']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['statusCode'] = 400;
        $response['message'] = 'Error 400 - Bad Request - Email not valid';
        $toast['title'] = "Invalid email provided";
        $toast['description'] = "The provided email address is invalid or has an invalid format";
        $toast['type'] = "warning";
        $response['toast'] = $toast;
    } else {
        $varArr = array("full name" => $fullName, "phone number" => $phoneNumber, "adresss line 1" => $addressOne, "city" => $city, "state" => $state, "postal code" => $postalCode, "country" => $country);
        $filled = array_filter($varArr);

        if (count($varArr) != count($filled)) {
            $empty = array_diff_key($varArr, $filled);
            $response['statusCode'] = 400;
            $response['message'] = 'Error 400 - Bad Request - Missing one or more required fields';
            $toast['title'] = "Missing required field(s)";
            $toast['description'] = 'The following fields are empty: ' . implode(', ', array_keys($empty));
            $toast['type'] = "warning";
            $response['toast'] = $toast;
        } else {
            $query = "UPDATE users SET fullName='$fullName', email='$email', phoneNumber = '$phoneNumber', addressOne='$addressOne', addressTwo='$addressTwo', city='$city', state='$state', postalCode='$postalCode', country='$country' WHERE twitchID='$id'";
            $result = mysqli_query($link, $query);

            $toast['title'] = "Information update";
            if (!$result) {
                $response['statusCode'] = 500;
                $response['message'] = 'Error 500 - Internal Server Error - ' . mysqli_error($link);

                $toast['description'] = "Error updating information";
                $toast['type'] = "error";
            } else {
                $response['data'] = mysqli_fetch_assoc($result);
                $response['statusCode'] = 200;
                $response['message'] = 'Query OK';

                $toast['description'] = "Shipping information successfully updated";
            }
            $response['toast'] = $toast;
        }
    }
} else {
    $response['data'] = null;
    $response['statusCode'] = 401;
    $response['message'] = 'Error 401 - Unauthorized - You are not logged in';
}

echo json_encode($response);
