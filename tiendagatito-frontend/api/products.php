<?php
include('../resources/db.php');
$response = array();
header("Content-Type: application/json");

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        if (isset($_GET['id'])) { //fetch that ID
            $id = mysqli_real_escape_string($link, $_GET['id']);
            $query = "select * from products where id = $id";
            $result = mysqli_query($link, $query);
            if (!$result) {
                $response['data'] = null;
                $response['statusCode'] = 500;
                $response['message'] = 'Error 500 - Internal Server Error';

                $toast['title'] = "Product info";
                $toast['description'] = "Error retrieving product data";
                $toast['type'] = "error";
                $response['toast'] = $toast;
            } else if (mysqli_num_rows($result) == 0) {
                $response['data'] = null;
                $response['statusCode'] = 404;
                $response['message'] = 'Error 404 - Not Found - Product not found';

                $toast['title'] = "Product info";
                $toast['description'] = "Product not found";
                $toast['type'] = "error";
                $response['toast'] = $toast;
            } else {
                $response['data'] = mysqli_fetch_assoc($result);
                $response['data']['image'] = 'data:' . getimagesizefromstring($response['data']['image'])['mime'] . ';base64,' . base64_encode($response['data']['image']);
                $response['statusCode'] = 200;
                $response['message'] = 'Query OK';
            }
        } else if (isset($_GET['sections'])) { //fetch all section prods
            $sections = json_decode(stripslashes($_GET['sections']));
            $where = false;
            if (count($sections) == 0) {
                $query = "select * from products";
            } else {
                $query = "select * from products where (section = " . mysqli_real_escape_string($link, $sections[0]);
                $where = true;
                if (count($sections) > 1) {
                    for ($i = 1; $i < count($sections); $i++) {
                        $query .= " OR section = " . mysqli_real_escape_string($link, $sections[$i]);
                    }
                }
                $query .= ")";
            }
            if (isset($_GET['search']) && strlen(trim($_GET['search'])) > 0) {
                $search = mysqli_real_escape_string($link, $_GET['search']);
                $query .= ($where ? " AND " : " WHERE ") . "(name LIKE '$search%' OR name LIKE '% $search%' OR description LIKE '$search%' OR description LIKE '% $search%')";
                $where = true;
            }
            if (isset($_GET['maxPrice']) && !empty($_GET['maxPrice'])) {
                $maxPrice = mysqli_real_escape_string($link, $_GET['maxPrice']);
                $query .= ($where ? " AND " : " WHERE ") . "(price <= $maxPrice)";
                $where = true;
            }
            $result = mysqli_query($link, $query);
            if (!$result) {
                $response['data'] = null;
                $response['statusCode'] = 500;
                $response['message'] = 'Error 500 - Internal Server Error';

                $toast['title'] = "Products refresh";
                $toast['description'] = "Error reloading products";
                $toast['type'] = "error";
                $response['toast'] = $toast;
            } else {
                $response['data'] = array();
                for ($i = 0; $i < mysqli_num_rows($result); $i++) {
                    $response['data'][$i] = mysqli_fetch_assoc($result);
                    $response['data'][$i]['image'] = 'data:' . getimagesizefromstring($response['data'][$i]['image'])['mime'] . ';base64,' . base64_encode($response['data'][$i]['image']);
                }
                $response['statusCode'] = 200;
                $response['message'] = 'Query OK';

                $toast['title'] = "Products refresh";
                $toast['description'] = "Successfully reloaded";
                $response['toast'] = $toast;
            }
        } else {        //fetch all prods
            $query = "select * from products";
            $result = mysqli_query($link, $query);
            if (!$result) {
                $response['data'] = null;
                $response['statusCode'] = 500;
                $response['message'] = 'Error 500 - Internal Server Error';

                $toast['title'] = "Products refresh";
                $toast['description'] = "Error reloading products";
                $toast['type'] = "error";
                $response['toast'] = $toast;
            } else {
                $response['data'] = array();
                for ($i = 0; $i < mysqli_num_rows($result); $i++) {
                    $response['data'][$i] = mysqli_fetch_assoc($result);
                    $response['data'][$i]['image'] = 'data:' . getimagesizefromstring($response['data'][$i]['image'])['mime'] . ';base64,' . base64_encode($response['data'][$i]['image']);
                }
                $response['statusCode'] = 200;
                $response['message'] = 'Query OK';

                $toast['title'] = "Products refresh";
                $toast['description'] = "Successfully reloaded";
                $response['toast'] = $toast;
            }
        }
        break;
    case 'POST':
        if (isset($_POST['action']) && $_POST['action'] == 'update') {
            if (isset($_POST['id'])) {
                $id = mysqli_real_escape_string($link, $_POST['id']);
                if (isset($_POST['name'])) {
                    $name = mysqli_real_escape_string($link, $_POST['name']);
                    if (isset($_POST['description'])) {
                        $description = mysqli_real_escape_string($link, $_POST['description']);
                        if (isset($_POST['stock'])) {
                            $stock = mysqli_real_escape_string($link, $_POST['stock']);
                            if (isset($_POST['price'])) {
                                $price = mysqli_real_escape_string($link, $_POST['price']);
                                if (isset($_POST['section'])) {
                                    $section = mysqli_real_escape_string($link, $_POST['section']);
                                    if (is_uploaded_file($_FILES['image']['tmp_name'])) {
                                        $image = mysqli_real_escape_string($link, file_get_contents($_FILES['image']['tmp_name']));
                                        $query = "update products set name = '$name', description = '$description', stock = $stock, price = $price, section = $section, image = '$image' where id = $id";
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

                                            $toast['title'] = "Update Error";
                                            $toast['description'] = "Internal Server Error";
                                            $toast['type'] = "error";
                                            $response['toast'] = $toast;
                                        }
                                    } else {
                                        $query = "update products set name = '$name', description = '$description', stock = $stock, price = $price, section = $section where id = $id";
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
                                            $response['message'] = 'Error 500 - Internal Server Error ' . mysqli_error($link);

                                            $toast['title'] = "Update Error";
                                            $toast['description'] = "Internal Server Error";
                                            $toast['type'] = "error";
                                            $response['toast'] = $toast;
                                        }
                                    }
                                } else {
                                    $response['data'] = null;
                                    $response['statusCode'] = 400;
                                    $response['message'] = 'Error 400 - Bad Request - Missing Required Field \'section\'';
                                }
                            } else {
                                $response['data'] = null;
                                $response['statusCode'] = 400;
                                $response['message'] = 'Error 400 - Bad Request - Missing Required Field \'price\'';
                            }
                        } else {
                            $response['data'] = null;
                            $response['statusCode'] = 400;
                            $response['message'] = 'Error 400 - Bad Request - Missing Required Field \'stock\'';
                        }
                    } else {
                        $response['data'] = null;
                        $response['statusCode'] = 400;
                        $response['message'] = 'Error 400 - Bad Request - Missing Required Field \'description\'';
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
                if (isset($_POST['description'])) {
                    $description = mysqli_real_escape_string($link, $_POST['description']);
                    if (isset($_POST['stock'])) {
                        $stock = mysqli_real_escape_string($link, $_POST['stock']);
                        if (isset($_POST['price'])) {
                            $price = mysqli_real_escape_string($link, $_POST['price']);
                            if (is_uploaded_file($_FILES['image']['tmp_name'])) {
                                $image = file_get_contents($_FILES['image']['tmp_name']);
                                $image = mysqli_real_escape_string($link, $image);
                                if (isset($_POST['section'])) {
                                    $section = $_POST['section'];
                                    $query = "insert into products (name, description, stock, price, image, section) values ('$name', '$description', $stock, $price, '$image', $section)";
                                    $result = mysqli_query($link, $query);
                                    if ($result) {
                                        $response['data'] = null;
                                        $response['statusCode'] = 200;
                                        $response['message'] = 'Query OK';
                                        $toast['title'] = "Product Creation";
                                        $toast['description'] = "Product successfully created";
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
                                    $response['statusCode'] = 400;
                                    $response['message'] = "Error 400 - Bad Request - Missing Required Field \'section\'";
                                    $toast['title'] = "Error Creating the Product";
                                    $toast['description'] = "Missing required field 'section' " . mysqli_error($link);
                                    $toast['type'] = "warning";
                                    $response['toast'] = $toast;
                                }
                            } else {
                                $response['data'] = null;
                                $response['statusCode'] = 400;
                                $response['message'] = "Error 400 - Bad Request - Missing Required Field \'image\'";
                                $toast['title'] = "Error Creating the Product";
                                $toast['description'] = "Missing required field 'image'";
                                $toast['type'] = "warning";
                                $response['toast'] = $toast;
                            }
                        } else {
                            $response['data'] = null;
                            $response['statusCode'] = 400;
                            $response['message'] = "Error 400 - Bad Request - Missing Required Field \'price\'";
                            $toast['title'] = "Error Creating the Product";
                            $toast['description'] = "Missing required field 'price'";
                            $toast['type'] = "warning";
                            $response['toast'] = $toast;
                        }
                    } else {
                        $response['data'] = null;
                        $response['statusCode'] = 400;
                        $response['message'] = "Error 400 - Bad Request - Missing Required Field \'stock\'";
                        $toast['title'] = "Error Creating the Product";
                        $toast['description'] = "Missing required field 'stock'";
                        $toast['type'] = "warning";
                        $response['toast'] = $toast;
                    }
                } else {
                    $response['data'] = null;
                    $response['statusCode'] = 400;
                    $response['message'] = "Error 400 - Bad Request - Missing Required Field \'description\'";
                    $toast['title'] = "Error Creating the Product";
                    $toast['description'] = "Missing required field 'description'";
                    $toast['type'] = "warning";
                    $response['toast'] = $toast;
                }
            } else {
                $response['data'] = null;
                $response['statusCode'] = 400;
                $response['message'] = "Error 400 - Bad Request - Missing Required Field \'name\'";
                $toast['title'] = "Error Creating the Product";
                $toast['description'] = "Missing required field 'name'";
                $toast['type'] = "warning";
                $response['toast'] = $toast;
            }
        }
        break;
    case 'DELETE':
        if (isset($_GET['id'])) {
            $id = mysqli_real_escape_string($link, $_GET['id']);
            $query = "delete from products where id = $id";
            $result = mysqli_query($link, $query);
            if ($result) {
                $response['data'] = null;
                $response['statusCode'] = 200;
                $response['message'] = 'Query OK';
                $toast['title'] = "Product Deletion";
                $toast['description'] = "Product successfully deleted";
                $response['toast'] = $toast;
            } else {
                $response['data'] = null;
                $response['statusCode'] = 500;
                $response['message'] = 'Error 500 - Internal Server Error';

                $toast['title'] = "Product Deletion";
                $toast['description'] = "Internal Server Error";
                $toast['type'] = "error";
                $response['toast'] = $toast;
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
