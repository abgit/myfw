<?php 

    class mysession{

        private $sessionactive = false;
        private $memcached     = null;

        public function __construct(){
            $this->app    = \Slim\Slim::getInstance();

            if( ! $this->sessionactive )
                $this->sessionactive = ( session_id() !== '' ) || ( ( APP_WEBMODE && ( function_exists( 'session_status' ) ? session_status() != PHP_SESSION_ACTIVE : session_id() === "" ) ) ? ( session_start() ) : false );
        }

        public function isActive(){
            return $this->sessionactive;
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

        public function deleteAll(){
            $_SESSION = array();
            return is_array( $_SESSION ) && empty( $_SESSION );
        }

        public function getHash( $sid = null ){
            return is_null( $sid ) ? $this->get( array( 'session', 'hash' ), null ) : md5( $sid . $_SERVER["REMOTE_ADDR"] . $_SERVER["HTTP_USER_AGENT"] );
        }

        public function setHash( $sid ){
            return $this->set( array( 'session', 'hash' ), md5( $sid . $_SERVER["REMOTE_ADDR"] . $_SERVER["HTTP_USER_AGENT"] ) );
        }
        
        public function memSet( $key, $value, $expiration = 0 ){
            if( !$this->memInit() )
                return false;
            return $this->memcached->set( $key, $value, $expiration );
        }

        public function memGet( $key, $default = false ){
            if( !$this->memInit() )
                return false;
            $res = $this->memcached->get( $key );
            return ( $res === false ) ? $default : $res;
        }

        public function & memDelete( $key ){
            if( !$this->memInit() )
                return false;
            $this->memcached->delete( $key );
            return $this;
        }
        
        private function memInit(){

            if( !is_null( $this->memcached ) )
                return true;
        
            if( !class_exists( 'Memcached' ) )
                return false;

            $this->memcached = new \Memcached();

            foreach( explode( ',', ini_get( 'session.save_path' ) ) as $server ){
                list( $host, $port ) = explode( ':', $server );
                $this->memcached->addServer( $host, intval( $port ) );
            }

            return true;
        }
    }
