<?php
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(__FILE__));

define('NONE', 'NONE');
define('COACH', 'COACH');
define('SUPERVISOR', 'SUPERVISOR');
define('ADMIN', 'ADMIN');

require_once(ROOT . '/helpers/http_codes.php');
require_once(ROOT . '/helpers/errors.php');
require_once(ROOT . '/helpers/get-report-cards.php');
require_once(ROOT . '/CRUD/CRUD.php');

//header("Access-Control-Allow-Headers: Authorization");
//header("Content-Type: application/json; charset=UTF-8");
//header("Access-Control-Allow-Methods: POST");
//header("Access-Control-Allow-Origin: *");



if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers:        {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
}




$error = new ErrorProcess();
$path_info = !empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : (!empty($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : '');

$url = explode('/', ltrim($path_info, '/'));

$accessLevel = 'NONE';
$loggedInUser = null;

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
                unset($user['password_hash']);
                $loggedInUser = $user;
            }
        }
    }
} else if(isLocal()) {
    $error->echoError('Probably no access -- silly silly -- required only for local. Something causes multiple call with CORS on local');
    die();
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

//crud
switch($selector) {
    case 'login':
    if(empty($loggedInUser)) {
        http_response_code(HTTP_CODE_NOT_AUTHORIZED);
        $error->echoError('Incorrect username or password');
    } else {
        http_response_code(HTTP_CODE_OK);
        echo json_encode($loggedInUser);
    }
    break;

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

    //others
    switch($url[0]) {
        case 'level-events':
        $stmt = $pdo->prepare("SELECT DISTINCT events.id, events.name FROM events INNER JOIN skills ON skills.events_id = events.id WHERE levels_id = :id AND events.active = 1");
        $stmt->execute(['id' => $item]);

        $results = $stmt->fetchAll();
        if(count($results) == 0) {
            http_response_code(HTTP_CODE_NOT_FOUND);
            $error->echoError('No data found');
        } else {
            http_response_code(HTTP_CODE_OK);
            echo json_encode($results);
        }
        break;

        case 'level-event-skills':
        $stmt = $pdo->prepare("SELECT DISTINCT id, name FROM skills WHERE levels_id = :levels_id AND events_id = :events_id AND active = 1");
        $stmt->execute(['levels_id' => $url[1], 'events_id' => $url[2]]);
    
        $results = $stmt->fetchAll();
        if(count($results) == 0) {
            http_response_code(HTTP_CODE_NOT_FOUND);
            $error->echoError('No data found');
        } else {
            http_response_code(HTTP_CODE_OK);
            echo json_encode($results);
        }
        break;

        case 'report-cards-requiring-approval':
        getReportCards($pdo, $error, 'approved is null');
        break;

        case 'report-cards-completed':
        $stmt = $pdo->prepare("SELECT access FROM users WHERE id = :id");
        $stmt->execute(['id' => $url[1]]);
        $access = $stmt->fetch()['access'];
        if($access === 'COACH') {
            getReportCards($pdo, $error, 'submitted_by = :id AND approved is NOT null', ['id' => $url[1]]);
        } else {
            getReportCards($pdo, $error, 'approved is NOT null');
        }
        break;

        case 'update-user-no-password':

        $stmt = $pdo->prepare('UPDATE users SET 
            username = :username, 
            email = :email,
            first_name = :first_name, 
            last_name = :last_name, 
            access = :access 
            WHERE id = :id'
        );

            
        $putData = json_decode(file_get_contents("php://input"), true);
        $stmt->execute([
            'username' => $putData['username'], 
            'email' => $putData['email'], 
            'first_name' => $putData['first_name'], 
            'last_name' => $putData['last_name'], 
            'access' => $putData['access'], 
            'id' => $url[1]
        ]);

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $users = new Users($pdo, $error);
        $users->process($item, $join, $accessLevel);

        break;

        default:
        http_response_code(HTTP_CODE_BAD_REQUEST);
        $error->echoError("Invalid Selector [$selector]");
        break;
    }

    break;
}