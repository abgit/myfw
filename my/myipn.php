<?php

    require_once( __DIR__  . "/3rdparty/PHP-PayPal-IPN/ipnlistener.php" );

class myipn{
    
    public function __construct(){
        $this->app = \Slim\Slim::getInstance();
        $this->api = new IpnListener();
        $this->api->use_sandbox = $this->app->config( 'ipn.test' ) === true;
    }
    
    public function verify( & $result, $currency = false ){
       try{
            $valid = $this->api->processIpn();
        }catch( Exception $e ){
            $result = $this->api->getTextReport();
            return false;
        }

        if( !$valid || ( !is_null( $currency ) && ( !isset( $_POST[ 'mc_currency' ] ) || $currency !== $_POST[ 'mc_currency' ] ) ) ){
            $result = $this->api->getTextReport();
            return false;
        }

        $result = array( 'payment_status'    => isset( $_POST[ 'payment_status' ] )    ? $_POST[ 'payment_status' ]    : '',
                         'txn_id'            => isset( $_POST[ 'txn_id' ] )            ? $_POST[ 'txn_id' ]            : '',
                         'receiver_email'    => isset( $_POST[ 'receiver_email' ] )    ? $_POST[ 'receiver_email' ]    : '',
                         'mc_gross'          => isset( $_POST[ 'mc_gross' ] )          ? $_POST[ 'mc_gross' ]          : '',
                         'option_selection1' => isset( $_POST[ 'option_selection1' ] ) ? $_POST[ 'option_selection1' ] : '',
                         'custom'            => isset( $_POST[ 'custom' ] )            ? $_POST[ 'custom' ]            : '',
                         'other'             => json_encode( $_POST ),
                         'payment_status_id' => isset( $_POST[ 'payment_status' ] ) && $_POST[ 'payment_status' ] === 'Completed' ? 1 : 0                         
                          );
        return true;
    }

}