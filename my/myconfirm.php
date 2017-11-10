<?php

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class myconfirm{

    /** @var mycontainer*/
    private $app;

    // TODO: remove globalapp hack
    public function __construct( $c ){
        $this->app = $c;
    }
/*
    public function middleware( \Slim\App $globalapp ){
        $this->globalapp = $globalapp;

        $this->globalapp->post( '/verify/{h:cf[a-f0-9]{32}}/{twotoken:[0-9A-Z]{16}}/', [ $this, 'processRequest' ] )
                        ->setName( 'myfwconfirm' );
    }
*/
    public function processRequest( Request $request, Response $response, $args ) {

        $h        = $args[ 'h' ];
        $twotoken = $args[ 'twotoken' ];

        $obj = $this->app->session->get( $h, false );

                if( isset( $obj[ 'uri' ] ) && isset( $obj[ 'method' ] ) ){

                    if( isset( $obj[ '2f' ] ) && $obj[ '2f' ] ){
                        if ( !$this->app->rules->twofactortoken( $twotoken ) || !isset( $this->app[ 'confirm.onvalidation'] ) || !$this->app[ 'confirm.onvalidation']( $twotoken ) ) /*call_user_func( $this->on2Fcall, $twotoken ) !== true*/
                            throw new Exception( 'Two-factor token invalid' );
                            //                            return $this->app->ajax->msgWarning( 'Token is not valid.' )->render();

                        $this->app->ajax->confirmDialogClose();
                    }

//                    $route = $this->router->getMatchedRoutes( $obj[ 'method' ], $obj[ 'uri' ], true );

//                    if( isset( $route[0] ) ){
                        $this->app->session->set( $h . 'confirm', 1 );

                        if( isset( $obj[ 'postvars' ] ) )
                            $_POST = $obj[ 'postvars' ];

                        // TODO: process request in client side
//                        return $this->globalapp->subRequest( $obj[ 'method' ], $obj[ 'uri' ] );
                        //                        return $route[0]->dispatch();
//                    }

                    return $response;
                }

                throw new myexception( myexception::NOTFOUND );
//                $this->notFound();

    }


    public function checkToken( $msg = null, $help = null, $title = null, $confirmByDefault = false, $customBefore = null ){
        return $this->check( $msg, $help, $title, '', 1, true, $confirmByDefault, $customBefore );
    }


    public function check( $msg = null, $help = null, $title = null, $description = '', $mode = 1, $twofactor = false, $confirmByDefault = false, $customBefore = null ){

        if( empty( $msg ) )   $msg   = 'Do you confirm your action ?';
        if( empty( $help ) )  $help  = '';
        if( empty( $title ) ) $title = 'Confirmation';

        $postvars = ( isset( $_POST ) ? $_POST : array() );
        foreach( $postvars as $k => $val )
            if( strpos( $k, 'csrf' ) )
                unset( $postvars[ $k ] );

        /** @var \Slim\Route $route */
        $route = $this->app->request->getAttribute('route');

        //$route = $this->router->getCurrentRoute();
        $hash  = 'cf' . md5( json_encode( array( $route->getName(), $route->getArguments() /*getParams()*/ ) + $postvars ) );

        if( $this->app->session->get( $hash . 'confirm', false ) === 1 ){
            $this->app->session->delete( $hash . 'confirm' );
            $this->app->session->delete( $hash );
            return true;
        }

        if( !is_null( $customBefore ) && is_callable( $customBefore ) ){
            $call = call_user_func( $customBefore );
        }else{
//          $call = ( isset( $this->bef2Fcall ) && is_callable( $this->bef2Fcall )  ) ? call_user_func( $this->bef2Fcall, $mode ) : false;
            $call = isset( $this->app[ 'confirm.oncheck' ] ) ? $this->app[ 'confirm.oncheck' ]( $mode ) : false;
        }

        if( $confirmByDefault === true && $call === false ){
            $this->app->session->delete( $hash . 'confirm' );
            $this->app->session->delete( $hash );
            return true;
        }

        if( is_string( $call ) ){
            throw new myexception( myexception::AJAXWARNING, $call );
            //$this->ajax()->msgError( $call )->render();
            //$this->stop();
        }

        $uri    = $this->app->request->getUri();   // $this->request->getResourceUri();
        $method = $this->app->request->getMethod();// $this->request->getMethod();

        $sms = ( $call === 2 );

        $pin   = ( $twofactor == true or $sms == true );
        $pinlabel = '';
        $pinhelp  = '';

        if( $sms == true ){
            $title    = 'Two-factor authentication by sms';
            $pinlabel = 'Pin';
            $pinhelp  = 'This action requires a 4-digit pin from a sms. An sms was sent.';
        }elseif( $twofactor == true ){
            $title    = 'Two-factor authentication by app';
            $pinlabel = 'Pin';
            $pinhelp  = 'Use your two-factor app to generate the 6-digit pin';
        }

        $this->app->session->set( $hash, array( 'uri' => $uri, 'method' => $method, '2f' => intval( $twofactor ), '2s' => intval( $sms ), 'postvars' => $_POST ) );
        $this->app->ajax->confirm( $this->app->urlfor->action( 'myfwconfirm', array( 'h' => $hash ) ), $msg, $title, $description, $help, $mode, $pin, $pinlabel, $pinhelp );
//                        ->render();
//        $this->stop();
        throw new myexception( myexception::STOP );
    }


}