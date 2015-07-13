<?php

class mysms{
    
    private $resultcode = null;

    public function __construct(){
        $this->app = \Slim\Slim::getInstance();
    }

    public function send( & $resultcode, $to, $text, $from ){

        $json = json_decode( file_get_contents( 'https://rest.nexmo.com/sms/json?api_key=' . $this->app->config( 'sms.nexmo.key' ) . '&api_secret=' . $this->app->config( 'sms.nexmo.secret' ) . '&from=' . $from . '&to=' . $to . '&text=' . urlencode( $text ) ), true );

        if( ! isset( $json[ 'messages' ][0][ 'status' ] ) ){
            $this->resultcode = -1;
            return false;
        }
        
        if( $json[ 'messages' ][0][ 'status' ] > 0 ){
            $this->resultcode = $json[ 'messages' ][0][ 'status' ];
            return false;
        }
        
        $this->resultcode = 0;
        return true;
    }

    public function errorMessage(){
        if( is_null( $this->resultcode ) )
            return 'Unknown error';

        switch( $this->resultcode ){
            case -1: return 'Internal error';
            case 0:  return 'Message sent';
            case 1:  return 'Message Throttled. Err:1';
            case 6:  return 'Un-recognized number prefix. Err:6';
            case 7:  return 'Number blacklisted. Err:7';
            default: return 'Internal error. Err:' . $this->resultcode;
        }
    }
}