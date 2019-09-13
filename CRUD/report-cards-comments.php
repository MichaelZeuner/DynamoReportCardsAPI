<?php
class ReportCardsComments extends CRUD
{
    public static function getTableName() {
        return 'report_cards_comments';
    }

    protected function getCreateAccess() {
        return [COACH, SUPERVISOR, ADMIN];
    }

    protected function getRequiredCreateData() {
        return ['intro_comment_id', 'skill_comment_id', 'event_id', 'skill_id', 'closing_comment_id'];
    }
    
    protected function getCreateSQL() {
        return 'INSERT INTO '.$this->getTableName().' (intro_comment_id, skill_comment_id, event_id, skill_id, closing_comment_id) VALUES (:intro_comment_id, :skill_comment_id, :event_id, :skill_id, :closing_comment_id)';
    }
    
    protected function getReadSQL() {
        $table = $this->getTableName();
        return "SELECT $table.id, $table.intro_comment_id, $table.skill_comment_id, $table.event_id, $table.skill_id, $table.closing_comment_id FROM $table WHERE 1";
    }

    protected function dataManipulationUpdate($data) {
        $newData['intro_comment_id'] = $data['intro_comment_id'];
        $newData['skill_comment_id'] = $data['skill_comment_id'];
        $newData['event_id'] = $data['event_id'];
        $newData['skill_id'] = $data['skill_id'];
        $newData['closing_comment_id'] = $data['closing_comment_id'];
        return $newData; 
    }

    protected function getUpdateAccess() {
        return $this->getCreateAccess();
    }

    protected function getRequiredUpdateData() {
        return ['intro_comment_id', 'skill_comment_id', 'event_id', 'skill_id', 'closing_comment_id'];
    }
    
    protected function getUpdateSQL() {
        return 'UPDATE '.$this->getTableName().' SET intro_comment_id = :intro_comment_id, skill_comment_id = :skill_comment_id, event_id = :event_id, skill_id = :skill_id, closing_comment_id = :closing_comment_id WHERE id = :id';
    }

    protected function getDeleteAccess() {
        return $this->getCreateAccess();
    }
}