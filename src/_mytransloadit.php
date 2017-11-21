<?php

    use transloadit\Transloadit;
    use transloadit\TransloaditRequest;

class mytransloadit{

    private $cache = array();

    public function __construct(){
        $this->app = \Slim\Slim::getInstance();
        $driver = $this->app->config( 'transloadit.driver' );

        if( $driver === 'heroku' ){
            $this->apikey    = getenv( 'TRANSLOADIT_AUTH_KEY' );
            $this->apisecret = getenv( 'TRANSLOADIT_SECRET_KEY' );
        }else{
            $this->apikey    = $this->app->config( 'transloadit.k' );
            $this->apisecret = $this->app->config( 'transloadit.s' );
        }        
    }

    public function createAssembly( & $returndata, $arg ){

        $this->app->log()->debug( 'mytransloadit::createAssembly,arg:' . json_encode( $arg ) );

        $tl  = new Transloadit( array( 'key' => $this->apikey, 'secret' => $this->apisecret ) );
        $res = $tl->createAssembly( $arg );
        $returndata = $res->error() == false ? $res->data : $res->error();

        return !$res->error();
    }

    public function request( & $returndata, $url ){

        $this->app->log()->debug( 'mytransloadit::request,url:' . $url );

        $tl = new TransloaditRequest( array( 'key' => $this->apikey, 'secret' => $this->apisecret ) );
        $tl->url = $url;
        $res = $tl->execute();
        $returndata = $res->error() == false ? $res->data : $res->error();

        return !$res->error();
    }
    
    public function requestAssembly( & $returndata, $id, $usecache = false, $encode = false ){

        if( $usecache && isset( $this->cache[ $id ] ) ){
            $returndata = $this->cache[ $id ];
            return true;
        }

        if( ! $this->app->rules()->alphanumeric( $id ) ){
            $returndata = '';
            if( isset( $this->cache[ $id ] ) )
                unset( $this->cache[ $id ] );
            return false;
        }

        $tl = new TransloaditRequest( array( 'key' => $this->apikey, 'secret' => $this->apisecret ) );
        $tl->path = '/assemblies/' . $id;
        $res = $tl->execute();

        if( $res->error() == false ){
            $returndata = $encode ? json_encode( $res->data ) : $res->data;
            $this->cache[ $id ] = $returndata;
        }else{
            $returndata = '';
            if( isset( $this->cache[ $id ] ) )
                unset( $this->cache[ $id ] );
        }

        return !$res->error();
    }
    
}

