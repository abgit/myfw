<?php

class myrules{

    public static function required( $value = '', $options = '', $input = array(), $el = array() ){
        return !empty( $value );
    }

    public static function numeric( $value, $opts='', $formelement = null ) {
        return myrules::regex( strval( $value ), '/(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)/' );
    }

    public static function maxlen( $value, $options = '', $input = array() ){
        return( intval( $options ) > 0 && strlen( $value ) <= intval( $options ) );
    }

    public static function regex( $value, $options, $input = array() ) {
        return preg_match( $options, $value );
    }

    public static function email( $value, $options = '', $input = array() ){
        return myrules::regex( $value, '/^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/' );
    }

    public static function not_email( $value, $opts='', $formelement = null ) {
        return !myrules::email( $value );
    }

    public static function money( $value, $opts='', $formelement = null){
        return myrules::regex( strval( $value ), '/^([0-9]{1,5}([\.\,][0-9]{1,2})?)$/' );
    }

    public static function unsigned( $value, $opts='', $formelement = null){
        return myrules::regex( strval( $value ), '/^([1-9]*[0-9]{1,20}(\.[0-9]{1,2}){0,1})$/' );
    }

    public static function ip( $value, $options = '', $input = array() ){
        return myrules::regex( $value, '/\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/' );
    }

    public static function md5( $value, $opts='', $formelement = null ) {
        return myrules::regex( strval( $value ), '/^([a-z0-9]{32})$/' );
    }

    public static function bitcoinaddress( $value, $opts='', $formelement = null ) {
        return myrules::regex( strval( $value ), '/^([13][a-km-zA-HJ-NP-Z0-9]{26,33})$/' );
    }

    public static function tag( $value, $opts='', $formelement = null ) {
        return myrules::regex( strval( $value ), '/^([0-9a-zA-Z\-]{3,20})$/' );
    }

    public static function twofactortoken( $value, $opts='', $formelement = null ) {
        return myrules::regex( strval( $value ), '/^([0-9]{6})$/' );
    }

    public static function lettersonly( $values, $opts='', $formelement = null ) {
        return ( myrules::regex( $values, '/([\D^ ]+)$/' ) && myrules::nopunctuation( $values, array() ) );
    }

    public static function character( $value, $opts='', $formelement = null ) {
        return ( strlen( strval( $value ) ) == 1 ) && myrules::lettersonly( $value, array() );
    }

    public static function maxhyperlinks( $val, $opts, $formelement = null ) {
        return (preg_match_all( "/href=/i", $val, $matches1 ) + preg_match_all( "/\[url=/i", $val, $matches2 ) + preg_match_all( "/http\:\/\//i", $val, $matches ) <= intval( $opts ) );
    }

    public static function value( $value, $options, $formelement = null ) {
        return $val === $options;
    }

    public static function maxlength( $value, $opts, $formelement = null ) {
        return ( strlen( $value ) <= intval( $opts ) );
    }

    public static function minlength( $value, $opts, $formelement = null ) {
        return ( strlen( $value ) >= intval( $opts ) );
    }

    public static function maxvalue( $value, $opts, $formelement = null ) {
        return ( intval( $value ) <= intval( $opts ) );
    }

    public static function minvalue( $value, $opts, $formelement = null ) {
        return ( intval( $value ) >= intval( $opts ) );
    }

    public static function minoptions( $value, $options = '', $input = array() ){

        $options = intval( $options );
        $counter = 0;
        $exp = explode( ';', $value );
        foreach( $exp as &$el ){
            if( strlen( trim( $el ) ) )
                $counter++;
            if( $counter >= $options )
                return true;
        }
        return false;
    }

    public static function maxoptions( $value, $options = '', $input = array() ){

        $options = intval( $options );
        $counter = 0;
        $exp = explode( ';', $value );
        foreach( $exp as &$el ){
            if( strlen( trim( $el ) ) )
                $counter++;
            if( $counter > $options )
                return false;
        }
        return true;
    }

    public static function maximagesize( $value, $opts, $formelement = null ) {
        list( $w, $h ) = explode( 'x', $opts );
        return ( strlen( $value ) >= intval( $opts ) );
    }

    public static function nopunctuation( $value, $opts='', $formelement = null ) {
        return myrules::regex( strval( $value ), '/^[^().\/\*\^\?#!@$%+=,\"\'><~\[\]{}]+$/' );
    }

    public static function alphanumeric( $value, $opts='', $formelement = null ) {
        return ( myrules::regex( strval( $value ), '/([\w^ ]+)$/' ) && myrules::nopunctuation( strval( $value ), array() ) );
    }

    public static function alphanumericstrict( $value, $opts='', $formelement = null ) {
        return myrules::regex( strval( $value ), '/^([a-zA-Z0-9]+)$/' );
    }

    public static function cpm( $value, $options = '', $input = array() ){
        return ( is_numeric( $value ) && $value > 0.01 && $value < 2000 );
    }

    public static function captcha( $value, $options = '', $input = array() ){
        return( isset( $_SESSION['captcha_string'] ) && strtolower($value) === strtolower( $_SESSION['captcha_string'] ) );
    }

    public static function selectvalid( $value, $options, $input = array() ){
        return ( is_array( $options ) && in_array( strval( $value ), array_map( "strval", array_keys( $options ) ) ) );
    }

    public static function matchfield( $value, $options, $input = array() ){
        return ( is_string( $options ) && is_array( $input ) && isset( $input[ $options ] ) && $value === $input[ $options ] );
    }

    // Validate that two attributes have different values: 1) 'password' => 'different:old_password', 2) username must be different than password
    public static function dontmatchfield( $value, $options, $input = array() ){
        return !myrules::matchfield( $value, $options, $input );
    }

    // "Validate that an attribute is present, when another attribute is present: 'last_name' => 'required_with:first_name'
    public static function fieldrequired( $value, $options, $input = array() ){
        return ( is_string( $options ) && is_array( $input ) && isset( $input[ $options ] ) && !empty( $input[ $options ] ) );
    }

    public static function httpurl( $val ) {

        // Convert to lowercase and trim
        $val = strtolower( trim( $val ) );

        // Check lenght
        if ( strlen( $val ) > 255 )
            return false;

        // Add http:// if needed
        if ( substr( $val, 0, 7 ) != 'http://' && substr( $val, 0, 8 ) != 'https://' )
            $val = 'http://' . $val;

        // Compute expression
        return myrules::regex( $val, '/^(https?:\/\/)' . 
                                     '?(([0-9a-z_!~*\'().&=+$%-]+:)?[0-9a-z_!~*\'().&=+$%-]+@)?' . // user@
                                     '(([0-9]{1,3}\.){3}[0-9]{1,3}' . // IP
                                     '|' . // or domain
                                     '([0-9a-z_!~*\'()-]+\.)*' . // tertiary domain(s), eg www.
                                     '([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.' . // second level domain
                                     '[a-z]{2,6}' /*)'*/ . // first level domain, eg com
                                     '|' . // or localhost
                                     'localhost)' .
                                     '(:[0-9]{1,5})?' . // port
                                     '((\/?)|' . // a slash isn't required if there is no file name
                                     '(\/[0-9a-z_!~*\'().;?:@&=+$,%#-]+)+\/?)$/' ); 
    }

    public static function domain( $val ){
        return ( $val === 'localhost' || myrules::regex( $val, '/^([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6}$/' ) );			
    }

    public static function subdomain( $val ){
        return myrules::regex( $val, '/^([0-9a-z_!~*\'()-]+\.)*([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6}$/' );
    }

    public static function hexcolor( $val ){
        return myrules::regex( $val, '/^#?([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?$/' );
    }
}
