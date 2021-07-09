<?php
require "vendor/autoload.php";

use Src\DatabaseManager\DatabaseConnector;
use Src\ApiController\ApiController;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$uri = explode('/', $uri);

if ($uri[1] !== 'api.php') {
    header("HTTP/1.1 404 Not Found");
    exit();
}

$envs = parse_ini_file('.env');
foreach ($envs as $key => $value) {
    putenv($key . '=' . $value);
}

$db = new DatabaseConnector;
$dbConnection = $db->getConnection();

$requestMethod = $_SERVER["REQUEST_METHOD"];
$apiController = new ApiController($requestMethod, $uri);
