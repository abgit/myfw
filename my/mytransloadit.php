<?php

    use transloadit\Transloadit;
    use transloadit\TransloaditRequest;

class mytransloadit{

    public function __construct(){
        $this->app = \Slim\Slim::getInstance();
    }

    public function createAssembly( & $returndata, $arg ){

        $this->app->log()->debug( 'mytransloadit::createAssembly,arg:' . json_encode( $arg ) );

        $tl  = new Transloadit( array( 'key' => $app->config( 'transloadit.k' ), 'secret' => $app->config( 'transloadit.s' ) ) );
        $res = $tl->createAssembly( $arg );
        $returndata = $res->error() ? array() : $res->data;

        return !$res->error();
    }

    public function request( & $returndata, $url ){

        $this->app->log()->debug( 'mytransloadit::request,url:' . $url );

        $tl = new TransloaditRequest( array( 'key' => $app->config( 'transloadit.k' ), 'secret' => $app->config( 'transloadit.s' ) ) );
        $tl->url = $url;
        $res = $tl->execute();
        $returndata = $res->error() ? array() : $res->data;

        return !$res->error();
    }
}

