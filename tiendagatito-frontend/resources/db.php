<?php

$adminIDs=array("216564148","110901469","166745044","506217994","706910143");
$host="localhost";
$utente="website";
$pass="website";
$link=mysqli_connect($host,$utente,$pass,"tiendagatito");
if(!$link){
  die("Database connection error");
}
session_start();
 ?>
