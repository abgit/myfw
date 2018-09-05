<?php


class mysession extends Slim\Middleware\Session{

    public function start(){
        $this->startSession();
    }

    public function check(){

        if( !isset( $_SESSION[ 'useragent' ] ) )
            $_SESSION[ 'useragent' ] = $_SERVER[ 'HTTP_USER_AGENT' ];

        if( $_SESSION[ 'useragent' ] !== $_SERVER[ 'HTTP_USER_AGENT' ] )
            session_destroy();
    }
}