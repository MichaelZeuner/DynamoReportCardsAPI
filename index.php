<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));

require_once(ROOT . '/helpers/http_codes.php');
require_once(ROOT . '/helpers/errors.php');
require_once(ROOT . '/CRUD/CRUD.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$error = new ErrorProcess();
$url = isset($_SERVER['PATH_INFO']) ? explode('/', ltrim($_SERVER['PATH_INFO'], '/')) : [];

if(count($url) <= 1 && empty($url[0])) {
    $error->echoError('No selector recevied');
    die();
}

$collection1 = $url[0];
$item = isset($url[1]) ? $url[1] : null;
$collection2 = isset($url[2]) ? $url[2] : null;

if(isset($collection2)) {
    $selector = $collection2;
    $join = $collection1;
} else {
    $selector = $collection1;
    $join = null; 
}

switch($selector) {
    case 'athletes':
    $athletes = new Athletes($pdo, $error);
    $athletes->process($item, $join);
    break;

    case 'levels':
    $levels = new Levels($pdo, $error);
    $levels->process($item, $join);
    break;

    case 'events':
    $skills = new Events($pdo, $error);
    $skills->process($item, $join);
    break;

    case 'skills':
    $skills = new Skills($pdo, $error);
    $skills->process($item, $join);
    break;

    case 'report-cards':

    break;

    default:
    $error->echoError("Invalid Selector [$selector]");
    break;
}