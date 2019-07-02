<?php
class ReportCards extends CRUD
{
    public static function getTableName() {
        return 'report_cards_mod';
    }

    protected function getCreateAccess() {
        return [COACH, SUPERVISOR, ADMIN];
    }

    protected function getRequiredCreateData() {
        return ['report_cards_id', 'comment_modifications'];
    }
    
    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().
                ' (report_cards_id, comment_modifications) VALUES'.
                " (:report_cards_id, :comment_modifications)";
    }

    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT $table.id, $table.report_cards_id, $table.comment_modifications FROM $table";
    }

    protected function getUpdateAccess() {
        return $this->getCreateAccess();
    }

    protected function dataManipulationUpdate($data) { 
        $newData['comment_modifications'] = $data['comment_modifications'];
        return $newData; 
    }

    protected function getRequiredUpdateData() {
        return ['comment_modifications'];
    }
    
    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().' SET comment_modifications = :comment_modifications WHERE id = :id';
    }

    protected function getDeleteAccess() {
        return $this->getCreateAccess();
    }
}