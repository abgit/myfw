<?php

    class mypusher extends Pusher{

        private $pkey, $pcluster;

        public function __construct(){

            $url  = parse_url( getenv( 'PUSHER_URL' ) );
            $path = pathinfo( $url[ 'path' ] );

            $this->pkey = $url[ 'user' ];
            $this->pcluster = substr( strstr( $url[ 'host' ], '.', true ), 4 );

            parent::__construct(
                  $this->pkey,
                  $url[ 'pass' ],
                  $path[ 'basename' ],
                  array( 'host' => $url[ 'host' ], 'encrypted' => true )
            );
        }

        public function getPKey(){
            return $this->pkey;
        }

        public function getPCluster(){
            return $this->pcluster;
        }

    }