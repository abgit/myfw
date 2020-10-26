<?php

use Nexmo\Client;
use Nexmo\Client\Credentials\Basic;

class mynexmo{

        /** @var mycontainer */
        private $app;

        /** @var Nexmo\Client  */
        private Client $client;

        public function __construct( $c ){
            $this->app = $c;
            $this->client = new Client( new Basic( $this->app->config[ 'nexmo.key' ], $this->app->config[ 'nexmo.secret' ] ) );
        }


        public function verifyCheck( $token, $pin ): bool
        {
            try{
                return (int)$this->client->verify()->check($token, $pin)->getStatus() === 0;
            } catch( Nexmo\Client\Exception\Request $e ){
                return false;
            }
        }


        public function verifyStart( $mobile ): ?string{

            $options = array(
                'number'      => $mobile,
                'code_length' => 4,
                'lg'          => 'en-us',
                'brand'       => $this->app->config[ 'nexmo.brand' ],
                'sender_id'   => $this->app->config[ 'nexmo.sender_id' ]
            );

            try {
                return $this->client->verify()->start($options)->getRequestId();
            }catch(Exception $exception){
                return '';
            }
        }
    }
