<?php

    class myclient implements arrayaccess{

        /** @var mycontainer */
        private $app;

        public function __construct( $c ){
            $this->app = $c;
        }

        public function islogged(){
            return isset( $this->app->session['myclient'][ 'uuid' ] );
        }

        public function getAll(){
            return isset( $this->app->session['myclient'] ) && is_array( $this->app->session['myclient'] ) ? $this->app->session['myclient'] : array();
        }

        public function ipaddress(){

            if (isset($_SERVER)) {
                if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
                    return $_SERVER["HTTP_X_FORWARDED_FOR"];

                if (isset($_SERVER["HTTP_CLIENT_IP"]))
                    return $_SERVER["HTTP_CLIENT_IP"];

                if (isset($_SERVER["REMOTE_ADDR"]))
                    return $_SERVER["REMOTE_ADDR"];

            }elseif (getenv('HTTP_X_FORWARDED_FOR')) {
                return getenv('HTTP_X_FORWARDED_FOR');

            }elseif (getenv('HTTP_CLIENT_IP')){
                return getenv('HTTP_CLIENT_IP');

            }elseif (getenv('REMOTE_ADDR')) {
                return getenv('REMOTE_ADDR');
            }

            return null;
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
            return $this->offsetExists( $setting ) ? $this->app->session['myclient'][ $setting ] : null;
        }

        public function offsetSet( $offset, $value ) {

            $client = $this->app->session['myclient'];

            $client[$offset] = $value;

            $this->app->session['myclient'] = $client;
        }

        public function offsetExists( $offset ) {
            return is_string( $offset ) && !empty( $offset ) && isset( $_SESSION ) && isset( $this->app->session['myclient'] ) && isset($this->app->session['myclient'][ $offset ] );
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
