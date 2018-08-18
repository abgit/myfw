<?php

class myblockchain{

    /** @var mycontainer*/
    private $app;

    private $exchange = null;

    public function __construct( $c ){
        $this->app = $c;
    }


    public function tobtc( $currency, $value ){
        return $this->load( 'tobtc', array( 'currency' => $currency, 'value' => $value ) );
    }


    public function exchangebtc(){
        $json = $this->load( 'ticker' );

        if( isset( $json[ 'USD' ][ 'buy' ] ) )
            return array( 'USD' => $json[ 'USD' ][ 'buy' ],
                          'GBP' => $json[ 'GBP' ][ 'buy' ],
                          'EUR' => $json[ 'EUR' ][ 'buy' ],
                          'CNY' => $json[ 'CNY' ][ 'buy' ],
                          'JPY' => $json[ 'JPY' ][ 'buy' ] );

        return array();
    }


    public function coinbasebtc(){
        $json = json_decode( file_get_contents( 'http://api.coindesk.com/v1/bpi/currentprice.json' ), true );

        if( isset( $json[ 'bpi' ][ 'USD' ][ 'rate_float' ] ) && isset( $json[ 'bpi' ][ 'GBP' ][ 'rate_float' ] ) && isset( $json[ 'bpi' ][ 'EUR' ][ 'rate_float' ] ) ){

            $jsoncny = json_decode( file_get_contents( 'http://api.coindesk.com/v1/bpi/currentprice/CNY.json' ), true );

            if( isset( $jsoncny[ 'bpi' ][ 'CNY' ][ 'rate_float' ] ) ){

                $jsonjpy = json_decode( file_get_contents( 'http://api.coindesk.com/v1/bpi/currentprice/JPY.json' ), true );

                if( isset( $jsonjpy[ 'bpi' ][ 'JPY' ][ 'rate_float' ] ) ){
                    return array( 'USD' => $json[ 'bpi' ][ 'USD' ][ 'rate_float' ],
                                  'GBP' => $json[ 'bpi' ][ 'GBP' ][ 'rate_float' ],
                                  'EUR' => $json[ 'bpi' ][ 'EUR' ][ 'rate_float' ],
                                  'CNY' => $jsoncny[ 'bpi' ][ 'CNY' ][ 'rate_float' ],
                                  'JPY' => $jsonjpy[ 'bpi' ][ 'JPY' ][ 'rate_float' ] );
                }
            }
        }

        return array();
    }


    public function frombtc( $currency, $value, $addsymbol = false, $decimals = 2 ){

        if( is_null( $this->exchange ) )
            $this->exchange = $this->load( 'ticker' );
    
        return isset( $this->exchange[ $currency ][ 'last' ] ) ? ( $addsymbol ? ( $this->exchange[ $currency ][ 'symbol' ] . ' ' ) : '' ) . ( number_format( floatval( $this->exchange[ $currency ][ 'last' ] ) * floatval( $value ), $decimals ) ) : false;
    }


    public function receive( $address = null, $callback = null ){
        return $this->load( 'api/receive', array( 'method' => 'create', 'address' => is_null( $address ) ? $this->app->config[ 'bitcoin.acc' ] : $address, 'callback' => is_null( $callback ) ? ( 'http://' . $this->app->config[ 'app.hostname' ] . $this->app->urlfor->action( $this->app->config[ 'bitcoin.callback' ] ) ) : $callback ) );
    }
    

    public function checkaddress( $address, $unique = false ){

        $address = trim( $address );

        $json = $this->load( 'address/' . $address, array( 'format' => 'json' ) );
    
        if( $unique == false )
            return $json;

        $addresses = array();

        foreach( $json['txs'] as $transaction )
            if( isset( $transaction[ 'inputs' ] ) )
                foreach( $transaction[ 'inputs' ] as $input )
                    if( isset( $input[ 'prev_out' ][ 'addr' ] ) && $input[ 'prev_out' ][ 'addr' ] != $address )
                        $addresses[] = $input[ 'prev_out' ][ 'addr' ];

        return array_reverse( array_unique( $addresses ) );
    }


    public function qrcode( $amount, $label = '', $account = '', $size = 200 ){

        $account = empty( $account ) ? $this->app->config[ 'bitcoin.acc' ]   : urlencode( $account );
        $label   = empty( $label )   ? $this->app->config[ 'bitcoin.label' ] : urlencode( $label );

        return $this->load( 'qr?data=bitcoin:' . $account, array( 'amount' => $amount, 'label' => $label, 'size' => $size ), true );
    }


    public function process( $func ){
    
        if( $_GET['confirmations'] >= 6 && $func() === true ){
            echo '*ok*';
        }
    }


    public function payment( $to, $amount, $guid = '', $password = '', $from = '', $second_password = '', $fee = '', $note = '' ){

        $params = array( 'password'        => empty( $password ) ? $this->app->config[ 'bitcoin.p' ] : $password,
                         'to'              => $to,
                         'amount'          => $amount );

        if( $from )
            $params[ 'from' ] = $from;
        if( $second_password )
            $params[ 'second_password' ] = $second_password;
        if( $fee )
            $params[ 'fee' ] = $fee;
        if( $note )
            $params[ 'note' ] = $note;

        return $this->load( 'merchant/' . ( empty( $guid ) ? $this->app->config[ 'bitcoin.guid' ] : $guid ) . '/payment', $params );
    }


    public function sendmany( $guid, $password, $recipients, $from = '', $second_password = '', $fee = '', $note = '' ){

        $params = array( 'password'   => $password,
                         'recipients' => $recipients );

        if( $from )
            $params[ 'from' ] = $from;
        if( $second_password )
            $params[ 'second_password' ] = $second_password;
        if( $fee )
            $params[ 'fee' ] = $fee;
        if( $note )
            $params[ 'note' ] = $note;

        return $this->load( 'merchant/' . $guid . '/sendmany', $params );
    }


    private function load( $uri, $options = array(), $returnUrl = false, $returnString = false ){

        $opts = array();
    
        foreach( $options as $k => $v )
            if( !empty( $v ) )
                $opts[] = $k . '=' . urlencode( $v );

        $url = 'https://blockchain.info/' . $uri . '?' . implode( '&', $opts );

        if( $returnUrl )
            return $url;

        if( ( $response = file_get_contents( $url ) ) === false )
            return false;

        return $returnString ? $response : json_decode( $response, true );
    }

}