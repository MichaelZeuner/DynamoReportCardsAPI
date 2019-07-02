<?php
class ReportCardsComponents extends CRUD
{
    public static function getTableName() {
        return 'report_cards_mod_components';
    }

    protected function getCreateAccess() {
        return [COACH, SUPERVISOR, ADMIN];
    }

    protected function getRequiredCreateData() {
        return ['report_cards_id', 'skills_id', 'rank'];
    }
    
    protected function additionalQuery($data) {
        unset($data['skills_id']);
        unset($data['rank']);
        $reportCardTable = ReportCards::getTableName();
        $date = $this->getCurrentDateTime();
        $stmt = $this->pdo->prepare("UPDATE $reportCardTable".
                                    " SET updated_date = '$date'".
                                    ' WHERE id = :report_cards_id');
        $stmt->execute($data);
    }

    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().
                ' (report_cards_id, skills_id, rank) VALUES'.
                " (:report_cards_id, :skills_id, :rank)";
    }

    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT $table.id, $table.report_cards_id, $table.skills_id, $table.rank FROM $table";
    }

    protected function getUpdateAccess() {
        return $this->getCreateAccess();
    }

    protected function dataManipulationUpdate($data) { 
        unset($data['id']);
        return $data;
    }

    protected function getRequiredUpdateData() {
        return ['report_cards_id', 'skills_id', 'rank'];
    }
    
    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().
                ' SET report_cards_id = :report_cards_id, skills_id = :skills_id, rank = :rank WHERE id = :id';
    }

    protected function getDeleteAccess() {
        return $this->getCreateAccess();
    }
}