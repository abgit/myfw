<?php

class myblockcypher{

    private $exchange = null;
    
    public function __construct(){
        $this->app   = \Slim\Slim::getInstance();
        $this->token = $this->app->config( 'blockcypher.token' );
    }


    public function test(){
        return $this->load( 'tobtc', array( 'currency' => $currency, 'value' => $value ) );
    }


    public function receive( $address = null, $callback = null ){
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