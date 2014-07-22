<?php

    require_once( __DIR__  . "/3rdparty/hybridauth/Hybrid/Auth.php" );
    require_once( __DIR__  . "/3rdparty/hybridauth/Hybrid/Endpoint.php" ); 

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

        public function onLogout( $callback ){

            if( is_callable( array( $this->hybridauth, 'logoutAllProviders' ) ) && $this->hybridauth->logoutAllProviders() )
                call_user_func( $callback );
        }

        public function process(){
            Hybrid_Endpoint::process();
        }
        
        public function postFacebookWall( &$id, &$post, $message, $picture = "", $link = "", $name = "", $caption = "", $description = "", $privacy = "" ){

            $values = array();
            
            if( is_string( $message ) && strlen( trim( $message ) ) )         $values[ 'message' ] = $message;
            if( is_string( $picture ) && strlen( trim( $picture ) ) )         $values[ 'picture' ] = $picture;
            if( is_string( $link ) && strlen( trim( $link ) ) )               $values[ 'link' ] = $link;
            if( is_string( $name ) && strlen( trim( $name ) ) )               $values[ 'name' ] = $name;
            if( is_string( $caption ) && strlen( trim( $caption ) ) )         $values[ 'caption' ] = $caption;
            if( is_string( $description ) && strlen( trim( $description ) ) ) $values[ 'description' ] = $description;
            if( is_string( $privacy ) && strlen( trim( $privacy ) ) )         $values[ 'privacy' ] = $privacy;
        
            $facebook = $this->hybridauth->authenticate( "Facebook" );
            $post = $facebook->api()->api("/me/feed", "post", $values );
            if( isset( $post[ 'id' ] ) ){
                $id = $post[ 'id' ];
                return true;
            }
            return false;
        }

        public function postTwitterWall( &$id, &$post, $message, $title, $description, $image = false ){

            $twitter = $this->hybridauth->authenticate( "Twitter" );

            if( !$image )
    	    	$post = $twitter->api()->post( 'statuses/update.json', array( 'status' => $message . "\n" . $title . "\n" . $description ) ); 
            else{
                $post = $twitter->api()->post( 'statuses/update_with_media.json', array( 'status' => $message, 'media[]' => file_get_contents( $image ) ) );                 
    
            }
    		// check the last HTTP status code returned
    		if ( $twitter->api()->http_code == 200 && isset( $post->id ) ){
                $id = $post->id;

	    		return true;
		    }

            return false;
        }

        public function getTwitterPost( &$info, $postid ){

            $twitter = $this->hybridauth->authenticate( "Twitter" );
	    	$info = $twitter->api()->get( 'statuses/show.json?id=' . $postid . '&include_entities=true' ); 

    		// check the last HTTP status code returned
    		if ( $twitter->api()->http_code == 200 && isset( $info->id ) ){
                $info = array( 'favorites' => $info->favorite_count,
                               'retweets'  => $info->retweet_count );
	    		return true;
		    }

            return false;
        }

        public function getFacebookPost( $postid ){
        
            $facebook = $this->hybridauth->authenticate( "Facebook" );
            return $facebook->api()->api("/" . $postid);
        }
        
        public function destroySession(){
            $facebook = $this->hybridauth->authenticate( "Facebook" );
            return $facebook->api()->destroySession();
            }

        public function getFacebookPostInfo( $postid ){

            $facebook = $this->hybridauth->authenticate( "Facebook" );
            $info = $facebook->api()->api( array( 'method' => 'fql.query', 'query' => 'SELECT like_info.like_count, comment_info.comment_count, share_count FROM stream WHERE post_id = "' . $postid . '"' ) );        
        
            if( isset( $info[0] ) && is_array( $info[0] ) ){
            
                $info = array( 'likes'    => intval( $info[0]['like_info']['like_count'] ),
                               'comments' => intval( $info[0]['comment_info']['comment_count'] ),
                               'shares'   => intval( $info[0]['share_count'] ) );
                return $info;
            }
            return false;
        }


        public function postFacebookPage( $pageid, $pageaccesstoken, $message ){
            $facebook = $this->hybridauth->authenticate( "Facebook" );
            return $facebook->api()->api( "/" . $pageid . "/feed", 'POST', array( 'access_token' => $pageaccesstoken, "message" => $message ) );
        }

        public function getFacebookPages(){
             $facebook = $this->hybridauth->authenticate( "Facebook" );
             return $facebook->api()->api('/me/accounts');
        }

    }
