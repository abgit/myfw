<?php

    class mypusher extends myajax{

        private $pkey, $pcluster, $pouts;

        public function __construct(){
            $this->pouts = array();

            $this->app = \Slim\Slim::getInstance();

            $url = $this->app->config( 'pusher.driver' );

            if( $url === 'heroku' ){
                $url = parse_url( getenv( 'PUSHER_URL' ) );
            }elseif( $url === 'fortrabbit' ){
                $url = parse_url( $this->app->configdecrypt( getenv( 'PUSHER_URL' ) ) );
            }

            $path = pathinfo( $url[ 'path' ] );

            $this->pkey = $url[ 'user' ];
            $this->pcluster = substr( strstr( $url[ 'host' ], '.', true ), 4 );

            $this->pusherObj = new Pusher(
                  $this->pkey,
                  $url[ 'pass' ],
                  $path[ 'basename' ],
                  array( 'host' => $url[ 'host' ], 'encrypted' => true )
            );
        }

        public function ajaxSubscribe( $channel, $event, $replace = false, $replaceWith = false ){
            $this->app->ajax()->pusherSubscribe( $this->pkey, $channel, $event, true, $this->pcluster, $replace, $replaceWith );
        }
        
        public function checkendpoint( $channel ){
 
            if( isset( $_POST['channel_name'] ) && is_string( $_POST['channel_name'] ) && $_POST['channel_name'] === $channel ){
                echo $this->pusherObj->socket_auth($_POST['channel_name'], $_POST['socket_id']);
            
                return true;
            }
            
            header('', true, 403);
            echo "Forbidden";
            return false;
        }

        public function send( $channel, $event = null ){

            if( empty( $channel ) )
                $channel = $this->app->config( 'pusher.channel' );

            if( empty( $event ) )
                $event = $this->app->config( 'pusher.event' );

            foreach( $this->out as $oper => $val )
                $this->pouts[ $channel ][ $event ][ $oper ] = $val;

            $this->out = array();
        }

        public function sendAll(){

            if( !empty( $this->out ) )
                foreach( $this->out as $oper => $val )
                    $this->pouts[ $this->app->config( 'pusher.channel' ) ][ $this->app->config( 'pusher.event' ) ][ $oper ] = $val;

            foreach( $this->pouts as $channel => $e)
                foreach( $e as $event => $obj )
                    $this->pusherObj->trigger( $channel, $event, json_encode( $obj ) );
        }
    }