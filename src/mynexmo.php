<?php


    class mynexmo{

        /** @var mycontainer */
        private $app;

        /** @var Nexmo\Client  */
        private $client;

        public function __construct( $c ){
            $this->app = $c;
            $this->client = new Nexmo\Client( new Nexmo\Client\Credentials\Basic( $this->app->config[ 'sms.nexmokey' ], $this->app->config[ 'sms.nexmosecret' ] ) );
        }


        public function verifyCheck( $token, $pin ){

            try{
                return intval( $this->client->verify()->check( $token, $pin )->getStatus() ) === 0;
            } catch( Nexmo\Client\Exception\Request $e ){
                return false;
            }
        }


        public function verifyStart( $mobile ){

            $options = array( 'number' => $mobile, 'code_length' => 4, 'lg' => 'en-us' );

            if( isset( $this->app->config[ 'nexmo.from' ] ) ) {
                $options['brand']     = $this->app->config[ 'nexmo.from' ];
                $options['sender_id'] = $this->app->config[ 'nexmo.from' ];
            }

            return $this->client->verify()->start( $options );
        }
    }
