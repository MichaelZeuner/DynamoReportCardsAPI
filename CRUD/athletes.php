<?php
class Athletes extends CRUD
{
    public static function getTableName() {
        return 'athletes';
    }

    protected function getRequiredCreateData() {
        return ['first_name', 'last_name'];
    }
    
    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().' (first_name, last_name) VALUES (:first_name, :last_name)';
    }
    
    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT $table.id, $table.first_name, $table.last_name FROM $table";
    }

    protected function getRequiredUpdateData() {
        return ['first_name', 'last_name'];
    }
    
    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().' SET first_name = :first_name, last_name = :last_name WHERE id = :id';
    }
}