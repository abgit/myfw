<?php

use \malkusch\lock\mutex\PHPRedisMutex;
use \Islambey\RSMQ\RSMQ;
use Ahc\Cron\Expression;

    class myredis extends Redis{

        /** @var mycontainer*/
        private $app;
        private array $mq   = array();
        private array $cron = array();
        private RSMQ $later;
        private float $process_start;

        public function __construct( $c ){

            parent::__construct();

            $this->app = $c;

            $this->processStartNow();

            if( $this->app->config[ 'redis.dsn' ] ){

                /** @var array $redis_url */
                $redis_url = parse_url( $this->app->config[ 'redis.dsn' ] );

                if( isset( $redis_url[ 'host' ] ) ){
                    $this->connect( $redis_url[ 'host' ], $redis_url['port'] ?? 6379);

                    if( isset( $redis_url[ 'pass' ] ) ){
                        $this->auth( $redis_url[ 'pass' ] );
                    }
                }
            }
        }

        public function processStartNow(){
            $this->process_start = microtime( true );
        }

        public function processStart():float{
            return $this->process_start;
        }

        public function getFunction( string $key, int $timeout, callable $function, bool $useCache = true ): ?string{

            if( $useCache ) {
                $res = $this->get($key);
                if ($res === false) {
                    $res = $function();

                    if( !is_null($res) && $timeout > 0 ) {
                        $this->setex($key, $timeout, $res);
                    }
                }
            }else{
                $res = $function();

                if( !is_null($res) && $timeout > 0 ){
                    $this->setex($key, $timeout, $res);
                }
            }

            return $res;
        }

        // returns the next index, round robin
        public function getRoundRobin( string $name, int $indexes ):int{

            $value = $this->get( 'roundrobin' . $name );

            if( $value === false || (int) $value >= $indexes || (int) $value < 0 ){
                $value = 0;
                $this->set( 'roundrobin' . $name, 0 );
            }

            $this->incr( 'roundrobin' . $name );
            return (int) $value;
        }


        public function timeSchedule( int $now, int $step = 1, string $prefix = '' ):int{

            $seconds = 0;
            while( true ) {
                if( $now % $step === 0 && $this->setnx( 'cronschedule' . $prefix . $now, '' ) ){
                    $this->expire( 'cronschedule' . $prefix . $now, 86400 * 60 ); // 60 days
                    return $now;
                }
                $now++;
                $seconds++;

                // protect infinite loop
                if( $seconds > 86400 * 60 ){
                    throw new Exception( 'no schedule slot available' );
                }
            }
        }

        public function timeScheduleNow( int $now, int $step = 1, string $prefix = '' ):int{
            $time = time();
            if( $now < $time ){
                $now = $time;
            }

            return $this->timeSchedule( $now, $step, $prefix ) - $time;
        }

        private function rateprefix( $persession, $perip, $perprefix ): string{

            $prefix  = $perprefix;
            $prefix .= $persession ? ( 's' . $this->app->session::id() ) : '';
            $prefix .= $perip      ? ( 'i' . $this->app->ipaddress )  : '';

            return $prefix;
        }

        private function ratecounter( $now, $prefix, $seconds ): int{

            $keys = array();

            for( $i = 0; $i < $seconds; $i++ ){
                $keys[] = md5( $prefix . date( 'YmdHis', $now - $i ) );
            }

            return empty( $keys ) ? 0 : array_sum( array_map( 'intval', $this->mget(  $keys ) ) );
        }

        private function ratecounterMinutes( $now, $prefix, $minutes ): int{

            $keys = array();

            for( $i = 0; $i < $minutes; $i++ ){
                $keys[] = md5( $prefix . date( 'YmdHi', $now - ( $i * 60 ) ) );
            }

            return empty( $keys ) ? 0 : array_sum( array_map( 'intval', $this->mget(  $keys ) ) );
        }

        public function rateisvalid( $persecond = 10, $per5second = null, $perminute = null, $perhour = null, $lockfor = 60, $persession = true, $perip = false, $perprefix = ''): bool{

            $now    = time();
            $prefix = $this->rateprefix( $persession, $perip, $perprefix );

            $keylock = md5( $prefix . 'myfwlock' );

            if( $this->exists( $keylock . 't' ) ) {
                return false;
            }

            // check limits
            if( ( is_numeric( $persecond )  && $this->ratecounter( $now, $prefix, 1 )         >= $persecond )  ||
                ( is_numeric( $per5second ) && $this->ratecounter( $now, $prefix, 5 )         >= $per5second ) ||
                ( is_numeric( $perminute )  && $this->ratecounter( $now, $prefix, 60 )        >= $perminute )  ||
                ( is_numeric( $perhour )    && $this->ratecounterMinutes( $now, $prefix, 60 ) >= $perhour )
            ){
                $this->setex( $keylock . 't', $lockfor, time() + $lockfor );
                return false;
            }

            $keysecond  = md5( $prefix . date( 'YmdHis', $now ) );

            if( $this->exists( $keysecond ) ){
                $this->incr( $keysecond );
            }else{
                $this->setex( $keysecond, 61, '' );
            }

            $keyminute  = md5( $prefix . date( 'YmdHi', $now ) );

            if( $this->exists( $keyminute ) ){
                $this->incr( $keyminute );
            }else{
                $this->setex( $keyminute, 3601, '' );
            }

            return true;
        }

        public function ratelocktimeout( $persession = true, $perip = false, $perprefix = '' ): int{

            $prefix  = $this->rateprefix( $persession, $perip, $perprefix );
            $keylock = md5( $prefix . 'myfwlock' );

            $t = $this->get( $keylock . 't' );

            return ( $t === false ) ? 0 : ( (int)$t - time() );
        }


        public function requestGet( int $timeout, bool $useCache, string $url, array $headers = array(), array $parameters = array() ): object{

            $res = $this->getFunction( md5( 'requestGet|' . json_encode( array( $url, $headers, $parameters ) ) ), $timeout,
                   function() use ( $url, $headers, $parameters ){

                try {
                    $r = \Unirest\Request::get($url, $headers, $parameters);
                    return $r->code >= 200 && $r->code < 300 && !empty($r->raw_body) ? $r->raw_body : null;
                }catch (Exception $exception){
                    return null;
                }

                   }, $useCache );

            return (object) array( 'body' => json_decode( $res ) );
        }

        public function requestPost( int $timeout, bool $useCache, string $url, array $headers, $parameters ): object{

            $res = $this->getFunction( md5( 'requestPost|' . json_encode( array( $url, $headers, $parameters ) ) ), $timeout,
                function() use ( $url, $headers, $parameters ){

                    $r = \Unirest\Request::post( $url, $headers, $parameters );
                    return $r->code >= 200 && $r->code < 300 && !empty($r->raw_body) ? $r->raw_body : null;

                }, $useCache );

            return (object) array( 'body' => json_decode( $res ) );
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

        public function listReset( $list ): void{
            $this->mqReset( $list, false );
        }

        public function listPush( string $list, string $msg, $timeout = null ){

            $hash = md5( $list . $msg );

            if($timeout === null) {
                $this->set($hash, $msg);
            }
            else {
                $this->setex($hash, $timeout, $msg);
            }

            return $this->lPush( $list, json_encode(array('h' => $hash), JSON_THROW_ON_ERROR, 512));
        }

        // returns: true - message processed; false - could not process message; null - empty list
        public function listPop( $list, $function ){

            // check if this process should pause (resetting working)
            while( $this->exists( $list . 'sleep' ) ){
                sleep( 1 );
            }

            while( $value = $this->rpoplpush( $list, $list . 'processed' ) ) {

                $message = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

                $hash = $message['h'];
                $msg  = $this->get( $hash );

                // if message is not expired
                if (is_string( $msg )) {

                    $result = $function( $msg );

                    // delete from mqprocessed if processed sucessfuly
                    if( $result === true ){
                        $this->lRem( $list . 'processed', $value, 0 );
                        $this->del( $hash );
                        return true;
                    }

                    return false;
                }

                if($msg === false) {
                    $this->lRem( $list . 'processed', $value, 0 );
                }
            }

            return null;
        }

        private function doNothing():void{
        }

        public function mqReset( $queue = 'mq', $wait = 21 ): void{

            // pause all consumers with a maximum time of 60
            if( $wait ) {
                $this->setex($queue . 'sleep', 60, 'sleep');
            }

            // check if mqprocessed queue has elements
            if( $this->lLen( $queue . 'processed' ) ) {

                // wait some seconds, so that all consumers have stopped
                if( $wait ){
                    sleep( $wait );
                }

                // move elements to mq list
                while ($this->rpoplpush( $queue . 'processed', $queue ) ){
                    $this->doNothing();
                }
            }

            if( $wait ) {
                $this->del($queue . 'sleep');
            }
        }

        public function & mqRegister( $queue, $function ): myredis{
            $this->mq[ 'mq' . $queue ] = $function;
            return $this;
        }

        public function & mqCron( string $name, string $expression, $function ): myredis{
            $this->cron[ $name ] = array( 'name' => $name, 'expression' => $expression, 'function' => $function, 'run' => '' );
            return $this;
        }

        public function mqPushAction( string $obj, string $method, array $msg, int $ttl = null, $forceRemove = false ){
            return $this->mqPush( 'mqpushaction', array( 'obj' => $obj, 'method' => $method, 'msg' => $msg ), $ttl, $forceRemove );
        }

        public function mqPush( string $queue, array $msgobj, $ttl = null, $forceRemove = false ){

            $msg = json_encode( $msgobj, JSON_THROW_ON_ERROR );

            $hash = md5( 'mq' . $queue . $msg . (int)$ttl . uniqid('', true) . mt_rand() . microtime() );

            if($ttl === null) {
                $this->set($hash, $msg);
            } else {
                $this->setex($hash, $ttl, $msg);
            }

            return $this->lPush( 'mq', json_encode(array('q' => 'mq' . $queue, 'h' => $hash, 'f' => $forceRemove), JSON_THROW_ON_ERROR, 512));
        }

        private function & later(): RSMQ{

            if( !isset( $this->later ) ) {
                $this->later = new RSMQ($this);

                if( !in_array('mqlater', $this->later->listQueues(), true)) {
                    $this->later->createQueue('mqlater');
                }
            }
            return $this->later;
        }

        public function mqPushActionLater( string $obj, string $method, array $msg, int $delay, int $ttl = null, bool $forceRemove = false ){
            return $this->mqPushLater( 'mqpushaction', array( 'obj' => $obj, 'method' => $method, 'msg' => $msg ), $delay, $ttl, $forceRemove );
        }

        public function mqPushLater( string $queue, array $msgobj, int $delay, int $ttl = null, bool $forceRemove = false ): string{

            $msg = json_encode($msgobj, JSON_THROW_ON_ERROR);

            $hash = md5( 'mq' . $queue . $msg . $delay . (int)$ttl . uniqid('', true) . mt_rand() . microtime() );

            if($ttl === null) {
                $this->set($hash, $msg);
            } else {
                $this->setex($hash, $delay + $ttl, $msg);
            }

            return $this->later()->sendMessage( 'mqlater', json_encode(array('q' => 'mq' . $queue, 'h' => $hash, 'f' => $forceRemove), JSON_THROW_ON_ERROR, 512), [ 'delay' => $delay ] );
        }

        public function mqProcess(): void{

            $this->mqRegister( 'mqpushaction', function( $message ){

                $json = json_decode($message, true, 512, JSON_THROW_ON_ERROR);
                return $this->app->{$json['obj']}->{$json['method']}( $json['msg'] );
            });

            if( !empty( $this->mq ) ){
                while( true ) {

                    $this->processStartNow();

                    // check if this process should pause
                    while( $this->exists( 'mqsleep' ) ){
                        sleep( 1 );
                    }

                    // add all delayed messages available to mq. msg hash already added
                    while( ( $msg = $this->later()->popMessage( 'mqlater' ) ) && !empty( $msg['message'] ) ){
                        $this->lPush( 'mq', $msg['message'] );
                    }

                    // cron actions
                    if( !empty( $this->cron ) ){
                        $nowminute = date('YmdHi');
                        $now       = time();
                        foreach ($this->cron as $n => $cron){
                            if( $cron[ 'run' ] !== $nowminute && Expression::isDue( $cron[ 'expression' ], $now ) ){
                                $this->cron[ $n ][ 'run' ] = $nowminute;
                                $this->cron[ $n ][ 'function' ]();
                            }
                        }
                    }

                    // process real-time message
                    $value = $this->brpoplpush( 'mq', 'mqprocessed', 5 );

                    // check if message is valid (note: if timeout passed, result is false)
                    if( !empty($value) && ( $message = json_decode($value, true, 512, JSON_THROW_ON_ERROR) ) && isset($message['q'], $message['h'])) {

                        $queue = $message['q'];
                        $hash  = $message['h'];
                        $msg   = $this->get( $hash );

                        // if message content not found (eg, is expired)
                        if( !is_string( $msg ) ) {
                            $this->lRem('mqprocessed', $value, 0);

                        // if message is found and function is registered, execute it (and check if result is exactly boolean true to delete it from queue)
                        }elseif( isset( $this->mq[$queue] ) ){

                            // check if message has force-remove property (even if execution is not boolean true),
                            if( isset( $message['f'] ) && $message['f'] === true ){
                                $this->lRem('mqprocessed', $value, 0);
                                $this->del($hash);
                            }

                            // if function is executed and result is boolean true and is registered not to be forcedRemove
                            $res = $this->mq[$queue]($msg);

                            if( $res === true ) {
                                $this->lRem('mqprocessed', $value, 0);
                                $this->del($hash);
                            }
                        }
                    }
                }
            }
        }
    }
