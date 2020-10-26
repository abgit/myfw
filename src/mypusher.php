<?php

use \Slim\Http\Request;
use \Slim\Http\Response;

use \Pusher\Pusher;

    class mypusher extends myajax{

        /** @var mycontainer */
        private $app;

        private Pusher $pusherObj;

        private string $pkey;
        private string $pcluster;
        private array $pouts = array();

        public function __construct( $c ){
            $this->app = $c;
        }

        public function empty(): bool {
            return empty( $this->pouts );
        }

        private function init(): bool
        {
            if( isset( $this->pusherObj ) ) {
                return false;
            }

            $urloriginal = $this->app->config[ 'pusher.url' ];

            $url = parse_url( $urloriginal );

            $path = pathinfo( $url[ 'path' ] );

            $this->pkey = $url[ 'user' ];
            $this->pcluster = $this->app->filters->urlregion( $urloriginal );

            $this->pusherObj = new Pusher(
                  $this->pkey,
                  $url[ 'pass' ],
                  $path[ 'basename' ],
                  array( 'cluster' => $this->pcluster, 'encrypted' => true )
            );

            return true;
        }

        public function ajaxSubscribe( $channel, $event, $replace = false, $replaceWith = false ): void
        {
            $this->init();
            $this->app->ajax->pusherSubscribe( $this->pkey, $channel, $event, true, $this->pcluster, $replace, $replaceWith );
        }

        public function checkEndpoint( Request $request, Response $response ): Response{
            $this->init();

            if(isset($_POST['channel_name'], $_POST['socket_id'], $this->app->config['pusher.privatechannels']) && is_string($_POST['channel_name']) && is_string($_POST['socket_id']) && is_array($this->app->config['pusher.privatechannels']) && in_array($_POST['channel_name'], $this->app->config['pusher.privatechannels'], true)) {
                return $response->write($this->pusherObj->socket_auth($_POST['channel_name'], $_POST['socket_id']));
            }

            return $response->withStatus(403)->write( 'Forbidden' );
        }

        public function send( $channel, $event = null ): void
        {
            $this->init();
            //if( empty( $channel ) ) {
            //    $channel = $this->app->config['pusher.channel'];
            //}

            //if( empty( $event ) ) {
            //    $event = $this->app->config['pusher.event'];
            //}

            foreach( $this->out as $oper => $val ) {
                $this->pouts[$channel ?? $this->app->config['pusher.channel'] ][$event ?? $this->app->config['pusher.event']][$oper] = $val;
            }

            $this->out = array();
        }

        public function sendAll( $channel, $event ): void
        {
            $this->init();

            $outs      = (string)$this;
            $this->out = array();

            //if( !empty( $this->out ) ) {
              //  foreach ($outs as $obj /*$oper => $val*/) {
                    //$this->pouts[$channel ?? $this->app->config['pusher.channel'] ][ $event ?? $this->app->config['pusher.event'] ][$oper] = $val;
                    $this->pusherObj->trigger($channel ?? $this->app->config['pusher.channel'], $event ?? $this->app->config['pusher.event'], $outs );
                //}
            //}

            //foreach( $this->pouts as $channel => $e) {
            //    foreach ($e as $event => $obj) {
            //        $this->pusherObj->trigger($channel, $event, json_encode($obj, JSON_THROW_ON_ERROR, 512));
            //    }
            //}
        }
    }