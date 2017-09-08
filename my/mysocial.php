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
            $json = file_get_contents( 'https://www.googleapis.com/youtube/v3/search?' . ( empty( $nextPageToken ) ? '' : '&pageToken=' . $nextPageToken ) . 'order=date&part=snippet&channelId='. $channel_id . '&maxResults=25&key='.\Slim\Slim::getInstance()->config( 'youtube.key' ));
            $json = json_decode( $json, true );

            foreach( $json[ 'items' ] as $item ){

                $jsonvideo = file_get_contents('https://www.googleapis.com/youtube/v3/videos?part=statistics&id='. $item[ 'id' ]['videoId'] . '&key='.\Slim\Slim::getInstance()->config( 'youtube.key' ));
                $jsonvideo = json_decode($jsonvideo, true);

                $res[] = array( 'id'       => $item[ 'id' ]['videoId'],
                                'date'     => strtotime( $item[ 'snippet' ][ 'publishedAt' ] ),
                                'views'    => intval( $jsonvideo[ 'items' ][0]['statistics']['viewCount'] ),
                                'likes'    => intval( $jsonvideo[ 'items' ][0]['statistics']['likeCount'] ),
                                'comments' => intval( $jsonvideo[ 'items' ][0]['statistics']['commentCount'] ) );

                if( $total < 2 )
                    return $res;

                $total--;
            }
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

            foreach( $json[ 'user' ][ 'media' ][ 'nodes' ] as $node ){

                $jsonembed = file_get_contents( 'https://api.instagram.com/oembed/?url=http://instagr.am/p/' . $node[ 'code' ] . '/' );
                $jsonembed = json_decode( $jsonembed, true );

                $res[] = array( 'id'       => $node[ 'code' ],
                                'date'     => $node[ 'date' ],
                                'isvideo'  => isset( $node[ 'is_video' ] ) ? $node[ 'is_video' ] : false,
                                'views'    => isset( $node[ 'video_views' ] ) ? $node[ 'video_views' ] : 0,
                                'likes'    => $node[ 'likes' ][ 'count' ],
                                'comments' => $node[ 'comments' ][ 'count' ],
                                'embed'    => $jsonembed[ 'html' ] );

                if( $total < 2 )
                    return $res;

                $total--;
            }

            $max_id = $json[ 'user' ][ 'media' ][ 'page_info' ][ 'end_cursor' ];
        }

        return $res;
    }

}