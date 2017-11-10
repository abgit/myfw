<?php

    use Intercom\IntercomClient;


    class myintercom extends IntercomClient{

        private $app;

        public function __construct(){

            $this->app = \Slim\Slim::getInstance();

            parent::__construct( $this->app->config( 'intercom.pat' ), null );
        }

    }
