<?php

    class mycalendar{

        /** @var mycontainer*/
        private $app;

        private string $id = '';
        private bool $init;
        private array $values = array();
        private string $onclick;
        private string $onclickmsg;
        private ?string $keyid;
        private ?string $keytitle;
        private ?string $keydate;
        private ?string $keycolor = null;
        private array $colorfilter;
        private string $addonpre = '';
        private string $addonpos = '';
        private string $url;
        private string $colordefault = '#3B5998';
        private string $idstart;
        private string $idend;
        private string $datestart;
        private string $dateend;
        public $onRefresh = null;

        public function __construct( $c ){
            $this->app  = $c;
            $this->init = true;
        }

        public function & setName( string $id ): mycalendar{
            $this->id = $id;
            return $this;
        }

        public function & setUrl( $url ): mycalendar{
            $this->url = $url;
            return $this;
        }

        public function & refresh(): mycalendar{

            if( $this->getStartEnd( $start, $end ) ) {
                $this->setValues( call_user_func($this->onRefresh, $start, $end ) );
                $this->ajaxUpdate();
            }
            return $this;
        }

        public function & setValuesOnRefresh( $onrefresh ): mycalendar{
            $this->onRefresh = $onrefresh;
            return $this;
        }

        public function & setIdFormat( string $start, string $end ): mycalendar{
            $this->idstart = $start;
            $this->idend   = $end;
            return $this;
        }

        public function & setDateFormat( string $start, string $end ): mycalendar{
            $this->datestart = $start;
            $this->dateend   = $end;
            return $this;
        }

        public function getDate(): ?string{
            return ( isset( $_POST[ 'id' ] ) && is_string( $_POST[ 'id' ] ) && strlen( $_POST[ 'id' ] ) >= $this->dateend && $this->app->rules->isdate( substr( $_POST[ 'id' ], $this->datestart, $this->dateend ) ) ) ? substr( $_POST[ 'id' ], $this->datestart, $this->dateend ) : null;
        }

        public function getID(): ?string{
            return ( isset( $_POST[ 'id' ] ) && is_string( $_POST[ 'id' ] ) && strlen( $_POST[ 'id' ] ) >= $this->idend ) ? substr( $_POST[ 'id' ], $this->idstart, $this->idend ) : null;
        }

        public function getDetails(): array{
            return array( $this->getID(), $this->getDate() );
        }

        public function & setOnClick( string $onclick, string $onclickmsg = '' ):mycalendar{
            $this->onclick    = $onclick;
            $this->onclickmsg = $onclickmsg;
            return $this;
        }

        public function & setKeys( string $id, string $title, string $date ):mycalendar{
            $this->keyid    = $id;
            $this->keytitle = $title;
            $this->keydate  = $date;
            return $this;
        }

        public function & setColor( string $key, array $filter = array() ):mycalendar{
            $this->keycolor    = $key;
            $this->colorfilter = $filter;
            return $this;
        }

        public function & setColorDefault( string $colordefault ):mycalendar{
            $this->colordefault = $colordefault;
            return $this;
        }

        public function & addAddon( string $value, bool $prefix = true ):mycalendar{
            if( $prefix ){
                $this->addonpre = $value;
            }else{
                $this->addonpos = $value;
            }
            return $this;
        }

        public function & setValues( $values ):mycalendar{
            $this->values = array();
    
            if( !is_array( $values ) ) {
                $values = json_decode($values, true);
            }

            if( is_array( $values ) ){

                foreach( $values as $x ){

                    if(!isset($x[$this->keyid], $x[$this->keydate])) {
                        continue;
                    }

                    $val = array( 'id'    => $x[ $this->keyid ],
                                  'title' => $this->addonpre . $x[ $this->keytitle ] . $this->addonpos,
                                  'start' => $x[ $this->keydate ] );

                    if( isset( $x[ $this->keycolor ] ) && !is_null( $this->keycolor ) ){
                        $val[ 'color' ] = empty( $this->colorfilter ) ? $x[ $this->keycolor ] : $this->app->filters->replaceonly( $x[ $this->keycolor ], $this->colorfilter );
                    }else{
                        $val[ 'color' ] = $this->colordefault;
                    }
                    $this->values[] = $val;
                }
            }
            return $this;
        }

        public function json(){
            return json_encode( $this->values, JSON_UNESCAPED_SLASHES );
        }

        public function ajaxUpdate():void{
            $this->app->ajax->setObj( $this->values );
        }

        public function & ajaxRemoveEvent( ?string $event_id = null ):mycalendar{
            $this->app->ajax->calendarEventRemove( '#cal' . $this->id, is_null( $event_id ) ? date( "Y-m-d") : $event_id );
            return $this;
        }

        public function & ajaxRefresh():mycalendar{
            $this->app->ajax->calendarRefresh( '#cal' . $this->id );
            return $this;
        }

        public function & ajaxAddEvent( string $event_title, ?string $event_id = null, ?string $event_start = null, ?string $event_end = null, ?string $event_color = null ): mycalendar{
            if( is_null( $event_start ) ) {
                $event_start = 'today';
            }
            if( is_null( $event_end ) ) {
                $event_end = $event_start;
            }
            if( is_null( $event_color ) ) {
                $event_color = $this->colordefault;
            }
            if( is_null( $event_id ) ) {
                $event_id = date("Y-m-d");
            }

            $event_start = date( "Y-m-d", strtotime( $event_start ) );
            $event_end   = date( "Y-m-d", strtotime( $event_end )   );

            $this->app->ajax->calendarEventAdd( '#cal' . $this->id, $this->addonpre . $event_title . $this->addonpos, $event_start, $event_end, $event_id, $event_color );
            return $this;
        }

        public function getStart():?string{
            return $this->dateValid() ? $_GET[ 'start' ] : null;
        }

        public function getEnd():?string{
            return $this->dateValid() ? $_GET[ 'end' ] : null;
        }

        private function dateValid():bool{
            return isset( $_GET[ 'start' ], $_GET[ 'end' ] ) &&
                   $this->app->rules->isdate( $_GET[ 'start' ] ) &&
                   $this->app->rules->isdate( $_GET[ 'end' ] ) &&
                  ( strtotime( $_GET[ 'start' ] ) < strtotime( $_GET[ 'end' ] ) ) &&
                  ( strtotime( $_GET[ 'start' ] ) > ( strtotime( $_GET[ 'end' ] ) - 5184000 ) );
        }

        public function getStartEnd( &$start, &$end ): bool{
            $start = $this->getStart();
            $end   = $this->getEnd();

            return ( !empty( $start ) && !empty( $end ) );
        }

        public function obj(): array{
            
            if( $this->init ) {
                $this->app->ajax->calendar('#cal' . $this->id);
            }

            return array( 'url'         => $this->url,
                          'onclick'     => $this->onclick,
                          'onclickmsg'  => $this->onclickmsg,
                          'id'          => $this->id,
                          'init'        => $this->init );
        }

        public function __toString():string{
            return $this->app->view->fetch( '@my/mycalendar.twig', $this->obj() );
        }
    }
