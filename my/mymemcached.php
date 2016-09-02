<?php

    class mymemcached extends Memcached{

        private $app;

        public function __construct(){

            $this->app = \Slim\Slim::getInstance();

            parent::__construct( 'memcached_pool' );

            if( $this->app->config( 'memcached.driver' ) === 'memcachier' ){

                $this->setOption( Memcached::OPT_BINARY_PROTOCOL, TRUE );
                $this->setOption( Memcached::OPT_NO_BLOCK, TRUE );
                $this->setOption( Memcached::OPT_AUTO_EJECT_HOSTS, TRUE );
                $this->setOption( Memcached::OPT_CONNECT_TIMEOUT, 2000 );
                $this->setOption( Memcached::OPT_POLL_TIMEOUT, 2000 );
                $this->setOption( Memcached::OPT_RETRY_TIMEOUT, 2 );

                $this->setSaslAuthData( $this->app->config( '@MEMCACHIER_USERNAME' ), $this->app->config( '@MEMCACHIER_PASSWORD' ) );

                if( !$this->getServerList() ){

                    $servers = explode( ',', $this->app->config( '@MEMCACHIER_SERVERS' ) );
                    foreach( $servers as $s ){
                        $parts = explode( ':', $s );
                        $this->addServer( $parts[0], $parts[1] );
                    }
                }

            }elseif( $this->app->config( 'memcached.driver' ) === 'fortrabbit' ){

                if( !$this->getServerList() ){

                    $servers = explode( ',', ini_get( 'session.save_path' ) );
                    foreach( $servers as $s ){
                        $parts = explode( ':', $s );
                        $this->addServer( $parts[0], $parts[1] );
                    }

                }
            }
        }


        public function ratevalid( $persecond = 3, $perminute = 200, $lockfor = 60, $persession = true, $perip = false, $mono = true ){

            $now = time();
            $prefix  = $persession ? ( 's' . $this->app->session()->getId() ) : '';
            $prefix .= $perip      ? ( 'i' . $this->app->request->getIp() )  : '';

            $keysecond = md5( $prefix . date( "YmdHis", $now ) );
            $keyminute = md5( $prefix . date( "YmdHi", $now ) );
            $keylock   = md5( $prefix . 'myfwlock' );
            $keymono   = md5( $prefix . 'myfwmono' );

            if( $this->get( $keylock ) === true )
                return false;

            if( $mono && !$this->add( $keymono, 1,20 ) )
                usleep( 100000 );

            $countersec = $this->get( $keysecond );
            if( $this->getResultCode() !== Memcached::RES_SUCCESS )
                $countersec = 0;

            $countermin = $this->get( $keyminute );
            if( $this->getResultCode() !== Memcached::RES_SUCCESS )
                $countermin = 0;

            // check limits
            if( $countersec >= $persecond || $countermin >= $perminute ){
                $this->delete( $keysecond );
                $this->delete( $keyminute );
                $this->set( $keylock, true, $lockfor );
                $this->set( $keylock . 't', time() + $lockfor, $lockfor );
                return false;
            }

            if( $countersec === 0 ){
                $this->set( $keysecond, 1, 3 );
            }else{
                $this->increment( $keysecond );
            }

            if( $countermin === 0 ){
                $this->set( $keyminute, 1, 63 );
            }else{
                $this->increment( $keyminute );
            }

            return true;
        }

        public function ratemonodelete( $persession = true, $perip = false ){

            $prefix  = $persession ? ( 's' . $this->app->session()->getId() ) : '';
            $prefix .= $perip      ? ( 'i' . $this->app->request->getIp() )  : '';

            $keymono = md5( $prefix . 'myfwmono' );

            return $this->delete( $keymono );
        }
        
        public function ratelocktimeout( $persession = true, $perip = false ){

            $prefix  = $persession ? ( 's' . $this->app->session()->getId() ) : '';
            $prefix .= $perip      ? ( 'i' . $this->app->request->getIp() )  : '';

            $keylock = md5( $prefix . 'myfwlock' );

            $now = time();
            $t = $this->get( $keylock . 't' );

            return ( $this->getResultCode() !== Memcached::RES_SUCCESS ) ? 0 : ( $t - $now );
        }
    }
