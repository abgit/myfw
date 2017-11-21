<?php

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;


class myconfig implements arrayaccess{

    /** @var mycontainer*/
    private $app;

    private $elements = array();

    public function __construct( $c ){
        $this->app = $c;
    }

    
    public function set( $key, $value ){

        if( is_array( $key ) )
            array_merge( $this->elements, $key );
        else
            $this->elements[ $key ] = $value;
    }


    public function get( $name ){
        if( !isset( $this->elements[$name] ) )
            return null;

        if( !is_string( $this->elements[$name] ) )
            return is_callable( $this->elements[$name] ) ? $this->elements[$name]() : $this->elements[$name];


        return $this->parse( $this->elements[$name] );
    }


    public function offsetGet( $setting ) {

        if( isset( $this->app[ $setting ] ) )
            return $this->parse( $this->app[ $setting ] );

        return null;
    }


    public function offsetSet($offset, $value) {
        $this->app[$offset] = $value;
    }


    public function offsetExists($offset) {
        return isset( $this->app[ $offset ] );
    }


    public function offsetUnset($offset) {
        unset( $this->app[ $offset ] );
    }


    /**
     * @param $setting
     * @return mixed|null|string
     */
    public function parse( $setting ){

        // optimization
        if( !is_string( $setting ) || empty( $setting ) )
            return $setting;

        preg_match("/([^$!@#][a-zA-Z0-9]+[-]{1})([$!@#][a-zA-Z0-9_]+.*)/", $setting, $vars );

        $prefix = '';

        if( is_array( $vars ) && !empty( $vars ) ){
            $setting = $vars[ 2 ];
            $prefix  = $vars[ 1 ];
        }

        switch( $setting{0} ){
            case '@': /** @noinspection PhpUnusedLocalVariableInspection */
                list( $all, $variable, $sufix ) = $this->configparse( $setting );
                      $var = $this->getenvconfigvar( $variable );
                      return is_null( $var ) ? null : ( $prefix . $var . $sufix );

            case '#': /** @noinspection PhpUnusedLocalVariableInspection */
                      list( $all, $variable, $sufix ) = $this->configparse( $setting );
                      $var = $this->getenvconfigvar( $variable );
                      return is_null( $var ) ? null : ( $prefix . $this->decrypt( $var ) . $sufix );

            case '!': /** @noinspection PhpUnusedLocalVariableInspection */
                      list( $all, $variable, $sufix ) = $this->configparse( $setting );
                      return $prefix . $this->decrypt( $variable ) . $sufix;

            case '$': /** @noinspection PhpUnusedLocalVariableInspection */
                      list( $all, $variable, $sufix ) = $this->configparse( $setting );
                      return $prefix . $this->app->session->get( $variable ) . $sufix;
        }

        return $setting;
    }


    private function configparse( $name ){
        preg_match("/([a-zA-Z0-9_+=\/]+)(.*)/", substr( $name, 1 ), $vars );
        return $vars;
    }


    private function getenvconfigvar( $name ){

        $var = getenv( $name );
        return $var === false ? null : $var;
    }


    public function encrypt( $message, $key = null ){
        return Crypto::encrypt( $message, Key::loadFromAsciiSafeString( is_null( $key ) ? $this->app[ 'config.crypt' ] : $key ) );
    }


    public function decrypt( $message, $key = null ){
        return Crypto::decrypt( $message, Key::loadFromAsciiSafeString( is_null( $key ) ? $this->app[ 'config.crypt' ] : $key ) );
    }


    public function cryptkey(){
        return Key::createNewRandomKey()->saveToAsciiSafeString();
    }


    public function encryptWithPassword( $message, $key = null ){
        return Crypto::encryptWithPassword( $message, $key );
    }


    public function decryptWithPassword( $message, $key = null ){
        return Crypto::decryptWithPassword( $message, $key );
    }

}
