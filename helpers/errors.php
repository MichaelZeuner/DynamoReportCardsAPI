<?php

class ErrorProcess {
    public function createError($message) {
        return ['message' => $message];
    }

    public function echoError($message) {
        echo json_encode($this->createError($message));
    }
}