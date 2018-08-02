<?php

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

    class mypusher extends myajax{

        /** @var mycontainer */
        private $app;

        /** @var Pusher\Pusher */
        private $pusherObj;

        private $pkey;
        private $pcluster;
        private $pouts = array();

        public function __construct( $c ){
            $this->app = $c;
        }

        public function empty(){
            return empty( $this->pouts );
        }

        private function init(){
            if( isset( $this->pusherObj ) )
                return false;

            $urloriginal = $this->app->config[ 'pusher.driver' ] === 'heroku' ? getenv( 'PUSHER_URL' ) : $this->app->config[ 'pusher.url' ];

            $url = parse_url( $urloriginal );

            $path = pathinfo( $url[ 'path' ] );

            $this->pkey = $url[ 'user' ];
            $this->pcluster = $this->app->filters->urlregion( $urloriginal );

            $this->pusherObj = new Pusher\Pusher(
                  $this->pkey,
                  $url[ 'pass' ],
                  $path[ 'basename' ],
                  array( 'cluster' => $this->pcluster, 'encrypted' => true )
            );

            return true;
        }

        public function ajaxSubscribe( $channel, $event, $replace = false, $replaceWith = false ){
            $this->init();
            $this->app->ajax->pusherSubscribe( $this->pkey, $channel, $event, true, $this->pcluster, $replace, $replaceWith );
        }

        public function checkEndpoint( Request $request, Response $response ){
            $this->init();
            $channel = $this->app->config[ 'pusher.channel' ];

            if( isset( $_POST['channel_name'] ) && is_string( $_POST['channel_name'] ) && $_POST['channel_name'] === $channel )
                return $response->write( $this->pusherObj->socket_auth($_POST['channel_name'], $_POST['socket_id']) );

            return $response->withStatus(403)->write( 'Forbidden' );
        }

        public function send( $channel, $event = null ){
            $this->init();
            if( empty( $channel ) )
                $channel = $this->app->config[ 'pusher.channel' ];

            if( empty( $event ) )
                $event = $this->app->config[ 'pusher.event' ];

            foreach( $this->out as $oper => $val )
                $this->pouts[ $channel ][ $event ][ $oper ] = $val;

            $this->out = array();
        }

        public function sendAll(){
            $this->init();
            if( !empty( $this->out ) )
                foreach( $this->out as $oper => $val )
                    $this->pouts[ $this->app->config[ 'pusher.channel' ] ][ $this->app->config[ 'pusher.event' ] ][ $oper ] = $val;

            foreach( $this->pouts as $channel => $e)
                foreach( $e as $event => $obj )
                    $this->pusherObj->trigger( $channel, $event, json_encode( $obj ) );
        }
    }