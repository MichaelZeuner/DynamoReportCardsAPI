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
            'username', 
            'password_hash', 
            'email',
            'first_name',
            'last_name',
            'access'
        ];
    }
    
    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().
                ' (username, password_hash, email, 
                    first_name, last_name, access, active) VALUES'.
                ' (:username, :password_hash, :email, 
                    :first_name, :last_name, :access, 1)';
    }

    protected function getReadAccess() {
        return $this->getCreateAccess();
    }

    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT * FROM $table WHERE active = 1";
    }

    protected function getUpdateAccess() {
        return $this->getCreateAccess();
    }

    protected function getRequiredUpdateData() {
        return $this->getRequiredCreateData();
    }
    
    protected function dataManipulationUpdate($data) {
        $newData['username'] = $data['username'];
        $newData['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT);
        $newData['email'] = $data['email'];
        $newData['first_name'] = $data['first_name'];
        $newData['last_name'] = $data['last_name'];
        $newData['access'] = $data['access'];
        return $newData; 
    }

    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().' SET 
                    username = :username, 
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