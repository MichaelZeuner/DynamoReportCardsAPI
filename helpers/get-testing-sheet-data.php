<?php

function getTestingSheetData($pdo, $error, $passedData) {
    $results = [];


//THIS LOOKS ALL PROMISING FOR THE PASSED LEVEL. nOW DO IT FOR +/- 1

    for($i=0; $i<sizeof($passedData);$i++) {
        $athleteData = $passedData[$i];
        
        $results[$i]['athlete'] = getAthleteInfo($pdo, $athleteData->athlete_id);

        $levelInfo = getLevelInfo($pdo, $athleteData->current_level);

        $levelMinus2 = getLevelExistsAndData($pdo, $levelInfo['level_groups_id'], $levelInfo['level_number']-2);
        $levelMinus1 = getLevelExistsAndData($pdo, $levelInfo['level_groups_id'], $levelInfo['level_number']-1);
        $levelPlus1 = getLevelExistsAndData($pdo, $levelInfo['level_groups_id'], $levelInfo['level_number']+1);
        $levelPlus2 = getLevelExistsAndData($pdo, $levelInfo['level_groups_id'], $levelInfo['level_number']+2);

        $results[$i]['levels'] = [];
        if($levelMinus1 != false && $levelPlus1 != false) {
            $results[$i]['levels'][0] = getDataForLevel($pdo, $levelMinus1['id'], $athleteData->athlete_id);
            $results[$i]['levels'][1] = getDataForLevel($pdo, $athleteData->current_level, $athleteData->athlete_id);
            $results[$i]['levels'][2] = getDataForLevel($pdo, $levelPlus1['id'], $athleteData->athlete_id);
        } else if($levelMinus2 != false && $levelMinus1 != false) {
            $results[$i]['levels'][0] = getDataForLevel($pdo, $levelMinus2['id'], $athleteData->athlete_id);
            $results[$i]['levels'][1] = getDataForLevel($pdo, $levelMinus1['id'], $athleteData->athlete_id);
            $results[$i]['levels'][2] = getDataForLevel($pdo, $athleteData->current_level, $athleteData->athlete_id);

        } else if($levelPlus1 != false && $levelPlus2 != false) {
            $results[$i]['levels'][0] = getDataForLevel($pdo, $athleteData->current_level, $athleteData->athlete_id);
            $results[$i]['levels'][1] = getDataForLevel($pdo, $levelPlus1['id'], $athleteData->athlete_id);
            $results[$i]['levels'][2] = getDataForLevel($pdo, $levelPlus2['id'], $athleteData->athlete_id);
        } else {
            http_response_code(HTTP_CODE_NOT_FOUND);
            return $error->createError('No triple level found with starting point of level id: ' . $athleteData->current_level);
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
    $stmt = $pdo->prepare("SELECT name, level_number, levels.id, report_cards.id AS report_cards_id FROM levels 
        INNER JOIN level_groups ON level_groups.id = levels.level_groups_id 
        LEFT JOIN report_cards ON levels.id = report_cards.levels_id
        WHERE levels.id = :levels_id AND levels.active = 1 AND (athletes_id = :athlete_id OR athletes_id IS NULL)");

    $stmt->execute(['levels_id' => $levelId, "athlete_id" => $athleteId]);

    $results = $stmt->fetchAll()[0];

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