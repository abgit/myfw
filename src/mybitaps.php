<?php

class mybitaps{


    /** @var mycontainer*/
    private $app;
    private $endpoint_market            = 'https://api.bitaps.com/market/v1/';
    private $endpoint_paymentforwarding = 'https://api.bitaps.com/btc/v1/';
    private $json_assoc = true;

    public function __construct( $c ){
        $this->app = $c;
    }

    public function setmarket( $url ){
        $this->endpoint_market = $url;
    }

    public function setpaymentforwarding( $url ){
        $this->endpoint_paymentforwarding = $url;
    }

    public function setJsonObject(){
        $this->json_assoc = false;
    }

    // Market API: Tickers - Average price
    public function market_AveragePrice( $pair = 'btcusd' ){
        return $this->get( $this->endpoint_market . '/ticker/' . $pair );
    }

    // Market API: Tickers - Ticker list
    public function market_TickerList( $list = 'btcusd' ){
        return $this->get( $this->endpoint_market . '/tickers/' . $list );
    }

    // Payment forwarding API: Payment forwarding - Create forwarding address
    public function paymentforwarding_CreateForwardingAddress( $forwarding_address, $callback_link = null, $confirmations = null ){
        return $this->post( $this->endpoint_paymentforwarding . '/create/payment/address',
            array( 'forwarding_address' => $forwarding_address,
                   'callback_link'      => $callback_link,
                   'confirmations'      => $confirmations )
        );
    }

    // Payment forwarding API: Payment forwarding - Payment address state
    public function paymentforwarding_PaymentAddressState( $address, $PaymentCode, $AccessToken ){
        return $this->get( $this->endpoint_paymentforwarding . '/payment/address/state/' . $address,
            array( 'Payment-Code ' => $PaymentCode,
                   'Access-Token ' => $AccessToken )
        );
    }

    // Payment forwarding API: Payment forwarding - List of payment address transactions
    public function paymentforwarding_ListOfPaymentAddressTransactions( $address, $PaymentCode, $AccessToken, $from = null, $to = null, $limit = null, $page = null ){
        return $this->get( $this->endpoint_paymentforwarding . '/payment/address/transactions/' . $address,
            array( 'Payment-Code ' => $PaymentCode,
                   'Access-Token ' => $AccessToken ),
            array( 'from'  => $from,
                   'to'    => $to,
                   'limit' => $limit,
                   'page'  => $page )
        );
    }

    // Payment forwarding API: Payment forwarding - Callback handler
    public function paymentforwarding_CallbackHandler(){
        return $this->post( $this->endpoint_paymentforwarding . '/callback/handler/example' );
    }

    // Payment forwarding API: Payment forwarding - Callback log for payment address
    public function paymentforwarding_CallbackLogForPaymentAddress( $address, $PaymentCode, $AccessToken, $limit = null, $page = null ){
        return $this->get( $this->endpoint_paymentforwarding . '/payment/address/callback/log/' . $address,
            array( 'Payment-Code ' => $PaymentCode,
                   'Access-Token ' => $AccessToken ),
            array( 'limit' => $limit,
                   'page'  => $page )
        );
    }

    // Payment forwarding API: Payment forwarding - Callback log for payment
    public function paymentforwarding_CallbackLogForPayment( $tx_hash, $output, $PaymentCode, $AccessToken, $limit = null, $page = null ){
        return $this->get( $this->endpoint_paymentforwarding . ' /payment/callback/log/' . $tx_hash . '/' . $output,
            array( 'Payment-Code ' => $PaymentCode,
                   'Access-Token ' => $AccessToken ),
            array( 'limit' => $limit,
                   'page'  => $page )
        );
    }

    // Payment forwarding API: Domain authorization - Create domain authorization code
    public function paymentforwarding_CreateDomainAuthorizationCode( $callback_link ){
        return $this->post( $this->endpoint_paymentforwarding . '/create/domain/authorization/code',
            array( 'callback_link' => $callback_link )
        );
    }

    // Payment forwarding API: Domain authorization - Create domain access token
    public function paymentforwarding_CreateDomainAccessToken( $callback_link ){
        return $this->post( $this->endpoint_paymentforwarding . '/create/domain/access/token',
            array( 'callback_link' => $callback_link )
        );
    }

    // Payment forwarding API: Domain statistics - Domain statistics
    public function paymentforwarding_DomainStatistics( $domainHash, $AccessToken ){
        return $this->get( $this->endpoint_paymentforwarding . '/domain/state/' . $domainHash,
            array( 'Access-Token ' => $AccessToken )
        );
    }

    // Payment forwarding API: Domain statistics - List of created addresses
    public function paymentforwarding_ListOfCreatedAddresses( $domainHash, $AccessToken, $from = null, $to = null, $limit = null, $page = null ){
        return $this->get( $this->endpoint_paymentforwarding . '/domain/addresses/' . $domainHash,
            array( 'Access-Token ' => $AccessToken ),
            array( 'from'  => $from,
                   'to'    => $to,
                   'limit' => $limit,
                   'page'  => $page )

        );
    }

    // Payment forwarding API: Domain statistics - List of domain transactions
    public function paymentforwarding_ListOfDomainTransactions( $domainHash, $AccessToken, $from = null, $to = null, $limit = null, $page = null, $type = null ){
        return $this->get( $this->endpoint_paymentforwarding . '/domain/transactions/' . $domainHash,
            array( 'Access-Token ' => $AccessToken ),
            array( 'from'  => $from,
                   'to'    => $to,
                   'limit' => $limit,
                   'page'  => $page,
                   'type'  => $type )
        );
    }

    // Payment forwarding API: Domain statistics - Daily domain statistics
    public function paymentforwarding_DailyDomainStatistics( $domainHash, $AccessToken, $from = null, $to = null, $limit = null, $page = null ){
        return $this->get( $this->endpoint_paymentforwarding . '/domain/daily/statistic/' . $domainHash,
            array( 'Access-Token ' => $AccessToken ),
            array( 'from'  => $from,
                   'to'    => $to,
                   'limit' => $limit,
                   'page'  => $page )
        );
    }


    private function get( $url, $headers = array(), $getparams = array() ){

        $getparams = $this->clear( $getparams );
        $getparams = empty( $getparams ) ? '' : ( '?' . http_build_query( $getparams ) );

        $req = Requests::get($url . $getparams, $headers );
        return $req->success ? json_decode( $req->body, $this->json_assoc ) : null;
    }


    private function post( $url, $data = array() ){

        $req = Requests::post( $url, array('Content-Type' => 'application/json'), json_encode( $this->clear( $data ) ) );
        return $req->success ? json_decode( $req->body, $this->json_assoc ) : null;
    }


    private function clear( $arr ){

        // delete empty values
        foreach ( $arr as $k => $v ){
            if( empty( $v ) && $v !== 0 && $v !== '0' ) {
                unset( $arr[$k] );
            }
        }

        return $arr;
    }
}
