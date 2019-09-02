<?php
class Athletes extends CRUD
{
    public static function getTableName() {
        return 'athletes';
    }

    protected function getCreateAccess() {
        return [ADMIN];
    }

    protected function getRequiredCreateData() {
        return ['first_name', 'last_name', 'date_of_birth'];
    }
    
    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().' (first_name, last_name, date_of_birth) VALUES (:first_name, :last_name, :date_of_birth)';
    }
    
    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT $table.id, $table.first_name, $table.last_name, $table.date_of_birth FROM $table WHERE active = 1";
    }

    protected function dataManipulationUpdate($data) {
        $newData['first_name'] = $data['first_name'];
        $newData['last_name'] = $data['last_name'];
        $newData['date_of_birth'] = $data['date_of_birth'];
        return $newData; 
    }

    protected function getUpdateAccess() {
        return $this->getCreateAccess();
    }

    protected function getRequiredUpdateData() {
        return ['first_name', 'last_name', 'date_of_birth'];
    }
    
    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().' SET first_name = :first_name, last_name = :last_name, date_of_birth = :date_of_birth WHERE id = :id';
    }

    protected function getDeleteAccess() {
        return $this->getCreateAccess();
    }

    protected function getDeleteSQL() {
        return 'UPDATE '.$this->getTableName().' SET active=0 WHERE id = :id';
    }
}