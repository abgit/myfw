<?php

use \malkusch\lock\mutex\PHPRedisMutex;


    class myredis extends Redis{

        /** @var mycontainer*/
        private $app;

        public function __construct( $c ){

            parent::__construct();

            $this->app = $c;

            if( $this->app->config[ 'redis.dsn' ] ){
                $this->redis_url  = parse_url( $this->app->config[ 'redis.dsn' ] );

                if( isset( $this->redis_url[ 'host' ] ) ){
                    $this->connect( $this->redis_url[ 'host' ], isset( $this->redis_url[ 'port' ] ) ? $this->redis_url[ 'port' ] : 6379 );

                    if( isset( $this->redis_url[ 'pass' ] ) ){
                        $this->auth( $this->redis_url[ 'pass' ] );
                    }
                }
            }
        }

        public function getFunction( $key, $timeout, $function ){

            $res = $this->get( $key );
            if( $res === false ){
                $res = $function();
                $this->setex( $key, $timeout, $res );
            }

            return $res;
        }

        private function rateprefix( $persession, $perip, $perprefix ){

            $prefix  = $perprefix;
            $prefix .= $persession ? ( 's' . $this->app->session->id() ) : '';
            $prefix .= $perip      ? ( 'i' . $this->app->ipaddress )  : '';

            return $prefix;
        }

        private function ratecounter( $now, $prefix, $seconds ){

            $counter = 0;

            for( $i = 0; $i < $seconds; $i++ ){
                $keysecond = md5( $prefix . date( "YmdHis", $now - $i ) );
                $counter  += intval( $this->get( $keysecond ) );
            }

            return $counter;
        }

        public function rateisvalid( $persecond = 10, $per5second = null, $perminute = null, $lockfor = 60, $persession = true, $perip = false, $perprefix = ''){

            $now    = time();
            $prefix = $this->rateprefix( $persession, $perip, $perprefix );

            $keylock = md5( $prefix . 'myfwlock' );
            $keymono = md5( $prefix . 'myfwmono' );

            if( $this->get( $keylock ) === true )
                return false;

            // check limits
            if( ( is_numeric( $persecond )  && $this->ratecounter( $now, $prefix, 1 )  >= $persecond )  ||
                ( is_numeric( $per5second ) && $this->ratecounter( $now, $prefix, 5 )  >= $per5second ) ||
                ( is_numeric( $perminute )  && $this->ratecounter( $now, $prefix, 60 ) >= $perminute ) ){

                $this->setex( $keylock,       $lockfor, true  );
                $this->setex( $keylock . 't', $lockfor, time() + $lockfor );
                return false;
            }

            $keysecond   = md5( $prefix . date( "YmdHis", $now ) );
            $countersec  = intval( $this->get( $keysecond ) );

            if( $countersec === 0 ){
                $this->setex( $keysecond, 61, 1 );
            }else{
                $this->incr( $keysecond );
            }

            return true;
        }

        public function ratelocktimeout( $persession = true, $perip = false, $perprefix = '' ){

            $prefix  = $this->rateprefix( $persession, $perip, $perprefix );
            $keylock = md5( $prefix . 'myfwlock' );

            $t = $this->get( $keylock . 't' );

            return ( $t === false ) ? 0 : ( $t - time() );
        }

        public function mutex( $id ):PHPRedisMutex{
            return ( new PHPRedisMutex( [ $this ], $id, $this->app->config[ 'redis.mutextimeout' ] ) );
        }

        public function mutexClient():PHPRedisMutex{
            return ( new PHPRedisMutex( [ $this ], $this->app->config[ 'redis.mutexclient' ], $this->app->config[ 'redis.mutextimeout' ] ) );
        }

        public function mutexSession():PHPRedisMutex{
            return ( new PHPRedisMutex( [ $this ], session_id(), $this->app->config[ 'redis.mutextimeout' ] ) );
        }

    }
