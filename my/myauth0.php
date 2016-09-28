<?php

    use Auth0\SDK\Auth0;

    class myauth0{

	    private $auth0;
    
        public function __construct(){
            $this->app = \Slim\Slim::getInstance();

            if( $this->app->config( 'auth0.driver' ) === 'heroku' ){
                    $this->app->config( 'auth0.domain',       '@AUTH0_DOMAIN' );
                    $this->app->config( 'auth0.clientid',     '@AUTH0_CLIENT_ID' );
                    $this->app->config( 'auth0.clientsecret', '@AUTH0_CLIENT_SECRET' );
                    $this->app->config( 'auth0.redirecturi',  '@AUTH0_CALLBACK_URL' );

            }elseif( $this->app->config( 'auth0.driver' ) === 'fortrabbit' ){
                    $this->app->config( 'auth0.domain',       '@AUTH0_DOMAIN' );
                    $this->app->config( 'auth0.clientid',     '@AUTH0_CLIENT_ID' );
                    $this->app->config( 'auth0.clientsecret', '#AUTH0_CLIENT_SECRET' );
                    $this->app->config( 'auth0.redirecturi',  '@AUTH0_CALLBACK_URL' );
            }

            $this->auth0 = new Auth0( array(
                    'domain'        => $this->app->config( 'auth0.domain' ),
                    'client_id'     => $this->app->config( 'auth0.clientid' ),
                    'client_secret' => $this->app->config( 'auth0.clientsecret' ),
                    'redirect_uri'  => $this->app->config( 'auth0.redirecturi' ),
                    'persist_access_token' => true
                ) );

            $this->app->config( 'auth0.accesstoken', $this->auth0->getAccessToken() );
        }

        public function islogged(){
            $client = $this->auth0->getUserInfo();
		
            if( isset( $client[ 'user_id' ] ) ){

                if( !isset( $client[ 'provider' ] ) )
                    $client[ 'provider' ] = strstr( $client[ 'user_id' ], '|', true );

                // custom properties
                $client[ 'auth0uuid' ] = md5( $client[ 'user_id' ] );
                $client[ 'loginmode' ] = $client[ 'provider' ] == 'auth0' ? 'username & password' : $client[ 'provider' ];

                $this->app->client = $client;
                return true;
            }

            return false;
        }
        
        public function logout(){
            return $this->auth0->logout();
        }
        
        public function getAccessToken(){
            return $this->auth0->getAccessToken();
        }
        
        public function logoutURL( $global = false ){

            $cid = $global ? '' : ( '&client_id=' . $this->app->config( 'auth0.clientid' ) );

//            if( $this->app->client[ 'provider' ] == 'facebook' ){
//                return 'https://' . $this->app->config( 'auth0.domain' ) . '/logout?access_token=' . $this->app->client[ 'identities' ][0][ 'access_token' ] . '&returnTo=' . urlencode( 'https://' . $this->app->config( 'auth0.domain' ). '/logout?returnTo=' . $this->app->config( 'auth0.logouturi' ) . $cid );
//            }else{
                return 'https://' . $this->app->config( 'auth0.domain' ) . '/logout?returnTo=' . $this->app->config( 'auth0.logouturi' ) . $cid;
//            }
        }

    }
