<?php

function getReportCards($pdo, $error, $where, $arr = [], $orderBy = 'updated_date DESC') {
    $stmt = $pdo->prepare("SELECT report_cards.id, submitted_by, suser.first_name AS submitted_first_name, suser.last_name AS submitted_last_name, athletes_id, levels_id, comment, day_of_week, approved, status, auser.first_name AS approved_first_name, auser.last_name AS approved_last_name, updated_date, created_date FROM report_cards INNER JOIN users suser ON suser.id = submitted_by LEFT JOIN users auser ON auser.id = approved WHERE $where ORDER BY $orderBy");
    $stmt->execute($arr);
    $results = $stmt->fetchAll();
    if(count($results) == 0) {
        http_response_code(HTTP_CODE_NOT_FOUND);
        $error->echoError("No data found with WHERE $where");
    } else {
        for($i=0; $i<count($results); $i++) {
            $report_card = $results[$i];

            $stmtAthlete = $pdo->prepare('SELECT * FROM athletes WHERE id = :athlete_id');
            $stmtAthlete->execute(['athlete_id' => $report_card['athletes_id']]);
            $report_card['athlete'] = $stmtAthlete->fetch();
            unset($report_card['athletes_id']);

            $stmtLevel = $pdo->prepare('SELECT levels.id, name, level_groups.id AS level_groups_id, level_number FROM levels INNER JOIN level_groups ON level_groups.id = levels.level_groups_id WHERE levels.id = :levels_id');
            $stmtLevel->execute(['levels_id' => $report_card['levels_id']]);
            $report_card['level'] = $stmtLevel->fetch();
            unset($report_card['levels_id']);

            $stmtEvents = $pdo->prepare("SELECT DISTINCT events.id, events.name FROM events INNER JOIN skills ON events.id = skills.events_id INNER JOIN report_cards_components ON report_cards_components.skills_id = skills.id WHERE report_cards_id = :report_cards_id");
            $stmtEvents->execute(['report_cards_id' => $report_card['id']]);
            $report_card['events'] = $stmtEvents->fetchAll();

            $stmtComponenets = $pdo->prepare("SELECT * FROM report_cards_components WHERE report_cards_id = :report_cards_id");
            $stmtComponenets->execute(['report_cards_id' => $report_card['id']]);
            $report_card['components'] = $stmtComponenets->fetchAll();

            for($x=0; $x<count($report_card['components']); $x++) {
                $component = $report_card['components'][$x];
                
                $stmtSkill = $pdo->prepare('SELECT * FROM skills WHERE id = :skills_id');
                $stmtSkill->execute(['skills_id' => $component['skills_id']]);
                $component['skill'] = $stmtSkill->fetch();
                unset($report_card['components']['skills_id']);

                $report_card['components'][$x] = $component;
            }

            $results[$i] = $report_card;
        }

        http_response_code(HTTP_CODE_OK);
        echo json_encode($results);
    }
}