<?php

    use Auth0\SDK\API\Authentication;
    use Auth0\SDK\API\Oauth2Client;
    use Auth0\SDK\API\Management;
    use Auth0\SDK\Store\SessionStore;

    /**
     * @property string $uuid
     */
    class myauth0 implements arrayaccess{

        /** @var mycontainer */
        private $app;

        /** @var Oauth2Client  */
        private $auth0;

        private $domain;
	    private $clientid;
        private $clientsecret;
        private $redirecturi;
        private $apptoken;
        private $logouturi;
        private $linkurl;

        // TODO: add /auth0/callback middleware
        public function __construct( $c ){
            $this->app = $c;
            $this->domain       = $this->app->config[ 'auth0.domain' ];
            $this->clientid     = $this->app->config[ 'auth0.clientid' ];
            $this->clientsecret = $this->app->config[ 'auth0.clientsecret' ];
            $this->redirecturi  = $this->app->config[ 'auth0.redirecturi' ];
            $this->apptoken     = $this->app->config[ 'auth0.apptoken' ];
            $this->logouturi    = $this->app->config[ 'auth0.logouturi' ];
            $this->linkurl      = $this->app->config[ 'auth0.linkurl' ];
        }

        private function init( $persist = true ){

            if( $persist )
                $options = [ 'persist_id_token'      => true,
                             'persist_refresh_token' => true ];
            else
                $options = [ 'persist_id_token'      => false,
                             'persist_refresh_token' => false,
                             'persist_user'          => false,
                             'store'                 => false ];

            $obj = new Authentication( $this->domain, $this->clientid );
            $this->auth0 = $obj->get_oauth_client( $this->clientsecret, $this->redirecturi, $options );
        }

        public function islogged(){

            $this->init();

            $client = ( new SessionStore() );
            $client = $client->get('user');

            if( !$client )
                $client = $this->auth0->getUser();
		
            if( isset( $client[ 'user_id' ] ) ){

                if( !isset( $client[ 'provider' ] ) )
                    $client[ 'provider' ] = strstr( $client[ 'user_id' ], '|', true );

                // custom properties
                $client[ 'auth0uuid' ] = md5( $client[ 'user_id' ] );
                $client[ 'loginmode' ] = $client[ 'provider' ] == 'auth0' ? 'username & password' : $client[ 'provider' ];

                $this->app->session->myauth0 = $client;

                return true;
            }

            return false;
        }

        public function client(){
            return isset( $this->app->session->myauth0 ) && is_array( $this->app->session->myauth0 ) ? $this->app->session->myauth0 : array();
        }

        public function offsetGet( $setting ) {

            if( isset( $this->app->session->myauth0[ $setting ] ) )
                return $this->app->session->myauth0[ $setting ];

            return null;
        }

        public function offsetSet( $offset, $value ) {

            $client = $this->app->session->myauth0;

            $client[$offset] = $value;

            $this->app->session->myauth0 = $client;
        }

        public function offsetExists( $offset ) {
            return isset($this->app->session->myauth0[ $offset ] );
        }

        public function offsetUnset( $offset ) {

            $client = $this->app->session->myauth0;

            unset( $client[$offset] );

            $this->app->session->myauth0 = $client;
        }

        public function logout(){

            $this->app->session->destroy();

            $this->init();

            $this->auth0->logout();
        }
        
        public function link(){

            $store = new SessionStore();
            $user  = $store->get('user');

            $this->init( false );

            $link = $this->auth0->getUser();

            if( $link && isset( $user["user_id"] ) && isset( $link["user_id"] ) && isset( $link["identities"][0]["provider"] ) && isset( $link["identities"][0]["user_id"] ) ){
                 $auth0Api = new Management( $this->apptoken, $this->domain );
                 $response = $auth0Api->users->linkAccount( $user["user_id"], array( "provider" => $link["identities"][0]["provider"], "user_id" => $link["identities"][0]["user_id"] ) );

                 $store = new SessionStore();
                 $store->set('user', $auth0Api->users->get( $user["user_id"] ) );

                 return $response;
            }
            
            return false;
        }

        public function unlinkprovider( $provider, $provideruserid ){

            $store = new SessionStore();
            $user  = $store->get('user');

            $auth0Api = new Management( $this->apptoken, $this->domain );
            $response = $auth0Api->users->unlinkAccount( $user["user_id"], $provider, $provideruserid );

            $store = new SessionStore();
            $store->set('user', $auth0Api->users->get( $user["user_id"] ) );

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

            $store = new SessionStore();
            $user  = $store->get('user');

            if( isset( $user[ 'identities' ] ) && is_array( $user[ 'identities' ] ) )
                return $user[ 'identities' ];

            return array();
        }
        
        public function logoutURL( $global = false ){

            return 'https://' . $this->domain . '/logout?returnTo=' . $this->logouturi . ( $global ? '' : ( '&client_id=' . $this->clientid ) );
        }

    }
