<?php

use \Slim\Http\Response as Response;


class myexception extends Exception {

    const REDIRECT     = 101;
    const MESSAGE      = 102;
    const AJAXWARNING  = 103;
    const NOTFOUND     = 104;
    const RATELIMIT    = 105;
    const STOP         = 106;
    const REDIRECTOK   = 107;

    public function __construct( $code, $message = '' ) {
        $this->message = $message;
        $this->code    = $code;
    }

    public static function response( $code, $message, Response $response, $c ){

        /** @var abContainer $c */
        switch( $code ){
            case myexception::REDIRECT:
            case myexception::REDIRECTOK:

                if( $c->isajax ) {
                    return $response->withJson( $c->ajax->redirect( $message, '', 1000, $code == myexception::REDIRECTOK )
                                                        ->obj() );
                }else {
                    return $response->withRedirect( $message );
                }
                break;

            case myexception::AJAXWARNING:
                if( $c->isajax ) {
                    return $response->withJson( $c->ajax->msgWarning( $message )
                                                        ->obj() );
                }else{
                    return $response;
                }
                break;

            case myexception::NOTFOUND:
                return $response->withStatus(404 )
                                ->withHeader('Content-Type', 'text/html')
                                ->write('Page not found' );
                break;

            case myexception::RATELIMIT:
                if( $c->isajax ) {
                    return $response->withJson( $c->ajax->msgWarning( $message )
                                                        ->obj() );
                }else {
                    return $response->withStatus(429)
                                    ->withHeader('Content-Type', 'text/html')
                                    ->write( $message );
                }
                break;

            case myexception::STOP:
                if( $c->isajax ) {
                    return $response->withJson( $c->ajax->obj() );
                }else {
                    return $response->withHeader('Content-Type', 'text/html')
                                    ->write($message);
                }
                break;
        }

        return false;
    }
}