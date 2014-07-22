<?php

require __DIR__ . '/3rdparty/GoogleAuthenticator/PHPGangsta_GoogleAuthenticator.php';

class myotp extends PHPGangsta_GoogleAuthenticator{

    public function getQRCode( $name, $secret, $size = '200x200' ){
        return '//chart.apis.google.com/chart?cht=qr&chs=' . $size . '&chld=H%7C0&chl=otpauth://totp/' . urlencode( $name ) . '?secret=' . $secret;
    }
}
