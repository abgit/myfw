<?php


    class mydb{

        /** @var mycontainer */
        private $app;

        private PDO $pdo;
        private array $pdo_url;
        private string $pdo_dsn;
        private array $pdo_options;
        private string $pdo_scheme;

        /** @var PDOStatement */
        private ?PDOStatement $stmt;

        private array $debugs = array();

        private float $debugs_sum = 0;
        private int $debugs_counter = 0;

        private bool $cache_enable = false;
        private int $cache_expiration;
        private string $cache_prefix;

        public function __construct( $container ){

            $this->app         = $container;
            $this->pdo_url     = parse_url( $this->app->config[ 'db.dsn' ] );
            $this->pdo_options = $this->app[ 'db.options' ] ?? array();
            $this->pdo_scheme  = '';

            if( isset( $this->pdo_url['scheme'] ) ){
                switch( $this->pdo_url['scheme'] ){
                    case 'postgres':
                    case 'pgsql': $this->pdo_scheme = 'pgsql'; break;
                    case 'mysql': $this->pdo_scheme = 'mysql'; break;
                }
            }

            $pdo_query = '';
            if( isset( $this->pdo_url['query'] ) ){
                $pdo_query = str_replace( '&', ';', $this->pdo_url['query'] );
            }

            $this->pdo_dsn = sprintf( '%s:host=%s;dbname=%s;port=%s%s', $this->pdo_scheme, $this->pdo_url[ 'host' ], substr( $this->pdo_url[ 'path' ], 1 ), $this->pdo_url[ 'port' ], empty( $pdo_query ) ? '' : ';' . $pdo_query );
        }

        public function & pdo(): PDO
        {
            //if( !isset( $this->pdo )) {
                $this->pdo = new PDO($this->pdo_dsn, $this->pdo_url['user'], $this->pdo_url['pass'], $this->pdo_options);
            //}

            return $this->pdo;
        }

        public function getDSN(): string
        {
            return $this->pdo_dsn;
        }

        public function & msg( $message = null, $fallback = null ): mydb
        {
            $errcode = $this->errorCode();

            if( is_array( $message ) ) {
                $message = $message[$errcode] ?? $fallback;
            }

            $this->app->ajax->msgError( $message, 'Problem found' );

            return $this;
        }

        private function & query( $procedure, $values = array() ){

            // add custom global values
            if( isset( $this->app[ 'db.queryargs' ] ) && is_array( $this->app[ 'db.queryargs' ] ) ) {
                $values = $this->app['db.queryargs'] + $values;
            }

            // debug time start
            $debug_start = microtime( true );

            // split procedure
            preg_match( '/([a-zA-Z0-9_]+)\(([a-zA-Z0-9\,|_-]*)\)/', $procedure, $s );

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
                                            $column_value = isset( $args[2] ) ? (int)$args[2] : ( ( isset( $values[ $column_name ] ) && ( is_numeric( $values[ $column_name ] ) || is_bool( $values[ $column_name ] ) ) ) ? intval( $values[ $column_name ] ) : null );
                                            break;
                        case 'intlist' :    $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name ] ) && is_array( $values[ $column_name ] ) ? implode( ';', array_filter( $values[ $column_name ], 'intval' ) ) : null;
                                            break;
                        case 'bool' :       $column_type  = PDO::PARAM_BOOL;
                                            $column_value = isset( $values[ $column_name ] ) ? !empty( $values[ $column_name ] ) : ( isset( $args[2] ) ? ( $args[2] === 'true' ? true : ( $args[2] === 'false' ? false : null ) ) : null );
                                            break;
                        case 'datetime' :   $column_type  = PDO::PARAM_STR;
                                            $column_value = ( isset( $values[ $column_name ] ) && strlen( $values[ $column_name ] ) > 5 ) ? date('Y-m-d H:i:s', strtotime( $values[ $column_name ] ) ) : null;
                                            break;
                        case 'date' :       $column_type  = PDO::PARAM_STR;
                                            $column_value = ( isset( $values[ $column_name ] ) && strlen( $values[ $column_name ] ) > 5 ) ? date('Y-m-d 00:00:00', strtotime( $values[ $column_name ] ) ) : null;
                                            break;
                        case 'bigint' :     $column_type  = PDO::PARAM_STR;
                                            $column_value = ( isset( $values[ $column_name ] ) && preg_match('/^[0-9]+$/', (string)$values[$column_name]) ) ? $values[ $column_name ]: null;
                                            break;
                        case 'text' :       $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name ] ) ? substr( $values[ $column_name ], 0, 65535 ) : null;
                                            break;
                        case 'tag' :        $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name ] ) ? substr( $values[ $column_name ], 0, 36 ) : null;
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
                                            $column_value = isset( $values[ $column_name ] ) ? (float)str_replace(',', '.', $values[$column_name]) : 0;
                                            break;
                        case 'uuid' :       $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name] ) ? substr( $values[ $column_name ], 0, 36 ) : null;
                                            break;
                        case 'json' :       $column_type  = PDO::PARAM_STR;
                                            $column_value = $values[$column_name] ?? null;
                                            if( empty( $column_value ) || !$this->app->rules->isJsonString( $column_value ) ){
                                                $column_value = json_encode($column_value, JSON_THROW_ON_ERROR, 512);
                                            }
                                            break;
                        case 'str' :
                        case 'varchar' :
                        default :           $column_type  = PDO::PARAM_STR;
                                            $column_value = ( isset( $values[ $column_name ] ) && $values[$column_name] !== '') ? substr( $values[ $column_name ], 0, $args[2] ?? 0 ) : null;
                    }

                    // add elements
                    $elements[ ':' . $column_name ] = array( $column_value, $column_type );
                }
            }

            switch( $this->pdo_scheme ){

                case 'pgsql': $this->stmt = $this->pdo()->prepare( 'select * from ' . $procedure_name . '(' . implode( ',', array_keys( $elements ) ) . ')' );
                              break;

                case 'mysql': $this->stmt = $this->pdo()->prepare( 'CALL ' . $procedure_name . '(' . implode( ',', array_keys( $elements ) ) . ')' );
                              break;
            }

            // bind
            foreach( $elements as $col => $sett ) {
                $this->stmt->bindValue($col, $sett[0], $sett[1]);
            }

            $this->stmt->execute();

            // get error
            $error = !empty( $this->errorCode() );

            // add debug
            if( isset( $this->app[ 'app.debug' ] ) && $this->app[ 'app.debug' ] === true ){
                $this->debugs_counter ++;
                $debug_time = (float)microtime(true) - $debug_start;
                $this->debugs_sum += $debug_time;
                $this->debugs[] = sprintf('%s|%.3f%s', $procedure_name, $debug_time, $error ? '|E' . $this->errorCode() : ''  );
            }

            // check error warning
            if($error && isset($this->app['db.onerror'])) {
                $this->app['db.onerror']( $this->errorCode(), $this->errorInfo(), $procedure, $values );
            }

            return $this->stmt;
        }

        public function getDebugsCounter(): string
        {
            return sprintf('%d in %.3f (%s)', $this->debugs_counter, $this->debugs_sum, implode( '+', $this->debugs ) );
        }

        public function & cache( $expiration = null, $prefix = '' ): mydb
        {
            $this->cache_enable     = true;
            $this->cache_expiration = $expiration ?? 3600;
            $this->cache_prefix     = $prefix;
            return $this;
        }

        public function & cacheSession( $expiration = null ): mydb
        {
            return $this->cache( $expiration, 'dbcachesession' . session_id() );
        }

        public function & cacheClient( $expiration = null ): mydb
        {
            return $this->cache( $expiration, 'dbcacheclient' . $this->app->config[ 'db.cacheclient' ] );
        }

        // return NULL if cache disable; TRUE if cache enable and found, FALSE if cache enable and missing
        private function onCache( &$result, &$return, $procedure, $args ): ?bool
        {

            if( $this->cache_enable !== true ) {
                return null;
            }

            $this->cache_enable = false;

            // debug time start
            $debug_start = microtime( true );

            // replace args
            $args = array_merge( $args, $this->app->config[ 'db.cachereplaceargs' ] );

            $hash       = md5( $procedure . json_encode($args, JSON_THROW_ON_ERROR, 512));
            $key_result = $this->cache_prefix . 'dbcache-res-' . $hash;
            $key_return = $this->cache_prefix . 'dbcache-ret-' . $hash;

            // get from cache
            [$result, $return] = $this->app->redis->mGet(array($key_result, $key_return));

            if( $result !== false && $return !== false ){
                $result = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
                $return = json_decode($return, true, 512, JSON_THROW_ON_ERROR);

                $debug_time = (float)microtime(true) - $debug_start;
                $this->debugs_counter ++;
                $this->debugs_sum += $debug_time;
                $this->debugs[] = sprintf('%s|%.3f|C', strstr( $procedure, '(', true), $debug_time );
                return true;
            }

            return false;
        }

        private function addCache( $result, $return, $procedure, $args ): bool
        {
            // replace args
            $args = array_merge( $args, $this->app->config[ 'db.cachereplaceargs' ] );

            $hash       = md5( $procedure . json_encode($args, JSON_THROW_ON_ERROR, 512));
            $key_result = $this->cache_prefix . 'dbcache-res-' . $hash;
            $key_return = $this->cache_prefix . 'dbcache-ret-' . $hash;

            $res_result = $this->app->redis->setex( json_encode($key_result, JSON_THROW_ON_ERROR, 512), $this->cache_expiration, $result );
            $res_return = $this->app->redis->setex( json_encode($key_return, JSON_THROW_ON_ERROR, 512), $this->cache_expiration, $return );

            return $res_result === true && $res_return === true;
        }

        public function findAll( &$result, $procedure, $args = array(), $returnobject = false ): bool
        {
            // get cache
            $oncache = $this->onCache($result, $return, $procedure, $args );

            // if cache is enable and exists
            if( $oncache === true ) {
                return $return;
            }

            $result = $this->query( $procedure, $args )->fetchAll( is_bool($returnobject) ? ( $returnobject ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC ) : $returnobject );
            $return = ( count( $result ) > 0 );

            // save cache if oncache is false (enable but missing)
            if( $oncache === false ) {
                $this->addCache($result, $return, $procedure, $args);
            }

            return $return;
        }


        public function findAllFunction( callable $function, string $procedure, $args = array(), $returnobject = false ): array{
            $elements = array();

            $stmt = $this->query( $procedure, $args );
            while ($row = $stmt->fetch( $returnobject ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC ) ){
                $function( $row );
                $elements[] = $row;
            }

            return $elements;
        }


        public function findOne( & $result, string $procedure, array $args = array(), bool $returnobject = false ): bool
        {
            // get cache
            $oncache = $this->onCache($result, $return, $procedure, $args );

            // if cannot get cache (disable or enable_but_not_found)
            if( $oncache === true ) {
                return $return;
            }

            $result = $this->query( $procedure, $args )->fetch( is_bool($returnobject) ? ( $returnobject ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC ) : $returnobject );
            $return = ( ! empty( $result ) );

            // save cache if oncache is false (enable but missing)
            if( $oncache === false ) {
                $this->addCache($result, $return, $procedure, $args);
            }

            return $return;
        }


        public function findResult( & $result, $procedure, $args = array() ): bool
        {

            // get cache
            $oncache = $this->onCache($result, $return, $procedure, $args );

            // if cannot get cache (disable or enable_but_not_found)
            if( $oncache === true ) {
                return $return;
            }

            $result = $this->query( $procedure, $args )->fetchAll();
            $return = ( !is_array( $result ) || count($result) !== 1 ) ? false : ( (int)reset($result[0]) > 0 );

            // save cache if oncache is false (enable but missing)
            if( $oncache === false ) {
                $this->addCache($result, $return, $procedure, $args);
            }

            return $return;
        }


        public function findAllReturn( string $procedure, array $args = array(), bool $returnobject = false ): array
        {
            return $this->findAll( $result, $procedure, $args, $returnobject ) ? $result : array();
        }


        public function findOneReturn( $procedure, $args = array(), $returnobject = false ){
            return $this->findOne( $result, $procedure, $args, $returnobject ) ? $result : false;
        }


        public function findValue( & $result, $procedure, $args = array(), $returnobject = false ): bool
        {

            if( !$this->findOne( $oneresult, $procedure, $args, $returnobject ) || empty( $oneresult ) ) {
                return false;
            }

            $result = array_shift($oneresult);

            return true;
        }


        public function findValueReturn( $procedure, $args = array(), $returnobject = false ){
            return $this->findValue( $result, $procedure, $args, $returnobject ) ? $result : false;
        }


        public function errorCode(): int
        {

            if($this->stmt !== null) {

                $errcode = $this->stmt->errorCode();

                // try to parse from errorInfo
                if( $errcode === '00000' ){

                    $arr = $this->stmt->errorInfo();
                    if (isset($arr[2]) && is_string($arr[2]) && strlen($arr[2]) > 8) {
                        return (int)substr($arr[2], 8);
                    }
                }else{
                    return (int)$errcode;
                }
            }

            return 0;
        }


        public function errorInfo():string{

            if($this->stmt !== null) {
                $arr = $this->stmt->errorInfo();
                if( isset( $arr[2] ) ) {
                    return $arr[2];
                }
            }

            return 'unknown error';
        }

    }
