<?php

class myrules{

    private $app;

    public function __construct( $c ){
        $this->app = $c;
    }

    public function required( $value = '', $options = '', $input = array(), $el = array() ){
        return !empty( $value );
    }

    public function numeric( $value, $opts='', $formelement = null ) {
        return $this->regex( strval( $value ), '/(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)/' );
    }

    public function maxlen( $value, $options = '', $input = array() ){
        return( intval( $options ) > 0 && strlen( $value ) <= intval( $options ) );
    }

    public function regex( $value, $options, $input = array() ) {
        return preg_match( $options, $value );
    }

    public function email( $value, $options = '', $input = array() ){
        return $this->regex( $value, '/^((\"[^\"\f\n\r\t\v\b]+\")|([\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+(\.[\w\!\#\$\%\&\'\*\+\-\~\/\^\`\|\{\}]+)*))@((\[(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))\])|(((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9]))\.((25[0-5])|(2[0-4][0-9])|([0-1]?[0-9]?[0-9])))|((([A-Za-z0-9\-])+\.)+[A-Za-z\-]+))$/' );
    }

    public function not_email( $value, $opts='', $formelement = null ) {
        return !$this->email( $value );
    }

    public function money( $value, $opts='', $formelement = null){
        return $this->regex( strval( $value ), '/^([0-9]{1,5}([\.\,][0-9]{1,8})?)$/' );
    }

   public function isdecimal( $value, $opts='', $formelement = null){
        return is_numeric( $value ) && ( floor( $value ) != $value );
    }

    public function bitcoin( $value, $opts='', $formelement = null){
        return $this->regex( strval( $value ), '/^([1-9]{0,3}[0-9]([\.\,][0-9]{1,8})?)$/' );
    }

    public function unsigned( $value, $opts='', $formelement = null){
        return $this->regex( strval( $value ), '/^([1-9]*[0-9]{1,20}(\.[0-9]{1,2}){0,1})$/' );
    }

    public function ip( $value, $options = '', $input = array() ){
        return $this->regex( $value, '/\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/' );
    }

    public function md5( $value, $opts='', $formelement = null ) {
        return $this->regex( strval( $value ), '/^([a-z0-9]{32})$/' );
    }

    public function bitcoinaddress( $value, $opts='', $formelement = null ) {
        return JaycoDesign\BTCHelper\BTCHelper::validBitcoinAddress( trim( strval( $value ) ) );
    }

    public function tag( $value, $opts='', $formelement = null ) {
        return $this->regex( strval( $value ), '/^([0-9a-zA-Z\-]{3,20})$/' );
    }

    public function phonenumber( $value, $opts='', $formelement = null ) {
        return $this->regex( strval( $value ), '/^([0-9]{10,18})$/' );
    }

    public function twofactortoken( $value, $opts='', $formelement = null ) {
        return $this->regex( strval( $value ), '/^([0-9]{4}|[0-9]{6})$/' );
    }

    public function lettersonly( $values, $opts='', $formelement = null ) {
        return ( $this->regex( $values, '/([\D^ ]+)$/' ) && $this->nopunctuation( $values, array() ) );
    }

    public function character( $value, $opts='', $formelement = null ) {
        return ( strlen( strval( $value ) ) == 1 ) && $this->lettersonly( $value, array() );
    }

    public function maxhyperlinks( $val, $opts, $formelement = null ) {
        return (preg_match_all( "/href=/i", $val, $matches1 ) + preg_match_all( "/\[url=/i", $val, $matches2 ) + preg_match_all( "/http\:\/\//i", $val, $matches ) <= intval( $opts ) );
    }

    public function value( $value, $options, $formelement = null ) {
        return $value === $options;
    }

    public function maxlength( $value, $opts, $formelement = null ) {
        return ( strlen( $value ) <= intval( $opts ) );
    }

    public function minlength( $value, $opts, $formelement = null ) {
        return ( strlen( $value ) >= intval( $opts ) );
    }

    public function maxvalue( $value, $opts, $formelement = null ) {
        return ( intval( $value ) <= intval( $opts ) );
    }

    public function instagramBio( $value, $bio ){
        $username = $this->app->filters->usernameinstagram( $value );

        if( !$username )
            return false;

        $json = file_get_contents( 'https://www.instagram.com/' . $username . '/?__a=1' );
        $json = json_decode( $json, true );
        return ( isset( $json[ 'user' ][ 'biography' ] ) && strpos( strtoupper( $json[ 'user' ][ 'biography' ] ), strtoupper( $bio ) ) !== false );
    }

    public function youtubeBio( $channel_id, $bio ){

        $channel_id = $this->app->channelyoutube( $channel_id );

        if( !$channel_id )
            return false;

        $res = file_get_contents('https://www.googleapis.com/youtube/v3/channels?part=statistics,snippet&id='.$channel_id.'&fields=items(id%2Csnippet(description%2Ctitle)%2Cstatistics(commentCount%2CsubscriberCount%2CvideoCount%2CviewCount))&key='.\Slim\Slim::getInstance()->config( 'youtube.key' ));
        $res = json_decode($res, true);
        return ( isset( $res['items'][0]['snippet']['description'] ) && strpos( strtoupper( $res['items'][0]['snippet']['description'] ), strtoupper( $bio ) ) !== false );
    }

    public function maxdecimal( $value, $opts, $formelement = null ) {
        $x = explode( '.', strval( $value ) );
        return ( is_array( $x ) && ( ( count( $x ) === 1 && isset( $x[0] ) && is_numeric( $x[0] ) ) || ( count( $x ) === 2 && isset( $x[0] ) && is_numeric( $x[0] ) && isset( $x[1] ) && is_numeric( $x[1] ) && strlen( $x[1] ) <= intval( $opts ) ) ) );
    }

    public function minvalue( $value, $opts, $formelement = null ) {
        return ( intval( $value ) >= intval( $opts ) );
    }

    public function isdate( $value ){
        if( is_string( $value ) && $this->regex( $value, '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/' ) ){
            $d = DateTime::createFromFormat( 'Y-m-d', $value );        
            return ( $d && $d->format( 'Y-m-d' ) === $value );
        }

        return false;
    }

    public function minoptions( $value, $options = '', $input = array() ){

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

    public function maxoptions( $value, $options = '', $input = array() ){

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

    public function maximagesize( $value, $opts, $formelement = null ) {
        list( $w, $h ) = explode( 'x', $opts );
        return ( strlen( $value ) >= intval( $opts ) );
    }

    public function nopunctuation( $value, $opts='', $formelement = null ) {
        return $this->regex( strval( $value ), '/^[^().\/\*\^\?#!@$%+=,\"\'><~\[\]{}]+$/' );
    }

    public function alphanumeric( $value, $opts='', $formelement = null ) {
        return ( $this->regex( strval( $value ), '/([\w^ ]+)$/' ) && $this->nopunctuation( strval( $value ), array() ) );
    }

    public function alphanumericstrict( $value, $opts='', $formelement = null ) {
        return $this->regex( strval( $value ), '/^([a-zA-Z0-9]+)$/' );
    }

    public function cpm( $value, $options = '', $input = array() ){
        return ( is_numeric( $value ) && $value > 0.01 && $value < 2000 );
    }

    public function captcha( $value, $options = '', $input = array() ){
        return( isset( $_SESSION['captcha_string'] ) && strtolower($value) === strtolower( $_SESSION['captcha_string'] ) );
    }

    public function selectvalid( $value, $options, $input = array() ){
        return ( is_array( $options ) && in_array( strval( $value ), array_map( "strval", array_keys( $options ) ) ) );
    }

    public function matchfield( $value, $options, $input = array() ){
        return ( is_string( $options ) && is_array( $input ) && isset( $input[ $options ] ) && $value === $input[ $options ] );
    }

    // Validate that two attributes have different values: 1) 'password' => 'different:old_password', 2) username must be different than password
    public function dontmatchfield( $value, $options, $input = array() ){
        return !$this->matchfield( $value, $options, $input );
    }

    // "Validate that an attribute is present, when another attribute is present: 'last_name' => 'required_with:first_name'
    public function fieldrequired( $value, $options, $input = array() ){
        return ( is_string( $options ) && is_array( $input ) && isset( $input[ $options ] ) && !empty( $input[ $options ] ) );
    }

    public function httpurl( $val ) {

        // Convert to lowercase and trim
        $val = strtolower( trim( $val ) );

        // Check lenght
        if ( strlen( $val ) > 255 )
            return false;

        // Add http:// if needed
        if ( substr( $val, 0, 7 ) != 'http://' && substr( $val, 0, 8 ) != 'https://' )
            $val = 'http://' . $val;

        // Compute expression
        return $this->regex( $val, '/^(https?:\/\/)' . 
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

    public function domain( $val ){
        return ( $val === 'localhost' || $this->regex( $val, '/^([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6}$/' ) );			
    }

    public function subdomain( $val ){
        return $this->regex( $val, '/^([0-9a-z_!~*\'()-]+\.)*([0-9a-z][0-9a-z-]{0,61})?[0-9a-z]\.[a-z]{2,6}$/' );
    }

    public function hexcolor( $val ){
        return $this->regex( $val, '/^#?([a-fA-F0-9]){3}(([a-fA-F0-9]){3})?$/' );
    }
}
