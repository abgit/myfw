<?php

    class myclient implements arrayaccess{

        /** @var mycontainer */
        private $app;

        // TODO: add /auth0/callback middleware
        public function __construct( $c ){
            $this->app = $c;
            $ipaddress = $_SERVER['REMOTE_ADDR'];

            if( !isset( $this->app->session->myclient[ 'ipaddress' ] ) )
                $this->app->session->myclient[ 'ipaddress' ] = $ipaddress;

            if( !isset( $this->app->session->myclient[ 'countrycode' ] ) )
                $this->app->session->myclient[ 'countrycode' ] = geoip_country_code_by_name( $ipaddress );

            if( !isset( $this->app->session->myclient[ 'countryname' ] ) )
                $this->app->session->myclient[ 'countryname' ] = geoip_country_name_by_name( $ipaddress );

            if( !isset( $this->app->session->myclient[ 'useragent' ] ) )
                $this->app->session->myclient[ 'useragent' ] = $_SERVER['HTTP_USER_AGENT'];
        }

        public function islogged(){
            return isset( $this->app->session->myclient[ 'uuid'] );
        }

        public function getAll(){
            return isset( $this->app->session->myclient ) && is_array( $this->app->session->myclient ) ? $this->app->session->myclient : array();
        }

        public function offsetGet( $setting ) {

            if( isset( $this->app->session->myclient[ $setting ] ) )
                return $this->app->session->myclient[ $setting ];

            return null;
        }

        public function offsetSet( $offset, $value ) {

            $client = $this->app->session->myclient;

            $client[$offset] = $value;

            $this->app->session->myclient = $client;
        }

        public function offsetExists( $offset ) {
            return isset($this->app->session->myclient[ $offset ] );
        }

        public function offsetUnset( $offset ) {

            $client = $this->app->session->myclient;

            unset( $client[$offset] );

            $this->app->session->myclient = $client;
        }

        public function logout(){
            return $this->app->session->destroy();
        }

    }
