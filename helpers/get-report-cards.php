<?php

function countReportCards($pdo, $error, $where, $firstName = '', $lastName = '', $year = '', $season = '') {
    $where .= " AND athletes.first_name LIKE '%$firstName%' AND athletes.last_name LIKE '%$lastName%'";
    if(empty($year) == false) {
        $where .= " AND YEAR(report_cards.create_date) = $year";
    }
    if(empty($season) == false) {
        $where .= " AND report_cards.session = '$season'";
    }
    
    $sql = "SELECT COUNT(report_cards.id) as count FROM report_cards INNER JOIN athletes ON athletes.id = athletes_id WHERE $where";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([]);
    $results = $stmt->fetchAll();
    if(count($results) == 0) {
        http_response_code(HTTP_CODE_NOT_FOUND);
        $error->echoError("No data found with WHERE $where");
    } else if(count($results) == 1) {
        http_response_code(HTTP_CODE_OK);
        echo json_encode($results[0]);
    } else {
        http_response_code(HTTP_CODE_BAD_REQUEST);
        $error->echoError("More than one response... WHERE $where");
    }
}

function getReportCards($pdo, $error, $where, $arr = [], $orderBy = 'updated_date DESC', $returnArrayOnEmpty = false, $limit = 10, $page = 1, $firstName = '', $lastName = '', $year = '', $season = '') {
    $where .= " AND athletes.first_name LIKE '%$firstName%' AND athletes.last_name LIKE '%$lastName%'";
    if(empty($year) == false) {
        $where .= " AND YEAR(report_cards.create_date) = $year";
    }
    if(empty($season) == false) {
        $where .= " AND report_cards.session = '$season'";
    }

    $offset = ($page-1)*$limit;
    $sql = "SELECT report_cards.id, submitted_by, secondary_coach_id, suser.first_name AS submitted_first_name, suser.last_name AS submitted_last_name, athletes_id, levels_id, comment, session,
            day_of_week, approved, status, auser.first_name AS approved_first_name, auser.last_name AS approved_last_name, comment_modifications, updated_date, created_date 
            FROM report_cards 
            INNER JOIN athletes ON athletes.id = athletes_id
            INNER JOIN users suser ON suser.id = submitted_by 
            LEFT JOIN users auser ON auser.id = approved 
            LEFT JOIN report_cards_mod ON report_cards_mod.report_cards_id = report_cards.id 
            WHERE $where 
            ORDER BY $orderBy
            LIMIT $limit OFFSET $offset";
    $stmt = $pdo->prepare($sql);
        
    $stmt->execute($arr);
    $results = $stmt->fetchAll();
    if(count($results) == 0) {
        if($returnArrayOnEmpty) { 
            http_response_code(HTTP_CODE_OK);
            echo json_encode($results);
        } else {
            http_response_code(HTTP_CODE_NOT_FOUND);
            $error->echoError("No data found with WHERE $where");
        }
    } else {
        for($i=0; $i<count($results); $i++) {
            $report_card = $results[$i];

            $stmtAthlete = $pdo->prepare('SELECT * FROM athletes WHERE id = :athlete_id');
            $stmtAthlete->execute(['athlete_id' => $report_card['athletes_id']]);
            $report_card['athlete'] = $stmtAthlete->fetch();

            $stmtSecondaryCoach = $pdo->prepare('SELECT * FROM users WHERE id = :secondary_coach_id'); 
            $stmtSecondaryCoach->execute(['secondary_coach_id' => $report_card['secondary_coach_id']]);
            $report_card['secondary_coach'] = $stmtSecondaryCoach->fetch();

            $stmtLevel = $pdo->prepare('SELECT levels.id, name, level_groups.id AS level_groups_id, level_number FROM levels INNER JOIN level_groups ON level_groups.id = levels.level_groups_id WHERE levels.id = :levels_id');
            $stmtLevel->execute(['levels_id' => $report_card['levels_id']]);
            $report_card['level'] = $stmtLevel->fetch();

            $stmtLevel = $pdo->prepare('SELECT intro_comment_id, skill_comment_id, personality_comment_id, closing_comment_id, event_id, skill_id FROM report_cards_comments WHERE id = :comment');
            $stmtLevel->execute(['comment' => $report_card['comment']]);
            $report_card['card_comments'] = $stmtLevel->fetch();

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