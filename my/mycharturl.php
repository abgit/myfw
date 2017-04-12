<?php

class mycharturl{
    
    private $app;
    private $curl;
    private $apikey;
    private $template;
    private $data;
    private $values;
    private $xkey;
    private $sizeW = 800;
    private $sizeH = 400;
    private $legend = true;
    private $json_response;
    private $groups = false;
    
    public function __construct(){
        $this->app  = \Slim\Slim::getInstance();
        $this->setAPI( $this->app->config( 'charturl.apikey' ) );
        $this->setTemplate( $this->app->config( 'charturl.template' ) );
    }

    public function & setX( $xkey ){
        $this->xkey = $xkey;
        return $this;
    }

    public function & setData( $key, $label, $type = 'line', $color = '' ){
        $this->data[] = array( 'key' => $key, 'label' => $label, 'type' => $type, 'color' => $color );
        return $this;
    }

    public function & setValues( $values ){
        $this->values = $values;
        return $this;
    }

    public function & setAPI( $apikey ){
        $this->apikey = $apikey;
        return $this;
    }

    public function & setTemplate( $template ){
        $this->template = $template;
        return $this;
    }

    public function & setSize( $w, $h ){
        $this->sizeW = $w;
        $this->sizeH = $h;
        return $this;
    }

    public function & setLegend( $legend ){
        $this->legend = $legend;
        return $this;
    }

    public function & setGroups( $elements ){

        $group = array();
        foreach( $this->data as $el )
            if( in_array( $el[ 'key' ], $elements ) )
                $group[] = $el[ 'label' ];
        
        $this->groups[] = $group;
        return $this;
    }

    public function __toString(){
        return $this->url();
    }

    public function url(){
        $this->curl = curl_init( 'https://charturl.com/short-urls.json?api_key=' . $this->apikey );

        $post = array();

        if( $this->template )
            $post[ 'template' ] = $this->template;

        $post[ 'options' ][ 'legend' ][ 'show' ] = $this->legend;

        $post[ 'options' ][ 'data' ][ 'columns' ] = array();

        $post[ 'options' ][ 'axis' ][ 'y' ][ 'tick' ][ 'outer' ] = false;

        $post[ 'options' ][ 'axis' ][ 'x' ][ 'tick' ][ 'outer' ] = false;
        $post[ 'options' ][ 'axis' ][ 'x' ][ 'type' ] = 'category';
        $post[ 'options' ][ 'axis' ][ 'x' ][ 'categories' ] = array();

        if( $this->data ){
            foreach( $this->data as $d => $col ){
                $post[ 'options' ][ 'data' ][ 'columns' ][ $d ] = array( $col[ 'label' ] );

                $post[ 'options' ][ 'data' ][ 'types' ][ $col[ 'label' ] ] = $col[ 'type' ];

                if( $col[ 'color' ] )
                    $post[ 'options' ][ 'color' ][ 'pattern' ][] = $col[ 'color' ];
            }
        }

        if( $this->groups )
            $post[ 'options' ][ 'data' ][ 'groups' ] = $this->groups;

        if( $this->values ){
            foreach( $this->values as $v => $value ){

                $post[ 'options' ][ 'axis' ][ 'x' ][ 'categories' ][] = $value[ $this->xkey ];

                foreach( $this->data as $d => $col ){
                    $post[ 'options' ][ 'data' ][ 'columns' ][ $d ][] = $value[ $col[ 'key' ] ];
                }
            }
        }
        if( $this->sizeW && $this->sizeH )
            $post[ 'options' ][ 'size' ] = array( 'width' => $this->sizeW, 'height' => $this->sizeH );

        $content = json_encode( $post );

        curl_setopt($this->curl, CURLOPT_HEADER, false);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array( "Content-type: application/json" ) );
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $content);

        $this->json_response = curl_exec($this->curl);

        $status = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        if ( $status != 200 && $status != 201 )
            return false;

        curl_close($this->curl);

        $response = json_decode( $this->json_response, true );

        return ( json_last_error() === JSON_ERROR_NONE && isset( $response[ 'short_url' ] ) ) ? $response[ 'short_url' ] : false;
    }

    public function getError(){
        $error = curl_error($this->curl);
        return empty( $error ) ? $this->json_response : $error;
    }

    public function getErrorNumber(){
        return curl_errno($this->curl);
    }

}