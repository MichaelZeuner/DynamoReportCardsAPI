<?php
require_once(ROOT . '/CRUD/CRUD.php');

class Athletes extends CRUD
{
    public function getTableName() {
        return 'athletes';
    }

    protected function getRequiredCreateData() {
        return ['first_name', 'last_name'];
    }
    
    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().' (first_name, last_name) VALUES (:first_name, :last_name)';
    }

    protected function getReadOneSQL() {
        return 'SELECT * FROM '.$this->getTableName().' WHERE id = :id';
    }

    protected function getReadSQL() {
        return 'SELECT * FROM '.$this->getTableName();
    }

    protected function getRequiredUpdateData() {
        return ['first_name', 'last_name'];
    }
    
    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().' SET first_name = :first_name, last_name = :last_name WHERE id = :id';
    }
    
    protected function getDeleteSQL() {
        return 'DELETE FROM '.$this->getTableName().' WHERE id = :id';
    }
}