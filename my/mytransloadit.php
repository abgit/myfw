<?php

    use transloadit\Transloadit;
    use transloadit\TransloaditRequest;

class mytransloadit{

    private $cache = array();

    public function __construct(){
        $this->app = \Slim\Slim::getInstance();
    }

    public function createAssembly( & $returndata, $arg ){

        $this->app->log()->debug( 'mytransloadit::createAssembly,arg:' . json_encode( $arg ) );

        $tl  = new Transloadit( array( 'key' => $this->app->config( 'transloadit.k' ), 'secret' => $this->app->config( 'transloadit.s' ) ) );
        $res = $tl->createAssembly( $arg );
        $returndata = $res->error() ? array() : $res->data;

        return !$res->error();
    }

    public function request( & $returndata, $url ){

        $this->app->log()->debug( 'mytransloadit::request,url:' . $url );

        $tl = new TransloaditRequest( array( 'key' => $this->app->config( 'transloadit.k' ), 'secret' => $this->app->config( 'transloadit.s' ) ) );
        $tl->url = $url;
        $res = $tl->execute();
        $returndata = $res->error() ? array() : $res->data;

        return !$res->error();
    }
    
    public function requestAssembly( & $returndata, $id, $refresh = false ){

        if( !$refresh && isset( $this->cache[ $id ] ) ){
            $returndata = $this->cache[ $id ];
            return true;
        }

        if( ! $this->app->rules()->alphanumeric( $id ) ){
            $returndata = '';
            if( isset( $this->cache[ $id ] ) )
                unset( $this->cache[ $id ] );
            return false;
        }

        $tl = new TransloaditRequest( array( 'key' => $this->app->config( 'transloadit.k' ), 'secret' => $this->app->config( 'transloadit.s' ) ) );
        $tl->url = 'http://api2.transloadit.com/assemblies/' . $id;
        $res = $tl->execute();

        if( $res->error() ){
            $returndata = '';
            if( isset( $this->cache[ $id ] ) )
                unset( $this->cache[ $id ] );
        }else{

            $returndata = json_encode( $res->data );
            $this->cache[ $id ] = json_encode( $res->data );
        }

        return !$res->error();
    }
    
}

