<?php
function getComments($pdo, $error, $accessLevel, $levelGroupId) {
    $commentsCRUD = new Comments($pdo, $error);
    $commentsCRUD->setAccessLevel($accessLevel);
    $comments = $commentsCRUD->read(null, null);

    for($i=count($comments)-1; $i>=0; $i--) {
        $stmt = $pdo->prepare("SELECT levels_id AS id, level_groups_id, name, level_number FROM comment_levels 
            INNER JOIN levels ON comment_levels.levels_id = levels.id
            INNER JOIN level_groups ON level_groups.id = levels.level_groups_id
            WHERE comments_id = :comments_id");
        $stmt->execute(['comments_id' => $comments[$i]['id']]);

        $levels = $stmt->fetchAll();
        $comments[$i]['levels'] = $levels;

        if($levelGroupId !== null) {
            $found = false;
            for($x=0; $x<count($levels); $x++) {
                if($levels[$x]['level_groups_id'] == $levelGroupId) {
                    $found = true;
                    break;
                }
            }
            if($found == false && count($levels) != 0) {
                array_splice($comments, $i, 1);
            }
        }
    }
    return $comments;
}

function addComments($pdo, $error, $accessLevel) {
    if(empty($_POST)) {
        $_POST = json_decode(file_get_contents('php://input'), true);
    }
    $commentsCRUD = new Comments($pdo, $error);
    $commentsCRUD->setAccessLevel($accessLevel);
    $createData['type'] = $_POST['type'];
    $createData['comment'] = $_POST['comment'];
    $comments = $commentsCRUD->create($createData);
    $comments['levels'] = $_POST['levels'];

    if(count($comments['levels']) > 0) {
        $values = '';
        for($i=0; $i<count($comments['levels']); $i++) {
            $values .= '(' . $comments['id'] . ',' . $comments['levels'][$i]['id'] . ')';
            if($i !== count($comments['levels'])-1) {
                $values .= ', ';
            }
        }

        $stmt = $pdo->prepare("INSERT INTO comment_levels(comments_id, levels_id) VALUES $values");
        $stmt->execute();
    }
    
    return $comments;
}

function putComments($pdo, $error, $accessLevel) {
    $commentsCRUD = new Comments($pdo, $error);
    $commentsCRUD->setAccessLevel($accessLevel);
    $putData = json_decode(file_get_contents("php://input"), true);
    $comments = $commentsCRUD->update($putData['id'], $putData);
    $comments['levels'] = $putData['levels'];
    
    $stmt = $pdo->prepare("DELETE FROM comment_levels WHERE comments_id = :comments_id");
    $stmt->execute(['comments_id' => $comments['id']]);

    if(count($comments['levels']) > 0) {
        $values = '';
        for($i=0; $i<count($comments['levels']); $i++) {
            $values .= '(' . $comments['id'] . ',' . $comments['levels'][$i]['id'] . ')';
            if($i !== count($comments['levels'])-1) {
                $values .= ', ';
            }
        }

        $stmt = $pdo->prepare("INSERT INTO comment_levels(comments_id, levels_id) VALUES $values");
        $stmt->execute();
    }
    
    return $comments;
}