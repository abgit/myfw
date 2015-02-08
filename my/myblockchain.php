<?php

class myblockchain{

    private $exchange = null;
    
    public function __construct(){
        $this->app = \Slim\Slim::getInstance();
    }


    public function tobtc( $currency, $value ){
        return $this->load( 'tobtc', array( 'currency' => $currency, 'value' => $value ) );
    }


    public function exchangebtc(){
        return $this->load( 'ticker' );
    }


    public function frombtc( $currency, $value, $addsymbol = false, $decimals = 2 ){

        if( is_null( $this->exchange ) )
            $this->exchange = $this->load( 'ticker' );
    
        return isset( $this->exchange[ $currency ][ 'last' ] ) ? ( $addsymbol ? ( $this->exchange[ $currency ][ 'symbol' ] . ' ' ) : '' ) . ( number_format( floatval( $this->exchange[ $currency ][ 'last' ] ) * floatval( $value ), $decimals ) ) : false;
    }


    public function receive( $address = null, $callback = null ){
        return $this->load( 'api/receive', array( 'method' => 'create', 'address' => is_null( $address ) ? $this->app->config( 'bitcoin.acc' ) : $address, 'callback' => is_null( $callback ) ? ( 'http://' . $this->app->config( 'app.hostname' ) . $this->app->urlFor( $this->app->config( 'bitcoin.callback' ) ) ) : $callback ) );    
    }
    

    public function checkaddress( $address ){
        return $this->load( 'address/' . $address, array( 'format' => 'json' ) );
    }


    public function qrcode( $amount, $label = '', $account = '', $size = 200 ){

        $account = empty( $account ) ? $this->app->config( 'bitcoin.acc' )   : urlencode( $account );
        $label   = empty( $label )   ? $this->app->config( 'bitcoin.label' ) : urlencode( $label );

        return $this->load( 'qr?data=bitcoin:' . $account, array( 'amount' => $amount, 'label' => $label, 'size' => $size ), true );
    }


    public function process( $func ){
    
        if( /*$this->getClientIP() === '190.93.243.195' &&*/
            /*isset( $_GET['confirmations'] ) && 
            isset( $_GET['transaction_hash'] ) &&
            isset( $_GET['input_transaction_hash'] ) &&
            isset( $_GET['input_address'] ) &&
            isset( $_GET['value'] ) &&
            $_GET['confirmations'] >= 6 &&*/
            $func() === true ){
    
                echo '*ok*';
        }
    }


    public function payment( $guid, $password, $second_password, $to, $amount, $from, $fee, $note ){

        return $this->load( 'api/merchant/' . $guid . '/payment', array( 'password'        => $password,
                                                                         'second_password' => $second_password,
                                                                         'to'              => $to,
                                                                         'amount'          => $amount,
                                                                         'from'            => $from,
                                                                         'fee'             => $fee,
                                                                         'note'            => $note ) );
    }


    private function load( $uri, $options = array(), $returnUrl = false ){

        $opts = array();
    
        foreach( $options as $k => $v )
            if( !empty( $v ) )
                $opts[] = $k . '=' . urlencode( $v );

        $url = 'https://blockchain.info/' . $uri . '?' . implode( '&', $opts );

        if( $returnUrl )
            return $url;

        if( !( $response = $this->app->cache()->memcachedget( md5( $returnUrl ) ) ) ){

            if( ( $response = file_get_contents( $url ) ) === false )
                return false;

            $this->app->cache()->memcachedset( md5( $returnUrl ), $response );
        }

        return json_decode( $response, true );
    }


    public function getClientIP() {

        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
                return $_SERVER["HTTP_X_FORWARDED_FOR"];

            if (isset($_SERVER["HTTP_CLIENT_IP"]))
                return $_SERVER["HTTP_CLIENT_IP"];

            return $_SERVER["REMOTE_ADDR"];
        }

        if (getenv('HTTP_X_FORWARDED_FOR'))
            return getenv('HTTP_X_FORWARDED_FOR');

        if (getenv('HTTP_CLIENT_IP'))
            return getenv('HTTP_CLIENT_IP');

        return getenv('REMOTE_ADDR');
    }

}