<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));

define('NONE', 'NONE');
define('COACH', 'COACH');
define('SUPERVISOR', 'SUPERVISOR');
define('ADMIN', 'ADMIN');

require_once(ROOT . '/helpers/http_codes.php');
require_once(ROOT . '/helpers/errors.php');
require_once(ROOT . '/CRUD/CRUD.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$error = new ErrorProcess();
$url = isset($_SERVER['PATH_INFO']) ? explode('/', ltrim($_SERVER['PATH_INFO'], '/')) : [];

$accessLevel = 'NONE';
if (isset($_SERVER) && isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
    $username = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];
    if (isset($username) && isset($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username LIMIT 1");
        $stmt->execute([ "username" => $username ]);
        $user = $stmt->fetch();
        if(isset($user) && isset($user['password_hash'])) {
            if (password_verify($password, $user['password_hash'])) {
                $accessLevel = $user['access'];
            }
        }
    }
}

if(count($url) <= 1 && empty($url[0])) {
    $error->echoError('No selector recevied');
    die();
}

$collection1 = $url[0];
$item = isset($url[1]) ? $url[1] : null;
$collection2 = isset($url[2]) ? $url[2] : null;

if(isset($collection2)) {
    $selector = $collection2;
    $join = str_replace('-', '_', $collection1);
} else {
    $selector = $collection1;
    $join = null; 
}

switch($selector) {
    case 'users':
    $users = new Users($pdo, $error);
    $users->process($item, $join, $accessLevel);
    break;

    case 'athletes':
    $athletes = new Athletes($pdo, $error);
    $athletes->process($item, $join, $accessLevel);
    break;

    case 'levels':
    $levels = new Levels($pdo, $error);
    $levels->process($item, $join, $accessLevel);
    break;

    case 'events':
    $events = new Events($pdo, $error);
    $events->process($item, $join, $accessLevel);
    break;

    case 'skills':
    $skills = new Skills($pdo, $error);
    $skills->process($item, $join, $accessLevel);
    break;
    
    case 'report-cards':
    $reportCards = new ReportCards($pdo, $error);
    $reportCards->process($item, $join, $accessLevel);
    break;
    
    case 'report-cards-components':
    $reportCardsComponents = new ReportCardsComponents($pdo, $error);
    $reportCardsComponents->process($item, $join, $accessLevel);
    break;

    default:
    $error->echoError("Invalid Selector [$selector]");
    break;
}