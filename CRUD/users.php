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
                    first_name, last_name, access) VALUES'.
                ' (:username, :password_hash, :email, 
                    :first_name, :last_name, :access)';
    }

    protected function getReadAccess() {
        return $this->getCreateAccess();
    }

    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT * FROM $table";
    }

    protected function getUpdateAccess() {
        return $this->getCreateAccess();
    }

    protected function getRequiredUpdateData() {
        return $this->getRequiredCreateData();
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

    protected function getDeleteAccess() {
        return $this->getCreateAccess();
    }
}