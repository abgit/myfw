<?php

class myfilters{


    public static function trim( $value ){
        return trim( $value );
    }

    public static function sha1( $value ){
        return sha1( $value );
    }

    public static function md5( $string ){
        return md5( $string );
    }

    public static function nl2br( $string ){
        return nl2br( $string );
    }

    public static function nozero( $string ){
        return empty( $string ) ? '' : $string;
    }

    public static function jsondecode( $string ){
        return is_string( $string ) ? json_decode( $string, true ) : $string;
    }

    public static function bitcoinqrcode( $amount, $acc, $size ){
        return \Slim\Slim::getInstance()->blockchain()->qrcode( $amount, '', $acc, $size );
    }
    
    public static function bitcoinfrombtc( $amount, $currencies ){
        $btc  = \Slim\Slim::getInstance()->blockchain()->exchangebtc();
        $vals = array();
        if( is_array( $currencies ) )
            foreach( $currencies as $cur )
                $vals[ $cur ] = isset( $btc[ $cur ] ) ? $btc[ $cur ] : array();

        return $vals;
    }

    public static function usernameinstagram( $value ){

        if( preg_match('/^([a-zA-Z0-9._]+)$/', $value, $matches ) )
            return $matches[1];

        if( preg_match('/^@([a-zA-Z0-9._]+)$/', $value, $matches ) )
            return $matches[1];            

        if( preg_match('/^https?:\/\/(www.)?instagram.com\/([a-zA-Z0-9._]+)/i', $value, $matches ) )
            return $matches[2];

        return false;
    }

    public static function usernamefacebook( $value ){

        $username = '';

        if( preg_match('/^([a-zA-Z0-9._]+)$/', $value, $matches ) )
            $username = $matches[1];

        if( preg_match('/^@([a-zA-Z0-9._]+)$/', $value, $matches ) )
            $username = $matches[1];            

        if( preg_match('/^https?:\/\/(www.)?facebook.com\/([a-zA-Z0-9._]+)/i', $value, $matches ) )
            $username = $matches[2];

        if( !empty( $username ) ){
            $json = file_get_contents( 'https://graph.facebook.com/v2.10/' . $username . '?fields=id&access_token=' . \Slim\Slim::getInstance()->config( 'facebook.key' ) );
            $json = json_decode($json, true);
            
            return isset( $json[ 'id' ] ) ? $json[ 'id' ] : false;
        }

        return false;
    }

    public static function channelyoutube( $value ){

        if( preg_match('/^([a-zA-Z0-9._]+)$/', $value, $matches ) || preg_match('/^@([a-zA-Z0-9._]+)$/', $value, $matches ) ){

            $res = file_get_contents('https://www.googleapis.com/youtube/v3/channels?part=statistics,snippet&id='.$matches[1].'&fields=items(id%2Csnippet(description%2Ctitle)%2Cstatistics(commentCount%2CsubscriberCount%2CvideoCount%2CviewCount))&key='.\Slim\Slim::getInstance()->config( 'youtube.key' ));
            $res = json_decode($res, true);

            if( isset( $res['items'][0]['id'] ) )
                return $matches[1];

            $res = file_get_contents('https://www.googleapis.com/youtube/v3/channels?part=statistics,snippet&forUsername='.$matches[1].'&fields=items(id%2Csnippet(description%2Ctitle)%2Cstatistics(commentCount%2CsubscriberCount%2CvideoCount%2CviewCount))&key='.\Slim\Slim::getInstance()->config( 'youtube.key' ));
            $res = json_decode($res, true);

            return isset( $res['items'][0]['id'] ) ? $res['items'][0]['id'] : false;
        }

        if( preg_match('/^(https?:\/\/)?(www.)?youtube.com\/channel\/([a-zA-Z0-9._]+)/i', $value, $matches ) ){
            $res = file_get_contents('https://www.googleapis.com/youtube/v3/channels?part=statistics,snippet&id='.$matches[3].'&fields=items(id%2Csnippet(description%2Ctitle)%2Cstatistics(commentCount%2CsubscriberCount%2CvideoCount%2CviewCount))&key='.\Slim\Slim::getInstance()->config( 'youtube.key' ));
            $res = json_decode($res, true);
            return isset( $res['items'][0]['id'] ) ? $res['items'][0]['id'] : false;
        }

        if( preg_match('/^(https?:\/\/)?(www.)?youtube.com\/user\/([a-zA-Z0-9._]+)/i', $value, $matches ) ){
            $res = file_get_contents('https://www.googleapis.com/youtube/v3/channels?part=statistics,snippet&forUsername='.$matches[3].'&fields=items(id%2Csnippet(description%2Ctitle)%2Cstatistics(commentCount%2CsubscriberCount%2CvideoCount%2CviewCount))&key='.\Slim\Slim::getInstance()->config( 'youtube.key' ));
            $res = json_decode($res, true);
            return isset( $res['items'][0]['id'] ) ? $res['items'][0]['id'] : false;
        }

        return false;
    }

    public static function toround( $value ){
        return rtrim( rtrim( $value, "0"), "." );
    }

    public static function satoshi( $amount ){
        return is_numeric( $amount ) ? round( 100000000 * floatval( str_replace( ',', '.', $amount ) ) ) : '';
    }

    public static function filestack( $urloriginal, $call = 'read', $custom = '', $process = true, $expiry = null ){

        $url = substr( $urloriginal, 33 );

        if( empty( $url ) || strpos( $urloriginal, 'https://cdn.filestackcontent.com' ) !== 0 )
            return '';

        $secret    = \Slim\Slim::getInstance()->config( 'filestack.secret' );
        $policy    = '{"expiry":' . ( is_numeric( $expiry ) ? $expiry : strtotime( 'first day of next month midnight' ) ) . ',"call":"' . $call . '"}';
        $policy64  = base64_encode( $policy );
        $signature = hash_hmac( 'sha256', $policy64, $secret );
        $security  = "policy:'" . $policy64 . "',signature:'" . $signature . "',";

        return $process ? 'https://process.filestackapi.com/' . \Slim\Slim::getInstance()->config( 'filestack.api' ) . '/security=policy:' . $policy64 . ',signature:' . $signature . '/' . $custom . ( empty( $custom ) ? '' : '/' ) . $url : 'https://www.filestackapi.com/api/file/' . $url . ( empty( $custom ) ? '' : '/' ) . $custom . '?signature=' . $signature . '&policy=' . $policy64;
    }

    public static function filestackresize( $url, $w, $h = null ){
        return myfilters::filestack( $url, 'convert', 'resize=w:' . intval( $w ) . ',h:' . intval( is_null( $h ) ? $w : $h ) . ',f:max/output=f:jpg', true, 4000000000 );
    }

    public static function filestackmovie( $url, $part ){

        $app  = \Slim\Slim::getInstance();
        $hash = 'fs' . md5( $url );
        
        $json = class_exists( 'Memcached' ) ? $app->memcached()->get( $hash ) : false;

        if( $json === false ){
            $url = myfilters::filestack( $url, 'convert', 'video_convert=width:320,height:240,aspect_mode:constrain' );

            if( !empty( $url ) ){
                
                try{

                    $json = file_get_contents( $url );
                    $json = json_decode( $json, true );

                    if( class_exists( 'Memcached' ) )
                        $app->memcached()->set( $hash, isset( $json[ 'data' ] ) ? $json : array(), 604800 );

                }catch( Exception $e ){}
            }
        }

        switch( $part ){
            case 'poster': return isset( $json[ 'data' ][ 'thumb' ] ) ? myfilters::filestack( $json[ 'data' ][ 'thumb' ] ) : '';
            case 'mp4':    return isset( $json[ 'data' ][ 'url' ] )   ? myfilters::filestack( $json[ 'data' ][ 'url' ] )   : '';
            case 'width':  return isset( $json[ 'metadata' ][ 'result' ][ 'width' ] ) ?  $json[ 'metadata' ][ 'result' ][ 'width' ] : '';
            case 'height': return isset( $json[ 'metadata' ][ 'result' ][ 'height' ] ) ? $json[ 'metadata' ][ 'result' ][ 'height' ] : '';
        }
        
        return '';
    }

    public static function toBTC( $satoshi, $decimal = 8 ) {
        return bcdiv((float)(string)$satoshi, 100000000, $decimal );
    }

    public static function toBTCString($satoshi) {
        return sprintf("%.8f", self::toBTC($satoshi));
    }

    public static function toSatoshiString($btc) {
        return bcmul(sprintf("%.8f", (float)$btc), 100000000, 0);
    }

    public static function toSatoshi($btc) {
        return (float)self::toSatoshiString($btc);
    }
    
    public static function nl2space( $string ){
        return preg_replace( "@( )*[\\r|\\n|\\t]+( )*@", " ", $string );
    }

    public static function label( $string, $number ){
        $exp = explode( '!', $string );

        if( count( $exp ) < 2 || strlen( $exp[1] ) == 0 )
            return $string;

        if( intval( $number ) === 1 )
            return $exp[1];

        return $exp[0];
    }

    public static function floatval( $string ){
        return is_array($string) ? array_map( 'floatval', $string ) : floatval( $string );
    }

    public static function intval( $value ){
        return is_array( $string ) ? array_map( 'intval', $string ) : intval( $string );
    }

    public static function shortify( $value, $onlyalpha = false ){
        return $onlyalpha ? preg_replace( "/[^a-zA-Z0-9]/", '', $value ) : preg_replace( "/[^a-zA-Z0-9_-]/", "-", $value );
    }

    public static function hexcolor( $val ){
        return ( strlen( $val ) > 2 && substr( $val, 0, 1 ) != '#' ) ? '#' . $val : $val;
    }

    public static function replaceurl( $val, $valuearray, $tags ){
        return str_replace( isset( $tags[1] ) ? $tags[1] : array(), array_map( function($n) use ( $valuearray ){ return isset( $valuearray[ $n ] ) ? $valuearray[ $n ] : ''; }, isset( $tags[0] ) ? $tags[0] : array() ), $val );
    }

    public static function urlobj( $val, $valuearray = array(), $tags = array() ){
        if( is_array( $val ) && isset( $val[ 'obj' ] ) ){
            switch( $val[ 'obj' ] ){
                case 'url':
                case 'urlsubmit':
                case 'urlajax':   return myfilters::_urlobj( $val, $valuearray, $tags );
                case 'urls':      return myfilters::_urlobjmultiple( $val, $valuearray );
            }
        }

    }

    public static function _urlobj( $val, $valuearray = array(), $tags = array() ){
        if( is_array( $val ) && isset( $val[ 'obj' ] ) ){

            if( isset( $val[ 'url' ] ) ){
                $url = ( !empty( $valuearray ) && !empty( $tags ) ) ? myfilters::replaceurl( $val[ 'url' ], $valuearray, $tags ) : $val[ 'url' ];
            }else{
                $url = null;
            }

            switch( $val[ 'obj' ] ){
                case 'urlajax':   return "onclick=\"myfwsubmit('" . $url . ( is_string( $val[ 'msg' ] ) ? "','" . $val[ 'msg' ] : '' ) . "')\"";
                case 'urlsubmit': return "onclick=\"myfwformsubmit('" . $val[ 'formname' ] . "','','','" . $val[ 'submitbutton' ] . "','" . $val[ 'msg' ] . "','" . $val[ 'action' ] . "'," . $val[ 'delay' ] . ")\"";
                case 'url':       return 'href="' . $url . '"' . ( ( isset( $val[ 'target' ] ) && !empty( $val[ 'target' ] ) ) ? ' target="' . $val[ 'target' ] . '"' : '' );
            }
        }
    }

    public static function _urlobjmultiple( $url, $values ){

        if( is_array( $url ) ){
            $keys               = $url[ 'keys' ];
            $urlmultiplekey     = $url[ 'code' ];
            $urlmultipledefault = $url[ 'default' ];
            $url                = $url[ 'urls' ];

            if( isset( $values[ $urlmultiplekey ] ) ){
                foreach( $url as $i => $urlobj ){
                    if( strval( $i ) === strval( $values[ $urlmultiplekey ] ) ){
                        return myfilters::_urlobj( $urlobj, $values, $keys );
                    }
                }
            }

            return is_string( $urlmultipledefault ) ? myfilters::_urlobj( $urlmultipledefault, $values, $keys ) : '';
        }
    }

    public static function cdn( $html ){
        $app  = \Slim\Slim::getInstance();
        return preg_replace( '~(href|src|url|content)([=(])(["\'])(?!(http|https|//))([^"\']+)(' . $app->config( 'filter.cdn.ext' ). ')(["\'])~i', '$1$2"' . $app->config( 'filter.cdn.domain' ) . '$5$6"', $html  );
    }

    public static function intervalmin( $value, $intervals ){
        if( !is_array( $intervals ) )
            return $intervals;

        $value = intval( $value );
        ksort( $intervals );

        foreach( $intervals as $k => $v ){
            if( is_numeric( $k ) && $value < intval( $k ) )
                return $v;
        }
    
        return isset( $intervals[ 'default' ] ) ? $intervals[ 'default' ] : '';
    }

    public static function intersect( $array, $optarray ){

        if( !is_array( $optarray ) )
            $optarray = explode( ';', $optarray );

        $res = array();

        foreach( $optarray as $k ){
            if( isset( $array[ $k ] ) )
                $res[ $k ] = $array[ $k ];
        }

        return $res;
    }

    public static function values( $array ){
        return is_array( $array ) ? implode( ', ', array_values( $array ) ) : $array;
    }

    public static function replaceonly( $string, $array ){
        
        if( empty( $array ) )
            $array = array();

        foreach( $array as $k => $v ){
            if( strval( $string ) === strval( $k ) ){
                return $v;
            }
        }
        return '';
    }


    public static function urls( $string, $separator = null ){

        $res = array();

        preg_match_all( '#\b((https?://)|(www.))[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $string, $match );

        if( isset( $match[0] ) ){
            foreach( $match[0] as $r ){
                if( !empty( $r ) )
                    $res[] = $r;
            }
        }

        return is_string( $separator ) ? implode( $separator, $res ) : $res;
    }


    public static function meta( $url, $separator = null ){

        $res = array();
        $dom = new DOMDocument( '1.0', 'UTF-8' );

        if( strtolower( substr( $url, 0, 7 ) ) != 'http://' && strtolower( substr( $url, 0, 8 ) ) != 'https://' )
            $url = 'http://' . $url;

        try{
            $content = file_get_contents( $url );
        }catch( Exception $e ){
        }

        if( !empty( $content ) ){

            // set error level
            $internalErrors = libxml_use_internal_errors( true );

            $dom->loadHTML( $content );

            // restore error level
            libxml_use_internal_errors( $internalErrors );

            foreach( $dom->getElementsByTagName( 'meta' ) as $meta ){

                switch( $meta->getAttribute( 'property' ) ){
                    case 'twitter:title':
                    case 'og:title':       $res[ 'title' ]       = $meta->getAttribute( 'content' );
                                           break;

                    case 'twitter:description':
                    case 'og:description': $res[ 'description' ] = $meta->getAttribute( 'content' );
                                       break;

                    case 'twitter:image':
                    case 'og:image':       $content = $meta->getAttribute( 'content' );
                                           if( strtolower( substr( $content, 0, 7 ) ) == 'http://' || strtolower( substr( $content, 0, 8 ) ) == 'https://' )
                                                $res[ 'image' ] = $content;
                                           break;
                }

                $res[ 'url' ] = $url;
            }
        }

        return is_string( $separator ) ? implode( $separator, $res ) : $res;
    }


    public static function inarray( $string, $array, $default = 'unknown' ){

        $string = strval( $string );
        if( is_array( $array ) )
            foreach( $array as $k => $v )
                if( strval( $k ) === $string )
                    return $v;

        return $default;
    }

    public static function statecolor($string){
        if( intval( $string ) > 0 ){ return '#090'; }
        if( intval( $string ) < 0 ){ return '#F00'; }
        return '#960';
    }

    public static function extension($string){
        return strtolower( pathinfo( $string, PATHINFO_EXTENSION ) );
    }

    public static function order( $string, $returnOriginal = true ){
        $sufix = 'th';
        switch( intval( $string ) ){
            case 1: $sufix = 'st'; break;
            case 2: $sufix = 'nd'; break;
            case 3: $sufix = 'rd'; break;
        }
        return $returnOriginal ? $string . $sufix : $sufix;
    }

    public static function t( $string, $chars = 10, $rep = '..', $right = true ){
        return $chars < strlen($string) ? ( $right ? ( substr( $string, 0, $chars ) . $rep ) : ( $rep . substr( $string, strlen( $string ) - $chars ) ) ) : $string;
    }

    public static function m( $string, $showSymbol = true ){
        return ( $showSymbol ? '&euro; ' : '' ) . round( $string, 2 );
    }

    public static function rnumber( $string, $mini = false ){
        $string = ( 0 + str_replace( ",", "", $string ) );
        if( !is_numeric( $string ) )
            return false;
        if( $string > 1000000000000 )
            return round( ( $string / 1000000000000 ), 1 ) . ( $mini ? 'T' : ' trillion' );
        elseif( $string > 1000000000 )
            return round( ( $string / 1000000000 ), 1 ) . ( $mini ? 'B' : ' billion' );
        elseif( $string > 1000000)
            return round( ( $string / 1000000 ), 1 ) . ( $mini ? 'M' : ' million' );
        elseif( $string > 1000 )
            return round( ( $string / 1000 ), 1 ) . ( $mini ? 'K' : ' thousand' );
        return ( $mini ? '<1K' : number_format( $string ) );
    }

    public static function bcloudname( $string ){
        $b = new mybcloud();
        $b = $b->getCategoriesList();
        return isset( $b[$string] ) ? $b[$string][0] : 'unknown';
    }

    public static function gravatar( $hash, $s = 80, $d = 'mm', $r = 'g' ){
        if( strpos( $hash, '@' ) )
            $hash = md5( strtolower( trim( $hash ) ) );

        return ( \Slim\Slim::getInstance()->request->isAjax() ? 'http://www.gravatar.com/avatar/' : 'https://secure.gravatar.com/avatar/' ) . $hash . "?s=$s&d=$d&r=$r";
    }

    public static function transloadit( $json, $step, $property = null, $property2 = null, $property3 = null ){
    
        $arr = is_string( $json ) ? json_decode( $json ) : $json;

        if( is_null( $property ) )
            return ( isset( $arr->ok ) && $arr->ok == 'ASSEMBLY_COMPLETED' && isset( $arr->results->$step ) );

        if( isset( $arr->results->$step ) ){
            $s = & $arr->results->$step;

            if( isset( $s[0]->$property ) ){
             
                $p = & $s[0]->$property;

                if( is_null( $property2 ) )
                    return $p;

                if( isset( $p->$property2->$property3 ) )
                    return $p->$property2->$property3;

                if( isset( $p->$property2 ) )
                    return $p->$property2;

                return null;
            }
        }elseif( isset( $arr->$property ) ){
            return $arr->$property;
        }

        return null;
    }

    public static function url( $value ){
        if( ! empty( $value ) )
            return ( strtolower( substr( $value, 0, 7 ) ) != 'http://' && strtolower( substr( $value, 0, 8 ) ) != 'https://' ) ? 'http://' . $value : $value;
    }

    public static function domain( $value ){
        return parse_url( ( strtolower( substr( $value, 0, 7 ) ) != 'http://' && strtolower( substr( $value, 0, 8 ) ) != 'https://' ) ? 'http://' . $value : $value, PHP_URL_HOST );
    }
 
    public static function urlusername( $value ){
        return parse_url( $value, PHP_URL_USER );
    }

    public static function urlregion( $value ){
        return substr( strstr( parse_url( $value,  PHP_URL_HOST ), '.', true ), 4 );
    }

    public static function markdown( $data ){
        $parser = new \Michelf\MarkdownExtra();
        $parser->no_markup = true;
        $parser->no_entities = true;
        return $parser->transform($data);
    }

    public static function ago( $datetime, $full = 0 , $includeoriginal = 0 ){

        if( strtotime( $datetime ) < 1 )
            return '';

        $now = new DateTime;
        $ago = new DateTime( $datetime );
        $diff = $now->diff( $ago );
        $diff->w = floor( $diff->d / 7 );
        $diff->d -= $diff->w * 7;
        $string = array( 'y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second' );
        foreach( $string as $k => &$v ){
            if( $diff->$k ){
                $v = $diff->$k . ' ' . $v . ( $diff->$k > 1 ? 's' : '' );
            }else{
                unset( $string[$k] );
            }
        }
        if( !$full )
            $string = array_slice( $string, 0, ( ( isset( $string[ 'y' ] ) && isset( $string[ 'm' ] ) ) || ( isset( $string[ 'm' ] ) && isset( $string[ 'w' ] ) ) ) ? 2 : 1 );

        $ago = strtotime( $datetime ) < time();
        
        return ( $string ? ( $ago ? '' : 'in ' ) . implode( ', ', $string ) . ( $ago ? ' ago' : '' ) : 'just now' ) . ( $includeoriginal ? ( ', ' . $datetime ) : '' );
    }

    public static function xss( $data ){

        // remove email before markdown
        $data = preg_replace("/[^@\s]*@[^@\s]*\.[^@\s]*/", "[email]", $data);

        // markdown
        $data = myfilters::markdown( $data );

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

    // js design helper: worldmap
    public static function worldmap( $value, $options ){

        // id
        if( !isset( $options[ 'id' ] ) )
            return;
        
        $id = myfilters::shortify( $options[ 'id' ], true );

        $html = '';
        if( !isset( $options[ 'js' ] ) || $options[ 'js' ] != false ){
            $html .= '<script type="text/javascript">';
        }

        $html .= 'var ' . $id . 'gdpData=' . json_encode( $value ) . ';
                $(function(){$("#' . $options[ 'id' ] . '").vectorMap({map:"world_mill_en",backgroundColor:false,
                onRegionLabelShow:function(event,label,code){if(' . $id . 'gdpData[code]!=undefined){label.text(label.html()+"  "+' . $id . 'gdpData[code]+"%");}else{label.text(label.html()+"  0%");}},
                series:{regions:[{values:' . $id . 'gdpData,scale:["#8FDFFC","#0B62A4"],normalizeFunction:"polynomial"}]},
                regionStyle:{initial:{fill:"#8FDFFC","fill-opacity":1,stroke:"none","stroke-width":0,"stroke-opacity":1},
                hover:{"fill-opacity": 0.8},selected:{fill:"yellow"},selectedHover:{}}});});';

        if( !isset( $options[ 'js' ] ) || $options[ 'js' ] != false ){
            $html .= '</script>';
        }

        return $html;
    }
    
    // js design helper: morris line
    public static function morrisline( $value, $options ){

        // id
        if( !isset( $options[ 'id' ] ) )
            return;
        
        $id = myfilters::shortify( $options[ 'id' ], true );

        $html = '';
        if( !isset( $options[ 'js' ] ) || $options[ 'js' ] != false ){
            $html .= '<script type="text/javascript">';
        }

        $html .= 'Morris.Line({element:"' . $id . '",data:' . json_encode( $value ) . ',grid:false,xkey:"' . ( isset( $options['xkey'] ) ? $options['xkey'] : '' ) . '",ykeys:["' . ( isset( $options['ykeys'] ) ? $options['ykeys'] : '' ) . '"],pointSize:false,yLabelFormat:function(y){if(parseInt(y.toString())==0){return "";}else{return numeral(y.toString()).format("0.0 a");}},labels:["' . ( isset( $options['labels'] ) ? $options['labels'] : '' ) . '"]});';

        if( !isset( $options[ 'js' ] ) || $options[ 'js' ] != false ){
            $html .= '</script>';
        }

        return $html;
    }

    // js design helper: morris donut
    public static function morrisdonut( $value, $options ){

        // id
        if( !isset( $options[ 'id' ] ) )
            return;

        $id = myfilters::shortify( $options[ 'id' ], true );

        $html = '';
        if( !isset( $options[ 'js' ] ) || $options[ 'js' ] != false ){
            $html .= '<script type="text/javascript">';
        }

        $html .= 'Morris.Donut({element:"' . $id . '",data:' . json_encode( $value ) . ',formatter:function(x){return x+"%"}});';

        if( !isset( $options[ 'js' ] ) || $options[ 'js' ] != false ){
            $html .= '</script>';
        }

        return $html;
    }

    public static function autolink( $string ){

        return preg_replace_callback( '~\b(?:https?)://\S+~i', function( $v ){
                    if( preg_match( '~\.jpe?g|\.png|\.gif|\.svg|\.bmp$~i', $v[0] ) ){
                        return '<img src="' . $v[0] . '">';
                    }else{
                        return '<a href="' . $v[0] . '" target="_blank">' . $v[0] . '</a>';
                    }
               }, $string );
    }

}
