<?php

use Otp\Otp;
use Otp\GoogleAuthenticator;
use Base32\Base32;

class myotp{

    public function qrcode( $string ){
        \Slim\Slim::getInstance()->response->headers->set('Content-Type', 'image/png');
        \PHPQRCode\QRcode::png( $string, false, 'L', 5, 1 );
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
