<?php

    class mychat{

        private $app;
        private $id;
        private $elements;
        private $urlupdate;
        private $interval;
        private $urlimage;
        private $formname;
        private $message;
        private $currentelementid;
        private $transloadit;
        private $wait;
        private $init;
        private $cdn;
        private $buttons = array();

        public function __construct( $id ){
            $this->app  = \Slim\Slim::getInstance();
            $this->id   = $id;
            $this->init = true;
        }

        public function & setUpdateAjax( $urlupdate, $interval = 4 ){
            $this->urlupdate = $urlupdate;
            $this->interval  = $interval * 1000;
            return $this;
        }
        
        public function & setMessage( $url, $caption = '', $textarea = '' ){
            $this->message = array( 'url' => $url, 'caption' => $caption, 'textarea' => $textarea );
            return $this;
        }

        public function & setWaitingMsg( $thumb, $me = false, $size = 40 ){
            $this->wait = array( 'thumb' => $thumb, 'me' => $me, 'size' => $size );
            return $this;
        }

        public function & addButton( $label, $onclick, $icon = '', $class = '' ){
            $this->buttons[] = array( 'label' => $label, 'icon' => $icon, 'onclick' => $onclick, 'class' => $class );
            return $this;
        }
        
        public function & setCDN( $cdn ){
            $this->cdn = $cdn;
            return $this;
        }

        public function getMessage(){
            return ( isset( $_POST[ 'msg' ] ) && is_string( $_POST[ 'msg' ] ) ) ? $_POST[ 'msg' ] : false;
        }

        public function getImage(){
            return ( isset( $_POST[ 'img' ] ) && is_string( $_POST[ 'img' ] ) && $this->app->rules()->md5( $_POST[ 'img' ] ) ) ? $_POST[ 'img' ] : false;
        }

        public function getCurrentElementId(){
            return $this->app->session()->get( 'myfwchat' . $this->id, false );
        }

        public function updateAjax( $elements, $currentelementid = false ){
            $this->init     = false;
            $this->elements = $elements;
            $this->app->ajax()->append( '#' . $this->id . 'msgs', $this->__toString() )
                              ->scrollBottom( '#' . $this->id . 'box' )
                              ->val( '#' . $this->id . 'msg', '' )
                              ->visibility( '#' . $this->id . 'wait', false );

            if( is_int( $currentelementid ) ){
                $this->app->session()->set( 'myfwchat' . $this->id, $currentelementid );
            }
        }

        public function & setTransloadit( $urlimage, $formname, $options ){

            if( $this->app->config( 'transloadit.driver' ) === 'heroku' ){
                $apikey    = getenv( 'TRANSLOADIT_AUTH_KEY' );
                $apisecret = getenv( 'TRANSLOADIT_SECRET_KEY' );
            }else{
                $apikey    = $this->app->config( 'transloadit.k' );
                $apisecret = $this->app->config( 'transloadit.s' );
            }        

            $options = $options + array( 'template_id' => '', 'width' => 0, 'height' => 0, 'steps' => array() );

            $params = array( 'auth' => array( 'key'     => $apikey,
                                              'expires' => gmdate('Y/m/d H:i:s+00:00', strtotime('+1 hour') ) ) );


            if( !empty( $options[ 'template_id' ] ) )
                $params[ 'template_id' ] = $options[ 'template_id' ];

            if( !empty( $options[ 'steps' ] ) )
                $params[ 'steps' ] = $options[ 'steps' ];

            $params = json_encode( $params, JSON_UNESCAPED_SLASHES );

            $this->transloadit = array( 'params' => $params, 'signature' => hash_hmac( 'sha1', $params, $apisecret ) );
            $this->urlimage    = $urlimage;
            $this->formname    = $formname;
        
            return $this;
        }


        public function obj(){
            
            if( $this->init ){

                if( !empty( $this->urlupdate ) )
                    $this->app->ajax()->interval( $this->urlupdate, $this->interval, 1 ); 

                if( is_int( $this->currentelementid ) )
                    $this->app->session()->set( 'myfwchat' . $id, $this->currentelementid );
            }

            return array( 'elements'    => $this->elements,
                          'transloadit' => $this->transloadit,
                          'urlupdate'   => $this->urlupdate,
                          'urlimage'    => $this->urlimage,
                          'formname'    => $this->formname,
                          'message'     => $this->message,
                          'buttons'     => $this->buttons,
                          'id'          => $this->id,
                          'init'        => $this->init );
        }
        
        public function __toString(){
            return $this->app->render( '@my/mychat', $this->obj(), null, null, APP_CACHEAPC, false, false );
        }
    }
