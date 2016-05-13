<?php

    class mychat{

        private $app;
        private $id;
        private $values;
        private $urlimage;
        private $formname;
        private $message;
        private $transloadit;
        private $wait;
        private $init;
        private $cdn;
        private $keyowner;
        private $keydate;
        private $keythumb;
        private $keythumbcdn;
        private $keyme;
        private $buttons = array();
        private $windowid;
        private $channel;

        public function __construct( $id ){
            $this->app  = \Slim\Slim::getInstance();
            $this->id   = $id;
            $this->init = true;
            $this->windowid = $id . 'box';
        }

        public function getWindowId(){
            return $this->windowid;
        }

        public function & setValues( $values ){
            $this->values = is_array( $values ) ? $values : json_decode( $values, true );

            $this->app->session()->set( 'myfwchat' . $this->id . 'f', empty( $this->values ) ? 0 : $this->values[0][ 'id' ] );
            $this->app->session()->set( 'myfwchat' . $this->id . 'l', empty( $this->values ) ? 0 : $this->values[count($this->values) - 1][ 'id' ] );
            return $this;
        }

        public function getFirst(){
            return $this->app->session()->get( 'myfwchat' . $this->id . 'f', 0 );
        }

        public function getLast(){
            return $this->app->session()->get( 'myfwchat' . $this->id . 'l', 0 );
        }

        public function & setMessage( $url, $key, $caption = '', $textarea = '' ){
            if( !is_array( $this->message ) )
                $this->message = array();
            $this->message[ 'url' ]      = $url;
            $this->message[ 'key' ]      = $key;
            $this->message[ 'caption' ]  = $caption;
            $this->message[ 'textarea' ] = $textarea;
            return $this;
        }

        public function & setImage( $key, $width = '', $height = ''){
            if( !is_array( $this->message ) )
                $this->message = array();
            $this->message[ 'imgkey' ]    = $key;
            $this->message[ 'imgwidth' ]  = $width;
            $this->message[ 'imgheight' ] = $height;
            return $this;
        }

        public function & setMovie( $keyThumb, $keyMp4, $keyWebM, $keyOgg, $keyWidth, $keyHeight ){
            if( !is_array( $this->message ) )
                $this->message = array();
            $this->message[ 'moviekeythumb' ]  = $keyThumb;
            $this->message[ 'moviekeymp4' ]    = $keyMp4;
            $this->message[ 'moviekeywebm' ]   = $keyWebM;
            $this->message[ 'moviekeyogg' ]    = $keyOgg;
            $this->message[ 'moviekeywidth' ]  = $keyWidth;
            $this->message[ 'moviekeyheight' ] = $keyHeight;
            return $this;
        }

        public function & setAttribution( $keyowner, $keydate ){
            $this->keyowner = $keyowner;
            $this->keydate  = $keydate;
            return $this;
        }

        public function & setThumb( $key, $cdn = '' ){
            $this->keythumb    = $key;
            $this->keythumbcdn = $cdn;
            return $this;
        }

        public function & setMe( $key ){
            $this->keyme = $key;
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

        public function addAjax( $values ){
            $this->init   = false;
            $this->values = $values;
            $this->app->ajax()->append( '#' . $this->id . 'msgs', $this->__toString() )
                              ->scrollBottom( '#' . $this->id . 'box' )
                              ->val( '#' . $this->id . 'msg', '' );
            $this->app->session()->set( 'myfwchat' . $this->id . 'l', empty( $values ) ? 0 : $this->values[count($values) - 1][ 'id' ] );
        }

        public function & pusher( $channel, $event ){
            $this->app->ajax()->pusher( $this->app->pusher()->getPKey(), $channel, $event, '#' . $this->id . 'msgs', true, $this->app->pusher()->getPCluster(), '#' . $this->id . 'box' );
            $this->channel = $channel;
            return $this;
        }
        
        public function getChannel(){
            return $this->channel;
        }
        
        public function & pusherAdd( $channel, $event, $values ){
            $this->init   = false;
            $this->values = $values;

            $this->app->pusher()->trigger( $channel, $event, $this->app->ajax()->filter( $this->__toString() ) );
            return $this;
        }

        public function & ajaxClearTextarea(){
            $this->app->ajax()->val( '#' . $this->id . 'msg', '' );
            return $this;
        }

        public function & setTransloadit( $urlimage, $formname, $options ){

            $driver = $this->app->config( 'transloadit.driver' );

            if( $driver === 'heroku' ){
                $apikey    = getenv( 'TRANSLOADIT_AUTH_KEY' );
                $apisecret = getenv( 'TRANSLOADIT_SECRET_KEY' );
            }elseif( $driver === 'fortrabbit' ){
                $apikey    = getenv( 'TRANSLOADIT_AUTH_KEY' );
                $apisecret = $this->app->configdecrypt( getenv( 'TRANSLOADIT_SECRET_KEY' ) );
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

            if( isset( $options[ 'fields' ] ) )
                $params[ 'fields' ] = $options[ 'fields' ];

            $params = json_encode( $params, JSON_UNESCAPED_SLASHES );

            $this->transloadit = array( 'params' => $params, 'signature' => hash_hmac( 'sha1', $params, $apisecret ) );
            $this->urlimage    = $urlimage;
            $this->formname    = $formname;
        
            return $this;
        }


        public function obj(){
            return array( 'values'      => $this->values,
                          'message'     => $this->message,
                          'transloadit' => $this->transloadit,
                          'windowid'    => $this->windowid,
                          'urlimage'    => $this->urlimage,
                          'formname'    => $this->formname,
                          'message'     => $this->message,
                          'buttons'     => $this->buttons,
                          'keyowner'    => $this->keyowner,
                          'keydate'     => $this->keydate,
                          'keythumb'    => $this->keythumb,
                          'keythumbcdn' => $this->keythumbcdn,
                          'keyme'       => $this->keyme,
                          'cdn'         => $this->cdn,
                          'id'          => $this->id,
                          'init'        => $this->init );
        }
        
        public function __toString(){
            return $this->app->render( '@my/mychat', $this->obj(), null, null, 0, false, false );
        }
    }