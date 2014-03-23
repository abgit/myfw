<?php


    class myoneall{

        private $token = null;
        private $profile = array();
        private $auth = false;

        public function __construct(){
            $this->app        = \Slim\Slim::getInstance();
            $this->domain     = $this->app->config( 'oneall.d' );
            $this->keypublic  = $this->app->config( 'oneall.public' );
            $this->keyprivate = $this->app->config( 'oneall.private' );
            $this->requestInfo();
        }

        public function requestInfo(){

            // check if we are on login
            if( isset( $_POST['connection_token'] ) ){

                // check if we already have a token. webservice call already made
                if( !is_null( $this->token ) )
                    return;
        
                $this->token = $_POST['connection_token'];

                if( $this->callapi( $this->profile, '/connections/' . $this->token . '.json' ) ){
                    $this->token   = $this->profile->result->data->user->user_token;
                    $this->tokenid = $this->profile->result->data->user->identity->identity_token;
                    $this->auth    = ( ( $this->profile->result->data->plugin->key == 'social_login' || $this->profile->result->data->plugin->key == 'single_sign_on' ) && $this->profile->result->status->code === 200 );
                    $this->app->session()->set( 'onealltoken',   $this->token );
                    $this->app->session()->set( 'onealltokenid', $this->tokenid );
                    $this->app->session()->set( 'oneallprofile', $this->profile );
                    $this->app->session()->set( 'oneallauth',    $this->auth );
                }
            }else{
                $this->token   = $this->app->session()->get( 'onealltoken',   null );
                $this->tokenid = $this->app->session()->get( 'onealltokenid', false );
                $this->profile = $this->app->session()->get( 'oneallprofile', array() );
                $this->auth    = $this->app->session()->get( 'oneallauth',    false );
            }
        }
        
        public function link(){
            return ( $this->profile->result->data->plugin->key === 'social_link' && $this->profile->result->data->plugin->data->status === 'success' && isset( $this->profile->result->data->plugin->data->action ) && $this->profile->result->data->plugin->data->action === 'link_identity' );
        }

        private function callapi( &$result, $uri, $post = null ){

            //Setup connection
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, 'https://' . $this->domain . '.api.oneall.com' . $uri );
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_USERPWD, $this->keypublic . ":" . $this->keyprivate );
            curl_setopt($curl, CURLOPT_TIMEOUT, 15);
            curl_setopt($curl, CURLOPT_VERBOSE, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($curl, CURLOPT_FAILONERROR, 0);
 
            // check if post
            if( !is_null( $post ) ){
                $post = json_encode( $post );
                
    			if( is_array( $post ) ){
    				$post_values = array ();
	    			foreach( $post as $key => $value ){
    					$post_values[] = $key . '=' . urlencode( $value );
                    }
		    		$post_value = implode( "&", $post_values );
    			}else{
				    $post_value = trim( $post );
	    		}

    			if( !empty( $post_value ) ){
                    curl_setopt( $curl, CURLOPT_POST, 1 );
                    curl_setopt( $curl, CURLOPT_POSTFIELDS, $post_value );
			    }
		    }

            //Send request
            $result_json = curl_exec($curl);

            curl_close($curl);           

            if( $result_json ){

                //Decode
                $json = json_decode($result_json);

                if( isset( $json->response ) ){
                    $result = $json->response;

                    return true;
                }
            }

            return false; 
        }

        public function islogged(){
            return $this->auth === true;
        }
        
        public function getProfile(){

            return array( 'userid'        => isset( $this->profile->result->data->user->user_token ) ? $this->profile->result->data->user->user_token : false,
                          'provider'      => isset( $this->profile->result->data->user->identity->provider ) ? $this->profile->result->data->user->identity->provider : false,
                          'fullname'      => isset( $this->profile->result->data->user->identity->displayName ) ? $this->profile->result->data->user->identity->displayName : false,
                          'username'      => isset( $this->profile->result->data->user->identity->preferredUsername ) ? $this->profile->result->data->user->identity->preferredUsername : false,
                          'email'         => isset( $this->profile->result->data->user->identity->emails[0]->value ) ? $this->profile->result->data->user->identity->emails[0]->value : false,
                          'emailverified' => isset( $this->profile->result->data->user->identity->emails[0]->is_verified ) ? $this->profile->result->data->user->identity->emails[0]->is_verified : false,
                          'gender'        => isset( $this->profile->result->data->user->identity->gender ) ? $this->profile->result->data->user->identity->gender : false,
                          'birthdate'     => isset( $this->profile->result->data->user->identity->birthday ) ? $this->profile->result->data->user->identity->birthday : false,
                          'location'      => isset( $this->profile->result->data->user->identity->currentLocation ) ? $this->profile->result->data->user->identity->currentLocation : false
                          );
        }
        
        public function getInfo(){
            return $this->profile;
        }

        public function onLogged( $callback ){
            if( $this->islogged() )
                return call_user_func( $callback );
        }

        public function post( &$result, $text, $picture, $url, $urltext, $caption, $description, $track = 1 ){

            $message_structure = array (
                'request' => array (
                    'sharing_message' => array (
                        'parts' => array (
                            'text' => array (
                                'body' => $text
                            ),
                            'picture' => array (
                                'url' => $picture
                            ),
                            'flags' => array (
                                'enable_tracking' => $track
                            ),
                            'link' => array (
                                'url' => $url,
                                'name' => $urltext,
                                'caption' => $caption,
                                'description' => $description
                            ),
                        ),
                        'publish_for_user' => array (
                            'user_token' => $this->token,
                            'providers' => 'facebook'
                        )
                    )
                )
            );

            $this->callapi( $result, '/sharing/messages.json', $message_structure );
            return ( isset( $result->request->status->code ) && $result->request->status->code === 200 && isset( $result->result->data->sharing_message->publications->entries[0]->status->code ) && $result->result->data->sharing_message->publications->entries[0]->status->code === 200 );
        }

        public function getPosts(){
            $obj = new LoginRadiusPosts($this->app->config( 'loginradius.s' ), $this->token );
            return $obj->loginradius_get_posts();
        }
    }
