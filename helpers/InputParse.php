<?php

function getInputData() {
    parse_str(file_get_contents('php://input'), $_INPUT);

    $inputData = [];
    foreach($_INPUT as $input) {
        $dataSet = explode("form-data;", $input);
        foreach($dataSet as $data) {
            $lines = explode("\n", $data);
            
            if(count($lines) >= 2) {
                $key_start = strpos($lines[0], '"') +1;
                $key_len = strrpos($lines[0], '"') - $key_start;
                $key = substr($lines[0], $key_start, $key_len);
                $value = trim($lines[2]);
                $inputData[$key] = $value;
            }
        }
    }
    return $inputData;
}

?>