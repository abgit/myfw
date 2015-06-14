<?php

class mysocialstats{

    public static function getStats( $url, $asjson = false, $findpattern = false, $socialonly = false ){

        $res = array();
        $urlparsed = parse_url($url);

        if( !isset( $urlparsed[ 'host' ] ) || !isset( $urlparsed[ 'path' ] ) || !isset( $urlparsed[ 'scheme' ] ) )
            return $res;

        if( $urlparsed[ 'host' ] == 'facebook.com' || $urlparsed[ 'host' ] == 'www.facebook.com' ){
            $agent = 'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL,$url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array(
        item1 => 'value',
        item2 => 'value2'
    ));
            $result = curl_exec($ch);

            preg_match_all("/\"commentcount\":([0-9]+)/", $result, $output_array_1);
            preg_match_all("/\"likecount\":([0-9]+)/", $result, $output_array_2);
            preg_match_all("/\"sharecount\":([0-9]+)/", $result, $output_array_3);

            if( isset( $output_array_1[1][0] ) && isset( $output_array_2[1][0] ) && isset( $output_array_3[1][0] ) ){
                $res = array( 'comments' => intval( $output_array_1[1][0] ), 'likes' => intval( $output_array_2[1][0] ), 'shares' => intval( $output_array_3[1][0] ) );

                if( $findpattern ){
                    preg_match_all("/.*(" . $findpattern . ").*/", strstr( $result, 'autoexpand_mode', true ), $output_array);
                    $res[ 'pattern' ] = ( isset( $output_array[1] ) && !empty($output_array[1]) ) ? 1 : 0;
                }
                
                $parseurl = parse_url( $url );
                
                switch( $parseurl[ 'path' ] ){
                    case '/video.php':     parse_str( $parseurl[ 'query' ], $parsequery );
                                           $id = isset( $parsequery[ 'v' ] ) ? $parsequery[ 'v' ] : $parseurl[ 'query' ];
                                           break;
                    case '/permalink.php': parse_str( $parseurl[ 'query' ], $parsequery );
                                           $id = isset( $parsequery[ 'story_fbid' ] ) ? $parsequery[ 'story_fbid' ] : $parseurl[ 'query' ];
                                           break;
                    default:               $id = $parseurl[ 'path' ];
                }
                $res[ 'id' ] = substr( $id, 0, 200 );
                $res[ 'engine' ] = 'facebook';
                $res[ 'provider' ] = 1;
            }

        }elseif( $urlparsed[ 'host' ] == 'twitter.com' || $urlparsed[ 'host' ] == 'www.twitter.com' ){

            $agent = 'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $agent);
            curl_setopt($ch, CURLOPT_URL,$url);
            $result = curl_exec($ch);

            preg_match_all("/.*actionCount\"  data-tweet-stat-count=\"([0-9]+).*/", $result, $output_array);

            if( isset( $output_array[1][0] ) && isset( $output_array[1][1] ) ){
                $res = array( 'shares' => intval( $output_array[1][0] ), 'likes' => intval( $output_array[1][1] ) );

                if( $findpattern ){
                    preg_match_all("/.*(" . $findpattern . ").*/", strstr( $result, 'stream-item-footer', true ), $output_array);
                    $res[ 'pattern' ] = ( isset( $output_array[1] ) && !empty($output_array[1]) ) ? 1 : 0;
                }

                $parseurl = parse_url( $url );
                
                $id = explode( '/', $parseurl[ 'path' ] );
 
                $res[ 'id' ] = isset( $id[ 3 ] ) ? $id[ 3 ] : '';
                $res[ 'engine' ] = 'twitter';
                $res[ 'provider' ] = 2;
            }

        }elseif( $urlparsed[ 'host' ] == 'plus.google.com' || $urlparsed[ 'host' ] == 'google.com' ){

            $agent = 'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, $agent);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Accept-Language: en-US;q=0.6,en;q=0.4") );
            curl_setopt($ch, CURLOPT_URL,$url);
            $result = curl_exec($ch);

            preg_match_all("/.*\[\[([0-9]+)\]\s\]\s\]\s}\);<\/script><script>AF_initDataCallback/", $result, $output_array_1);
            preg_match_all("/([0-9]+)(,[^,]*){16}social.google.com\"(,[^,]*){8},([0-9]+)(,[^,]*){2},([0-9]+)/", $result, $output_array_2);

            if( isset( $output_array_1[1][0] ) && isset( $output_array_2[1][0] ) && isset( $output_array_2[6][0] ) ){
                $res = array( 'views' => $output_array_1[1][0], 'likes' => intval( $output_array_2[1][0] ), 'shares' => intval( $output_array_2[6][0] ), 'comments' => intval( $output_array_2[4][0] ) );

                if( $findpattern ){
                    preg_match_all("/.*(" . $findpattern . ").*/", strstr( $result, 'span class="Ss"', true ), $output_array);
                    $res[ 'pattern' ] = ( isset( $output_array[1] ) && !empty($output_array[1]) ) ? 1 : 0;
                }

                $parseurl = parse_url( $url );
                
                $id = explode( '/', $parseurl[ 'path' ] );
 
                $res[ 'id' ] = ( isset( $id[ 1 ] ) && isset( $id[ 3 ] ) ) ? ( $id[ 1 ] . $id[ 3 ] ) : '';
                $res[ 'engine' ] = 'google';
                $res[ 'provider' ] = 3;
            }

        }elseif( !$socialonly ){

            $twcounter = file_get_contents( 'http://cdn.api.twitter.com/1/urls/count.json?url=' . urlencode( $url ) );
            $twcounter = json_decode( $twcounter, true );
            if( isset( $twcounter[ 'count' ] ) )
                $res[ 'twitter' ] = $twcounter[ 'count' ];

            $fwcounter = file_get_contents( 'https://graph.facebook.com/fql?q=' . urlencode( 'SELECT like_count,total_count,share_count,click_count,comment_count FROM link_stat WHERE url="' . $url . '"' ) );
            $fwcounter = json_decode( $fwcounter, true );
            if( isset( $fwcounter[ 'data' ][0][ 'like_count' ] ) )
                $res[ 'fblikes' ] = $fwcounter[ 'data' ][0][ 'like_count' ];

            if( isset( $fwcounter[ 'data' ][0][ 'share_count' ] ) )
                $res[ 'fbshares' ] = $fwcounter[ 'data' ][0][ 'share_count' ];

            if( isset( $fwcounter[ 'data' ][0][ 'click_count' ] ) )
                $res[ 'fbclicks' ] = $fwcounter[ 'data' ][0][ 'click_count' ];

            if( isset( $fwcounter[ 'data' ][0][ 'comment_count' ] ) )
                $res[ 'fbcomments' ] = $fwcounter[ 'data' ][0][ 'comment_count' ];

            $linkcounter = file_get_contents( 'http://www.linkedin.com/countserv/count/share?format=json&url=' . urlencode( $url ) );
            $linkcounter = json_decode( $linkcounter, true );
            if( isset( $linkcounter[ 'count' ] ) )
                $res[ 'linkedin' ] = $linkcounter[ 'count' ];

            $pinterestcounter = file_get_contents( 'http://api.pinterest.com/v1/urls/count.json?url=' . urlencode( $url ) );
            $pinterestcounter = str_replace( array( 'receiveCount(', ')' ) , '', $pinterestcounter );
            $pinterestcounter = json_decode( $pinterestcounter, true );
            if( isset( $pinterestcounter[ 'count' ] ) )
                $res[ 'pinterest' ] = $pinterestcounter[ 'count' ];
        }

        return $asjson ? json_encode( $res ) : $res;
    }

}