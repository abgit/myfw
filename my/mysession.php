<?php 

    class mysession{

        private $sessionactive = false;

        public function __construct(){
            $this->app    = \Slim\Slim::getInstance();
            $this->ttl    = $this->app->config( 'session.ttl' ) || ini_get( 'session.gc_maxlifetime' ) || 360;
            $this->mode   = $this->app->config( 'session.mode' ) || 9;
            $this->prefix = 'PHPSESSID:';

            if( ! $this->sessionactive ){

                // start custom session handler
                if( ( $mode === APP_CACHEAPC && function_exists( 'apc_exists' ) ) || ( $mode === APP_CACHEREDIS && class_exists( 'Redis' ) ) ){

                    if( defined( 'PHP_VERSION_ID' ) && PHP_VERSION_ID > 50399 ){
                        session_set_save_handler($this);
                    }else{
                        session_set_save_handler( array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'), array($this, 'destroy'), array($this, 'gc') );
                    }
                }

                $this->sessionactive = ( session_id() !== '' ) || ( ( APP_WEBMODE && ( function_exists( 'session_status' ) ? session_status() != PHP_SESSION_ACTIVE : session_id() === "" ) ) ? ( session_start() && session_regenerate_id() ) : false );
            }
        }

        public function exists( $key ){
            if( ! $this->sessionactive )
                return false;

            if( is_string( $key ) ){
                return isset( $_SESSION[ $key ] );
            }

            if( is_array( $key ) ){
                switch( count( $key ) ){
                    case 1: return isset( $_SESSION[ $key[0] ] );
                    case 2: return isset( $_SESSION[ $key[0] ][ $key[1] ] );
                    case 3: return isset( $_SESSION[ $key[0] ][ $key[1] ][ $key[2] ] );
                    case 4: return isset( $_SESSION[ $key[0] ][ $key[1] ][ $key[2] ][ $key[3] ] );
                }		
            }

            return false;
        }

        public function set( $key, $value ){
            if( ! $this->sessionactive )
                return false;

            if( is_string( $key ) ){
                $_SESSION[ $key ] = $value;
                return isset( $_SESSION[ $key ] );
            }

            if( is_array( $key ) ){
                switch( count( $key ) ){
                    case 1: $_SESSION[ $key[0] ] = $value; return isset( $_SESSION[ $key[0] ] );
                    case 2: $_SESSION[ $key[0] ][ $key[1] ] = $value; return isset( $_SESSION[ $key[0] ][ $key[1] ] );
                    case 3: $_SESSION[ $key[0] ][ $key[1] ][ $key[2] ] = $value; return isset( $_SESSION[ $key[0] ][ $key[1] ][ $key[2] ] );
                    case 4: $_SESSION[ $key[0] ][ $key[1] ][ $key[2] ][ $key[3] ] = $value; return isset( $_SESSION[ $key[0] ][ $key[1] ][ $key[2] ][ $key[3] ] );
                }		
            }

            return false;
        }

        public function setcheck( $key, $value ){
            return $this->exists( $key ) ? false : $this->set( $key, $value );
        }

        public function get( $key, $default = false ){
            if( ! isset( $this->sessionactive ) )
                return $default;

            if( is_string( $key ) ){
                return isset( $_SESSION[ $key ] ) ? $_SESSION[ $key ] : $default;
            }

            if( is_array( $key ) ){
                switch( count( $key ) ){
                    case 1: return isset( $_SESSION[ $key[0] ] ) ? $_SESSION[ $key[0] ] : $default;
                    case 2: return isset( $_SESSION[ $key[0] ][ $key[1] ] ) ? $_SESSION[ $key[0] ][ $key[1] ] : $default;
                    case 3: return isset( $_SESSION[ $key[0] ][ $key[1] ][ $key[2] ] ) ? $_SESSION[ $key[0] ][ $key[1] ][ $key[2] ] : $default;
                    case 4: return isset( $_SESSION[ $key[0] ][ $key[1] ][ $key[2] ][ $key[3] ] ) ? $_SESSION[ $key[0] ][ $key[1] ][ $key[2] ][ $key[3] ] : $default;
                }
            }

            return $default;
        }

        public function delete( $key ){
            if( ! $this->sessionactive )
                return false;

            if( is_string( $key ) ){
                unset( $_SESSION[ $key ] );
                return !isset( $_SESSION[ $key ] );
            }

            if( is_array( $key ) ){
                switch( count( $key ) ){
                    case 1: unset( $_SESSION[ $key[0] ] ); return !isset( $_SESSION[ $key[0] ] );
                    case 2: unset( $_SESSION[ $key[0] ][ $key[1] ] ); return !isset( $_SESSION[ $key[0] ][ $key[1] ] );
                    case 3: unset( $_SESSION[ $key[0] ][ $key[1] ][ $key[2] ] ); return !isset( $_SESSION[ $key[0] ][ $key[1] ][ $key[2] ] );
                    case 4: unset( $_SESSION[ $key[0] ][ $key[1] ][ $key[2] ][ $key[3] ] ); return !isset( $_SESSION[ $key[0] ][ $key[1] ][ $key[2] ][ $key[3] ] );
                }
            }

            return false;
        }


        // php session interface methods
        public function read( $id ){
            return $this->app->cache()->settimeout( $this->mode, $this->prefix . $id, $this->ttl )->get( $this->mode, $this->prefix . $id );
        }

        public function write( $id, $data ){
            return $this->app->cache()->set( $this->mode, $this->prefix . $id, $data, $this->ttl );
        }

        public function destroy( $id ){
            return $this->app->cache()->delete( $this->mode, $this->prefix . $id );
        }

        public function open( $savePath, $sessionName ){
        }

        public function close(){
        }

        public function gc( $maxLifetime ){
        }

    }
