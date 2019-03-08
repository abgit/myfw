<?php

    class mychat{

        /** @var mycontainer*/
        private $app;

        private $id;
        private $values;
        private $urlimage;
        private $formname;
        private $message;
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
        private $filestackimage;
        private $filestackvideo;
        private $selfid;
        private $url;
        private $textarea;
        private $uploadcare;
        public $onInit = null;
        public $onRefresh = null;
        public $onAddMessage = null;

        public function __construct( $c ){
            $this->app      = $c;
            $this->init     = true;
        }

        public function & setName( $id ){
            $this->id       = $id;
            $this->windowid = $id . 'box';
            return $this;
        }

        public function & setUrl( $url ){
            $this->url = $url;
            return $this;
        }

        public function init(){
            call_user_func($this->onInit);
            return $this;
        }

        public function refresh(){
            call_user_func($this->onRefresh);
            return $this;
        }

        public function addMessage(){
            call_user_func($this->onAddMessage);
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

        public function & setTextarea( $help = 'Enter your message...' ){
            $this->textarea = array( 'help' => $help );
            return $this;
        }

        public function & setMessage( $key, $caption = '', $textarea = '' ){
            if( !is_array( $this->message ) )
                $this->message = array();
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

            $this->app->pusher->chatAdd( '#' . $this->id . 'msgs', $this->__toString(), '#' . $this->id . 'box' )
                              ->send( $this->pchannel, $this->pevent );
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

        public function & setFilestackImage( $urlimage, $picker_options ){

            if( isset( $this->app[ 'filestack.options'] ) )
                $picker_options = $picker_options + $this->app[ 'filestack.options'];

            $this->filestackimage[ 'urlimage' ]  = $urlimage;
            $this->filestackimage[ 'fsoptions' ] = json_encode( $picker_options, JSON_HEX_APOS );
            return $this;
        }

        public function & setFilestackVideo( $urlvideo, $picker_options ){

            if( isset( $this->app[ 'filestack.options'] ) )
                $picker_options = $picker_options + $this->app[ 'filestack.options'];

            $this->filestackvideo[ 'urlvideo' ]  = $urlvideo;
            $this->filestackvideo[ 'fsoptions' ] = json_encode( $picker_options, JSON_HEX_APOS );
            return $this;
        }

        public function & addFroala( $froalaoptions = array(), $uploadcareoptions = array() ){

            if( isset( $this->app[ 'froala.options' ] ) )
                $froalaoptions = $froalaoptions + $this->app[ 'froala.options'];

            if( isset( $this->app[ 'uploadcare.options'] ) )
                $uploadcareoptions = $uploadcareoptions + $this->app[ 'uploadcare.options'];

            $processing = $this->app->urlfor->action( 'myfwuploadcare', array( 'fsid' => $this->id . 'msg' ) );

            $this->uploadcare = array( 'processing' => $processing, 'froalaoptions' => json_encode( $froalaoptions, JSON_HEX_APOS ), 'uploadcareoptions' => json_encode( $uploadcareoptions, JSON_HEX_APOS ) );

            $this->app->ajax->froala( $this->id . 'msg' );

            return $this;
        }


        public function obj(){
            return array( 'values'          => $this->values,
                          'message'         => $this->message,
                          'filestackimage'  => $this->filestackimage,
                          'filestackvideo'  => $this->filestackvideo,
                          'windowid'        => $this->windowid,
                          'urlimage'        => $this->urlimage,
                          'formname'        => $this->formname,
                          'buttons'         => $this->buttons,
                          'keyowner'        => $this->keyowner,
                          'keydate'         => $this->keydate,
                          'keythumb'        => $this->keythumb,
                          'keythumbdefault' => $this->keythumbdefault,
                          'keyme'           => $this->keyme,
                          'cdn'             => $this->cdn,
                          'id'              => $this->id,
                          'selfid'          => $this->selfid,
                          'url'             => $this->url,
                          'textarea'        => $this->textarea,
                          'uploadcare'      => $this->uploadcare,
                          'init'            => $this->init );
        }
        
        public function __toString(){
            return $this->app->view->fetch( '@my/mychat.twig', $this->obj() );
        }
    }
