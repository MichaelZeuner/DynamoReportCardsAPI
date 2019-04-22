<?php
class Levels extends CRUD
{
    public static function getTableName() {
        return 'levels';
    }

    protected function getCreateAccess() {
        return [ADMIN];
    }

    protected function getRequiredCreateData() {
        return ['name'];
    }
    
    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().' (name) VALUES (:name)';
    }

    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT $table.id, $table.name FROM $table WHERE active = 1";
    }

    protected function getUpdateAccess() {
        return $this->getCreateAccess();
    }

    protected function getRequiredUpdateData() {
        return ['name'];
    }
    
    protected function dataManipulationUpdate($data) {
        $newData['name'] = $data['name'];
        return $newData; 
    }

    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().' SET name = :name WHERE id = :id';
    }

    protected function getDeleteAccess() {
        return $this->getCreateAccess();
    }

    protected function getDeleteSQL() {
        return 'UPDATE '.$this->getTableName().' SET active=0 WHERE id = :id';
    }
}