<?php

    class mydb{

        private $pdo;

        public function __construct(){
            $this->app = \Slim\Slim::getInstance();
            $this->pdo = new PDO( get_cfg_var( 'abrands.db.d' ), get_cfg_var( 'abrands.db.u' ), get_cfg_var( 'abrands.db.p' ), array( 1002 => 'SET NAMES utf8' ) );

            if ( $this->app->config( 'debug' ) !== false )
                $this->pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
        }

        private function & query( $procedure, $values = array() ){

            // split procedure
            preg_match( '/([a-z0-9]+)\(([a-z0-9\,|_]*)\)/', $procedure, $s );

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
                                            $column_value = isset( $values[ $column_name ] ) ? substr( $values[ $column_name ], 0, 3000 ) : null;
                                            break;
                        case 'float' :      $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name ] ) ? printf( '%0.0f', str_replace( ',', '.', $values[ $column_name] ) ) : 0;
                                            break;
                        case 'double' :     $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name ] ) ? strval( round( floatval( str_replace( ',', '.', $values[ $column_name] ) ), 2 ) ) : 0;
                                            break;
                        case 'uuid' :       $column_len   = 40;
                        case 'str' :
                        case 'varchar' :
                        default :           $column_type  = PDO::PARAM_STR;
                                            $column_value = isset( $values[ $column_name] ) ? substr( $values[ $column_name ], 0, $column_len ) : null;
                    }

                    // add elements
                    $elements[ ':' . $column_name ] = array( $column_value, $column_type );
                }
            }

            $q = $this->pdo->prepare( 'CALL ' . $procedure_name . '(' . implode( ',', array_keys( $elements ) ) . ')' );

            // bind
            foreach( $elements as $col => $sett )
                $q->bindValue( $col, $sett[0], $sett[1] );

            $q->execute();
            return $q;
        }

        public function findAll( & $result, $procedure, $args = array(), $returnobject = false ){

            $this->app->log()->debug( "mydb::findAll,procedure:" . $procedure . ',args:' . json_encode( $args ) );

            $result = $this->query( $procedure, $args )->fetchAll( is_bool($returnobject) ? ( $returnobject ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC ) : $returnobject );		
            return ( count( $result ) > 0 );
        }

        public function findOne( & $result, $procedure, $args = array(), $returnobject = false ){

            $result = $this->query( $procedure, $args )->fetch( is_bool($returnobject) ? ( $returnobject ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC ) : $returnobject );
            return ( ! empty( $result ) );
        }

        public function countAll( $procedure, $args = array() ){

            $result = $this->query( $procedure, $args )->fetchAll();
            return ( count($result) == 1 && isset( $result[0]['total'] ) ) ? intval( $result[0]['total'] ) : 0;
        }

        public function apply( $procedure, $args = array() ){

            $this->app->log()->debug( "mydb::apply,procedure:" . $procedure . ',args:' . json_encode( $args ) );

            $result = $this->query( $procedure, $args )->fetchAll();
            if ( is_array( $result ) && count($result) == 1 && isset( $result[0] ) && is_array( $result[0] ) ){
                $result = array_values( $result[0] );
                return intval( $result[0] );
            }
            return false;
        }

    }
