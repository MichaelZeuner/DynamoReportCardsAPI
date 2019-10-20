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
require_once(ROOT . '/helpers/get-report-cards-sent-back.php');
require_once(ROOT . '/helpers/get-printable-report-card.php');
require_once(ROOT . '/CRUD/CRUD.php');
require_once(ROOT . '/CRUD/commentsCRUD.php');

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
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND active = 1 LIMIT 1");
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

    case 'level-groups':
    $levelGroups = new LevelGroups($pdo, $error);
    $levelGroups->process($item, $join, $accessLevel);
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

    case 'report-card-mod':
    $reportCardsMod = new ReportCardsMod($pdo, $error);
    $reportCardsMod->process($item, $join, $accessLevel);
    break;

    case 'report-card-mod-components':
    $reportCardsModComponents = new ReportCardsModComponents($pdo, $error);
    $reportCardsModComponents->process($item, $join, $accessLevel);
    break;

    case 'report-cards-comments':
    $reportCardsComments = new ReportCardsComments($pdo, $error);
    $reportCardsComments->process($item, $join, $accessLevel);
    break;

    default:

    //others
    switch($url[0]) {
        case 'delete-report-card':
        $stmt = $pdo->prepare("DELETE FROM report_cards_components WHERE report_cards_id = :report_cards_id");
        $stmt->execute(['report_cards_id' => $item]);

        $stmt = $pdo->prepare("DELETE FROM report_cards WHERE id = :report_cards_id");
        $stmt->execute(['report_cards_id' => $item]);
        break;

        case 'coaches-in-progress-cards':
        getReportCards($pdo, $error, 'submitted_by = :coach_id AND status = :partial', ['coach_id' => $url[1], 'partial' => 'Partial'], 'updated_date DESC', true);
        break;

        case 'add-athlete-if-new':
        if(empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }

        $stmt = $pdo->prepare("SELECT * FROM athletes WHERE first_name LIKE :first_name AND last_name LIKE :last_name AND date_of_birth LIKE :date_of_birth");
        $stmt->execute($_POST);

        
        $results = $stmt->fetchAll();
        if(count($results) == 0) {
            $stmt1 = $pdo->prepare("INSERT INTO athletes (first_name, last_name, date_of_birth) VALUES (:first_name, :last_name, :date_of_birth)");
            $stmt1->execute($_POST);
            $results1 = $stmt1->fetchAll();
        }

        http_response_code(HTTP_CODE_OK);
        break;

        case 'add-or-update-report-cards-components':
        if(empty($_POST)) {
            $_POST = json_decode(file_get_contents('php://input'), true);
        }

        $noRank = [];
        $noRank['report_cards_id'] = $_POST['report_cards_id'];
        $noRank['skills_id'] = $_POST['skills_id'];
        $stmt = $pdo->prepare("SELECT * FROM report_cards_components WHERE report_cards_id = :report_cards_id AND skills_id = :skills_id");
        $stmt->execute($noRank);

        $results = $stmt->fetchAll();
        if(count($results) == 0) {
            $stmt1 = $pdo->prepare("INSERT INTO report_cards_components (report_cards_id, skills_id, rank) VALUES (:report_cards_id, :skills_id, :rank)");
            $stmt1->execute($_POST);
            $results1 = $stmt1->fetchAll();
        } else {
            $stmt2 = $pdo->prepare("UPDATE report_cards_components SET rank=:rank WHERE report_cards_id=:report_cards_id AND skills_id=:skills_id");
            $stmt2->execute($_POST);
            $results2 = $stmt2->fetchAll();
        }

        http_response_code(HTTP_CODE_OK);
        break;

        case 'delete-report-card-components':
        $stmt = $pdo->prepare("DELETE FROM report_cards_components WHERE report_cards_id = :report_cards_id");
        $stmt->execute(['report_cards_id' => $item]);

        http_response_code(HTTP_CODE_NO_CONTENT);
        break;

        case 'read-levels':
        $stmt = $pdo->prepare("SELECT levels.id, level_groups.id AS level_groups_id, level_groups.name, level_number FROM levels INNER JOIN level_groups ON levels.level_groups_id = level_groups.id WHERE levels.active = 1 AND level_groups.active = 1");
        $stmt->execute();

        $results = $stmt->fetchAll();
        if(count($results) == 0) {
            http_response_code(HTTP_CODE_NOT_FOUND);
            $error->echoError('No data found');
        } else {
            http_response_code(HTTP_CODE_OK);
            echo json_encode($results);
        }
        break;

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

        case 'athletes-attempts-at-level':
        getReportCards($pdo, $error, 'athletes_id = :athletes_id AND levels_id = :levels_id', ['athletes_id' => $url[1], 'levels_id' => $url[2]], 'updated_date DESC', true);
        break;

        case 'report-cards-requiring-approval':
        getReportCards($pdo, $error, 'approved is null AND comment_modifications is null AND status != "Partial"', [], 'submitted_by');
        break;

        case 'report-cards-sent-back':
        getReportCardsSentBack($pdo, $error, $url[1]);
        break;

        case 'report-cards-completed':
        $stmt = $pdo->prepare("SELECT access FROM users WHERE id = :id");
        $stmt->execute(['id' => $url[1]]);
        $access = $stmt->fetch()['access'];
        if($access === 'COACH') {
            getReportCards($pdo, $error, 'submitted_by = :id AND approved is NOT null AND status != "Partial"', ['id' => $url[1]]);
        } else {
            getReportCards($pdo, $error, 'approved is NOT null AND status != "Partial"');
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

        case 'get-athletes-recent-report-cards':
        $stmt = $pdo->prepare("SELECT id, created_date FROM report_cards WHERE athletes_id = :athletes_id ORDER BY created_date DESC");
        $stmt->execute(['athletes_id' => $url[1]]);
    
        $results = $stmt->fetchAll();
        if(count($results) == 0) {
            http_response_code(HTTP_CODE_NOT_FOUND);
            $error->echoError('No data found');
        } else {
            http_response_code(HTTP_CODE_OK);
            echo json_encode($results);
        }
        break;

        case 'report-cards-components-for-report-card':
        getReportCards($pdo, $error, 'id = :id', ['id' => $url[1]]);
        break;

        case 'printable-report-card':
        getPrintableReportCard($pdo, $error, $url[1]);
        break;

        case 'get-comments':
        echo json_encode(getComments($pdo, $error, $accessLevel, $item));
        break;

        case 'put-comments':
        echo json_encode(putComments($pdo, $error, $accessLevel));
        break;

        case 'add-comments':
            echo json_encode(addComments($pdo, $error, $accessLevel));
        break;

        default:
        http_response_code(HTTP_CODE_BAD_REQUEST);
        $error->echoError("Invalid Selector [$selector]");
        break;
    }

    break;
} 