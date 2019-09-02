<?php
class ReportCardsModComponents extends CRUD
{
    public static function getTableName() {
        return 'report_cards_mod_components';
    }

    protected function getCreateAccess() {
        return [COACH, SUPERVISOR, ADMIN];
    }

    protected function getRequiredCreateData() {
        return ['report_cards_components_id', 'suggested_rank'];
    }
    
    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().
                ' (report_cards_components_id, suggested_rank) VALUES'.
                " (:report_cards_components_id, :suggested_rank)";
    }

    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT $table.id, $table.report_cards_components_id, $table.suggested_rank FROM $table";
    }

    protected function getUpdateAccess() {
        return $this->getCreateAccess();
    }

    protected function dataManipulationUpdate($data) { 
        $newData['suggested_rank'] = $data['suggested_rank'];
        return $newData; 
    }


    protected function getRequiredUpdateData() {
        return ['suggested_rank'];
    }
    
    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().
                ' SET suggested_rank = :suggested_rank WHERE id = :id';
    }

    protected function getDeleteAccess() {
        return $this->getCreateAccess();
    }
}