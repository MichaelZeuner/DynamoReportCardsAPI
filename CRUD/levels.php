<?php
require_once(ROOT . '/CRUD/CRUD.php');

class Levels extends CRUD
{
    public function getTableName() {
        return 'levels';
    }

    protected function getRequiredCreateData() {
        return ['name'];
    }
    
    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().' (name) VALUES (:name)';
    }

    protected function getReadOneSQL() {
        return 'SELECT * FROM '.$this->getTableName().' WHERE id = :id';
    }

    protected function getReadSQL() {
        return 'SELECT * FROM '.$this->getTableName();
    }

    protected function getRequiredUpdateData() {
        return ['name'];
    }
    
    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().' SET name = :name WHERE id = :id';
    }
    
    protected function getDeleteSQL() {
        return 'DELETE FROM '.$this->getTableName().' WHERE id = :id';
    }
}