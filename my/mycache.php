<?php

    class mycache{

        public function __construct(){
            $this->app = \Slim\Slim::getInstance();
        }

        // standard
        public function exists( $mode, $id ){
            return ( intval( $mode ) == APP_CACHEAPC ) ? $this->apcexists( $id ) : $this->redisexists( $id );
        }

        public function set( $mode, $id, $content, $ttl = false ){
            return ( intval( $mode ) == APP_CACHEAPC ) ? $this->apcset( $id, $content, $ttl ) : $this->redisset( $id, $content, $ttl );
        }

        public function get( $mode, $id ){
            return ( intval( $mode ) == APP_CACHEAPC ) ? $this->apcget( $id ) : $this->redisget( $id );
        }

        public function delete( $mode, $k ){
            return ( intval( $mode ) == APP_CACHEAPC ) ? $this->apcdelete( $k ) : $this->redisdelete( $k );
        }

        public function & settimeout( $mode, $k, $ttl ){
            ( intval( $mode ) == APP_CACHEAPC ) ? $this->apcsettimeout( $k, $ttl ) : $this->redissettimeout( $k, $ttl );
            return $this;
        }


        // apc
        public function apcexists( $id ){
            return function_exists( 'apc_exists' ) ? apc_exists( $id ) : false;
        }

        public function apcset( $id, $content, $ttl = false ){
            return function_exists( 'apc_store' ) ? apc_store( $id, $content, $ttl || $this->app->config( 'apc.ttl' ) ) : false;
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


        // redis
        public function redisexists( $id ){
            return $this->redisroinit() ? $this->redisro->exists( $id ) : false;
        }

        public function redisset( $id, $content, $ttl = false ){
            return $this->redisrwinit() ? ($this->redisrw->setex( $id, $ttl || $this->app->config( 'redis.ttl' ), $content )) : false;
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
                $this->redisro->connect( $this->app->config( 'redis.hostro' ), $this->app->config( 'redis.hostroport' ) || 6379 );
            }
            return ( !is_null( $this->redisro ) && $this->redisro->IsConnected() );
        }

        private function redisrwinit(){
            if( is_null( $this->redisrw ) && class_exists( 'Redis' ) ){
                $this->redisrw = new Redis();
                $this->redisrw->connect( $this->app->config( 'redis.hostrw' ), $this->app->config( 'redis.hostrwport' ) || 6379 );
            }
            return ( !is_null( $this->redisrw ) && $this->redisrw->IsConnected() );
        }

    }
