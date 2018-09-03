<?php

use \Slim\Http\Response as Response;


class myexception extends Exception {

    const REDIRECT     = 101;
    const ERROR        = 103;
    const NOTFOUND     = 104;
    const RATELIMIT    = 105;
    const STOP         = 106;
    const REDIRECTOK   = 107;
    const FORBIDDEN    = 108;

    public function __construct( $code, $message = '' ) {
        $this->code    = $code;
        $this->message = $message;
    }

    public static function response( $code, $message, Response $response, $container ){

        /** @var abContainer $container */

        // check debug mode
        if( isset( $container->config[ 'app.debug' ] ) && $container->config[ 'app.debug' ] === true ) {
            $response = $response->withAddedHeader('X-myfw-page', sprintf("%.3f", defined('APP_START' ) ? (float)microtime(true) - APP_START : 0 ) );
            $response = $response->withAddedHeader('X-myfw-db', $container->db->getDebugsCounter() );
        }

        switch( $code ){
            case myexception::REDIRECT:
            case myexception::REDIRECTOK:

                if( $container->isajax ) {
                    return $response->withJson( $container->ajax->redirect( $message, '', 1000, $code == myexception::REDIRECTOK )
                                                          ->obj() );
                }else {
                    return $response->withRedirect( $message );
                }
                break;

            case myexception::ERROR:
                if( $container->isajax ) {
                    return $response->withJson( $container->ajax->msgError( $message )
                                                                ->obj() );
                }else{
                    return $response->withHeader('Content-Type', 'text/html')
                                    ->write($message);
                }
                break;

            case myexception::NOTFOUND:
                return $response->withStatus(404 )
                                ->withHeader('Content-Type', 'text/html')
                                ->write('Page not found' );
                break;

            case myexception::RATELIMIT:
                if( $container->isajax ) {
                    return $response->withJson( $container->ajax->msgWarning( $message )
                                                                ->obj() );
                }else {
                    return $response->withStatus(429)
                                    ->withHeader('Content-Type', 'text/html')
                                    ->write( $message );
                }
                break;

            case myexception::STOP:
                if( $container->isajax ) {
                    return $response->withJson( $container->ajax->obj() );
                }else {
                    return $response;
                }
                break;

            case myexception::FORBIDDEN:
                if( $container->isajax ) {
                    return $response->withJson( $container->ajax->msgError( $message, 'Forbidden'  )
                                                                ->obj() );
                }else {
                    return $response->withStatus(403 )
                                    ->withHeader('Content-Type', 'text/html')
                                    ->write( $message );
                }
                break;
        }

        return false;
    }
}