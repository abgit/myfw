<?php

use \malkusch\lock\mutex\PHPRedisMutex;


    class myredis extends Redis{

        /** @var mycontainer*/
        private $app;
        private $mq = array();
        private $mqlater = array();
        private $later = null;

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

        public function listReset( $list ){
            return $this->mqReset( $list, false );
        }

        public function listPush( string $list, string $msg, $timeout = null ){

            $hash = md5( $list . $msg );

            if( is_null( $timeout ) )
                $this->set( $hash, $msg );
            else
                $this->setex( $hash, $timeout, $msg );

            return $this->lPush( $list, json_encode( array( 'h' => $hash ) ) );
        }

        // returns: true - message processed; false - could not process message; null - empty list
        public function listPop( $list, $function ){

            // check if this process should pause (resetting working)
            while( $this->exists( $list . 'sleep' ) ){
                sleep( 1 );
            }

            while( $value = $this->rpoplpush( $list, $list . 'processed' ) ) {

                $message = json_decode( $value, true );

                //if( isset( $message['h'] ) ){

                    $hash = $message['h'];
                    $msg  = $this->get( $hash );

                    // if message is not expired
                    if( is_string( $msg ) ){

                        $result = $function( $msg );

                        // delete from mqprocessed if processed sucessfuly
                        if( $result === true ){
                            $this->lRem( $list . 'processed', $value );
                            $this->del( $hash );
                            return true;
                        }

                        return false;

                    }elseif( $msg === false ){
                        $this->lRem( $list . 'processed', $value );
                    }
                //}
            }

            return null;
        }


        public function mqReset( $queue = 'mq', $wait = 21 ){

            // pause all consumers with a maximum time of 60
            if( $wait )
                $this->setex( $queue . 'sleep', 60, 'sleep' );

            // check if mqprocessed queue has elements
            if( $this->lLen( $queue . 'processed' ) ) {

                // wait some seconds, so that all consumers have stopped
                if( $wait )
                    sleep( $wait );

                // move elements to mq list
                while ($this->rpoplpush( $queue . 'processed', $queue ) ){
                }
            }

            if( $wait )
                $this->del( $queue . 'sleep' );
        }

        public function mqRegister( $queue, $function ){

            $this->mq[ 'mq' . $queue ] = $function;
        }

        public function mqPush( string $queue, string $msg, $ttl = null ){

            $hash = md5( 'mq' . $queue . $msg . intval( $ttl ) . uniqid() );

            if( is_null( $ttl ) )
                $this->set( $hash, $msg );
            else
                $this->setex( $hash, $ttl, $msg );

            return $this->lPush( 'mq', json_encode( array( 'q' => 'mq' . $queue, 'h' => $hash ) ) );
        }

        private function & later():\Islambey\RSMQ\RSMQ{
            if( is_null( $this->later ) ) {
                $this->later = new \Islambey\RSMQ\RSMQ($this);

                if( !in_array( 'mqlater', $this->later->listQueues() ) )
                    $this->later->createQueue( 'mqlater' );
            }
            return $this->later;
        }

        public function mqPushLater( string $queue, string $msg, int $delay, $ttl = null ){

            $hash = md5( 'mq' . $queue . $msg . intval( $delay ) . intval( $ttl ) . uniqid() );

            if( is_null( $ttl ) )
                $this->set( $hash, $msg );
            else
                $this->setex( $hash, $delay + $ttl, $msg );

            return $this->later()->sendMessage( 'mqlater', json_encode( array( 'q' => 'mq' . $queue, 'h' => $hash ) ), [ 'delay' => $delay ] );
        }

        public function mqProcess(){
            if( !empty( $this->mq ) ){
                while( true ) {

                    // check if this process should pause
                    while( $this->exists( 'mqsleep' ) ){
                        sleep( 1 );
                    }

                    // add all delayed messages available to mq. msg hash already added
                    while( ( $msg = $this->later()->popMessage( 'mqlater' ) ) && isset( $msg['message'] ) ){
                        $this->lPush( 'mq', $msg['message'] );
                    }

                    // process delayed message
/*                    $msg = $this->later()->receiveMessage( 'mqlater' );

                    if( isset( $msg['message'] ) && isset( $msg['id'] ) && $this->lPush( 'mq', $msg['message'] ) ) {
                        //$hash = $this->processMessage($msg['message']);
                        //$this->lPush( 'mq', $msg['message'] );

                        //if( is_string( $hash ) && !empty( $hash ) ) {
                        $this->later()->deleteMessage( 'mqlater', $msg['id'] );
                        //    $this->del( $hash );
                        //}elseif( is_null( $hash ) ){
                        //    $this->later()->deleteMessage( 'mqlater', $msg['id'] );
                        //}
                    }*/

                    // process real-time message
                    $value = $this->brpoplpush( 'mq', 'mqprocessed', 10 );
                    $hash  = $this->processMessage( $value );

                    if( is_string( $hash ) && !empty( $hash ) ) {
                        $this->lRem( 'mqprocessed', $value );
                        $this->del( $hash );
                    }elseif( is_null( $hash ) ){
                        $this->lRem( 'mqprocessed', $value );
                    }

                }
            }
        }

        private function processMessage( $value ){

            $message = json_decode( $value, true );

            if( isset( $message['q'] ) && isset( $message['h'] ) ){

                $queue = $message['q'];
                $hash  = $message['h'];
                $msg   = $this->get( $hash );

                // if message not found (expired)
                if( !is_string( $msg ) )
                    return null;

                // if message exists (not expired) and returns successfuly
                if( is_string( $msg ) && $this->mq[$queue]($msg) === true )
                    return $hash;
            }

            return false;
        }

    }
