<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../resources/phpmailer/Exception.php';
require '../resources/phpmailer/PHPMailer.php';
require '../resources/phpmailer/SMTP.php';
include('../resources/db.php');
$response = array();
header("Content-Type: application/json");

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        if (isset($_SESSION['data']->id)) {
            $twitchID = mysqli_real_escape_string($link, $_SESSION['data']->id);
            if (isset($_POST['product'])) {
                $product = mysqli_real_escape_string($link, $_POST['product']);

                $query = "select * from users where twitchID = '" . $twitchID . "'";
                $result = mysqli_query($link, $query);
                if (mysqli_num_rows($result) == 0) {
                    $response['data'] = null;
                    $response['statusCode'] = 404;
                    $response['message'] = 'Error 404 - Not Found - User was not found';
                } else {
                    $fields = mysqli_fetch_assoc($result);
                    $isComplete = true;
                    $response['fields'] = $fields;
                    foreach ($fields as $key => $val) {
                        if ($key !== "addressTwo") {
                            if ($val == "" || $val == null) {
                                $isComplete = false;
                                break;
                            }
                        }
                    }
                    if ($isComplete) {
                        $query = "select * from products where id = $product";
                        $result = mysqli_query($link, $query);
                        if (mysqli_num_rows($result) == 0) {
                            $response['data'] = null;
                            $response['statusCode'] = 403;
                            $response['message'] = 'Error 400 - Bad Request - Invalid value for field \'product\'';

                            $toast['title'] = "Información de compra";
                            $toast['description'] = "Fallo al seleccionar el producto";
                            $toast['type'] = "error";
                            $response['toast'] = $toast;
                        } else {
                            $productInfo = mysqli_fetch_assoc($result);
                            if($productInfo['stock'] > 0){
                            $userInfo = mysqli_fetch_assoc(mysqli_query($link, "select * from users where twitchID = '$twitchID'"));
                            if ($userInfo['points'] >= $productInfo['price']) {
                                mysqli_begin_transaction($link);
                                mysqli_autocommit($link, false);
                                $pointsq = mysqli_query($link, "update users set points = points - " . $productInfo['price'] . " where twitchID = '$twitchID'");
                                $purchaseq = mysqli_query($link, "insert into purchases (user, product, purchase_datetime, points, fullName, addressOne, addressTwo, city, state, postalCode, country, email, phoneNumber) values ('$twitchID', $product, now(), " . $productInfo['price'] . ", '" . $userInfo['fullName'] . "',' " . $userInfo['addressOne'] . "', " . (empty($userInfo['addressTwo']) ? "NULL" : "'" . $userInfo['addressTwo'] . "'") . ", '" . $userInfo['city'] . "', '" . $userInfo['state'] . "', '" . $userInfo['postalCode'] . "', '" . $userInfo['country'] . "', '" . $userInfo['email'] . "', '" . $userInfo['phoneNumber'] . "')");
                                $stockq = mysqli_query($link, "update products set stock = stock - 1 where id = $product");
                                if ($pointsq && $purchaseq && $stockq) {
                                    mysqli_commit($link);
                                    $response['remainingPts'] = $userInfo['points'] - $productInfo['price'];
                                    $response['statusCode'] = 200;
                                    $response['message'] = 'Query OK';
                                    sendEmail($userInfo['email'], $userInfo['fullName'], "Gracias por tu compra! | tiendagatito.com", "Has completado correctamente una compra en tiendagatito.com, puedes checar los detalles de tu compra en la pagina de tu cuenta de este sitio web.");

                                    sendEmail('contactogatoronron@gmail.com', 'GatoRonRon', 'New Purchase on tiendagatito.com', 'Someone made a new purchase on the website. Login into the admin panel for more info');

                                    $toast['title'] = "Thank You for your purchase";
                                    $toast['description'] = "You have successfully completed the purchase";
                                    $response['toast'] = $toast;
                                } else {
                                    mysqli_rollback($link);
                                    $response['data'] = null;
                                    $response['statusCode'] = 500;
                                    $response['message'] = 'Error 500 - Internal Server Error - Internal Server Error';

                                    $toast['title'] = "Error";
                                    $toast['description'] = "Error de Servidor Interno";
                                    $toast['type'] = "error";
                                    $response['toast'] = $toast;
                                }
                            } else {
                                $response['data'] = null;
                                $response['statusCode'] = 403;
                                $response['message'] = 'Error 403 - Unauthorized - You don\'t have enough points';

                                $toast['title'] = "No se pudo completar la compra";
                                $toast['description'] = "No tienes puntos suficientes";
                                $toast['type'] = "error";
                                $response['toast'] = $toast;
                            }
                        }else{
                            $response['data'] = null;
                                $response['statusCode'] = 403;
                                $response['message'] = 'Error 403 - Unauthorized - Product is out of stock';
                                $toast['title'] = "No se pudo completar la compra";
                                $toast['description'] = "El producto está fuera de stock";
                                $toast['type'] = "error";
                                $response['toast'] = $toast;
                            }
                        }
                    } else {
                        $response['data'] = null;
                        $response['statusCode'] = 403;
                        $response['message'] = 'Missing profile data';

                        $toast['title'] = "No se pudo completar la compra";
                        $toast['description'] = "Compra no autorizada!<br>Faltan datos de perfil";
                        $toast['type'] = "error";
                        $response['toast'] = $toast;
                    }
                }
            } else {
                $response['data'] = null;
                $response['statusCode'] = 403;
                $response['message'] = 'Error 400 - Bad Request - Missing required field \'product\'';

                $toast['title'] = "Información de compra";
                $toast['description'] = "Fallo al seleccionar el producto";
                $toast['type'] = "error";
                $response['toast'] = $toast;
            }
        } else {
            $response['data'] = null;
            $response['statusCode'] = 401;
            $response['message'] = 'Error 401 - Unauthorized - You are not logged in';

            if (isset($_POST['product'])) {
                $product = mysqli_real_escape_string($link, $_POST['product']);
                $query = "select * from products where id = $product";
                $result = mysqli_query($link, $query);
                if (mysqli_num_rows($result) == 1) {
                    $response['confirmPurchase'] = true;
                }
            } else {
                $_SESSION['redirect'] = "/catalog";

                $toast['title'] = "Información del producto";
                $toast['description'] = "Producto no encontrado";
                $toast['type'] = "error";
                $_SESSION['toast'][] = $toast;
            }
        }
        break;
    case 'GET':
        if (isset($_SESSION['data']->id)) {
            $twitchID = mysqli_real_escape_string($link, $_SESSION['data']->id);
            if (isset($_GET['product'])) {
                $product = mysqli_real_escape_string($link, $_GET['product']);
                $query = "select * from products where id = $product";
                $result = mysqli_query($link, $query);
                if (mysqli_num_rows($result) == 0) {
                    $response['data'] = null;
                    $response['statusCode'] = 403;
                    $response['message'] = 'Error 400 - Bad Request - Invalid value for field \'product\'';

                    $toast['title'] = "Información de compra";
                    $toast['description'] = "Fallo al seleccionar el producto";
                    $toast['type'] = "error";
                    $response['toast'] = $toast;
                } else {
                    $result = mysqli_fetch_assoc($result);
                    if ($result['stock'] == 0) {
                        $response['data'] = null;
                        $response['statusCode'] = 403;
                        $response['message'] = 'Error 400 - Bad Request - Invalid value for field \'product\'';

                        $toast['title'] = "Out of stock";
                        $toast['description'] = "El producto seleccionado no está disponible actualmente";
                        $toast['type'] = "error";
                        $response['toast'] = $toast;
                    } else {
                        $response['data'] = null;
                        $response['statusCode'] = 200;
                        $response['message'] = 'Query OK';
                        if (!isset($_GET['page']) || $_GET['page'] != "local") {
                            $_SESSION['confirmPurchase'] = true;
                        }
                    }
                }
            } else {
                $response['data'] = null;
                $response['statusCode'] = 403;
                $response['message'] = 'Error 400 - Bad Request - Missing required field \'product\'';
                $toast['title'] = "Información de compra";
                $toast['description'] = "Fallo al seleccionar el producto";
                $toast['type'] = "error";
                $response['toast'] = $toast;
            }
        } else {
            $response['data'] = null;
            $response['statusCode'] = 401;
            $response['message'] = 'Error 401 - Unauthorized - You are not logged in';

            if (isset($_GET['product'])) {
                $product = mysqli_real_escape_string($link, $_GET['product']);
                $query = "select * from products where id = $product";
                $result = mysqli_query($link, $query);
                if (mysqli_num_rows($result) == 1) {
                    $_SESSION['confirmPurchase'] = true;
                }
                $_SESSION['redirect'] = "/product/$product";
            } else {
                $_SESSION['redirect'] = "/catalog";

                $toast['title'] = "Información del producto";
                $toast['description'] = "Producto no encontrado";
                $toast['type'] = "error";
                $_SESSION['toast'][] = $toast;
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

function sendEmail($recipientMail, $recipientName, $subj, $body)
{
    $mail = new PHPMailer;

    $mail->isSMTP(true);
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "gatoronronshop@gmail.com";
    $mail->Password = "grr8player";
    $mail->SMTPSecure = "tls";
    $mail->Port = 587;
    $mail->From = "gatoronronshop@gmail.com";
    $mail->FromName = "Tiendagatito.com";
    $mail->addAddress($recipientMail, $recipientName);
    $mail->Subject = $subj;
    $mail->Body = $body;

    try {
        $mail->send();
        //echo "Message has been sent successfully";
    } catch (Exception $e) {
        //echo "Mailer Error: " . $mail->ErrorInfo;
    }
}
