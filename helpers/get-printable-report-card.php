<?php

function getPrintableReportCard($pdo, $error, $athleteId) {
    $printableReportCard = [];
    $printableReportCard['levels'] = getRecentLevel($pdo, $error, $athleteId);
    $printableReportCard['events'] = getEvents($pdo, $error, $printableReportCard['levels'], $athleteId);
    $printableReportCard['coachComments'] = getCoachComments($pdo, $error, $athleteId);
    $printableReportCard['athlete'] = getAthlete($pdo, $error, $athleteId);

    echo json_encode($printableReportCard);
}

function getAthlete($pdo, $error, $athleteId) {
    $stmt = $pdo->prepare("SELECT first_name, last_name FROM athletes WHERE id = :athletes_id");
    $stmt->execute(['athletes_id' => $athleteId]);

    $athlete = $stmt->fetch();
    if(count($athlete) == 0) {
        http_response_code(HTTP_CODE_NOT_FOUND);
        $error->echoError('No athlete found when attempting to generate printable report card... getAthlete()');
    } else {
        return $athlete['first_name'] . ' ' . $athlete['last_name'];
    }
}

function getCoachComments($pdo, $error, $athleteId) {
    $numberOfComments = 5;
    $stmt = $pdo->prepare("SELECT name AS level_name, level_number, session, first_name, last_name, comment, status, updated_date FROM report_cards INNER JOIN levels ON report_cards.levels_id = levels.id INNER JOIN level_groups ON levels.level_groups_id = level_groups.id INNER JOIN users ON report_cards.submitted_by = users.id WHERE athletes_id = :athletes_id AND approved IS NOT null ORDER BY updated_date DESC LIMIT $numberOfComments");
    $stmt->execute(['athletes_id' => $athleteId]);

    $comments = $stmt->fetchAll();
    if(count($comments) == 0) {
        http_response_code(HTTP_CODE_NOT_FOUND);
        $error->echoError('No coaches comments found when attempting to generate printable report card... getCoachComments()');
    } else {
        for($i=0; $i < count($comments); $i++) {
            $stmtComments = $pdo->prepare('SELECT intro_comment_id, skill_comment_id, closing_comment_id, event_id, skill_id FROM report_cards_comments WHERE id = :comment');
            $stmtComments->execute(['comment' => $comments[$i]['comment']]);
            $card_comments = $stmtComments->fetch();

            $stmtIntro = $pdo->prepare('SELECT comment FROM comments WHERE id = :id');
            $stmtIntro->execute(['id' => $card_comments['intro_comment_id']]);
            $introComment = $stmtIntro->fetch()['comment'];

            $stmtSkill = $pdo->prepare('SELECT comment, events.name AS event_name, skills.name AS skill_name FROM comments INNER JOIN events ON events.id = :event_id INNER JOIN skills ON skills.id = :skill_id WHERE comments.id = :id');
            $stmtSkill->execute(['id' => $card_comments['skill_comment_id'], 'event_id' => $card_comments['event_id'], 'skill_id' => $card_comments['skill_id']]);
            $skillObj = $stmtSkill->fetch();

            $skillComment = str_replace("~!EVENT!~", $skillObj['event_name'], str_replace("~!SKILL!~", $skillObj['skill_name'], $skillObj['comment']));
            
            $stmtOutro = $pdo->prepare('SELECT comment FROM comments WHERE id = :id');
            $stmtOutro->execute(['id' => $card_comments['closing_comment_id']]);
            $outroComment = $stmtOutro->fetch()['comment'];

            $comments[$i]['comment'] = str_replace("~!NAME!~", $comments[$i]['first_name'], $introComment);
            $comments[$i]['comment'] .= ' ';
            $comments[$i]['comment'] .= str_replace("~!NAME!~", $comments[$i]['first_name'], $skillComment);
            $comments[$i]['comment'] .= ' ';
            $comments[$i]['comment'] .= str_replace("~!NAME!~", $comments[$i]['first_name'], $outroComment);
        }

        if(count($comments) < $numberOfComments) {
            $empty = [];
            $empty['level_name'] = 'empty';
            $empty['first_name'] = '';
            $empty['last_name'] = '';
            $empty['comment'] = '';
            $empty['update_date'] = '';
            $commentsCount = count($comments);
            for($i=0; $i < $numberOfComments - $commentsCount; $i++) {
                array_push($comments, $empty);
            }
        }
        return $comments;
    }
}

function getEvents($pdo, $error, $levels, $athleteId) {
    $whereLevels = "";
    for($i=0; $i<count($levels); $i++) {
        $whereComponent = "levels_id = " . $levels[$i]['id'];
        if($i===0) { $whereLevels = "(" . $whereComponent; }
        else { $whereLevels .= " OR " . $whereComponent; }
    }
    $whereLevels .= ")";

    $stmt = $pdo->query("SELECT DISTINCT events.id, events.name FROM skills INNER JOIN events ON skills.events_id = events.id WHERE skills.active = 1 AND $whereLevels");

    $allEvents = $stmt->fetchAll();

    if(count($allEvents) == 0) {
        http_response_code(HTTP_CODE_NOT_FOUND);
        $error->echoError('No matching events found when attempting to generate printable report card... getLevelsEvents()');
    } else {
        return getEventsSkills($pdo, $error, $levels, $allEvents, $athleteId);
    }
}

function getEventsSkills($pdo, $error, $levels, $events, $athleteId) {
    //this will just be the maximum number of skill in the event across the levels.
    //aka the number of rows required for this event
    //each skillRow will have ['levels'] = getEventsSkillsLevels()

    for($i=0; $i<count($events); $i++) {
        $skillsInEventLevel = [];
        $maxSkills = 0;
        for($x=0; $x<count($levels); $x++) {
            $stmt = $pdo->prepare("SELECT id, name FROM skills WHERE active = 1 AND levels_id = :level_id AND events_id = :event_id");
            $stmt->execute(['level_id' => $levels[$x]['id'], 'event_id' => $events[$i]['id']]);

            $allSkills = $stmt->fetchAll();

            if(count($allSkills) == 0) {
                http_response_code(HTTP_CODE_NOT_FOUND);
                $error->echoError('No recent level found when attempting to generate printable report card... getRecentLevel()');
                return;
            } else {
                if(count($allSkills) > $maxSkills) { $maxSkills = count($allSkills); }
                array_push($skillsInEventLevel, $allSkills);
            }
        }

        $events[$i]['skills'] = [];
        for($x=0; $x<$maxSkills; $x++) {
            array_push($events[$i]['skills'], []);
            $events[$i]['skills'][$x]['levels'] = [];
            for($s=0; $s<count($skillsInEventLevel); $s++) {
                if(array_key_exists($x, $skillsInEventLevel[$s])) {
                    //pass a real rank
                    $skillsInEventLevel[$s][$x]['rank'] = getAthletesRankForSkill($pdo, $error, $skillsInEventLevel[$s][$x]['id'], $athleteId);

                    array_push($events[$i]['skills'][$x]['levels'], $skillsInEventLevel[$s][$x]);
                } else {
                    $empty = [];
                    $empty['id'] = -1;
                    $empty['rank'] = '';
                    $empty['name'] = '';
                    array_push($events[$i]['skills'][$x]['levels'], $empty);
                }
            }
        }
    }
    
    return $events;
}

function getAthletesRankForSkill($pdo, $error, $skillId, $athleteId) {
    $stmt = $pdo->prepare("SELECT rank FROM report_cards INNER JOIN report_cards_components ON report_cards.id = report_cards_components.report_cards_id WHERE athletes_id = :athletes_id AND skills_id = :skills_id AND approved IS NOT null");
    $stmt->execute(['athletes_id' => $athleteId, 'skills_id' => $skillId]);

    $ranks = $stmt->fetchAll();
    $rank = '';
    if(count($ranks) > 0) {
        for($i=0; $i<count($ranks); $i++) {
            $rank = substr($ranks[$i]['rank'], 0, 1);
            if($rank === 'M') { break; }
        }
    }
    return $rank;
}

function getRecentLevel($pdo, $error, $athleteId) {
    $stmt = $pdo->prepare("SELECT levels.id, name, level_number FROM report_cards INNER JOIN levels ON report_cards.levels_id = levels.id INNER JOIN level_groups ON levels.level_groups_id = level_groups.id WHERE athletes_id = :athletes_id ORDER BY created_date DESC LIMIT 1");
    $stmt->execute(['athletes_id' => $athleteId]);

    $levelName = $stmt->fetch();
    if(count($levelName) == 0) {
        http_response_code(HTTP_CODE_NOT_FOUND);
        $error->echoError('No recent level found when attempting to generate printable report card... getRecentLevel() [1]');
    } else {
        $stmt2 = $pdo->prepare("SELECT levels.id, name, level_number FROM `levels` INNER JOIN level_groups ON levels.level_groups_id = level_groups.id WHERE name LIKE :name ORDER BY level_number ASC");
        $stmt2->execute(['name' => $levelName['name']]);

        $allLevels = $stmt2->fetchAll();

        if(count($allLevels) == 0) {
            http_response_code(HTTP_CODE_NOT_FOUND);
            $error->echoError('No recent level found when attempting to generate printable report card... getRecentLevel() [2]');
        } else {
            $levelNames = [];
            $FULL = 6;
            $PREVIOUS_LEVELS = 3;
            $FUTURE_LEVELS = 2;
            for($i=0; $i<count($allLevels);$i++) {
                //if the amount of levels doesnt pass the full value just push them all
                if(count($allLevels) <= $FULL) {
                    array_push($levelNames, $allLevels[$i]);
                } else {
                    if($allLevels[$i]['level_number'] === $levelName['level_number']) {
                        //this is the ideal senario
                        if($i >= $PREVIOUS_LEVELS && count($allLevels) - ($i+1) >= $FUTURE_LEVELS) {
                            for($x = $i-$PREVIOUS_LEVELS; $x < $FULL + ($i-$PREVIOUS_LEVELS); $x++) {
                                array_push($levelNames, $allLevels[$x]);
                            }
                            return $levelNames;
                        }
                        //theres not enough previous levels so just start at zero
                        else if($i < $PREVIOUS_LEVELS) {
                            for($x = 0; $x < $FULL; $x++) {
                                array_push($levelNames, $allLevels[$x]);
                            }
                            return $levelNames;
                        }
                        //theres not enough future levels so push the last FULL
                        else {
                            for($x = count($allLevels) - $FULL; $x < count($allLevels); $x ++) {
                                array_push($levelNames, $allLevels[$x]);
                            }
                            return $levelNames;
                        }
                    }
                }
            }
            return $levelNames;
        }
    }
    
}

