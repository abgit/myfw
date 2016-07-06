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

    public static function nl2br( $string ){
        return nl2br( $string );
    }

    public static function nozero( $string ){
        return empty( $string ) ? '' : $string;
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

    public static function toround( $value ){
        return rtrim( rtrim( $value, "0"), "." );
    }

    public static function satoshi( $amount ){
        return is_numeric( $amount ) ? round( 100000000 * floatval( str_replace( ',', '.', $amount ) ) ) : '';
    }

    public static function filestack( $url ){

        $url = substr( $url, 33 );

        if( empty( $url ) )
            return '';

        $secret    = \Slim\Slim::getInstance()->config( 'filestack.secret' );
        $policy    = '{"expiry":' . ( time() + 3600 ) . ',"call":"read"}';
        $policy64  = base64_encode( $policy );
        $signature = hash_hmac( 'sha256', $policy64, $secret );
        $security  = "policy:'" . $policy64 . "',signature:'" . $signature . "',";

        return 'https://process.filestackapi.com/' . \Slim\Slim::getInstance()->config( 'filestack.api' ) . '/security=policy:' . $policy64 . ',signature:' . $signature . '/' . $url;
    }

    public static function toBTC($satoshi) {
        return bcdiv((int)(string)$satoshi, 100000000, 8);
    }

    public static function toBTCString($satoshi) {
        return sprintf("%.8f", self::toBTC($satoshi));
    }

    public static function toSatoshiString($btc) {
        return bcmul(sprintf("%.8f", (float)$btc), 100000000, 0);
    }

    public static function toSatoshi($btc) {
        return (int)self::toSatoshiString($btc);
    }
    
    public static function nl2space( $string ){
        return preg_replace( "@( )*[\\r|\\n|\\t]+( )*@", " ", $string );
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

    public static function cdn( $html ){
        $app  = \Slim\Slim::getInstance();
        return preg_replace( '~(href|src|url)([=(])(["\'])(?!(http|https|//))([^"\']+)(' . $app->config( 'filter.cdn.ext' ). ')(["\'])~i', '$1$2"' . $app->config( 'filter.cdn.domain' ) . '$5$6"', $html  );
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
        foreach( $array as $k => $v ){
            if( strval( $string ) === strval( $k ) ){
                return $v;
            }
        }
        return '';
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

    public static function t( $string, $chars=10, $rep='...' ){
        return $chars < strlen($string) ? substr( $string, 0, $chars ) . $rep : $string;
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

    public static function auth0config( $string ){
        return $string === 'access_token' ? \Slim\Slim::getInstance()->auth0()->getAccessToken() : \Slim\Slim::getInstance()->auth0()->getParams( $string );
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
            $string = array_slice( $string, 0, 1 );
        
        return ( $string ? implode( ', ', $string ) . ( strtotime( $datetime ) < time() ? ' ago' : '' ) : 'just now' ) . ( $includeoriginal ? ( ', ' . $datetime ) : '' );
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
}
