<?php
header("Access-Control-Allow-Origin:*");
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
require_once "ServerInfo.class.php";

$info= new ServerInfo();
echo json_encode($info->info);
