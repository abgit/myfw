<?php

    use Auth0\SDK\Auth0;

    class myauth0{

	    private $auth0;
        private $params;
    
        public function __construct(){
            $this->app = \Slim\Slim::getInstance();

            if( $this->app->config( 'auth0.driver' ) === 'heroku' ){
                $this->params = array(
                    'domain'        => getenv( 'AUTH0_DOMAIN' ),
                    'client_id'     => getenv( 'AUTH0_CLIENT_ID' ),
                    'client_secret' => getenv( 'AUTH0_CLIENT_SECRET' ),
                    'redirect_uri'  => getenv( 'AUTH0_CALLBACK_URL' ),
                    'persist_access_token' => true
                );
            }elseif( $this->app->config( 'auth0.driver' ) === 'fortrabbit' ){
                $this->params = array(
                    'domain'        => getenv( 'AUTH0_DOMAIN' ),
                    'client_id'     => getenv( 'AUTH0_CLIENT_ID' ),
                    'client_secret' => $this->app->configdecrypt( getenv( 'AUTH0_CLIENT_SECRET' ) ),
                    'redirect_uri'  => getenv( 'AUTH0_CALLBACK_URL' ),
                    'persist_access_token' => true
                );
            }else{
                $this->params = array(
                    'domain'        => $this->app->config( 'auth0.domain' ),
                    'client_id'     => $this->app->config( 'auth0.clientid' ),
                    'client_secret' => $this->app->config( 'auth0.clientsecret' ),
                    'redirect_uri'  => $this->app->config( 'auth0.redirecturi' ),
                    'persist_access_token' => true
                );
            }

            $this->auth0 = new Auth0( $this->params );
        }

        public function getParams( $key ){
            return isset( $this->params[ $key ] ) ? $this->params[ $key ] : '';
        }

        public function islogged(){
            $client = $this->auth0->getUserInfo();
		
            if( isset( $client[ 'user_id' ] ) ){

                if( !isset( $client[ 'provider' ] ) )
                    $client[ 'provider' ] = strstr( $client[ 'user_id' ], '|', true );

                // custom properties
                $client[ 'uuid' ]      = md5( $client[ 'user_id' ] );
                $client[ 'fullname' ]  = isset( $client[ 'nickname' ] ) ? $client[ 'nickname' ] : ( $client[ 'given_name' ] . ' ' . $app->client[ 'family_name' ] );
                $client[ 'icon' ]      = $client[ 'provider' ] == 'auth0' ? 'user' : $client[ 'provider' ];
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
        
        public function logoutURL(){

            if( $this->app->client[ 'provider' ] == 'facebook' ){
                return 'https://' . $this->params[ 'domain' ] . '/logout?access_token=' . $this->app->client[ 'identities' ][0][ 'access_token' ] . '&returnTo=' . urlencode( 'https://' . $this->params[ 'domain' ]. '/logout?returnTo=' . $this->app->config( 'auth0.logouturi' ) );
            }else{
                return 'https://' . $this->params[ 'domain' ] . '/logout?returnTo=' . $this->app->config( 'auth0.logouturi' );
            }
        }

    }
?>
