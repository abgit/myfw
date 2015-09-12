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

        public function __construct( $id = null ){
            $this->app  = \Slim\Slim::getInstance();
            $this->id   = $id;
            $this->init = true;
        }

        public function & setID( $id ){
            $this->id = $id;
            return $this;
        }

        public function & setOnClick( $onclick, $onclickloadingmsg = '' ){
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

            foreach( is_array( $values ) ? $values : json_decode( $values, true ) as $x ){
                $val = array( 'id'    => $x[ $this->keyid ],
                              'title' => $this->addonpre . $x[ $this->keytitle ] . $this->addonpos,
                              'start' => $x[ $this->keydate ] );

                if( !is_null( $this->keycolor ) && isset( $x[ $this->keycolor ] ) )
                    $val[ 'color' ] = empty( $this->colorfilter ) ? $x[ $this->keycolor ] : $this->app->filters()->replaceonly( $x[ $this->keycolor ], $this->colorfilter );

                $this->values[] = $val;
            };
            return $this;
        }


        public function obj(){
            
            if( $this->init )
                $this->app->ajax()->calendar( '#cal' . $this->id );

            return array( 'values'      => json_encode($this->values, JSON_UNESCAPED_SLASHES ),
                          'onclick'     => $this->onclick,
                          'onclickmsg'  => $this->onclickmsg,
                          'id'          => $this->id,
                          'init'        => $this->init );
        }
        
        public function __toString(){
            return $this->app->render( '@my/mycalendar', $this->obj(), null, null, APP_CACHEAPC, false, false );
        }
    }
