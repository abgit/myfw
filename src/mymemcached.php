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


        public function getFunction( $key, $timeout, $function ){

            $res = $this->get( $key );
            if( $this->getResultCode() !== Memcached::RES_SUCCESS ){
                $res = $function();
                $this->set( $key, $res, $timeout );
            }

            return $res;
        }

        private function rateprefix( $persession, $perip, $perprefix = '' ){

            $prefix  = $perprefix;
            $prefix .= $persession ? ( 's' . $this->app->session->id() ) : '';
            $prefix .= $perip      ? ( 'i' . $this->app->ipaddress )  : '';

            return $prefix;
        }


        private function ratecounter( $now, $prefix, $seconds ){

            $counter = 0;

            for( $i = 0; $i < $seconds; $i++ ){
                $keysecond = md5( $prefix . date( "YmdHis", $now - $i ) );

                $counter += intval( $this->get( $keysecond ) );
            }

            return $counter;
        }


        public function rateisvalid( $persecond = 10, $per5second = null, $perminute = null, $lockfor = 60, $persession = true, $perip = false, $perprefix = '', $mono = true ){

            $now    = time();
            $prefix = $this->rateprefix( $persession, $perip, $perprefix );

            $keylock      = md5( $prefix . 'myfwlock' );
            $keymono      = md5( $prefix . 'myfwmono' );

            if( $this->get( $keylock ) === true )
                return false;

            if( $mono ){
                while( !$this->add( $keymono, 1, intval( ini_get('max_execution_time') ) + 1 ) ){
                    usleep( 30000 );
                }
            }

            // check limits
            if( ( is_numeric( $persecond )  && $this->ratecounter( $now, $prefix, 1 )  >= $persecond )  ||
                ( is_numeric( $per5second ) && $this->ratecounter( $now, $prefix, 5 )  >= $per5second ) ||
                ( is_numeric( $perminute )  && $this->ratecounter( $now, $prefix, 60 ) >= $perminute ) ){

                $this->set( $keylock, true, $lockfor );
                $this->set( $keylock . 't', time() + $lockfor, $lockfor );
                return false;
            }

            $keysecond   = md5( $prefix . date( "YmdHis", $now ) );
            $countersec  = intval( $this->get( $keysecond ) );

            if( $countersec === 0 ){
                $this->set( $keysecond, 1, 61 );
            }else{
                $this->increment( $keysecond );
            }

            return true;
        }

        public function ratemonodelete( $persession = true, $perip = false, $perprefix = '' ){

            $prefix  = $this->rateprefix( $persession, $perip, $perprefix );
            $keymono = md5( $prefix . 'myfwmono' );

            return $this->delete( $keymono );
        }
        
        public function ratelocktimeout( $persession = true, $perip = false, $perprefix = '' ){

            $prefix  = $this->rateprefix( $persession, $perip, $perprefix );
            $keylock = md5( $prefix . 'myfwlock' );

            $t = $this->get( $keylock . 't' );

            return ( $this->getResultCode() !== Memcached::RES_SUCCESS ) ? 0 : ( $t - time() );
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
