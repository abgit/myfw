<?php


class mysession extends Slim\Middleware\Session{

    public function start(): void
    {
        $this->startSession();
    }

    public function check(): void
    {

        if( !isset( $_SESSION[ 'useragent' ] ) ) {
            $_SESSION['useragent'] = $_SERVER['HTTP_USER_AGENT'];
        }

        if( $_SESSION[ 'useragent' ] !== $_SERVER[ 'HTTP_USER_AGENT' ] ) {
            session_destroy();
        }
    }
}