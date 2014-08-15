<?php

    require_once( __DIR__  . "/3rdparty/Hybrid/Auth.php" );
    require_once( __DIR__  . "/3rdparty/Hybrid/Endpoint.php" ); 

    class myauth{

        public function __construct(){
            $this->app        = \Slim\Slim::getInstance();
            $this->hybridauth = new Hybrid_Auth( array( "base_url"  => $this->app->request->getScheme() . '://' . $this->app->request->getHost() . $this->app->urlFor( $this->app->config( 'auth.callname' ) ),
                                                        "providers" => $this->app->config( 'auth.providers' ) ) );
  
            if( $sessiondata = $this->app->session()->get( 'auth', false ) && !empty( $sessiondata ) )
                $this->hybridauth->restoreSessionData( $sessiondata );
        }

        public function onLogged( $prov, $callback ){

            $adapter = $this->hybridauth->authenticate( $prov );

            $this->app->session()->set( 'auth', $this->hybridauth->getSessionData() );

            if( is_callable( array( $adapter, 'isUserConnected' ) ) && $adapter->isUserConnected() )
                call_user_func( $callback, (array) $adapter->getUserProfile() );
        }

        public function logout(){
            return $this->hybridauth->logoutAllProviders();
        }

        public function process(){
            Hybrid_Endpoint::process();
        }

        // posts
        public function postFacebookWall( &$id, &$post, $message, $picture = "", $link = "", $name = "", $caption = "", $description = "", $privacy = "" ){

            $values = array();
            
            if( is_string( $message ) && strlen( trim( $message ) ) )         $values[ 'message' ] = $message;
            if( is_string( $picture ) && strlen( trim( $picture ) ) )         $values[ 'picture' ] = $picture;
            if( is_string( $link ) && strlen( trim( $link ) ) )               $values[ 'link' ] = $link;
            if( is_string( $name ) && strlen( trim( $name ) ) )               $values[ 'name' ] = $name;
            if( is_string( $caption ) && strlen( trim( $caption ) ) )         $values[ 'caption' ] = $caption;
            if( is_string( $description ) && strlen( trim( $description ) ) ) $values[ 'description' ] = $description;
            if( is_string( $privacy ) && strlen( trim( $privacy ) ) )         $values[ 'privacy' ] = $privacy;
        
            $post = $this->hybridauth->authenticate( "Facebook" )->setUserStatus( $values );
            if( isset( $post[ 'id' ] ) ){
                $id = $post[ 'id' ];
                return true;
            }
            return false;
        }

        public function postTwitterWall( $status ){
            return $this->hybridauth->authenticate( "Twitter" )->setUserStatus( $status );
        }

        // contacts
        public function getFacebookContacts(){
            return $this->hybridauth->authenticate( "Facebook" )->getUserContacts();
        }

        public function getTwitterContacts(){
            return $this->hybridauth->authenticate( "Twitter" )->getUserContacts();
        }

        // posts
        public function getFacebookPost( $postid ){
            return $this->hybridauth->authenticate( "Facebook" )->getUserStatus( $postid );
        }

        public function getTwitterPost( $postid ){
            return $this->hybridauth->authenticate( "Twitter" )->getUserStatus( $postid );
        }

        // pages
        public function postFacebookPage( $message, $pageid ){
            return $this->hybridauth->authenticate( "Facebook" )->setUserStatus( $message, $pageid );
        }

        public function getFacebookPages(){
            return $this->hybridauth->authenticate( "Facebook" )->getUserPages(true);
        }

    }
