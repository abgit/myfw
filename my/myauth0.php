<?php

    use Auth0\SDK\API\Authentication;
    use Auth0\SDK\API\Management;
    use Auth0\SDK\Store\SessionStore;


    class myauth0{

	    private $auth0;
    
        public function __construct(){
            $this->app = \Slim\Slim::getInstance();

            if( $this->app->config( 'auth0.driver' ) === 'heroku' ){
                    $this->app->config( 'auth0.domain',       '@AUTH0_DOMAIN' );
                    $this->app->config( 'auth0.clientid',     '@AUTH0_CLIENT_ID' );
                    $this->app->config( 'auth0.clientsecret', '@AUTH0_CLIENT_SECRET' );
                    $this->app->config( 'auth0.redirecturi',  '@AUTH0_CALLBACK_URL' );
                    $this->app->config( 'auth0.apptoken',     '@AUTH0_APPTOKEN' );

            }elseif( $this->app->config( 'auth0.driver' ) === 'fortrabbit' ){
                    $this->app->config( 'auth0.domain',       '@AUTH0_DOMAIN' );
                    $this->app->config( 'auth0.clientid',     '@AUTH0_CLIENT_ID' );
                    $this->app->config( 'auth0.clientsecret', '#AUTH0_CLIENT_SECRET' );
                    $this->app->config( 'auth0.redirecturi',  '@AUTH0_CALLBACK_URL' );
                    $this->app->config( 'auth0.apptoken',     '@AUTH0_APPTOKEN' );
            }

        }

        private function init( $persist = true ){

            $auth0 = new Authentication( $this->app->config( 'auth0.domain' ), $this->app->config( 'auth0.clientid' ) );

            if( $persist )
                $this->auth0 = $auth0->get_oauth_client( $this->app->config( 'auth0.clientsecret' ), $this->app->config( 'auth0.redirecturi' ), [
                              'persist_id_token' => true,
                              'persist_refresh_token' => true,
                               ]);
            else
                $this->auth0 = $auth0->get_oauth_client( $this->app->config( 'auth0.clientsecret' ), $this->app->config( 'auth0.redirecturi' ), [
                              'persist_id_token' => false,
                              'persist_refresh_token' => false,
                              'persist_user' => false,
                              'store' => false
                              ]);

//$userInfo = $auth0Oauth->getUser();
        }

        public function islogged(){

            $this->init();

            $client = ( new SessionStore() )->get('user');

            if( !$client )
                $client = $this->auth0->getUser();
		
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

            $this->init();

            return $this->auth0->logout();
        }
        
        public function link(){

            $store = new SessionStore();
            $user  = $store->get('user');

            $this->init( false );

            $link = $this->auth0->getUser();

            if( $link && isset( $user["user_id"] ) && isset( $link["user_id"] ) && isset( $link["identities"][0]["provider"] ) && isset( $link["identities"][0]["user_id"] ) ){
                 $auth0Api = new Management( $this->app->config( 'auth0.apptoken' ), $this->app->config( 'auth0.domain' ) );
                 $response = $auth0Api->users->linkAccount( $user["user_id"], array( "provider" => $link["identities"][0]["provider"], "user_id" => $link["identities"][0]["user_id"] ) );

                 $store = new SessionStore();
                 $user  = $store->set('user', $auth0Api->users->get( $user["user_id"] ) );

                 return $response;
            }
            
            return false;
        }

        public function unlinkprovider( $provider, $provideruserid ){

            $store = new SessionStore();
            $user  = $store->get('user');

            $auth0Api = new Management( $this->app->config( 'auth0.apptoken' ), $this->app->config( 'auth0.domain' ) );
            $response = $auth0Api->users->unlinkAccount( $user["user_id"], $provider, $provideruserid );

            $store = new SessionStore();
            $user  = $store->set('user', $auth0Api->users->get( $user["user_id"] ) );

            return $response;
        }

        public function unlinkproviderid( $id ){

            $store = new SessionStore();
            $user  = $store->get('user');

            if( isset( $user[ 'identities' ] ) && is_array( $user[ 'identities' ] ) && $id > 0 && isset( $user[ 'identities' ][ $id ] ) ){
                try{
                    $this->unlinkprovider( $user[ 'identities' ][ $id ][ 'provider' ], $user[ 'identities' ][ $id ][ 'user_id' ] );
                    return true;
                }catch( Exception $e ){
                }
            }
    
            return false;
        }

        public function getConnection( $provider ){

            $store = new SessionStore();
            $user  = $store->get('user');

            if( isset( $user[ 'identities' ] ) && is_array( $user[ 'identities' ] ) )
                foreach( $user[ 'identities' ] as $arr )
                    if( isset( $arr[ 'provider' ] ) && $arr[ 'provider' ] === $provider )
                        return $arr[ 'connection' ];

            return '';
        }

        public function getSocial( $provider ){

            $store = new SessionStore();
            $user  = $store->get('user');

            if( isset( $user[ 'identities' ] ) && is_array( $user[ 'identities' ] ) )
                foreach( $user[ 'identities' ] as $arr )
                    if( isset( $arr[ 'provider' ] ) && $arr[ 'provider' ] === $provider )
                        return true;

            return false;
        }

        public function getSocialProviders(){

//            $providers = array();
            $store = new SessionStore();
            $user  = $store->get('user');

            if( isset( $user[ 'identities' ] ) && is_array( $user[ 'identities' ] ) )
                return $user[ 'identities' ];
//                foreach( $user[ 'identities' ] as $arr )
//                    if( isset( $arr[ 'provider' ] ) )
//                        $providers[] = array( 'provider' => $arr[ 'provider' ], 'connection' => $arr[ 'connection' ], 'user_id' => $arr[ 'user_id' ] );

            return array();
        }
        
        public function logoutURL( $global = false ){

            return 'https://' . $this->app->config( 'auth0.domain' ) . '/logout?returnTo=' . $this->app->config( 'auth0.logouturi' ) . ( $global ? '' : ( '&client_id=' . $this->app->config( 'auth0.clientid' ) ) );
        }

    }
