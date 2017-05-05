<?php

    class mycalendar{

        private $app;
        private $id;
        private $values;
        private $onclick;
        private $onclickmsg;
        private $keyid;
        private $keytitle;
        private $keydate;
        private $keycolor = null;
        private $colorfilter;
        private $addonpre = '';
        private $addonpos = '';
        private $url;
        private $colordefault = '#3B5998';
        private $idstart;
        private $idend;
        private $datestart;
        private $dateend;

        public function __construct( $id = null ){
            $this->app  = \Slim\Slim::getInstance();
            $this->id   = $id;
            $this->init = true;
        }

        public function & setID( $id ){
            $this->id = $id;
            return $this;
        }

        public function & setUrl( $url ){
            $this->url = $url;
            return $this;
        }

        public function & setIdFormat( $start, $end ){
            $this->idstart = $start;
            $this->idend   = $end;
            return $this;
        }

        public function & setDateFormat( $start, $end ){
            $this->datestart = $start;
            $this->dateend   = $end;
            return $this;
        }

        public function getDate(){
            return ( isset( $_POST[ 'id' ] ) && is_string( $_POST[ 'id' ] ) && strlen( $_POST[ 'id' ] ) >= $this->dateend && myrules::isdate( substr( $_POST[ 'id' ], $this->datestart, $this->dateend ) ) ) ? substr( $_POST[ 'id' ], $this->datestart, $this->dateend ) : null;
        }

        public function getID(){
            return ( isset( $_POST[ 'id' ] ) && is_string( $_POST[ 'id' ] ) && strlen( $_POST[ 'id' ] ) >= $this->idend ) ? substr( $_POST[ 'id' ], $this->idstart, $this->idend ) : null;
        }

        public function & setOnClick( $onclick, $onclickmsg = '' ){
            $this->onclick    = $onclick;
            $this->onclickmsg = $onclickmsg;
            return $this;
        }

        public function & setKeys( $id, $title, $date ){
            $this->keyid = $id;
            $this->keytitle = $title;
            $this->keydate = $date;
            return $this;
        }

        public function & setColor( $key, $filter = array() ){
            $this->keycolor = $key;
            $this->colorfilter = $filter;
            return $this;
        }

        public function & setColorDefault( $colordefault ){
            $this->colordefault = $colordefault;
            return $this;
        }

        public function & addAddon( $value, $prefix = true ){
            if( $prefix ){
                $this->addonpre = $value;
            }else{
                $this->addonpos = $value;
            }
            return $this;
        }

        public function & setValues( $values ){
            $this->values = array();
    
            if( !is_array( $values ) )
                $values = json_decode( $values, true );

            if( is_array( $values ) ){
                foreach( $values as $x ){
                    if( !isset( $x[ $this->keyid ] ) || !isset( $x[ $this->keydate ] ) )
                        continue;

                    $val = array( 'id'    => $x[ $this->keyid ],
                                  'title' => $this->addonpre . $x[ $this->keytitle ] . $this->addonpos,
                                  'start' => $x[ $this->keydate ] );

                    if( !is_null( $this->keycolor ) && isset( $x[ $this->keycolor ] ) ){
                        $val[ 'color' ] = empty( $this->colorfilter ) ? $x[ $this->keycolor ] : $this->app->filters()->replaceonly( $x[ $this->keycolor ], $this->colorfilter );
                    }else{
                        $val[ 'color' ] = $this->colordefault;
                    }
                    $this->values[] = $val;
                };
            };
            return $this;
        }

        public function json(){
            print json_encode( $this->values, JSON_UNESCAPED_SLASHES );
        }
        
        public function & ajaxRemoveEvent( $event_id = null ){
            $this->app->ajax()->calendarEventRemove( '#cal' . $this->id, is_null( $event_id ) ? date( "Y-m-d", time() ) : $event_id );
            return $this;
        }

        public function & ajaxRefresh(){
            $this->app->ajax()->calendarRefresh( '#cal' . $this->id );
            return $this;
        }

        public function & ajaxAddEvent( $event_title, $event_id = null, $event_start = null, $event_end = null, $event_color = null ){
            if( is_null( $event_start ) )
                $event_start = time();
            if( is_null( $event_end ) )
                $event_end = $event_start;
            if( is_null( $event_color ) )
                $event_color = $this->colordefault;
            if( is_null( $event_id ) )
                $event_id = date( "Y-m-d", time() );

            $event_start = date( "Y-m-d", is_string( $event_start ) ? strtotime( $event_start ) : intval( $event_start ) );
            $event_end   = date( "Y-m-d", is_string( $event_end )   ? strtotime( $event_end )   : intval( $event_end ) );

            $this->app->ajax()->calendarEventAdd( '#cal' . $this->id, $this->addonpre . $event_title . $this->addonpos, $event_start, $event_end, $event_id, $event_color );
            return $this;
        }

        public function getStart(){
            if( isset( $_GET[ 'start' ] ) && myrules::isdate( $_GET[ 'start' ] ) && isset( $_GET[ 'end' ] ) && myrules::isdate( $_GET[ 'end' ] ) && ( strtotime( $_GET[ 'start' ] ) < strtotime( $_GET[ 'end' ] ) ) && ( strtotime( $_GET[ 'start' ] ) > ( strtotime( $_GET[ 'end' ] ) - 5184000 ) ) )
                return $_GET[ 'start' ];
            
            return false;
        }

        public function getEnd(){
            if( isset( $_GET[ 'start' ] ) && myrules::isdate( $_GET[ 'start' ] ) && isset( $_GET[ 'end' ] ) && myrules::isdate( $_GET[ 'end' ] ) && ( strtotime( $_GET[ 'start' ] ) < strtotime( $_GET[ 'end' ] ) ) && ( strtotime( $_GET[ 'start' ] ) > ( strtotime( $_GET[ 'end' ] ) - 5184000 ) ) )
                return $_GET[ 'end' ];
            
            return false;
        }

        public function obj(){
            
            if( $this->init )
                $this->app->ajax()->calendar( '#cal' . $this->id );

            return array( 'url'         => $this->url,
                          'onclick'     => $this->onclick,
                          'onclickmsg'  => $this->onclickmsg,
                          'id'          => $this->id,
                          'init'        => $this->init );
        }
        
        public function __toString(){
            return $this->app->render( '@my/mycalendar', $this->obj(), null, null, 0, false, false );
        }
    }
