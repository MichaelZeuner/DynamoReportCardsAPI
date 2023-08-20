<?php
class Users extends CRUD
{
    public static function getTableName() {
        return 'users';
    }

    protected function dataManipulation($data) {
        $password = $data['password'];
        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        unset($data['password']);
        $data['password_hash'] = $password_hash;
        return $data;
    }

    protected function getCreateAccess() {
        return [ADMIN];
    }

    protected function getRequiredCreateData() {
        return [
            'password_hash', 
            'email',
            'first_name',
            'last_name',
            'access'
        ];
    }
    
    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().
                ' (password_hash, email, 
                    first_name, last_name, access, active) VALUES'.
                ' (:password_hash, :email, 
                    :first_name, :last_name, :access, 1)';
    }

    protected function getReadAccess() {
        return [ADMIN, COACH, SUPERVISOR, NONE];
    }

    protected function getReadSQL() {
        $table = $this->getTableName();
        $currentUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $parsedUrl = parse_url($currentUrl);
        $queryParams = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $queryParams);
        }
        $onlyActive = isset($queryParams['onlyActive']) ? filter_var($queryParams['onlyActive'], FILTER_VALIDATE_BOOLEAN) : true;
        $sql = "SELECT * FROM $table";
        if($onlyActive == true) {
            $sql .=  " WHERE active = 1";
        } 
        return $sql;
    }

    protected function getUpdateAccess() {
        return $this->getCreateAccess();
    }

    protected function getRequiredUpdateData() {
        return $this->getRequiredCreateData();
    }
    
    protected function dataManipulationUpdate($data) {
        $newData['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $newData['email'] = $data['email'];
        $newData['first_name'] = $data['first_name'];
        $newData['last_name'] = $data['last_name'];
        $newData['access'] = $data['access'];
        return $newData; 
    }

    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().' SET 
                    password_hash = :password_hash, 
                    email = :email,
                    first_name = :first_name, 
                    last_name = :last_name, 
                    access = :access '.
                ' WHERE id = :id';
    }

    protected function getDeleteSQL() {
        return 'UPDATE '.$this->getTableName().' SET active=0 WHERE id = :id';
    }

    protected function getDeleteAccess() {
        return $this->getCreateAccess();
    }
}