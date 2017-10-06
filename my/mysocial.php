<?php

class mysocial{

    public function instagram( $url ){

        $username = myfilters::usernameinstagram( $url );

        if( !$username )
           return false;

        $json = file_get_contents( 'https://www.instagram.com/' . $username . '/?__a=1' );
        $json = json_decode( $json, true );

        if( isset( $json[ 'user' ][ 'id' ] ) )
            return array( 'followers' => $json[ 'user' ][ 'followed_by' ][ 'count' ],
                          'posts'     => $json[ 'user' ][ 'media' ][ 'count' ] );
    }

    public function facebook( $url ){

        $username = myfilters::usernamefacebook( $url );

        if( !$username )
           return false;

        $json = file_get_contents( 'https://graph.facebook.com/v2.10/' . $username . '/?fields=fan_count,about&access_token=' . \Slim\Slim::getInstance()->config( 'facebook.key' ) );
        $json = json_decode( $json, true );

        if( isset( $json[ 'fan_count' ] ) )
            return array( 'fans' => $json[ 'fan_count' ] );
    }


    public function facebookVideos( $url, $total ){

        $username = myfilters::usernamefacebook( $url );

        if( !$username )
           return false;

        $after = '';
        $res   = array();

        while(1){
            $json = file_get_contents( 'https://graph.facebook.com/v2.10/' . $username . '/videos?' . ( empty( $after ) ? '' : 'after=' . $after . '&' ) . 'limit=25&access_token=' . \Slim\Slim::getInstance()->config( 'facebook.key' ) );
            $json = json_decode( $json, true );

            if( !isset( $json[ 'data' ] ) )
                return $res;                

            foreach( $json[ 'data' ] as $node ){

                if( !isset( $node[ 'id' ] ) )
                    return $res;                

                $jsonvideo = file_get_contents( 'https://graph.facebook.com/v2.10/' . $node[ 'id' ] . '?fields=title,likes.summary(true),comments.summary(true)&access_token=' . \Slim\Slim::getInstance()->config( 'facebook.key' ) );
                $jsonvideo = json_decode( $jsonvideo, true );

                if( !isset( $jsonvideo[ 'title' ] ) )
                    return $res;                

                $res[] = array( 'id'          => $node[ 'id' ],
                                'date'        => strtotime( $node[ 'updated_time' ] ),
                                'title'       => $jsonvideo[ 'title' ],
                                'description' => $node[ 'description' ],
                                'likes'       => $jsonvideo[ 'likes' ][ 'summary' ][ 'total_count' ],
                                'comments'    => $jsonvideo[ 'comments' ][ 'summary' ][ 'total_count' ] );

                if( $total < 2 )
                    return $res;

                $total--;
            }

            $after = $json[ 'paging' ][ 'cursors' ][ 'after' ];
            sleep( 5 );
        }

        return $res;
    }


    public function youtube( $channel_id ){

        $res = file_get_contents('https://www.googleapis.com/youtube/v3/channels?part=statistics,snippet&id='.$channel_id.'&fields=items(id%2Csnippet(description%2Ctitle)%2Cstatistics(commentCount%2CsubscriberCount%2CvideoCount%2CviewCount))&key='.\Slim\Slim::getInstance()->config( 'youtube.key' ));
        $res = json_decode($res, true);

        if( isset( $res['items'][0]['statistics'] ) )
            return array( 'viewcount'       => $res['items'][0]['statistics']['viewCount'],
                          'commentcount'    => $res['items'][0]['statistics']['commentCount'],
                          'subscribercount' => $res['items'][0]['statistics']['subscriberCount'],
                          'videocount'      => $res['items'][0]['statistics']['videoCount'] );

        return isset( $res['items'][0]['statistics'] ) ? $res['items'][0]['statistics'] : false;
    }

    public function youtubeVideos( $channel_id, $total ){

       $nextPageToken = '';
       $res           = array();

        while(1){
            $json = file_get_contents( 'https://www.googleapis.com/youtube/v3/search?' . ( empty( $nextPageToken ) ? '' : 'pageToken=' . $nextPageToken . '&' ) . 'order=date&part=snippet&channelId='. $channel_id . '&maxResults=25&key='.\Slim\Slim::getInstance()->config( 'youtube.key' ));
            $json = json_decode( $json, true );

            if( !isset( $json[ 'items' ] ) )
                return $res;                

            foreach( $json[ 'items' ] as $item ){

                $jsonvideo = file_get_contents('https://www.googleapis.com/youtube/v3/videos?part=statistics&id='. $item[ 'id' ]['videoId'] . '&key='.\Slim\Slim::getInstance()->config( 'youtube.key' ));
                $jsonvideo = json_decode($jsonvideo, true);

                if( !isset( $jsonvideo[ 'items' ] ) )
                    return $res;                

                $res[] = array( 'id'          => $item[ 'id' ][ 'videoId' ],
                                'title'       => $item[ 'snippet' ][ 'title' ],
                                'description' => $item[ 'snippet' ][ 'description' ],
                                'date'        => strtotime( $item[ 'snippet' ][ 'publishedAt' ] ),
                                'videoviews'  => isset( $jsonvideo[ 'items' ][0]['statistics']['viewCount'] )    ? intval( $jsonvideo[ 'items' ][0]['statistics']['viewCount'] )    : null,
                                'likes'       => isset( $jsonvideo[ 'items' ][0]['statistics']['likeCount'] )    ? intval( $jsonvideo[ 'items' ][0]['statistics']['likeCount'] )    : null,
                                'comments'    => isset( $jsonvideo[ 'items' ][0]['statistics']['commentCount'] ) ? intval( $jsonvideo[ 'items' ][0]['statistics']['commentCount'] ) : null );

                if( $total < 2 )
                    return $res;

                $total--;
            }

            if( !isset( $json[ 'nextPageToken' ] ) )
                return $res;                

            $nextPageToken = $json[ 'nextPageToken' ];
        }
    }


    public function instagramPosts( $url, $total ){

        $username = myfilters::usernameinstagram( $url );

        if( !$username )
           return false;

        $max_id = '';
        $res    = array();

        while(1){
            $json = file_get_contents( 'https://www.instagram.com/' . $username . '/?__a=1' . ( empty( $max_id ) ? '' : '&max_id=' . $max_id ) );
            $json = json_decode( $json, true );

            if( !isset( $json[ 'user' ][ 'media' ][ 'nodes' ] ) )
                return $res;                

            foreach( $json[ 'user' ][ 'media' ][ 'nodes' ] as $node ){

                $res[] = array( 'id'          => $node[ 'code' ],
                                'date'        => $node[ 'date' ],
                                'description' => $node[ 'caption' ],
                                'isvideo'     => isset( $node[ 'is_video' ] ) ? $node[ 'is_video' ] : false,
                                'videoviews'  => isset( $node[ 'video_views' ] ) ? $node[ 'video_views' ] : 0,
                                'likes'       => $node[ 'likes' ][ 'count' ],
                                'comments'    => $node[ 'comments' ][ 'count' ] );

                if( $total < 2 )
                    return $res;

                $total--;
            }

            $max_id = $json[ 'user' ][ 'media' ][ 'page_info' ][ 'end_cursor' ];
        }

        return $res;
    }

}