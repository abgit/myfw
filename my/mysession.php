<?php


class mysession extends Slim\Middleware\Session{

    public function start(){
        $this->startSession();
    }
}