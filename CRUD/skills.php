<?php
class Skills extends CRUD
{
    public function getTableName() {
        return 'skills';
    }

    protected function getRequiredCreateData() {
        return ['level_id', 'event_id', 'name'];
    }
    
    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().
                ' (level_id, event_id, name) VALUES'.
                ' (:level_id, :event_id, :name)';
    }

    protected function getReadOneSQL() {
        return 'SELECT * FROM '.$this->getTableName().' WHERE id = :id';
    }

    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT $table.id, $table.name FROM $table";
    }

    protected function getRequiredUpdateData() {
        return ['level_id', 'event_id', 'name'];
    }
    
    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().
                ' SET level_id = :level_id, event_id = :event_id, name = :name'.
                ' WHERE id = :id';
    }
    
    protected function getDeleteSQL() {
        return 'DELETE FROM '.$this->getTableName().' WHERE id = :id';
    }
}