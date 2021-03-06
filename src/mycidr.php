<?php


class mycidr{

    /** @var mycontainer*/
    private $app;

    
    public function __construct( $c ){
        $this->app  = $c;
    }

    public function match( $ip, $rangelist ){
        foreach( explode( ';', $rangelist ) as $range ){
            if( $this->ip_in_range( $ip, $range ) ){
                return true;
            }
        }
        return false;
    }

    private function ip_in_range( $ip, $range ) {
        if ( strpos( $range, '/' ) == false ){
            $range .= '/32';
        }

        // $range is in IP/CIDR format eg 127.0.0.0/24
        list( $range, $netmask ) = explode( '/', $range, 2 );
        $range_decimal = ip2long( $range );
        $ip_decimal = ip2long( $ip );
        $wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
        $netmask_decimal = ~ $wildcard_decimal;
        return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
    }

}