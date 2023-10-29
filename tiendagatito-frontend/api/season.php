<?php
include('../resources/db.php');
$response = array();
header("Content-Type: application/json");

switch ($_SERVER['REQUEST_METHOD']) {
    case 'DELETE':
        if (in_array($_SESSION['data']->id, $adminIDs)) {
            $query = 'update users set points=0';
            $result = mysqli_query($link, $query);
            $query2 = 'update season set creation_timestamp=now()';
            $result2 = mysqli_query($link, $query2);
            if ($result && $result2) {
                $response['data'] = null;
                $response['statusCode'] = 200;
                $response['message'] = 'Query OK';

                $toast['title'] = "Season Reset";
                $toast['description'] = "Season Reset Successful";

                $response['toast'] = $toast;
            } else {
                $response['data'] = null;
                $response['statusCode'] = 500;
                $response['message'] = 'Error 500 - Internal Server Error - Internal Server Error';

                $toast['title'] = "Error";
                $toast['description'] = "Internal Server Error";
                $toast['type'] = "error";
                $response['toast'] = $toast;
            }
        } else {
            $response['data'] = null;
            $response['statusCode'] = 403;
            $response['message'] = 'Error 403 - Forbidden - You are not allowed to interact with this resource';
        }
        break;
    case 'POST':
        if (in_array($_SESSION['data']->id, $adminIDs)) {
            if (isset($_POST['name'])) {
                $name = mysqli_real_escape_string($link, $_POST['name']);
                if (isset($_POST['number'])) {
                    $number = mysqli_real_escape_string($link, $_POST['number']);
                    if (isset($_POST['end'])) {
                        $end = mysqli_real_escape_string($link, $_POST['end']);
                        $query = "update season set name = '$name', number = '$number', end='$end'";
                        $result = mysqli_query($link, $query);
                        if ($result) {
                            $response['data'] = null;
                            $response['statusCode'] = 200;
                            $response['message'] = 'Query OK';

                            $toast['title'] = "Season Update";
                            $toast['description'] = "Update Successful";
                            $response['toast'] = $toast;
                        } else {
                            $response['data'] = null;
                            $response['statusCode'] = 500;
                            $response['message'] = 'Error 500 - Internal Server Error - Internal Server Error';

                            $toast['title'] = "Error";
                            $toast['description'] = "Internal Server Error".mysqli_error($link);
                            $toast['type'] = "error";
                            $response['toast'] = $toast;
                        }
                    } else {
                        $response['data'] = null;
                        $response['statusCode'] = 400;
                        $response['message'] = 'Error 400 - Bad Request - Missing Required Field \'end\'';

                        $toast['title'] = "Season update";
                            $toast['description'] = "Missing Required Field 'end'";
                            $toast['type'] = "warning";
                            $response['toast'] = $toast;
                    }
                } else {
                    $response['data'] = null;
                    $response['statusCode'] = 400;
                    $response['message'] = 'Error 400 - Bad Request - Missing Required Field \'number\'';

                    $toast['title'] = "Season update";
                            $toast['description'] = "Missing Required Field 'number'";
                            $toast['type'] = "warning";
                            $response['toast'] = $toast;
                }
            } else {
                $response['data'] = null;
                $response['statusCode'] = 400;
                $response['message'] = 'Error 400 - Bad Request - Missing Required Field \'name\'';

                $toast['title'] = "Season update";
                            $toast['description'] = "Missing Required Field 'name'";
                            $toast['type'] = "warning";
                            $response['toast'] = $toast;
            }
        } else {
            $response['data'] = null;
            $response['statusCode'] = 403;
            $response['message'] = 'Error 403 - Forbidden - You are not allowed to interact with this resource';
        }
        break;
    default:
        $response['data'] = null;
        $response['statusCode'] = 405;
        $response['message'] = 'Error 405 - Method Not Allowed - Unsupported Operation';
        break;
}

echo json_encode($response);
