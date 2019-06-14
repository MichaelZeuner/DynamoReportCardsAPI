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
        return ['level_groups_id', 'level_number'];
    }
    
    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().' (level_groups_id, level_number) VALUES (:level_groups_id, :level_number)';
    }

    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT $table.id, $table.level_groups_id, $table.level_number FROM $table WHERE active = 1";
    }

    protected function getUpdateAccess() {
        return $this->getCreateAccess();
    }

    protected function getRequiredUpdateData() {
        return ['level_number'];
    }
    
    protected function dataManipulationUpdate($data) {
        $newData['level_number'] = $data['level_number'];
        return $newData; 
    }

    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().' SET level_number = :level_number WHERE id = :id';
    }

    protected function getDeleteAccess() {
        return $this->getCreateAccess();
    }

    protected function getDeleteSQL() {
        return 'UPDATE '.$this->getTableName().' SET active=0 WHERE id = :id';
    }
}