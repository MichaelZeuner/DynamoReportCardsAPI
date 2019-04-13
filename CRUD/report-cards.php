<?php
class ReportCards extends CRUD
{
    public static function getTableName() {
        return 'report_cards';
    }

    protected function getCreateAccess() {
        return [COACH, SUPERVISOR, ADMIN];
    }

    protected function getRequiredCreateData() {
        return ['submitted_by', 'athletes_id', 'levels_id', 'comment'];
    }
    
    protected function getCreateSQL() {
        $date = $this->getCurrentDateTime();
        return 'INSERT INTO '.$this->getTableName().
                ' (submitted_by, athletes_id, levels_id, comment, updated_date, created_date) VALUES'.
                " (:submitted_by, :athletes_id, :levels_id, :comment, '$date', '$date')";
    }

    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT $table.id, $table.submitted_by, $table.athletes_id, $table.levels_id, $table.comment, $table.approved, $table.updated_date, $table.created_date FROM $table";
    }

    protected function getUpdateAccess() {
        return $this->getCreateAccess();
    }

    protected function dataManipulationUpdate($data) { 
        $newData['athletes_id'] = $data['athlete']['id'];
        $newData['levels_id'] = $data['level']['id'];
        $newData['comment'] = $data['comment'];
        $newData['approved'] = $data['approved'];
        return $newData; 
    }

    protected function getRequiredUpdateData() {
        return ['athletes_id', 'levels_id', 'comment', 'approved'];
    }
    
    protected function getUpdateSQL() {
        $date = $this->getCurrentDateTime();
        return 'UPDATE '.$this->getTableName().
                " SET athletes_id = :athletes_id, levels_id = :levels_id, comment = :comment, approved = :approved, updated_date = '$date'".
                ' WHERE id = :id';
    }

    protected function getDeleteAccess() {
        return $this->getCreateAccess();
    }
}