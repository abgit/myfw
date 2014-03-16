<?php

    require __DIR__ . '/3rdparty/loginradius/LoginRadiusSDK.php';
    require __DIR__ . '/3rdparty/loginradius/LoginRadiusStatusUpdate.php';
    require __DIR__ . '/3rdparty/loginradius/LoginRadiusPosts.php';

    class myloginradius{

        private $token = null;
        private $profile = array();
        private $auth = false;

        public function __construct(){
            $this->app = \Slim\Slim::getInstance();
            $this->requestInfo();
        }

        public function requestInfo(){

            // check if we are on login
            if( isset( $_REQUEST['token'] ) ){

                // check if we already have a token. webservice call already made
                if( !is_null( $this->token ) )
                    return;
        
                $this->token   = $_REQUEST['token'];
                $obj           = new LoginRadius( $this->app->config( 'loginradius.s' ), $this->token );
                $this->profile = (array) $obj->loginradius_get_data();
                $this->auth    = ( $obj->IsAuthenticated === true );
                $this->app->session()->set( 'lradiustoken',   $this->token );
                $this->app->session()->set( 'lradiusprofile', $this->profile );
                $this->app->session()->set( 'lradiusauth',    $this->auth );
            }else{
                $this->token   = $this->app->session()->get( 'lradiustoken',   null );
                $this->profile = $this->app->session()->get( 'lradiusprofile', array() );
                $this->auth    = $this->app->session()->get( 'lradiusauth',    false );
            }
        }

        public function islogged(){
            return $this->auth === true;
        }
        
        public function getInfo(){
            return $this->profile;
        }

        public function onLogged( $callback ){
            if( $this->islogged() )
                return call_user_func( $callback );
        }

        public function post( $to, $title, $url, $imageurl, $status, $caption, $description ){

            $obj = new LoginRadiusStatusUpdate( $this->app->config( 'loginradius.s' ), $this->token );
            return $obj->loginradius_post_status( $to, $title, $url, $imageurl, $status, $caption, $description );
        }
        
        public function getPosts(){
            $obj = new LoginRadiusPosts($this->app->config( 'loginradius.s' ), $this->token );
            return $obj->loginradius_get_posts();
        }
    }
