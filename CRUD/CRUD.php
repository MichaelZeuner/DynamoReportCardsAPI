<?php
require_once(ROOT . '/helpers/connect.php');
require_once(ROOT . '/helpers/InputParse.php');

abstract class CRUD
{
    public $pdo;
    protected $error;

    function __construct($pdo, $error) {
        $this->pdo = $pdo;
        $this->error = $error;
    }

    public function process($item) {
        $method = $_SERVER['REQUEST_METHOD'];

        if('POST' === $method){
            if(isset($item)) {
                http_response_code(HTTP_CODE_BAD_REQUEST);
                return $this->error->createError('Post to item is invalid.');;
            }
            echo json_encode($this->create($_POST));
        }
        else if('PUT' === $method){
            if(isset($item) === false) {
                http_response_code(HTTP_CODE_BAD_REQUEST);
                return $this->error->createError('Bulk put not supported at this time.');;
            }
            echo json_encode($this->update($item, getInputData()));
        }
        else if('DELETE' === $method){
            if(isset($item) === false) {
                http_response_code(HTTP_CODE_BAD_REQUEST);
                return $this->error->createError('Bulk delete not supported at this time.');;
            }
            $this->delete($item);
        }
        else if('GET' === $method) {
            echo json_encode($this->read($item));
        }
    }

    abstract public function getTableName();

    abstract protected function getRequiredCreateData();
    abstract protected function getCreateSQL();
    public function create($data) {
        if($this->isRequiredData($data, $this->getRequiredCreateData())) {
            $stmt = $this->pdo->prepare($this->getCreateSQL());

            $stmt->execute($data);
            http_response_code(HTTP_CODE_CREATED);
            return $this->read($this->pdo->lastInsertId());
        } else {
            http_response_code(HTTP_CODE_BAD_REQUEST);
            return $this->error->createError('Data mismatch. Required data: ' . json_encode($this->getRequiredCreateData()) . ' Found data: ' . json_encode(array_keys($data)));
        }
    }
    
    abstract protected function getReadOneSQL();
    abstract protected function getReadSQL();
    public function read($item) {
        if(isset($item)) {
            $stmt = $this->pdo->prepare($this->getReadOneSQL());
            $stmt->execute($this->getIdArray($item));
        } 
        else {
            $stmt = $this->pdo->query($this->getReadSQL());
        }

        $results = $stmt->fetchAll();
        if(count($results) == 0) {
            http_response_code(HTTP_CODE_NOT_FOUND);
            return $this->error->createError('No data found');
        }
        
        http_response_code(HTTP_CODE_OK);
        return $results;
    }
    
    abstract protected function getRequiredUpdateData();
    abstract protected function getUpdateSQL();
    public function update($item, $data) {
        if($this->isRequiredData($data, $this->getRequiredUpdateData())) {
            $stmt = $this->pdo->prepare($this->getUpdateSQL());
            $stmt->execute($this->getDataArrayWithId($data, $item));

            http_response_code(HTTP_CODE_OK);
            return $this->read($item);
        } else {
            http_response_code(HTTP_CODE_BAD_REQUEST);
            return $this->error->createError('Data mismatch. Required data: ' . json_encode($this->getRequiredUpdateData()) . ' Found data: ' . json_encode(array_keys($data)));
        }
    }
    
    abstract protected function getDeleteSQL();
    public function delete($item) {
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
                if(isset($data[$req]) === false) {
                    return false;
                }
            }
            return true;
        } else {
            return false;
        }
    }
}

?>