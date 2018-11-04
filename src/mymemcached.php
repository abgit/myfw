<?php

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;


    // dev environment support
    if( !class_exists( 'Memcached' ) ){
        include_once __DIR__ . '/mymemcached_.php';
        class_alias( 'Memcached_', 'Mem' . 'cached' );
    }

    class mymemcached extends Memcached{

        /** @var mycontainer*/
        private $app;

        private $userprefix = false;
        private $pagettl = 20;

        public function __construct( $c ){

            parent::__construct( APP_NAME );

            $this->app = $c;

            $servers = explode(',', $this->app->config['memcached.servers'] );

            $this->setOptions( $this->app->config[ 'memcached.options' ] );

            if( !$this->getServerList() ){
                foreach( $servers as $s ){
                    $parts = explode( ':', $s );
                    if( isset( $parts[0] ) && isset( $parts[1] ) )
                        $this->addServer( $parts[0], $parts[1] );
                }
            }
        }


        private function rateprefix( $persession, $perip ){

            $prefix  = $persession ? ( 's' . $this->app->session->id() ) : '';
            $prefix .= $perip      ? ( 'i' . $this->app->ipaddress )  : '';

            return $prefix;
        }


        public function rateisvalid( $persecond = 3, $perminute = 200, $lockfor = 60, $persession = true, $perip = false, $mono = true ){

            $now = time();
            $prefix = $this->rateprefix( $persession, $perip );

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

            $prefix  = $this->rateprefix( $persession, $perip );
            $keymono = md5( $prefix . 'myfwmono' );

            return $this->delete( $keymono );
        }
        
        public function ratelocktimeout( $persession = true, $perip = false ){

            $prefix  = $this->rateprefix( $persession, $perip );
            $keylock = md5( $prefix . 'myfwlock' );

            $now = time();
            $t = $this->get( $keylock . 't' );

            return ( $this->getResultCode() !== Memcached::RES_SUCCESS ) ? 0 : ( $t - $now );
        }


        public function & setPageCache( $userprefix ){
            $this->userprefix = $userprefix;
            return $this;
        }


        public function __invoke(Request $request, Response $response, callable $next){

            $key = md5( ( $this->userprefix ? $this->app->config[ 'memcached.userprefix' ] : '' ) . '||' . $request->getMethod() . '||' . $request->getUri()->getPath() . '||' . $request->getBody()->getContents() );

            $cache = $this->getMulti( array( $key . '_h', $key . '_b' ) );

            if( $this->getResultCode() !== Memcached::RES_SUCCESS || !isset( $cache[ $key . '_h' ] ) || !isset( $cache[ $key . '_b' ] ) ) {

                /** @var Response $response */
                $response = $next($request, $response);

                if ($response->getStatusCode() == 200) {
                    $this->setMulti( array( $key . '_h' => array_map( function($x){return implode(',', $x);}, $response->getHeaders() ),
                                            $key . '_b' => $response->getBody()->__toString() ),
                                     $this->pagettl );
                }

            }else{

                foreach( $cache[ $key . '_h' ] as $k => $v ){
                    $response = $response->withHeader( $k, $v );
                }

                $response->getBody()->write( $cache[ $key . '_b' ] );
            }

            return $response;
        }

    }
