<?php

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class myconfirm{

    /** @var mycontainer*/
    private $app;

    public function __construct( $c ){
        $this->app = $c;
    }

    public function processRequest( Request $request, Response $response, $args ) {

        $obj = $this->app->session->get( $args[ 'h' ], false );

        if( isset( $obj[ 'url' ] ) && isset( $obj[ 'xconfirm' ] ) && isset( $obj[ 'pin' ] ) ){

            if( $obj[ 'pin' ] === true ){

                if ( !isset( $args[ 'twotoken' ] ) || !$this->app->rules->twofactortoken( $args[ 'twotoken' ] ) || !isset( $this->app[ 'confirm.onvalidation'] ) || !$this->app[ 'confirm.onvalidation']( $args[ 'twotoken' ] ) )
                    throw new myexception( myexception::AJAXWARNING, 'Two-factor token invalid' );
            }

            $this->app->ajax->confirmSubmit( $obj[ 'url' ], $obj[ 'xconfirm' ] );
            return;
        }

        throw new myexception( myexception::NOTFOUND );
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

        $actual_link = $_SERVER[ 'REQUEST_URI' ];

        $hash = 'cf' . md5( $actual_link . json_encode( $postvars ) );

        $current_hash = $this->app->session->get( $hash, false );

        if( isset( $current_hash[ 'xconfirm' ] ) && $current_hash[ 'xconfirm' ] === $this->app->request->getHeaderLine('X-Confirm') ){
            $this->app->session->delete( $hash );
            $this->app->ajax->confirmDialogClose();
            return true;
        }

        if( !is_null( $customBefore ) && is_callable( $customBefore ) ){
            $call = call_user_func( $customBefore );
        }else{
            $call = isset( $this->app[ 'confirm.oncheck' ] ) ? $this->app[ 'confirm.oncheck' ]( $mode ) : false;
        }

        if( $confirmByDefault === true && $call === false ){
            $this->app->session->delete( $hash );
            $this->app->ajax->confirmDialogClose();
            return true;
        }

        if( is_string( $call ) ){
            throw new myexception( myexception::AJAXWARNING, $call );
        }

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

        $xconfirm = md5( $hash . rand( 1, 1000000 ) );

        $this->app->session->set( $hash, array( 'url' => $actual_link, 'xconfirm' => $xconfirm, 'pin' => $pin, 'postvars' => $_POST ) );
        $this->app->session->set( $xconfirm, $hash );

        $this->app->ajax->confirm( $this->app->urlfor->action( 'myfwconfirm', array( 'h' => $hash ) ), $msg, $title, $description, $help, $mode, $pin, $pinlabel, $pinhelp );

        throw new myexception( myexception::STOP );
    }

}
