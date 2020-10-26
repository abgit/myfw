<?php


// TODO: port variables to social.* syntax
class mysocial{

    /** @var mycontainer*/
    private $app;

    public function __construct( $c ){
        $this->app = $c;
    }

    public function instagram( $url ): array {

        $username = $this->app->filters->usernameinstagram( $url );

        if( !$username ) {
            return array();
        }

        $req = Requests::get( 'https://www.instagram.com/' . $username . '/?__a=1' );
        if( !$req->success ){
            return array();
        }

        $json = json_decode($req->body, true, 512, JSON_THROW_ON_ERROR);

        if( !isset( $json[ 'graphql' ][ 'user' ] ) ) {
            return array();
        }

        $followers = $json[ 'graphql' ][ 'user' ][ 'edge_followed_by' ][ 'count' ];

        $likes        = 0;
        $comments     = 0;
        $interactions = 0;
        $engage       = array();
        $posts        = $json[ 'graphql' ][ 'user' ][ 'edge_owner_to_timeline_media' ][ 'edges' ];
        $videostotal  = count( $posts );

        foreach( $posts as $post ){
            $likes        += (int)$post[ 'node' ][ 'edge_liked_by' ][ 'count' ];
            $comments     += (int)$post[ 'node' ][ 'edge_media_to_comment' ][ 'count' ];
            $interactions += (int)$post[ 'node' ][ 'edge_liked_by' ][ 'count' ] + (int)$post[ 'node' ][ 'edge_media_to_comment' ][ 'count' ];
        }

        return array( 'title'           => $json[ 'graphql' ][ 'user' ][ 'full_name' ],
                      'description'     => empty( $json[ 'graphql' ][ 'user' ][ 'biography' ] ) ? null : $json[ 'graphql' ][ 'user' ][ 'biography' ],
                      'username'        => $json[ 'graphql' ][ 'user' ][ 'username' ],
                      'isprivate'       => $json[ 'graphql' ][ 'user' ][ 'is_private' ],
                      'isverified'      => $json[ 'graphql' ][ 'user' ][ 'is_verified' ],
                      'thumb'           => $json[ 'graphql' ][ 'user' ][ 'profile_pic_url_hd' ],
                      'followers'       => $followers,
                      'postscounter'    => $json[ 'graphql' ][ 'user' ][ 'edge_owner_to_timeline_media' ][ 'count' ],
                      'categories'      => $json[ 'graphql' ][ 'user' ][ 'business_category_name' ],
                      'avglatest'       => $videostotal,
                      'avglikes'        => $videostotal > 0 ? (int)($likes / $videostotal) : 0,
                      'avgcomments'     => $videostotal > 0 ? (int)($comments / $videostotal) : 0,
                      'avginteractions' => $videostotal > 0 ? (int)($interactions / $videostotal) : 0,
                      'posts'           => $posts );
    }

    public function facebook( $url ){

        $username = $this->app->filters->usernamefacebook( $url );

        if( !$username )
           return false;

        $json = file_get_contents( 'https://graph.facebook.com/v2.10/' . $username . '/?fields=fan_count,about&access_token=' . $this->app->config[ 'facebook.key' ] );
        $json = json_decode( $json, true );

        if( isset( $json[ 'fan_count' ] ) )
            return array( 'fans' => $json[ 'fan_count' ] );

        return false;
    }


    public function facebookVideos( $url, $total ){

        $username = $this->app->filters->usernamefacebook( $url );

        if( !$username )
           return false;

        $after = '';
        $res   = array();

        while(1){
            $json = file_get_contents( 'https://graph.facebook.com/v2.10/' . $username . '/videos?' . ( empty( $after ) ? '' : 'after=' . $after . '&' ) . 'limit=25&access_token=' . $this->app->config[ 'facebook.key' ] );
            $json = json_decode( $json, true );

            if( !isset( $json[ 'data' ] ) )
                return $res;                

            foreach( $json[ 'data' ] as $node ){

                if( !isset( $node[ 'id' ] ) )
                    return $res;                

                $jsonvideo = file_get_contents( 'https://graph.facebook.com/v2.10/' . $node[ 'id' ] . '?fields=title,likes.summary(true),comments.summary(true)&access_token=' . $this->app->config[ 'facebook.key' ] );
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

    public function youtubeid( $channel_name ){
        $res = file_get_contents('https://www.googleapis.com/youtube/v3/channels?part=id&forUsername='.$channel_name.'&key=' . $this->app->config[ 'youtube.key' ] );
        $res = json_decode($res, true);

        return isset( $item['id'] ) ? $item['id'] : null;
    }

    public function youtubeChannels( $channel_id ){

        $res = file_get_contents('https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&id='.$channel_id.'&key=' . $this->app->config[ 'youtube.key' ] );
        $res = json_decode($res, true);

        $return = array();

        if( isset( $res['items'] ) )
            foreach ($res['items'] as $item ) {

            $chid        = $item[ 'id' ];
            $playlistid  = 'UU' . substr( $chid , 2);
            $subscribers = empty($item['statistics']['subscriberCount']) ? 0 : intval($item['statistics']['subscriberCount']);

            // get categories
            $categories = array();

            if (isset($item['topicDetails']['topicIds']))
                foreach (array_unique($item['topicDetails']['topicIds']) as $cat)
                    if (isset($this->app->list->youtubecategories[$cat]))
                        $categories[] = $this->app->list->youtubecategories[$cat];

            $return[ $chid ] = array(
                'title' => $item['snippet']['title'],
                'description' => $item['snippet']['description'],
                'playlistid'  => $playlistid,
                'username' => isset($item['snippet']['customUrl']) ? $item['snippet']['customUrl'] : null,
                'countrycode' => isset($item['snippet']['country']) ? $item['snippet']['country'] : null,
                'countryname' => isset($item['snippet']['country']) && isset($this->app->list->countries[$item['snippet']['country']]) ? $this->app->list->countries[$item['snippet']['country']] : null,
                'thumb' => $item['snippet']['thumbnails']['high']['url'],
                'views'           => empty( $item['statistics']['viewCount'] )       ? null : intval( $item['statistics']['viewCount'] ),
                'comments'        => empty( $item['statistics']['commentCount'] )    ? null : intval( $item['statistics']['commentCount'] ),
                'subscribers' => $subscribers,
                'videos'          => empty( $item['statistics']['videoCount'] )      ? null : intval( $item['statistics']['videoCount'] ),
                'categories' => implode(' ', $categories),
  //              'avglatest' => $videostotal,
  //              'avgviews' => $videostotal == 0 ? 0 : intval($views / $videostotal),
  //              'avglikes' => $videostotal == 0 ? 0 : intval($likes / $videostotal),
  //              'avgcomments' => $videostotal == 0 ? 0 : intval($comments / $videostotal),
  //              'avginteractions' => $videostotal == 0 ? 0 : intval($interactions / $videostotal),
  //              'avgengage' => $videostotal == 0 ? 0 : floatval(bcdiv(array_sum($engage) * 100 / $videostotal, 1, 3))
                );
        }

        //d( $return );
        return $return;
    }


    public function youtubePlaylist( $playlistid, $maxResults = 12 ){

        $json = file_get_contents('https://www.googleapis.com/youtube/v3/playlistItems?part=snippet&playlistId=' . $playlistid . '&maxResults=' . $maxResults . '&key=' . $this->app->config['youtube.key']);
        $json = json_decode($json, true);

        $videoids = array();

        foreach ($json['items'] as $item)
            if (isset($item['snippet']['resourceId']['videoId']))
                $videoids[] = $item['snippet']['resourceId']['videoId'];

        return $videoids;
    }

    public function youtubeVideos( $videoid ){

        $json = file_get_contents('https://www.googleapis.com/youtube/v3/videos?part=statistics&id=' . $videoid . '&key=' . $this->app->config['youtube.key']);
        $json = json_decode($json, true);

        $return = array();

        foreach ($json['items'] as $item)
            if (isset($item['statistics']))
                $return[ $item['id'] ] = $item['statistics'];

        return $return;
    }


    public function youtubeBio( $url, $word ){
        $html = file_get_contents( $url );

        preg_match_all('/about\-stat".*/', $html, $output_array);

        $res = array();
        $res[ 'bio' ] = strpos( $html, $word ) !== false;
        $res[ 'subscribers' ] = preg_replace("/[^0-9\/]/", "", strip_tags( $output_array[0][0] ) );
        $res[ 'views' ]       = preg_replace("/[^0-9\/]/", "", strip_tags( $output_array[0][1] ) );
        $res[ 'datesince' ]   = preg_replace("/[^0-9\/]/", "", strip_tags( $output_array[0][2] ) );

        return $res;
    }


    public function instagramBio( $url, $word ){
        $html = file_get_contents( $url );

        preg_match('/([0-9kKmM.]+) Followers/', $html, $output_array);

        $res = array();
        $res[ 'bio' ]         = strpos( $html, $word ) !== false;
        $res[ 'subscribers' ] = $this->app->filters->irnumber( $output_array[1] );

        return $res;
    }

    public function facebookBio( $url, $word ){
        $html = file_get_contents( $url );

        preg_match('/([0-9kKmM.]+) people follow this/', $html, $output_array);

        $res = array();
        $res[ 'bio' ]         = strpos( $html, $word ) !== false;
        $res[ 'subscribers' ] = $output_array[1];

        return $res;
    }


    public function instagramPosts( $url, $total ){

        $username = $this->app->filters->usernameinstagram( $url );

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