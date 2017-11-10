<?php

    class mychat{

        /** @var mycontainer*/
        private $app;

        private $id;
        private $values;
        private $urlimage;
        private $formname;
        private $message;
        private $transloadit;
        private $wait;
        private $init = false;
        private $cdn;
        private $keyowner;
        private $keydate;
        private $keythumb;
        private $keythumbdefault;
        private $keyme;
        private $buttons = array();
        private $windowid;
        private $pchannel;
        private $pevent;
        private $filestack;
        private $selfid;

        public function __construct( $c ){
            $this->app      = $c;
            $this->init     = true;
        }

        public function & setID( $id ){
            $this->id       = $id;
            $this->windowid = $id . 'box';
            return $this;
        }

        public function getWindowId(){
            return $this->windowid;
        }

        public function & setValues( $values ){
            $this->values = is_array( $values ) ? $values : json_decode( $values, true );

            $this->app->session->set( 'myfwchat' . $this->id . 'f', empty( $this->values ) ? 0 : $this->values[0][ 'id' ] );
            $this->app->session->set( 'myfwchat' . $this->id . 'l', empty( $this->values ) ? 0 : $this->values[count($this->values) - 1][ 'id' ] );
            return $this;
        }

        public function getFirst(){
            return $this->app->session->get( 'myfwchat' . $this->id . 'f', 0 );
        }

        public function getLast(){
            return $this->app->session->get( 'myfwchat' . $this->id . 'l', 0 );
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

        public function & setImage( $key, $width, $height ){
            if( !is_array( $this->message ) )
                $this->message = array();
            $this->message[ 'imgkey' ]    = $key;
            $this->message[ 'imgwidth' ]  = $width;
            $this->message[ 'imgheight' ] = $height;
            return $this;
        }

        public function & setLabel( $label, $depends ){
            if( !is_array( $this->message ) )
                $this->message = array();
            $this->message[ 'labels' ][]  = array( 'label' => $label, 'labeldepends' => $depends );
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

        public function & setThumb( $key, $default = '' ){
            $this->keythumb        = $key;
            $this->keythumbdefault = $default;
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
            return ( isset( $_POST[ 'img' ] ) && is_string( $_POST[ 'img' ] ) && $this->app->rules->md5( $_POST[ 'img' ] ) ) ? $_POST[ 'img' ] : false;
        }

        public function getFilestackImage(){

            if( isset( $_POST[ 'img' ] ) && is_string( $_POST[ 'img' ] ) && strpos( $_POST[ 'img' ], 'https://cdn.filestackcontent.com/' ) === 0 ){

                $json = json_decode( file_get_contents( $this->app->filters->filestack( $_POST[ 'img' ], 'read', 'metadata', false ) ), true );

                if( isset( $json[ 'mimetype' ] ) && strpos( $json[ 'mimetype' ], 'image' ) !== false )
                    return $_POST[ 'img' ];
            }
            
            return false;
        }

        public function getFilestackMovie(){

            if( isset( $_POST[ 'mov' ] ) && is_string( $_POST[ 'mov' ] ) && strpos( $_POST[ 'mov' ], 'https://cdn.filestackcontent.com/' ) === 0 ){

                $json = json_decode( file_get_contents( $this->app->filters->filestack( $_POST[ 'mov' ], 'read', 'metadata', false ) ), true );

                if( isset( $json[ 'mimetype' ] ) && strpos( $json[ 'mimetype' ], 'video' ) !== false )
                    return $_POST[ 'mov' ];
            }

            return false;
        }

        public function addAjax( $values ){
            $this->init   = false;
            $this->values = is_array( $values ) ? $values : json_decode( $values, true );
            $this->app->ajax->append( '#' . $this->id . 'msgs', $this->__toString() )
//                              ->scrollBottom( '#' . $this->id . 'box' )
                              ->val( '#' . $this->id . 'msg', '' );
            $this->app->session->set( 'myfwchat' . $this->id . 'l', empty( $values ) ? 0 : $this->values[count($values) - 1][ 'id' ] );
        }

        public function & setSelfId( $selfid ){
            $this->selfid = $selfid;
            return $this;
        }

        public function & pusherChannel( $channel, $event ){
            $this->pchannel = $channel;
            $this->pevent   = $event;
            return $this;
        }

        public function getPusherChannel(){
            return $this->pchannel;
        }

        public function & pusherSubscribe(){
            $this->app->pusher->ajaxSubscribe( $this->pchannel, $this->pevent, $this->selfid, 'reversed' );
            return $this;
        }
        
        public function & pusherAdd( $values ){
            $this->init   = false;
            $this->values = is_array( $values ) ? $values : json_decode( $values, true );

            $this->app->pusher->chatAdd( '#' . $this->id . 'msgs', $this->__toString(), '#' . $this->id . 'box' )->send( $this->pchannel, $this->pevent );
            return $this;
        }

        public function & ajaxClearTextarea(){
            $this->app->ajax->val( '#' . $this->id . 'msg', '' );
            return $this;
        }

        public function & load( $loadurl ){
            $this->app->ajax->callAction( $loadurl );
            return $this;
        }

        public function & setFilestack( $urlimage, $urlvideo = false ){

            $secret    = $this->app->config[ 'filestack.secret' ];
            $policy    = '{"expiry":' . strtotime( 'first day of next month midnight' ) . ',"call":["pick","store"]}';
            $policy64  = base64_encode( $policy );
            $signature = hash_hmac( 'sha256', $policy64, $secret );
            $security  = "policy:'" . $policy64 . "',signature:'" . $signature . "',";

            $fsoptions = new stdClass();
        
            $location = $this->app->config[ 'filestack.location' ];
            $path     = $this->app->config[ 'filestack.path' ];
        
            if( $location )
                $fsoptions->location = $location;

            if( $path )
                $fsoptions->path = $path;

            $this->filestack[ 'security' ]  = $security;
            $this->filestack[ 'urlimage' ]  = $urlimage;
            $this->filestack[ 'urlvideo' ]  = $urlvideo;
            $this->filestack[ 'fsoptions' ] = $fsoptions;
            return $this;
        }

        public function & setTransloadit( $urlimage, $formname, $options ){

            $driver = $this->app->config[ 'transloadit.driver' ];

            if( $driver === 'heroku' ){
                $apikey    = getenv( 'TRANSLOADIT_AUTH_KEY' );
                $apisecret = getenv( 'TRANSLOADIT_SECRET_KEY' );
            }else{
                $apikey    = $this->app->config[ 'transloadit.k' ];
                $apisecret = $this->app->config[ 'transloadit.s' ];
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

        public function getTransloadit(){
            return is_array( $this->transloadit ) ? 1 : 0;
        }

        public function obj(){
            return array( 'values'      => $this->values,
                          'message'     => $this->message,
                          'transloadit' => $this->transloadit,
                          'filestack'   => $this->filestack,
                          'windowid'    => $this->windowid,
                          'urlimage'    => $this->urlimage,
                          'formname'    => $this->formname,
                          'buttons'     => $this->buttons,
                          'keyowner'    => $this->keyowner,
                          'keydate'     => $this->keydate,
                          'keythumb'    => $this->keythumb,
                          'keythumbdefault' => $this->keythumbdefault,
                          'keyme'       => $this->keyme,
                          'cdn'         => $this->cdn,
                          'id'          => $this->id,
                          'selfid'      => $this->selfid,
                          'init'        => $this->init );
        }
        
        public function __toString(){
            return $this->app->view->fetch( '@my/mychat.twig', $this->obj() );
        }
    }
