<?php
include('../resources/db.php');
$response = array();
header("Content-Type: application/json");

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) { //fetch that ID
            $id = mysqli_real_escape_string($link, $_GET['id']);
            $query = "select pu.*,pr.name prodName, pr.description prodDesc, pr.price prodPrice from purchases pu JOIN products pr ON pu.product = pr.id where pu.id = $id";
            $result = mysqli_query($link, $query);
            if (mysqli_num_rows($result) == 0) {
                $response['data'] = null;
                $response['statusCode'] = 404;
                $response['message'] = 'Error 404 - Not Found - Purchase was not found';
            } else {
                $response['data'] = mysqli_fetch_assoc($result);
                $response['statusCode'] = 200;
                $response['message'] = 'Query OK';
            }
        } else if (isset($_GET['user'])) { //fetch purchases by user
            $user = $_GET['user'];
            $query = "select * from purchases where user = '$user'";
            $result = mysqli_query($link, $query);
            if (mysqli_num_rows($result) == 0) {
                $response['data'] = null;
                $response['statusCode'] = 404;
                $response['message'] = 'Error 404 - Not Found - Purchase(s) were not found';
            } else {
                $response['data'] = mysqli_fetch_assoc($result);
                $response['statusCode'] = 200;
                $response['message'] = 'Query OK';
            }
        } else if (isset($_GET['type'])) { //fetch purchases by user
            $type = $_GET['type'];
            if ($type == "includedetails") {
                $query = "select pur.id idpur, pro.name prodname, usr.twitchID twitchid from purchases pur join products pro on pur.product = pro.id join users usr on pur.user = usr.twitchID order by purchase_datetime desc";
                $result = mysqli_query($link, $query);
                if (mysqli_num_rows($result) == 0) {
                    $response['data'] = null;
                    $response['statusCode'] = 404;
                    $response['message'] = 'Error 404 - Not Found - Purchase(s) were not found';
                } else {
                    $response['data'] = array();
                    for ($i = 0; $i < mysqli_num_rows($result); $i++) {
                        $response['data'][$i] = mysqli_fetch_assoc($result);
                    }
                    $response['statusCode'] = 200;
                    $response['message'] = 'Query OK';
                }
            }
        } else {        //fetch all purchases
            $query = "select * from purchases";
            $result = mysqli_query($link, $query);
            if (mysqli_num_rows($result) == 0) {
                $response['data'] = null;
                $response['statusCode'] = 404;
                $response['message'] = 'Error 404 - Not Found - Purchase was not found';
            } else {
                $response['data'] = array();
                for ($i = 0; $i < mysqli_num_rows($result); $i++) {
                    $response['data'][$i] = mysqli_fetch_assoc($result);
                }
                $response['statusCode'] = 200;
                $response['message'] = 'Query OK';
            }
        }
        break;
    default:
        $response['data'] = null;
        $response['statusCode'] = 405;
        $response['message'] = 'Error 405 - Method Not Allowed - Unsupported Operation';
        break;
}
echo json_encode($response);
