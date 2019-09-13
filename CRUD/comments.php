<?php
class Comments extends CRUD
{
    public static function getTableName() {
        return 'comments';
    }

    protected function getCreateAccess() {
        return [ADMIN];
    }

    protected function getRequiredCreateData() {
        return ['type', 'comment'];
    }
    
    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().' (type, comment, active) VALUES (:type, :comment, 1)';
    }
    
    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT $table.id, $table.type, $table.comment FROM $table WHERE active = 1";
    }

    protected function dataManipulationUpdate($data) {
        $newData['type'] = $data['type'];
        $newData['comment'] = $data['comment'];
        return $newData; 
    }

    protected function getUpdateAccess() {
        return $this->getCreateAccess();
    }

    protected function getRequiredUpdateData() {
        return ['type', 'comment'];
    }
    
    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().' SET type = :type, comment = :comment WHERE id = :id';
    }

    protected function getDeleteAccess() {
        return $this->getCreateAccess();
    }

    protected function getDeleteSQL() {
        return 'UPDATE '.$this->getTableName().' SET active=0 WHERE id = :id';
    }
}