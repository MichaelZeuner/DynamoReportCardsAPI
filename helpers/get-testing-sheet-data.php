<?php

function getTestingSheetData($pdo, $error, $passedData) {
    $results = [];
    $page = 0;
    $newPage = true;
    for($i=0; $i<ceil(sizeof($passedData)/2);$i++) {
        $results[$i] = [];
    }

    for($i=0; $i<sizeof($passedData);$i++) {
        $athleteData = $passedData[$i];
        
        if($newPage) {
            $results[$page]['athlete'] = [];
            $results[$page]['levels'] = [];
            $results[$page]['events'] = [];
        }

        array_push($results[$page]['athlete'], getAthleteInfo($pdo, $athleteData->athlete_id));

        $levelInfo = getLevelInfo($pdo, $athleteData->current_level);

        $levelMinus2 = getLevelExistsAndData($pdo, $levelInfo['level_groups_id'], $levelInfo['level_number']-2);
        $levelMinus1 = getLevelExistsAndData($pdo, $levelInfo['level_groups_id'], $levelInfo['level_number']-1);
        $levelPlus1 = getLevelExistsAndData($pdo, $levelInfo['level_groups_id'], $levelInfo['level_number']+1);
        $levelPlus2 = getLevelExistsAndData($pdo, $levelInfo['level_groups_id'], $levelInfo['level_number']+2);

        $currentLevel = [];
        if($levelMinus1 != false && $levelPlus1 != false) {
            array_push($currentLevel, getDataForLevel($pdo, $levelMinus1['id'], $athleteData->athlete_id));
            array_push($currentLevel, getDataForLevel($pdo, $athleteData->current_level, $athleteData->athlete_id));
            array_push($currentLevel, getDataForLevel($pdo, $levelPlus1['id'], $athleteData->athlete_id));
        } else if($levelMinus2 != false && $levelMinus1 != false) {
            array_push($currentLevel, getDataForLevel($pdo, $levelMinus2['id'], $athleteData->athlete_id));
            array_push($currentLevel, getDataForLevel($pdo, $levelMinus1['id'], $athleteData->athlete_id));
            array_push($currentLevel, getDataForLevel($pdo, $athleteData->current_level, $athleteData->athlete_id));

        } else if($levelPlus1 != false && $levelPlus2 != false) {
            array_push($currentLevel, getDataForLevel($pdo, $athleteData->current_level, $athleteData->athlete_id));
            array_push($currentLevel, getDataForLevel($pdo, $levelPlus1['id'], $athleteData->athlete_id));
            array_push($currentLevel, getDataForLevel($pdo, $levelPlus2['id'], $athleteData->athlete_id));
        } else {
            http_response_code(HTTP_CODE_NOT_FOUND);
            return $error->createError('No triple level found with starting point of level id: ' . $athleteData->current_level);
        }

        array_push($results[$page]['events'], getEvents($pdo, $error, $currentLevel, $athleteData->athlete_id));
        array_push($results[$page]['levels'], $currentLevel);

        $newPage = !$newPage;
        if($newPage) {
            $page++;
        }
    }

    return $results;
}

function getAthleteInfo($pdo, $athleteId) {
    $stmt = $pdo->prepare("SELECT * FROM athletes WHERE id = :athlete_id");
    $stmt->execute(['athlete_id' => $athleteId]);
    return $stmt->fetchAll()[0];
}

function getLevelInfo($pdo, $levelId) {
    $stmt = $pdo->prepare("SELECT level_groups_id, level_number FROM levels WHERE id = :levels_id");
    $stmt->execute(['levels_id' => $levelId]);
    return $stmt->fetchAll()[0];
}

function getLevelExistsAndData($pdo, $levelGroupId, $levelNumber) {
    $stmt = $pdo->prepare("SELECT * FROM levels WHERE level_groups_id = :level_groups_id AND level_number = :level_number");
    $stmt->execute(['level_groups_id' => $levelGroupId, "level_number" => $levelNumber]);
    
    $results = $stmt->fetchAll();
    if (sizeof($results) == 0) { return false; }
    else { return $results[0]; }
}

function getDataForLevel($pdo, $levelId, $athleteId) {
    $stmt = $pdo->prepare("SELECT name, level_number, levels.id FROM levels 
        INNER JOIN level_groups ON level_groups.id = levels.level_groups_id 
        WHERE levels.id = :levels_id AND levels.active = 1");

    $stmt->execute(['levels_id' => $levelId]);

    $results = $stmt->fetchAll()[0];


    $stmt = $pdo->prepare("SELECT id AS report_cards_id FROM report_cards
        WHERE levels_id = :levels_id AND athletes_id = :athlete_id");

    $stmt->execute(['levels_id' => $levelId, "athlete_id" => $athleteId]);

    $reportCardsResults = $stmt->fetchAll();
    if(count($reportCardsResults) == 0) {
        $results['report_cards_id'] = NULL;
    } else {
        $results['report_cards_id'] = $reportCardsResults[0]['report_cards_id'];
    }


    $stmt = $pdo->prepare("SELECT name, skills.id, events_id, rank FROM skills 
        LEFT JOIN report_cards_components ON report_cards_components.skills_id = skills.id
        WHERE levels_id = :levels_id AND active = 1 AND (report_cards_id = :report_cards_id OR report_cards_id IS NULL) 
        ORDER BY events_id ASC");
    $stmt->execute(['levels_id' => $levelId, 'report_cards_id' => $results['report_cards_id']]);
    $components = $stmt->fetchAll();

    $events = [];
    for($j=0;$j<count($components);$j++) {

        $insertRequired = true;
        for($k=0;$k<count($events);$k++) {
            if($events[$k]['id'] === $components[$j]['events_id']) {
                array_push($events[$k]['components'], $components[$j]);
                $insertRequired = false;
                break;
            }
        }

        if($insertRequired) {
            $stmt = $pdo->prepare("SELECT name, id FROM events WHERE id = :events_id AND active = 1");
            $stmt->execute(['events_id' => $components[$j]['events_id']]);
            $event = $stmt->fetchAll()[0];
            $event['components'] = [];
            array_push($event['components'], $components[$j]);

            array_push($events, $event);
        }
    }
    $results['events'] = $events;
    return $results;
}
