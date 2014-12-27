<?php

    use Auth0SDK\Auth0;

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
                    'redirect_uri'  => getenv( 'AUTH0_CALLBACK_URL' )
                );
            }else{
                $this->params = array(
                    'domain'        => $this->app->config( 'auth0.domain' ),
                    'client_id'     => $this->app->config( 'auth0.clientid' ),
                    'client_secret' => $this->app->config( 'auth0.clientsecret' ),
                    'redirect_uri'  => $this->app->config( 'auth0.redirecturi' )
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

                // custom properties
                $client[ 'provider' ] = strstr( $client[ 'user_id' ], '|', true );
                $client[ 'uuid' ]     = md5( $client[ 'user_id' ] );
                $client[ 'fullname' ] = isset( $client[ 'nickname' ] ) ? $client[ 'nickname' ] : ( $client[ 'given_name' ] . ' ' . $app->client[ 'family_name' ] );
                
                if( $client[ 'provider' ] == 'auth0' )
                    $client[ 'provider' ] = 'user';
                
                $this->app->client = $client;
                return true;
            }

            return false;
        }
        
        public function logout(){
            return $this->auth0->logout();
        }
        
        public function logoutURL(){

            if( $this->app->client[ 'provider' ] == 'facebook' ){
                return 'https://' . $this->params[ 'domain' ]. '/logout?returnTo=' . urlencode( 'https://' . $this->params[ 'domain' ]. '/logout?returnTo=' . $this->app->config( 'auth0.logouturi' ) ) . '&access_token=' . $this->app->client[ 'identities' ][0][ 'access_token' ];
            }else{
                return 'https://' . $this->params[ 'domain' ]. '/logout?returnTo=' . $this->app->config( 'auth0.logouturi' );
            }
        }

    }
?>
