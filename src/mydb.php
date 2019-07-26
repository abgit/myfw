<?php


    class mydb{

        /** @var abcontainer */
        private $app;

        /** @var PDO */
        private $pdo = null;
        private $pdo_url = null;
        private $pdo_options = array();

        /** @var PDOStatement */
        private $stmt = null;

        private $debugs = array();

        private $debugs_sum       = 0;
        private $debugs_counter   = 0;

        private $cache_enable     = false;
        private $cache_expiration;

        public function __construct( $container ){

            $this->app     = $container;
            $this->pdo_url = parse_url( $this->app->config[ 'db.dsn' ] );

            if( isset( $this->app[ 'db.options' ] ) && is_array( $this->app[ 'db.options' ] ) )
                $this->pdo_options = $this->app[ 'db.options' ];
        }

        public function & pdo(){
            if( is_null( $this->pdo ) )
                $this->pdo = new PDO( sprintf( '%s:host=%s;dbname=%s;port=%s', $this->pdo_url['scheme'], $this->pdo_url[ 'host' ], substr( $this->pdo_url[ 'path' ], 1 ), $this->pdo_url[ 'port' ] ), $this->pdo_url[ 'user' ], $this->pdo_url[ 'pass' ], $this->pdo_options );

            return $this->pdo;
        }
        
        public function & msg( $msgs = null, $headers = null ){

            $errcode = $this->errorCode();

            $message = ( is_array( $msgs )    && isset( $msgs[ $errcode ] )    ) ? $msgs[ $errcode ]    : ( is_string( $msgs ) ? $msgs : $errcode );
            $header  = ( is_array( $headers ) && isset( $headers[ $errcode ] ) ) ? $headers[ $errcode ] : ( is_string( $headers ) ? $headers : null );

            $this->app->ajax->msgError( $message, $header );

            return $this;
        }

        private function & query( $procedure, $values = array() ){

            // add custom global values
            if( isset( $this->app[ 'db.queryargs' ] ) && is_array( $this->app[ 'db.queryargs' ] ) )
                $values = $this->app[ 'db.queryargs' ] + $values;

            // debug time start
            $debug_start = microtime( true );

            // split procedure
            preg_match( '/([a-zA-Z0-9]+)\(([a-zA-Z0-9\,|_]*)\)/', $procedure, $s );

            $procedure_name = $s[1];
            $procedure_args = $s[2];

            $elements = array();

            if( ! empty( $procedure_args ) ){
                $binds = explode( ',', $procedure_args );
                foreach( $binds as $bind ){
                    $args = explode( '|', $bind );

                    $column_name = $args[0];
                    $column_type = $args[1];

                    // column type & value
                    switch( $column_type ){
                        case 'null' :       $column_type  = PDO::PARAM_NULL; 
                                            $column_value = null;
                                            break;
                        case 'int' :        $column_type  = PDO::PARAM_INT;
                                            $column_value = isset( $args[2] ) ? intval( $args[2] ) : ( ( isset( $values[ $column_name ] ) && ( is_numeric( $values[ $column_name ] ) || is_bool( $values[ $column_name ] ) ) ) ? intval( $values[ $column_name ] ) : null );
                                            break;
                        case 'bool' :       $column_type  = PDO::PARAM_BOOL;
                                            $column_value = isset( $values[ $column_name] ) ? !empty( $values[ $column_name ] ) : ( isset( $args[2] ) ? ( $args[2] === 'true' ? true : ( $args[2] === 'false' ? false : null ) ) : null );
                                            break;
                        case 'datetime' :   $column_type  = PDO::PARAM_STR;
                                            $column_value = ( isset( $values[ $column_name ] ) && strlen( $values[ $column_name ] ) > 5 ) ? date("Y-m-d H:i:s", strtotime( $values[ $column_name ] ) ) : null;
                                            break;
                        case 'date' :       $column_type  = PDO::PARAM_STR;
                                            $column_value = ( isset( $values[ $column_name ] ) && strlen( $values[ $column_name ] ) > 5 ) ? date("Y-m-d 00:00:00", strtotime( $values[ $column_name ] ) ) : null;
                                            break;
                        case 'bigint' :     $column_type  = PDO::PARAM_STR;
                                            $column_value = ( isset( $values[ $column_name ] ) && preg_match('/^[0-9]+$/', strval($values[ $column_name ] ) ) ) ? $values[ $column_name ]: null;
                                            break;
                        case 'text' :       $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name ] ) ? substr( $values[ $column_name ], 0, 65535 ) : null;
                                            break;
                        case 'tag' :        $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name ] ) ? substr( preg_replace( "/[^A-Za-z0-9]/", '', $values[ $column_name ] ), 0, 100 ) : null;
                                            break;
                        case 'btcaddr' :    $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name ] ) && preg_match( "/^[13][a-km-zA-HJ-NP-Z1-9]{25,34}$/", $values[ $column_name ] ) ? $values[ $column_name ] : null;
                                            break;
                        case 'checkbox' :   $column_type  = PDO::PARAM_INT;
                                            $column_value = isset( $values[ $column_name ] ) && $values[ $column_name ] === 'on' ? 1 : null;
                                            break;
                        case 'float' :      $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name ] ) ? str_replace( ',', '.', $values[ $column_name] ) : 0;
                                            break;
                        case 'numeric' :
                        case 'double' :     $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name ] ) ? floatval( str_replace( ',', '.', $values[ $column_name] ) ) : 0;
                                            break;
                        case 'uuid' :       $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name] ) ? substr( $values[ $column_name ], 0, 40 ) : null;
                                            break;
                        case 'json' :       $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name ] ) ? $values[ $column_name ] : null;
                                            if( empty( $column_value ) || !is_string( $column_value ) || !is_array( json_decode( $column_value, true ) ) || json_last_error() != 0 ){
                                                $column_value = json_encode( $column_value );
                                            }
                                            break;
                        case 'str' :
                        case 'varchar' :
                        default :           $column_type  = PDO::PARAM_STR;
                                            $column_value = ( isset( $values[ $column_name ] ) && strlen( $values[ $column_name ] ) ) ? substr( $values[ $column_name ], 0, ( isset( $args[2] ) ? $args[2] : 0 ) ) : null;
                    }

                    // add elements
                    $elements[ ':' . $column_name ] = array( $column_value, $column_type );
                }
            }

            switch( $this->pdo_url[ 'scheme' ] ){

                case 'pgsql': $this->stmt = $this->pdo()->prepare( 'select * from ' . $procedure_name . '(' . implode( ',', array_keys( $elements ) ) . ')' );
                              break;

                case 'mysql': $this->stmt = $this->pdo()->prepare( 'CALL ' . $procedure_name . '(' . implode( ',', array_keys( $elements ) ) . ')' );
                              break;
            }

            // bind
            foreach( $elements as $col => $sett )
                $this->stmt->bindValue( $col, $sett[0], $sett[1] );

            $this->stmt->execute();

            // get error
            $error = !empty( $this->errorCode() );

            // add debug
            if( isset( $this->app[ 'app.debug' ] ) && $this->app[ 'app.debug' ] === true ){
                $this->debugs_counter ++;
                $debug_time = (float)microtime(true) - $debug_start;
                $this->debugs_sum += $debug_time;
                $this->debugs[] = sprintf("%s|%.3f%s", $procedure_name, $debug_time, $error ? '|E' . $this->errorCode() : ''  );
            }

            // check error warning
            if( $error ){

                if( isset( $this->app[ 'db.onerror' ] ) ) {
                    $this->app['db.onerror']( $this->errorCode(), $this->errorInfo(), $procedure, $values );
                }

            }

            return $this->stmt;
        }

        public function getDebugsCounter(){
            return sprintf("%d in %.3f (%s)", $this->debugs_counter, $this->debugs_sum, implode( "+", $this->debugs ) );
        }

        public function & cache( $expiration = null ){
            $this->cache_enable     = true;
            $this->cache_expiration = is_null( $expiration ) ? time() + 300 : $expiration;
            return $this;
        }

        // return NULL if cache disable; TRUE if cache enable and found, FALSE if cache enable and missing
        private function onCache( &$result, &$return, $procedure, $args ){

            if( $this->cache_enable !== true )
                return null;

            $this->cache_enable = false;

            // debug time start
            $debug_start = microtime( true );

            // replace args
            $args = array_merge( $args, $this->app->config[ 'db.cachereplaceargs' ] );

            $hash       = md5( $procedure . json_encode($args) );
            $key_result = 'dbcache-res-' . $hash;
            $key_return = 'dbcache-ret-' . $hash;

            // get from cache
            $cache = $this->app->memcached->getMulti( array( $key_result, $key_return ) );

            if( $this->app->memcached->getResultCode() === \Memcached::RES_SUCCESS && isset( $cache[ $key_result ] ) && isset( $cache[ $key_return ] ) ){
                $result = $cache[ $key_result ];
                $return = $cache[ $key_return ];

                $debug_time = (float)microtime(true) - $debug_start;
                $this->debugs_counter ++;
                $this->debugs_sum += $debug_time;
                $this->debugs[] = sprintf("%s|%.3f|C", strstr( $procedure, '(', true), $debug_time );
                return true;
            }

            return false;
        }

        private function addCache( $result, $return, $procedure, $args ){

            // replace args
            $args = array_merge( $args, $this->app->config[ 'db.cachereplaceargs' ] );

            $hash       = md5( $procedure . json_encode($args) );
            $key_result = 'dbcache-res-' . $hash;
            $key_return = 'dbcache-ret-' . $hash;

            return $this->app->memcached->setMulti( array( $key_result => $result, $key_return => $return ), $this->cache_expiration );
        }

        public function findAll( & $result, $procedure, $args = array(), $returnobject = false ){

            // get cache
            $oncache = $this->onCache($result, $return, $procedure, $args );

            // if cache is enable and exists
            if( $oncache === true )
                return $return;

            $result = $this->query( $procedure, $args )->fetchAll( is_bool($returnobject) ? ( $returnobject ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC ) : $returnobject );
            $return = ( count( $result ) > 0 );

            // save cache if oncache is false (enable but missing)
            if( $oncache === false )
                $this->addCache( $result, $return, $procedure, $args );

            return $return;
        }


        public function findOne( & $result, string $procedure, array $args = array(), bool $returnobject = false ){

            // get cache
            $oncache = $this->onCache($result, $return, $procedure, $args );

            // if cannot get cache (disable or enable_but_not_found)
            if( $oncache === true )
                return $return;

            $result = $this->query( $procedure, $args )->fetch( is_bool($returnobject) ? ( $returnobject ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC ) : $returnobject );
            $return = ( ! empty( $result ) );

            // save cache if oncache is false (enable but missing)
            if( $oncache === false )
                $this->addCache( $result, $return, $procedure, $args );

            return $return;
        }


        public function findResult( & $result, $procedure, $args = array() ){

            // get cache
            $oncache = $this->onCache($result, $return, $procedure, $args );

            // if cannot get cache (disable or enable_but_not_found)
            if( $oncache === true )
                return $return;

            $result = $this->query( $procedure, $args )->fetchAll();
            $return = ( !is_array( $result ) || count($result) != 1 ) ? false : ( intval( reset( $result[0] ) ) > 0 );

            // save cache if oncache is false (enable but missing)
            if( $oncache === false )
                $this->addCache( $result, $return, $procedure, $args );

            return $return;
        }


        public function findAllReturn( $procedure, $args = array(), $returnobject = false ){
            return $this->findAll( $result, $procedure, $args, $returnobject ) ? $result : array();
        }


        public function findOneReturn( $procedure, $args = array(), $returnobject = false ){
            return $this->findOne( $result, $procedure, $args, $returnobject ) ? $result : false;
        }


        public function findValue( & $result, $procedure, $args = array(), $returnobject = false ){
            if( !$this->findOne( $oneresult, $procedure, $args, $returnobject ) || empty( $oneresult ) )
                return false;

            foreach( $oneresult as $result )
                break;

            return ( count( $oneresult ) > 0 );
        }


        public function findValueReturn( $procedure, $args = array(), $returnobject = false ){
            return $this->findValue( $result, $procedure, $args, $returnobject ) ? $result : false;
        }


        public function errorCode(){

            if( !is_null( $this->stmt ) ) {

                $errcode = $this->stmt->errorCode();

                // try to parse from errorInfo
                if( $errcode === '00000' ){

                    $arr = $this->stmt->errorInfo();
                    if (isset($arr[2]) && is_string($arr[2]) && strlen($arr[2]) > 8) {
                        return intval(substr($arr[2], 8));
                    }
                }else{
                    return intval($errcode);
                }
            }

            return 0;
        }


        public function errorInfo(){

            if( !is_null( $this->stmt ) ) {
                $arr = $this->stmt->errorInfo();
                if( isset( $arr[2] ) )
                    return $arr[2];
            }

            return 'unknown error';
        }

    }
