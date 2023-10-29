<?php
include('../resources/db.php');
$response = array();
header("Content-Type: application/json");

//if (isset($_SESSION['data']) && in_array($_SESSION['data']->id, $adminIDs)) {
    // if (isset($_GET['id'])) {
    //     $id = mysqli_real_escape_string($link, $_GET['id']);
    //     $query = "select * from users where twitchID = '" . $id . "'";
    //     $result = mysqli_query($link, $query);
    //     if (mysqli_num_rows($result) == 0) {
    //         $response['data'] = null;
    //         $response['statusCode'] = 404;
    //         $response['message'] = 'Error 404 - Not Found - User was not found';
    //     } else {
    //         $response['data'] = mysqli_fetch_assoc($result);
    //         $response['statusCode'] = 200;
    //         $response['message'] = 'Query OK';
    //     }
    // } else {
    //     $response['data'] = null;
    //     $response['statusCode'] = 400;
    //     $response['message'] = 'Error 400 - Bad Request - Missing Required Field \'id\'';
    // }
//} else {
 //   http_response_code(404);
 //   die();
//}
if (isset($_SESSION['data']->id)) {
    $twitchID = mysqli_real_escape_string($link, $_SESSION['data']->id);
    if (isset($_GET['id'])) {
        $query = "select * from users where twitchID = '" . $id . "'";
        $result = mysqli_query($link, $query);
        if (mysqli_num_rows($result) == 0) {
            $response['data'] = null;
            $response['statusCode'] = 404;
            $response['message'] = 'Error 404 - Not Found - User was not found';
        } else {
            $fields = mysqli_fetch_assoc($result);
            $isComplete = true;
            foreach($fields as $key => $val){
                if($val == "null"){
                    $isComplete = false;
                    break;
                }
            }
            if($isComplete){
                $response['data'] = null;
                $response['statusCode'] = 200;
                $response['message'] = 'Profile data complete';
            }else{
                $response['data'] = null;
                $response['statusCode'] = 200;
                $response['message'] = 'Missing profile data';
            }
            
        }
    } else {
        $response['data'] = null;
        $response['statusCode'] = 400;
        $response['message'] = 'Error 400 - Bad Request - Missing Required Field \'id\'';
    }
}else{
    $response['data'] = null;
    $response['statusCode'] = 401;
    $response['message'] = 'Error 401 - Unauthorized - You are not logged in';
}
echo json_encode($response);
