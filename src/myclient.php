<?php

    class myclient implements arrayaccess{

        /** @var mycontainer */
        private $app;

        public function __construct( $c ){
            $this->app = $c;
        }

        public function islogged(){
            return isset( $this->app->session['myclient'][ 'uuid'] );
        }

        public function getAll(){
            return isset( $this->app->session['myclient'] ) && is_array( $this->app->session['myclient'] ) ? $this->app->session['myclient'] : array();
        }

        public function ipaddress(){
            return $_SERVER['REMOTE_ADDR'];
        }

        public function countryCode(){
            return function_exists('geoip_country_code_by_name') ? geoip_country_code_by_name( $this->ipaddress() ) : null;
        }

        public function countryName(){
            return function_exists('geoip_country_name_by_name') ? geoip_country_name_by_name( $this->ipaddress() ) : null;
        }

        public function useragent(){
            return $_SERVER['HTTP_USER_AGENT'];
        }

        public function offsetGet( $setting ) {

            if( isset( $this->app->session['myclient'][ $setting ] ) )
                return $this->app->session['myclient'][ $setting ];

            return null;
        }

        public function offsetSet( $offset, $value ) {

            $client = $this->app->session['myclient'];

            $client[$offset] = $value;

            $this->app->session['myclient'] = $client;
        }

        public function offsetExists( $offset ) {
            return isset($this->app->session['myclient'][ $offset ] );
        }

        public function offsetUnset( $offset ) {

            $client = $this->app->session['myclient'];

            unset( $client[$offset] );

            $this->app->session['myclient'] = $client;
        }

        public function logout(){
            return $this->app->session->destroy();
        }

    }
