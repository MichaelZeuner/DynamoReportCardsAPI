<?php
require_once(ROOT . '/helpers/connect.php');
require_once(ROOT . '/helpers/InputParse.php');
require_once(ROOT . '/CRUD/users.php');
require_once(ROOT . '/CRUD/athletes.php');
require_once(ROOT . '/CRUD/levels.php');
require_once(ROOT . '/CRUD/level_groups.php');
require_once(ROOT . '/CRUD/events.php');
require_once(ROOT . '/CRUD/skills.php');
require_once(ROOT . '/CRUD/report-cards.php');
require_once(ROOT . '/CRUD/report-cards-components.php');
require_once(ROOT . '/CRUD/report-cards-mod.php');
require_once(ROOT . '/CRUD/report-cards-mod-components.php');

abstract class CRUD
{
    public $pdo;
    protected $error;
    protected $accessLevel = NONE;
    protected $errMsg = '';

    function __construct($pdo, $error) {
        $this->pdo = $pdo;
        $this->error = $error;
        $this->errMsg = '';
    }

    public function process($item, $join, $accessLevel) {
        $this->accessLevel = $accessLevel;
        $method = $_SERVER['REQUEST_METHOD'];

        if('POST' === $method){
            if(empty($_POST)) {
                $_POST = json_decode(file_get_contents('php://input'), true);
            }
            if(isset($item)) {
                http_response_code(HTTP_CODE_BAD_REQUEST);
                if(empty($join)) {
                    echo json_encode($this->error->createError('Post to item is invalid.'));
                } else {
                    echo json_encode($this->error->createError('Post for a join not supported.'));
                }
            } else {
                echo json_encode($this->create($_POST));
            }
        }
        else if('PUT' === $method){
            if(isset($item) === false) {
                http_response_code(HTTP_CODE_BAD_REQUEST);
                echo json_encode($this->error->createError('Bulk put not supported at this time.'));
            } else {
                //$putData = getInputData();
                $putData = json_decode(file_get_contents("php://input"), true);
                echo json_encode($this->update($item, $putData));
            }
        }
        else if('DELETE' === $method){
            if(isset($item) === false) {
                http_response_code(HTTP_CODE_BAD_REQUEST);
                echo json_encode($this->error->createError('Bulk delete not supported at this time.'));
            } else {
                $this->delete($item);
            }
        }
        else if('GET' === $method) {
            echo json_encode($this->read($item, $join));
        }
    }

    abstract static public function getTableName();

    abstract protected function getCreateAccess();
    abstract protected function getRequiredCreateData();
    abstract protected function getCreateSQL();
    public function create($data) {
        if(!in_array($this->accessLevel, $this->getCreateAccess())) {
            http_response_code(HTTP_CODE_NOT_AUTHORIZED);
            return $this->error->createError('NOT AUTHORIZED! Your access level: ' . $this->accessLevel . ', access levels permitted: ' . json_encode($this->getCreateAccess()));
        }

        $data = $this->dataManipulation($data);
        if($this->isRequiredData($data, $this->getRequiredCreateData())) {
            $stmt = $this->pdo->prepare($this->getCreateSQL());
            $stmt->execute($data);
            $createdResult = $this->read($this->pdo->lastInsertId(), null);
            $this->additionalQuery($data);

            http_response_code(HTTP_CODE_CREATED);
            return $createdResult;
        } else {
            http_response_code(HTTP_CODE_BAD_REQUEST);
            return $this->error->createError('Data mismatch. Required data: ' . json_encode($this->getRequiredCreateData()) . ' Found data: ' . json_encode(array_keys($data)));
        }
    }
    
    protected function getReadAccess() {
        return [NONE, COACH, SUPERVISOR, ADMIN];
    }
    protected function getReadOneSQL() {
        return 'SELECT * FROM '.$this->getTableName().' WHERE id = :id';
    }
    abstract protected function getReadSQL();
    public function read($item, $join) {
        if(!in_array($this->accessLevel, $this->getReadAccess())) {
            http_response_code(HTTP_CODE_NOT_AUTHORIZED);
            return $this->error->createError('NOT AUTHORIZED! Your access level: ' . $this->accessLevel . ', access levels permitted: ' . json_encode($this->getReadAccess()));
        }

        if(isset($item) && empty($join)) {
            $stmt = $this->pdo->prepare($this->getReadOneSQL());
            $stmt->execute($this->getIdArray($item));
            $results = $stmt->fetch();
        } else if(empty($item)) {
            $stmt = $this->pdo->query($this->getReadSQL());
            $results = $stmt->fetchAll();
        } else {
            $stmt = $this->pdo->prepare($this->getReadSQL() . " JOIN $join WHERE $join"."_id = :id");
            $stmt->execute($this->getIdArray($item));
            $results = $stmt->fetchAll();
        }

        if(count($results) == 0) {
            http_response_code(HTTP_CODE_NOT_FOUND);
            return $this->error->createError('No data found');
        }
        
        http_response_code(HTTP_CODE_OK);
        return $results;
    }
    
    abstract protected function getUpdateAccess();
    abstract protected function getRequiredUpdateData();
    abstract protected function getUpdateSQL();
    protected function dataManipulationUpdate($data) { return $data; }
    public function update($item, $data) {
        if(!in_array($this->accessLevel, $this->getUpdateAccess())) {
            http_response_code(HTTP_CODE_NOT_AUTHORIZED);
            return $this->error->createError('NOT AUTHORIZED! Your access level: ' . $this->accessLevel . ', access levels permitted: ' . json_encode($this->getUpdateAccess()));
        }

        $data = $this->dataManipulationUpdate($data);
        if($this->isRequiredData($data, $this->getRequiredUpdateData())) {
            $stmt = $this->pdo->prepare($this->getUpdateSQL());
            $stmt->execute($this->getDataArrayWithId($data, $item));
            $this->additionalQuery($data);

            http_response_code(HTTP_CODE_OK);
            return $this->read($item, null);
        } else {
            http_response_code(HTTP_CODE_BAD_REQUEST);
            return $this->error->createError($this->errMsg . ' Data mismatch. Required data: ' . json_encode($this->getRequiredUpdateData()) . ' Found data: ' . json_encode(array_keys($data)));
        }
    }
    
    abstract protected function getDeleteAccess();
    protected function getDeleteSQL() {
        return 'DELETE FROM '.$this->getTableName().' WHERE id = :id';
    }
    public function delete($item) {
        if(!in_array($this->accessLevel, $this->getDeleteAccess())) {
            http_response_code(HTTP_CODE_NOT_AUTHORIZED);
            return $this->error->createError('NOT AUTHORIZED! Your access level: ' . $this->accessLevel . ', access levels permitted: ' . json_encode($this->getDeleteAccess()));
        }

        $stmt = $this->pdo->prepare($this->getDeleteSQL());
        $stmt->execute($this->getIdArray($item));
        
        if($stmt->rowCount() === 0) {
            http_response_code(HTTP_CODE_NOT_FOUND);
            return $this->error->createError('No rows affected');
        }
        else {
            http_response_code(HTTP_CODE_NO_CONTENT);
        }
    }

    protected function dataManipulation($data) { return $data; }

    protected function additionalQuery($data) {}

    public function getCurrentDateTime() {
        return gmdate('Y-m-d H:i:s', time());
    }

    private function getIdArray($item) {
        return ['id' => $item];
    }

    private function getDataArrayWithId($data, $item) {
        $data['id'] = $item;
        return $data;
    }

    public function isRequiredData($data, $requiredData) {
        if(count($data) === count($requiredData)) {
            foreach($requiredData as $req) {
                if(array_key_exists($req, $data) === false) {
                    $this->errMsg = 'MISSING: '.$req;
                    return false;
                }
            }
            return true;
        } else {
            $this->errMsg = 'COUNT WRONG';
            return false;
        }
    }
}