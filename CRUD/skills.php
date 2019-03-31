<?php
class Skills extends CRUD
{
    public static function getTableName() {
        return 'skills';
    }

    protected function getCreateAccess() {
        return [ADMIN];
    }

    protected function getRequiredCreateData() {
        return ['levels_id', 'events_id', 'name'];
    }
    
    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().
                ' (levels_id, events_id, name) VALUES'.
                ' (:levels_id, :events_id, :name)';
    }

    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT $table.id, $table.name FROM $table";
    }

    protected function getUpdateAccess() {
        return $this->getCreateAccess();
    }

    protected function getRequiredUpdateData() {
        return ['levels_id', 'events_id', 'name'];
    }
    
    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().
                ' SET levels_id = :levels_id, events_id = :events_id, name = :name'.
                ' WHERE id = :id';
    }

    protected function getDeleteAccess() {
        return $this->getCreateAccess();
    }
}