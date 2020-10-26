<?php

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class myconfirm{

    /** @var mycontainer*/
    private $app;

    public function __construct( $c ){
        $this->app = $c;
    }

    /** @throws myexception */
    public function processRequest( Request $request, Response $response, $args ) {

        $obj = $this->app->session->get( $args[ 'h' ], false );

        if( isset( $obj[ 'url' ] ) && isset( $obj[ 'xconfirm' ] ) && isset( $obj[ 'pin' ] ) ){

            if( $obj[ 'pin' ] === true ){

                if ( !isset( $args[ 'twotoken' ] ) || !isset( $this->app[ 'confirm.onvalidation'] ) || !$this->app[ 'confirm.onvalidation']( $args[ 'twotoken' ] ) )
                    throw new myexception( myexception::ERROR, 'Invalid pin' );
            }

            $this->app->ajax->confirmSubmit( $obj[ 'url' ], $obj[ 'xconfirm' ] );
            return;
        }

        $this->app->ajax->confirmDialogClose();
    }

    /** @throws myexception */
    public function checkToken( $msg = null, $help = null, $title = null ){
        return $this->check( $msg, $help, $title, '', 1, true, true );
    }

    /** @throws myexception */
    public function checkTokenIfConfigurared( $msg = null, $help = null, $title = null){
        return $this->check( $msg, $help, $title, '', 1, true, false );
    }

    /** @throws myexception */
    public function checkConfirm( $msg = null, $help = null, $title = null, $description = '' ){
        return $this->check( $msg, $help, $title, $description, 1, false, true );
    }

    /** @throws myexception */
    private function check( $msg = null, $help = null, $title = null, $description = '', $mode = 1, $twofactor = false, $required = true ){

        if( empty( $msg ) )   $msg   = 'Do you confirm your action ?';
        if( empty( $help ) )  $help  = '';
        if( empty( $title ) ) $title = 'Confirmation';

        $postvars = ( isset( $_POST ) ? $_POST : array() );
        foreach( $postvars as $k => $val )
            if( strpos( $k, 'csrf' ) )
                unset( $postvars[ $k ] );

        $actual_link = $_SERVER[ 'REQUEST_URI' ];

        $hash = 'cf' . md5( $actual_link . json_encode( $postvars ) );

        $current_hash = $this->app->session->get( $hash, false );

        if( isset( $current_hash[ 'xconfirm' ] ) && $current_hash[ 'xconfirm' ] === $this->app->request->getHeaderLine('X-Confirm') ){
            $this->app->session->delete( $hash );
            $this->app->ajax->confirmDialogClose();
            return true;
        }
/*
        $call = true;

        if( !is_null( $customBefore ) && is_callable( $customBefore ) ){
            $call = call_user_func( $customBefore );
        }else{
            $call = isset( $this->app[ 'confirm.oncheck' ] ) ? $this->app[ 'confirm.oncheck' ]( $twofactor ) : false;
        }

        if( $call !== true ) {
            if (is_string($call)) {
                throw new myexception(myexception::ERROR, $call);
            }else{
                throw new myexception( myexception::STOP );
            }
        }

        if( $confirmByDefault === true ){
            $this->app->session->delete( $hash );
            $this->app->ajax->confirmDialogClose();
            return true;
        }
*/
        //$title    = '';
        $pinlabel = '';
        $pinhelp  = '';

        // if check is only executed if configured
        if( !$required && $this->app[ 'confirm.isrequired' ] === false ){
            return true;
        }

        if( $twofactor === true ){

            if( isset( $this->app[ 'confirm.oncheck' ] ) ) {
                $this->app['confirm.oncheck']();
            }

            $title    = 'Two-factor authentication';
            $pinlabel = 'Pin';
            $pinhelp  = 'Fill your 6-digit pin';
        }

        $xconfirm = md5( $hash . random_int( 1, 1000000 ) );

        $this->app->session->set( $hash, array( 'url' => $actual_link, 'xconfirm' => $xconfirm, 'pin' => $twofactor, 'postvars' => $_POST ) );
        $this->app->session->set( $xconfirm, $hash );

        $this->app->ajax->confirm( $this->app->urlfor->action( 'myfwconfirm', array( 'h' => $hash ) ), $msg, $title, $description, $help, $mode, $twofactor, $pinlabel, $pinhelp );

        throw new myexception( myexception::STOP );
    }

}
