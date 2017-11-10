<?php

use Otp\Otp;
use Otp\GoogleAuthenticator;
use Base32\Base32;

class myotp{

    /** @var mycontainer */
    private $app;

    public function __construct( $c ){
        $this->app = $c;
    }

    public function createSecret(){
        return GoogleAuthenticator::generateRandom();
    }

    public function createKey( $secret ){
        return (new Otp())->totp(Base32::decode($secret));
    }

    public function verifyCode( $secret, $key ){
        return (new Otp())->checkTotp(Base32::decode($secret), $key);
    }
}
