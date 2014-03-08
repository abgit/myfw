<?php

    use \Michelf\Markdown;
    use \Michelf\MarkdownExtra;

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

    public static function floatval( $string ){
        return is_array($string) ? array_map( 'floatval', $string ) : floatval( $string );
    }

    public static function intval( $value ){
        return is_array( $string ) ? array_map( 'intval', $string ) : intval( $string );
    }

    public static function shortify( $value ){
        return preg_replace( "/[^a-zA-Z0-9_-]/", "-", $value );
    }

    public static function hexcolor( $val ){
        return ( strlen( $val ) > 2 && substr( $val, 0, 1 ) != '#' ) ? '#' . $val : $val;
    }

    public static function statecolor($string){
        if( intval( $string ) > 0 ){ return '#090'; }
        if( intval( $string ) < 0 ){ return '#F00'; }
        return '#960';
    }

    public static function extension($string){
        return strtolower( pathinfo( $string, PATHINFO_EXTENSION ) );
    }

    public static function order( $string ){
        switch( intval( $string ) ){
            case 1: return '1st';
            case 2: return '2nd';
            case 3: return '3rd';
            default: return $string . 'th';
        }
    }

    public static function t( $string, $chars=10, $rep='...' ){
        return $chars > strlen($string) ? $string : substr( $string, 0, $chars ) . $rep;
    }

    public static function m( $string, $showSymbol = true ){
        return ( $showSymbol ? '&euro; ' : '' ) . round( $string, 2 );
    }

    public static function rnumber( $string ){
        $string = ( 0 + str_replace( ",", "", $string ) );
        if( !is_numeric( $string ) )
            return false;
        if( $string > 1000000000000 )
            return round( ( $string / 1000000000000 ), 1 ) . ' trillion';
        elseif( $string > 1000000000 )
            return round( ( $string / 1000000000 ), 1 ) . ' billion';
        elseif( $string > 1000000)
            return round( ( $string / 1000000 ), 1 ) . ' million';
        elseif( $string > 1000 )
            return round( ( $string / 1000 ), 1 ) . ' thousand';
        return number_format( $string );
    }

    public static function bcloudname( $string ){
        $b = new mybcloud();
        $b = $b->getCategoriesList();
        return isset( $b[$string] ) ? $b[$string][0] : 'unknown';
    }

    public static function gravatar( $email, $s = 80, $d = 'mm', $r = 'g' ){
        return '//www.gravatar.com/avatar/' . md5( strtolower( trim( $email ) ) ) . "?s=$s&d=$d&r=$r";
    }

    public static function url( $value ){
        if( ! empty( $value ) )
            return ( substr( $value, 0, 7 ) != 'http://' && substr( $value, 0, 8 ) != 'https://' ) ? 'http://' . $value : $value;
    }

    public static function domain( $value ){
        return parse_url( ( substr( $value, 0, 7 ) != 'http://' && substr( $value, 0, 8 ) != 'https://' ) ? 'http://' . $value : $value, PHP_URL_HOST );
    }
 
    public static function markdown( $data ){
        $parser = new MarkdownExtra();
        $parser->no_markup = true;
        $parser->no_entities = true;
        return $parser->transform($data);
    }

    public static function ago( $datetime, $full = 0 ){
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
            $string = array_slice( $string, 0, 1 );
        
        return $string ? implode( ', ', $string ) . ' ago' : 'just now';
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
}
