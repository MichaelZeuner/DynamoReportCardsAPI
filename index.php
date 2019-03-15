<?php
define('HTTP_CODE_OK', 200);
define('HTTP_CODE_CREATED', 201);
define('HTTP_CODE_NO_CONTENT', 204);
define('HTTP_CODE_BAD_REQUEST', 400);
define('HTTP_CODE_NOT_FOUND', 404);

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));

require_once(ROOT . '/helpers/errors.php');
require_once(ROOT . '/CRUD/athletes.php');
require_once(ROOT . '/CRUD/levels.php');
require_once(ROOT . '/CRUD/events.php');
require_once(ROOT . '/CRUD/skills.php');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$error = new ErrorProcess();
$url = isset($_SERVER['PATH_INFO']) ? explode('/', ltrim($_SERVER['PATH_INFO'], '/')) : [];

if(count($url) <= 1 && empty($url[0])) {
    $error->echoError('No selector recevied');
    die();
}

$selector = $url[0];
$item = isset($url[1]) ? $url[1] : null;

switch($selector) {
    case 'athletes':
    $athletes = new Athletes($pdo, $error);
    $athletes->process($item);
    break;

    case 'levels':
    $levels = new Levels($pdo, $error);
    $levels->process($item);
    break;

    case 'events':
    $skills = new Events($pdo, $error);
    $skills->process($item);
    break;

    case 'skills':
    $skills = new Skills($pdo, $error);
    $skills->process($item);
    break;

    default:
    $error->echoError('Invalid Selector');
    break;
}