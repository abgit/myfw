<?php

    class mydb{

        private $pdo;
        private $stmt;

        public function __construct(){
            $this->app  = \Slim\Slim::getInstance();
            $this->pdo  = new PDO( get_cfg_var( 'abrands.db.d' ), get_cfg_var( 'abrands.db.u' ), get_cfg_var( 'abrands.db.p' ), array( 1002 => 'SET NAMES utf8' ) );
            $this->stmt = null;

            if ( $this->app->config( 'debug' ) !== false )
                $this->pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        }

        private function & query( $procedure, $values = array() ){

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
                    $column_len  = isset( $args[2] ) ? $args[2] : 0;

                    // column type & value
                    switch( $column_type ){
                        case 'null' :       $column_type  = PDO::PARAM_NULL; 
                                            $column_value = isset( $values[ $column_name ] ) ? $values[ $column_name ] : null;
                                            break;
                        case 'int' :        $column_type  = PDO::PARAM_INT;
                                            $column_value = isset( $values[ $column_name ] ) ? intval( $values[ $column_name ] ) : null;
                                            break;
                        case 'datetime' :   $column_type  = PDO::PARAM_STR;
                                            $column_value = ( isset( $values[ $column_name ] ) && strlen( $values[ $column_name ] ) > 5 ) ? substr( $values[ $column_name ], 0, 19 ) : null;
                                            break;
                        case 'text' :       $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name ] ) ? substr( $values[ $column_name ], 0, 65535 ) : null;
                                            break;
                        case 'float' :      $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name ] ) ? str_replace( ',', '.', $values[ $column_name] ) : 0;
                                            break;
                        case 'double' :     $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name ] ) ? strval( round( floatval( str_replace( ',', '.', $values[ $column_name] ) ), 2 ) ) : 0;
                                            break;
                        case 'uuid' :       $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name] ) ? substr( $values[ $column_name ], 0, 40 ) : null;
                                            break;
                        case 'tag' :        $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name] ) ? substr( $values[ $column_name ], 0, 20 ) : null;
                                            break;
                        case 'str' :
                        case 'varchar' :
                        default :           $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name] ) ? substr( $values[ $column_name ], 0, $column_len ) : null;
                    }

                    // add elements
                    $elements[ ':' . $column_name ] = array( $column_value, $column_type );
                }
            }

            $this->stmt = $this->pdo->prepare( 'CALL ' . $procedure_name . '(' . implode( ',', array_keys( $elements ) ) . ')' );

            // bind
            foreach( $elements as $col => $sett )
                $this->stmt->bindValue( $col, $sett[0], $sett[1] );

            $this->stmt->execute();
            return $this->stmt;
        }

        public function findAll( & $result, $procedure, $args = array(), $returnobject = false ){


            $this->app->log()->debug( "mydb::findAll,procedure:" . $procedure . ',args:' . json_encode( $args ) );

            try{
                $result = $this->query( $procedure, $args )->fetchAll( is_bool($returnobject) ? ( $returnobject ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC ) : $returnobject );		
            }catch( ErrorException $e ){
                $result = is_null( $this->stmt ) ? 0 : intval( $this->stmt->errorCode() );
                return false;
            }

            return ( count( $result ) > 0 );
        }

        public function findOne( & $result, $procedure, $args = array(), $returnobject = false ){

            try{
                $result = $this->query( $procedure, $args )->fetch( is_bool($returnobject) ? ( $returnobject ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC ) : $returnobject );
            }catch( ErrorException $e ){
                $result = is_null( $this->stmt ) ? 0 : intval( $this->stmt->errorCode() );
                return false;
            }

            return ( ! empty( $result ) );
        }

        public function findResult( & $result, $procedure, $args = array() ){

            try{
                $result = $this->query( $procedure, $args )->fetchAll();
            }catch( ErrorException $e ){
                $result = is_null( $this->stmt ) ? 0 : intval( $this->stmt->errorCode() );
                return false;
            }
            
            if( !is_array( $result ) || count($result) != 1 )
                return false;

            return intval( reset( $result[0] ) ) > 0;
        }

/*        public function apply( $procedure, $args = array() ){

            $this->app->log()->debug( "mydb::apply,procedure:" . $procedure . ',args:' . json_encode( $args ) );

            return $this->query( $procedure, $args );
        }
*/
    }
