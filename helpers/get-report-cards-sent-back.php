<?php

function getReportCardsSentBack($pdo, $error, $coach_id) {
    $stmt = $pdo->prepare(
        'SELECT report_cards_id, submitted_by, athletes_id, levels_id, comment, comment_modifications, updated_date, status, report_cards_mod.id AS report_cards_mod_id
        FROM report_cards 
        LEFT JOIN report_cards_mod ON report_cards_mod.report_cards_id = report_cards.id 
        WHERE submitted_by = :coach_id AND comment_modifications IS NOT null AND partial = 0
        ORDER BY updated_date DESC');

    $stmt->execute(['coach_id' => $coach_id]);
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

            $stmtLevel = $pdo->prepare('SELECT levels.id, name, level_groups.id AS level_groups_id, level_number FROM levels INNER JOIN level_groups ON level_groups.id = levels.level_groups_id WHERE levels.id = :levels_id');
            $stmtLevel->execute(['levels_id' => $report_card['levels_id']]);
            $report_card['level'] = $stmtLevel->fetch();

            $stmtLevel = $pdo->prepare('SELECT intro_comment_id, skill_comment_id, closing_comment_id, event_id, skill_id FROM report_cards_comments WHERE id = :comment');
            $stmtLevel->execute(['comment' => $report_card['comment']]);
            $report_card['card_comments'] = $stmtLevel->fetch();

            $stmtLevel = $pdo->prepare('SELECT intro_comment_id, skill_comment_id, closing_comment_id, event_id, skill_id FROM report_cards_comments WHERE id = :comment_modifications');
            $stmtLevel->execute(['comment_modifications' => $report_card['comment_modifications']]);
            $report_card['card_mod_comments'] = $stmtLevel->fetch();

            $stmtEvents = $pdo->prepare("SELECT DISTINCT events.id, events.name FROM events INNER JOIN skills ON events.id = skills.events_id INNER JOIN report_cards_components ON report_cards_components.skills_id = skills.id WHERE report_cards_id = :report_cards_id");
            $stmtEvents->execute(['report_cards_id' => $report_card['report_cards_id']]);
            $report_card['events'] = $stmtEvents->fetchAll();

            $stmtComponenets = $pdo->prepare(
                'SELECT report_cards_components.id AS report_cards_components_id, report_cards_mod_components.id AS report_cards_mod_components_id, skills_id, rank, suggested_rank
                FROM report_cards_components 
                LEFT JOIN report_cards_mod_components ON report_cards_components.id = report_cards_mod_components.report_cards_components_id 
                WHERE report_cards_id = :report_cards_id');

            $stmtComponenets->execute(['report_cards_id' => $report_card['report_cards_id']]);
            $report_card['components'] = $stmtComponenets->fetchAll();

            for($x=0; $x<count($report_card['components']); $x++) {
                $component = $report_card['components'][$x];
                
                $stmtSkill = $pdo->prepare('SELECT * FROM skills WHERE id = :skills_id');
                $stmtSkill->execute(['skills_id' => $component['skills_id']]);
                $component['skill'] = $stmtSkill->fetch();
                unset($component['skills_id']);

                $report_card['components'][$x] = $component;
            }

            $results[$i] = $report_card;
        }
    }
    http_response_code(HTTP_CODE_OK);
    echo json_encode($results);
}