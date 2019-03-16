<?php
class ReportCardsComponents extends CRUD
{
    public static function getTableName() {
        return 'report_cards_components';
    }

    protected function getCreateAccess() {
        return [COACH, SUPERVISOR, ADMIN];
    }

    protected function getRequiredCreateData() {
        return ['report_card_id', 'skill_id', 'rank'];
    }
    
    protected function additionalQuery($data) {
        unset($data['skill_id']);
        unset($data['rank']);
        $reportCardTable = ReportCards::getTableName();
        $date = $this->getCurrentDateTime();
        $stmt = $this->pdo->prepare("UPDATE $reportCardTable".
                                    " SET updated_date = '$date'".
                                    ' WHERE id = :report_card_id');
        $stmt->execute($data);
    }

    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().
                ' (report_card_id, skill_id, rank) VALUES'.
                " (:report_card_id, :skill_id, :rank)";
    }

    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT $table.id, $table.report_card_id, $table.skill_id, $table.rank FROM $table";
    }

    protected function getUpdateAccess() {
        return $this->getCreateAccess();
    }

    protected function getRequiredUpdateData() {
        return ['report_card_id', 'skill_id', 'rank'];
    }
    
    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().
                ' SET report_card_id = :report_card_id, skill_id = :skill_id, rank = :rank WHERE id = :id';
    }

    protected function getDeleteAccess() {
        return $this->getCreateAccess();
    }
}