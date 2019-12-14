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
    const LOGOUT       = 109;

    public function __construct( $code, $message = '' ) {
        parent::__construct( $message, $code);
    }

    public static function response( $code, $message, ?Response $response, $container ){

        if( is_null( $response ) )
            $response = new \Slim\Http\Response();

        /** @var abContainer $container */

        // check debug mode
        if( isset( $container->config[ 'app.debug' ] ) && $container->config[ 'app.debug' ] === true ) {
            $response = $response->withAddedHeader('x-myfw-page', sprintf("%.3f", defined('APP_START' ) ? (float)microtime(true) - APP_START : 0 ) );
            $response = $response->withAddedHeader('x-myfw-d', $container->db->getDebugsCounter() );
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

                    if( $container[ 'exception.template' ] )
                        $message = $container->view->fetch( $container[ 'exception.template' ], array( 'message' => $message, 'title' => $container[ 'exception.title' ] ) );

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

            case myexception::LOGOUT:
                if( $container->ismobile ) {
                    return $response->withJson($container->ajax->alert('Forbidden')
                        ->obj());
                }elseif( $container->isajax ) {
                    return $response->withJson( $container->ajax->redirect( $message, '', 1000, $code == myexception::REDIRECTOK )
                        ->obj() );
                }else {
                    return $response->withRedirect( $message );
                }

                break;

        }

        return false;
    }
}