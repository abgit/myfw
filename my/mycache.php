<?php

    class mycache{

        private $redisrw   = null;
        private $redisro   = null;
        private $memcached = null;

        public function __construct(){
            $this->app = \Slim\Slim::getInstance();

            if( $this->app->config( 'redis.driver' ) === 'heroku' ){
                $this->redisro      = parse_url( getenv( 'REDISCLOUD_URL' ), PHP_URL_HOST );
                $this->redisroport  = parse_url( getenv( 'REDISCLOUD_URL' ), PHP_URL_PORT );
                $this->redisropass  = parse_url( getenv( 'REDISCLOUD_URL' ), PHP_URL_PASS );
                $this->redisrw      = $this->redisro;
                $this->redisrwport  = $this->redisroport;
                $this->redisrwpass  = $this->redisropass;
                $this->memcachedusr = getenv( 'MEMCACHIER_USERNAME' );
                $this->memcachedpwd = getenv( 'MEMCACHIER_PASSWORD' );
                $this->memcachedsrv = getenv( 'MEMCACHIER_SERVERS' );
            }else{
                $this->redisro      = $this->app->config( 'redis.hostro' );
                $this->redisroport  = $this->app->config( 'redis.hostroport' );
                $this->redisropass  = $this->app->config( 'redis.hostropass' );
                $this->redisrw      = $this->app->config( 'redis.hostrw' );
                $this->redisrwport  = $this->app->config( 'redis.hostrwport' );
                $this->redisrwpass  = $this->app->config( 'redis.hostrwpass' );
                $this->memcachedusr = $this->app->config( 'memcached.username' );
                $this->memcachedpwd = $this->app->config( 'memcached.password' );
                $this->memcachedsrv = $this->app->config( 'memcached.servers' );
            }
            
            $this->apcttl       = $this->app->config( 'apc.ttl' )       === false ? 900 : $this->app->config( 'apc.ttl' );
            $this->redisttl     = $this->app->config( 'redis.ttl' )     === false ? 900 : $this->app->config( 'redis.ttl' );
            $this->memcachedttl = $this->app->config( 'memcached.ttl' ) === false ? 900 : $this->app->config( 'memcached.ttl' );
        }

        // standard
        public function exists( $mode, $id ){
            return ( intval( $mode ) == 0 ) ? $this->apcexists( $id ) : $this->redisexists( $id );
        }

        public function set( $mode, $id, $content, $ttl = false ){
            return ( intval( $mode ) == 0 ) ? $this->apcset( $id, $content, $ttl ) : $this->redisset( $id, $content, $ttl );
        }

        public function get( $mode, $id ){
            return ( intval( $mode ) == 0 ) ? $this->apcget( $id ) : $this->redisget( $id );
        }

        public function delete( $mode, $k ){
            return ( intval( $mode ) == 0 ) ? $this->apcdelete( $k ) : $this->redisdelete( $k );
        }

        public function & settimeout( $mode, $k, $ttl ){
            ( intval( $mode ) == 0 ) ? $this->apcsettimeout( $k, $ttl ) : $this->redissettimeout( $k, $ttl );
            return $this;
        }


        // apc
        public function apcexists( $id ){
            return function_exists( 'apc_exists' ) ? apc_exists( $id ) : false;
        }

        public function apcset( $id, $content, $ttl = false ){
            if( ! $ttl ){
                $ttl = $this->apcttl;
            }

            return function_exists( 'apc_store' ) ? apc_store( $id, $content, intval( $ttl ) ) : false;
        }

        public function apcget( $id ){
            return function_exists( 'apc_fetch' ) ? apc_fetch( $id ) : null;
        }

        public function apcdelete( $k ){
            return apc_delete( $k );
        }

        public function & apcsettimeout( $k, $ttl ){
            apc_exists( $k ) ? apc_store( $k, apc_get( $k ), $ttl ) : false;
            return $this;
        }


        // memcached
        public function memcachedexists( $id ){
            return $this->memcachedinit() ? ( $this->memcached->get( $id ) === false ) : false;
        }

        public function memcachedset( $id, $content, $ttl = false ){
            if( ! $ttl ){
                $ttl = $this->memcachedttl;
            }
            
            return $this->memcachedinit() ? $this->memcached->set( $id, $content, intval( $ttl ) ) : false;
        }

        public function memcachedget( $id ){
            return $this->memcachedinit() ? $this->memcached->get( $id ) : false;
        }

        public function memcacheddelete( $k ){
            return $this->memcachedinit() ? $this->memcached->delete( $k ) : false;
        }

        public function memcachedinit(){
            if( !class_exists( 'Memcached' ) )
                return false;

            if( is_null( $this->memcached ) ){

                $this->memcached = new Memcached( 'memcached_pool' );
                $this->memcached->setOption( Memcached::OPT_BINARY_PROTOCOL, TRUE );
                $this->memcached->setOption( Memcached::OPT_NO_BLOCK, TRUE );
                $this->memcached->setOption( Memcached::OPT_AUTO_EJECT_HOSTS, TRUE );
                $this->memcached->setOption( Memcached::OPT_CONNECT_TIMEOUT, 2000 );
                $this->memcached->setOption( Memcached::OPT_POLL_TIMEOUT, 2000 );
                $this->memcached->setOption( Memcached::OPT_RETRY_TIMEOUT, 2 );

                $this->memcached->setSaslAuthData( $this->memcachedusr, $this->memcachedpwd );

                if( !$this->memcached->getServerList() ){

                    $servers = explode( ',', $this->memcachedsrv );
                    foreach( $servers as $s ){
                        $parts = explode( ':', $s );
                        $this->memcached->addServer( $parts[0], $parts[1] );
                    }
                }
                return true;
            }
            return true;
        }


        // redis
        public function redisexists( $id ){
            return $this->redisroinit() ? $this->redisro->exists( $id ) : false;
        }

        public function redisset( $id, $content, $ttl = false ){
            if( ! $ttl ){
                $ttl = $this->redisttl;
            }

            return $this->redisrwinit() ? $this->redisrw->set( $id, $content, intval( $ttl ) ) : false;
        }

        public function redisget( $id ){
            return $this->redisroinit() ? $this->redisro->get( $id ) : null;
        }

        public function redisdelete( $k ){
            return $this->redisrwinit() ? $this->redisrw->delete( $k ) : null;
        }

        public function & redissettimeout( $k, $ttl ){
            $this->redisrwinit() ? $this->redisrw->setTimeout( $k, $ttl ) : null;
            return $this;
        }

        private function redisroinit(){
            if( is_null( $this->redisro ) && class_exists( 'Redis' ) ){
                $this->redisro = new Redis();
                $this->redisro->connect( $this->redisro, $this->redisroport );
            
                if( $this->redisropass ){
                    $this->redisro->auth( $this->redisropass );
                }
            }
            return ( !is_null( $this->redisro ) && $this->redisro->IsConnected() );
        }

        private function redisrwinit(){
            if( is_null( $this->redisrw ) && class_exists( 'Redis' ) ){
                $this->redisrw = new Redis();
                $this->redisrw->connect( $this->redisrw, $this->redisrwport );

                if( $this->redisrwpass ){
                    $this->redisrw->auth( $this->redisrwpass );
                }
            }
            return ( !is_null( $this->redisrw ) && $this->redisrw->IsConnected() );
        }

    }
