<?php
include('../resources/db.php');
$response = array();
header("Content-Type: application/json");

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) { //fetch that ID
            $id = mysqli_real_escape_string($link,$_GET['id']);
            $query = "select * from sections where id = $id";
            $result = mysqli_query($link, $query);
            if (mysqli_num_rows($result) == 0) {
                $response['data'] = null;
                $response['statusCode'] = 404;
                $response['message'] = 'Error 404 - Not Found - Section was not found';
            } else {
                $response['data'] = mysqli_fetch_assoc($result);
                $response['statusCode'] = 200;
                $response['message'] = 'Query OK';
                $response['data']['image'] = 'data:' . getimagesizefromstring($response['data']['image'])['mime'] . ';base64,' . base64_encode($response['data']['image']);
            }
        } else {        //fetch all sections
            $query = "select * from sections";
            $result = mysqli_query($link, $query);
            if (mysqli_num_rows($result) == 0) {
                $response['data'] = null;
                $response['statusCode'] = 404;
                $response['message'] = 'Error 404 - Not Found - Section(s) were not found';
            } else {
                $response['data'] = array();
                for ($i = 0; $i < mysqli_num_rows($result); $i++) {
                    $response['data'][$i] = mysqli_fetch_assoc($result);
                    $response['data'][$i]['image'] = 'data:' . getimagesizefromstring($response['data'][$i]['image'])['mime'] . ';base64,' . base64_encode($response['data'][$i]['image']);
                }
                $response['statusCode'] = 200;
                $response['message'] = 'Query OK';
            }
        }
        break;
    case 'POST':
        if (isset($_POST['action']) && $_POST['action'] === 'update') {
            if (isset($_POST['id'])) {
                $id = mysqli_real_escape_string($link,$_POST['id']);
                if (isset($_POST['name'])) {
                    $name = mysqli_real_escape_string($link,$_POST['name']);
                    if (is_uploaded_file($_FILES['image']['tmp_name'])) {
                        $image = file_get_contents($_FILES['image']['tmp_name']);
                        $image = mysqli_real_escape_string($link, $image);
                        $query = "UPDATE sections SET name = '$name', image = '$image' where id = $id";
                        $result = mysqli_query($link, $query);
                        if ($result) {
                            $response['data'] = null;
                            $response['statusCode'] = 200;
                            $response['message'] = 'Query OK - With Image';

                            $toast['title'] = "Edit status";
                            $toast['description'] = "Edit successful";
                            $response['toast'] = $toast;
                        } else {
                            $response['data'] = null;
                            $response['statusCode'] = 500;
                            $response['message'] = 'Error 500 - Internal Server Error';
                        }
                    } else {
                        $query = "UPDATE sections SET name = '$name' where id = $id";
                        $result = mysqli_query($link, $query);
                        if ($result) {
                            $response['data'] = null;
                            $response['statusCode'] = 200;
                            $response['message'] = 'Query OK - No Image';

                            $toast['title'] = "Edit status";
                            $toast['description'] = "Edit successful";
                            $response['toast'] = $toast;
                        } else {
                            $response['data'] = null;
                            $response['statusCode'] = 500;
                            $response['message'] = 'Error 500 - Internal Server Error';
                        }
                    }
                } else {
                    $response['data'] = null;
                    $response['statusCode'] = 400;
                    $response['message'] = 'Error 400 - Bad Request - Missing Required Field \'name\'';
                }
            } else {
                $response['data'] = null;
                $response['statusCode'] = 400;
                $response['message'] = 'Error 400 - Bad Request - Missing Required Field \'id\'';
            }
        } else {
            if (isset($_POST['name'])) {
                $name = mysqli_real_escape_string($link, $_POST['name']);
                if (is_uploaded_file($_FILES['image']['tmp_name'])) {
                    $image = file_get_contents($_FILES['image']['tmp_name']);
                    $image = mysqli_real_escape_string($link, $image);
                    $query = "insert into sections (name, image) values ('$name', '$image')";
                    $result = mysqli_query($link, $query);
                    if (!$result) {
                        $response['data'] = null;
                        $response['statusCode'] = 500;
                        $response['message'] = 'Error 500 - Internal Server Error';

                        $toast['title'] = "Upload Error";
                        $toast['description'] = "Internal Server Error";
                        $toast['type'] = "error";
                        $response['toast'] = $toast;
                    } else {
                        $response['data'] = null;
                        $response['statusCode'] = 200;
                        $response['message'] = 'Query OK';
                        $toast['title'] = "Creation status";
                        $toast['description'] = "Creation Successful";
                        $response['toast'] = $toast;
                    }
                } else {
                    $response['data'] = null;
                    $response['statusCode'] = 400;
                    $response['message'] = 'Error 400 - Bad Request - Missing Required Field \'image\'';

                    $toast['title'] = "Error Creating the Section";
                    $toast['description'] = "Missing required field 'image'";
                    $toast['type'] = "warning";
                    $response['toast'] = $toast;
                }
            } else {
                $response['data'] = null;
                $response['statusCode'] = 400;
                $response['message'] = 'Error 400 - Bad Request - Missing Required Field \'name\'';

                $toast['title'] = "Error Creating the Section";
                $toast['description'] = "Missing required field 'name'";
                $toast['type'] = "warning";
                $response['toast'] = $toast;
            }
        }
        break;
    case 'DELETE':
        if (isset($_GET['id'])) { //delete that ID
            $id = mysqli_real_escape_string($link,$_GET['id']);
            if (isset($_GET['force_delete']) && $_GET['force_delete'] == 'true') {
                $query = "delete from products where section = $id";
                $result = mysqli_query($link, $query);
                if ($result) {
                    $query = "delete from sections where id = $id";
                    $result = mysqli_query($link, $query);
                    if ($result) {
                        $response['data'] = null;
                        $response['statusCode'] = 200;
                        $response['message'] = 'Query OK';
                        $toast['title'] = "Deletion Status";
                        $toast['description'] = "Deletion Successful";
                        $response['toast'] = $toast;
                    } else {
                        $response['data'] = null;
                        $response['statusCode'] = 500;
                        $response['message'] = 'Error 500 - Internal Server Error';

                        $toast['title'] = "Upload Error";
                        $toast['description'] = "Internal Server Error";
                        $toast['type'] = "error";
                        $response['toast'] = $toast;
                    }
                } else {
                    $response['data'] = null;
                    $response['statusCode'] = 500;
                    $response['message'] = 'Error 500 - Internal Server Error';

                    $toast['title'] = "Upload Error";
                    $toast['description'] = "Internal Server Error";
                    $toast['type'] = "error";
                    $response['toast'] = $toast;
                }
            } else {
                $query = "delete from sections where id = $id";
                $result = mysqli_query($link, $query);
                if ($result) {
                    $response['data'] = null;
                    $response['statusCode'] = 200;
                    $response['message'] = 'Query OK';
                    $toast['title'] = "Section Deletion";
                    $toast['description'] = "Section successfully deleted";
                    $response['toast'] = $toast;
                } else {
                    $response['data'] = null;
                    $response['statusCode'] = 500;
                    $response['message'] = 'Error 500 - Internal Server Error';

                    $toast['title'] = "Section Deletion";
                    $toast['description'] = "Could not delete the section - there most likely still are products in that section";
                    $toast['type'] = "error";
                    $response['toast'] = $toast;
                }
            }
        } else {
            $response['data'] = null;
            $response['statusCode'] = 400;
            $response['message'] = 'Error 400 - Bad Request - Missing Required Field \'id\'';
        }
        break;
    default:
        $response['data'] = null;
        $response['statusCode'] = 405;
        $response['message'] = 'Error 405 - Method Not Allowed - Unsupported Operation';
        break;
}
echo json_encode($response);
