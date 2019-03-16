<?php
class ReportCards extends CRUD
{
    public static function getTableName() {
        return 'report_cards';
    }

    protected function getRequiredCreateData() {
        return ['athlete_id', 'level_id'];
    }
    
    protected function getCreateSQL() {
        $date = $this->getCurrentDateTime();
        return 'INSERT INTO '.$this->getTableName().
                ' (athlete_id, level_id, updated_date, created_date) VALUES'.
                " (:athlete_id, :level_id, '$date', '$date')";
    }

    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT $table.id, $table.athlete_id, $table.level_id, $table.updated_date, $table.created_date FROM $table";
    }

    protected function getRequiredUpdateData() {
        return ['athlete_id', 'level_id'];
    }
    
    protected function getUpdateSQL() {
        $date = $this->getCurrentDateTime();
        return 'UPDATE '.$this->getTableName().
                " SET athlete_id = :athlete_id, level_id = :level_id, updated_date = '$date'".
                ' WHERE id = :id';
    }
}