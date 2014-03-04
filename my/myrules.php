<?php

    use \Michelf\Markdown;
    use \Michelf\MarkdownExtra;

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

    public static function ip( $value, $options = '', $input = array() ){
        return myrules::regex( $value, '/\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/' );
    }

    public static function md5( $value, $opts='', $formelement = null ) {
        return myrules::regex( strval( $value ), '/^([a-z0-9]{32})$/' );
    }

    public static function tag( $value, $opts='', $formelement = null ) {
        return myrules::regex( strval( $value ), '/^([0-9a-zA-Z\-]{3,20})$/' );
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

    // FILTERS
    public static function ftrim( $value ){
        return trim( $value );
    }

    public static function fsha1( $value ){
        return sha1( $value );
    }

    public static function fintval( $value ){
        return intval( $value );
    }

    public static function fshortify( $value ){
        return preg_replace( "/[^a-zA-Z0-9_-]/", "-", $value );
    }

    public static function fhexcolor( $val ){
        return ( strlen( $val ) > 2 && substr( $val, 0, 1 ) != '#' ) ? '#' . $val : $val;
    }

    public static function fhost( $value ){
        if( ! empty( $value ) )
            return ( substr( $value, 0, 7 ) != 'http://' && substr( $value, 0, 8 ) != 'https://' ) ? 'http://' . $value : $value;
    }

    public static function fdomain( $value ){
        return parse_url( ( substr( $value, 0, 7 ) != 'http://' && substr( $value, 0, 8 ) != 'https://' ) ? 'http://' . $value : $value, PHP_URL_HOST );
    }

    public static function fmarkdown( $data ){
        $parser = new MarkdownExtra();
        $parser->no_markup = true;
        $parser->no_entities = true;
        return $parser->transform($data);
    }

    public static function fxss( $data ){

        // remove email before markdown
        $data = preg_replace("/[^@\s]*@[^@\s]*\.[^@\s]*/", "[email]", $data);

        // markdown
        $data = myrules::fmarkdown( $data );

        // xss
        // Fix &entity\n;
        $data = str_replace(array('&amp;','&lt;','&gt;'), array('&amp;amp;','&amp;lt;','&amp;gt;'), $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
 
        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
 
        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
 
        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
 
        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
 
        do{
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        }while ($old_data !== $data);

        // remove email after markdown
        $data = preg_replace("/[^@\s]*@[^@\s]*\.[^@\s]*/", "[email]", $data);
 
        return $data;	
    }
}

