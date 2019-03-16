<?php
class Events extends CRUD
{
    public static function getTableName() {
        return 'events';
    }

    protected function getRequiredCreateData() {
        return ['name'];
    }
    
    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().' (name) VALUES (:name)';
    }

    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT $table.id, $table.name FROM $table";
    }

    protected function getRequiredUpdateData() {
        return ['name'];
    }
    
    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().' SET name = :name WHERE id = :id';
    }
}