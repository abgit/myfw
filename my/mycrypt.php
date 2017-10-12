<?php

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class mycrypt{

    public static function encrypt( $message, $key = null ){
        return Crypto::encryptWithPassword( $message, is_null( $key ) ? APP_CRYPT : $key );
    }

    public static function decrypt( $message, $key = null ){
        return Crypto::decryptWithPassword( $message, is_null( $key ) ? APP_CRYPT : $key );
    }
}