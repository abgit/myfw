<?php

use BlockCypher\Api\PaymentForward;
use BlockCypher\Auth\SimpleTokenCredential;
use BlockCypher\Rest\ApiContext;
use BlockCypher\Client\PaymentForwardClient;

class myblockcypher{

    private $exchange = null;
    
    public function __construct(){
        $this->app   = \Slim\Slim::getInstance();
        $this->token = $this->app->config( 'blockcypher.token' );

        $this->apiContext = ApiContext::create( 'main', 'btc', 'v1', new SimpleTokenCredential( $this->app->config( 'blockcypher.token' ) ), array( 'validation.level' => 'strict' ) );
/*        $this->apiContext->setConfig( array( 'mode' => 'sandbox',
                                             'log.LogEnabled' => false,
                                             'log.LogLevel' => 'INFO' ) );
  */
    }


    public function createPaymentAddress( $address = null, $callback = null, $process_fees_address = null, $process_fees_percent = null ){

        $options = array( 'callback_url' => ( is_null( $callback ) ? $this->app->config( 'blockcypher.callback' ) : $callback ) );
        
        if( ! empty( $process_fees_address ) )
            $options[ 'process_fees_address' ] = $process_fees_address;

        if( ! empty( $process_fees_percent ) )
            $options[ 'process_fees_percent' ] = $process_fees_percent;

        try{
            $p = ( new PaymentForwardClient( $this->apiContext ) )->createForwardingAddress( ( is_null( $address ) ? $this->app->config( 'blockcypher.add' ) : $address ), $options );
        }catch( Exception $e ){
            return array();
        }

        return array( 'address'    => $p->getInputAddress(),
                      'identifier' => $p->getId(),
                      'dump'       => var_export( $p, true ) );
    }

    public function processPayment(){
        $json = file_get_contents('php://input');
        return $json;
//        return json_decode($json);
    }




    public function test(){
        return $this->load( 'tobtc', array( 'currency' => $currency, 'value' => $value ) );
    }


    public function receive( $address = null, $callback = null ){
d( array( 'token' => $this->token, 'destination' => is_null( $address ) ? $this->app->config( 'bitcoin.acc' ) : $address, 'callback_url' => is_null( $callback ) ? ( 'http://' . $this->app->config( 'app.hostname' ) . $this->app->urlFor( $this->app->config( 'bitcoin.callback' ) ) ) : $callback ) );
        return $this->load( 'btc/main/payments', array( 'token' => $this->token, 'destination' => is_null( $address ) ? $this->app->config( 'bitcoin.acc' ) : $address, 'callback_url' => is_null( $callback ) ? ( 'http://' . $this->app->config( 'app.hostname' ) . $this->app->urlFor( $this->app->config( 'bitcoin.callback' ) ) ) : $callback ) );    
    }
    
    public function process(){
        $json = file_get_contents('php://input');
        return json_decode($json);
    }

    public function event( $address = null, $callback = null ){
        return $this->load( 'btc/main/hooks', array( 'event' => 'unconfirmed-tx', 'token' => $this->token, 'address' => $address, 'url' => $callback ) );    
    }

    public function checkaddress( $address ){
        $json = file_get_contents( 'http://api.blockcypher.com/v1/btc/main/addrs/' . $address . '/full' );
        $json = json_decode( $json, true  );
    
        $addresses = array();

         foreach( $json['txs'] as $transaction )
            if( isset( $transaction[ 'addresses' ] ) )
                $addresses = array_merge( $addresses , $transaction[ 'addresses' ] );

        return array( 'addresses' => array_reverse( array_diff( array_unique( $addresses ), array( $address ) ) ), 'final_balance' => $json[ 'final_balance' ] );
    }

    private function load( $uri, $options = array(), $returnUrl = false ){

        $url = 'https://api.blockcypher.com/v1/' . $uri;

        if( $returnUrl )
            return $url;

        $options = json_encode( $options );

        $ch = curl_init( $url );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
        curl_setopt($ch, CURLOPT_POSTFIELDS, $options );                                                                  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
        curl_setopt($ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json', 'Content-Length: ' . strlen( $options ) ) );

        $response = curl_exec($ch);
        
        return json_decode( $response, true );
    }
}