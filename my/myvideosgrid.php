<?php

class myvideosgrid{

    /** @var mycontainer*/
    private $app;

    private $elements = array();
    private $values;

    public function __construct( $c ){
        $this->app = $c;
    }

    public function & setEmbed( $keyembed ){
        $this->elements[ 'embed' ] = array( 'keyembed' => $keyembed );
        return $this;
    }

    public function & setTitle( $keytitle, $keyhref ){
        $this->elements[ 'title' ] = array( 'keytitle' => $keytitle, 'keyhref' => $keyhref );
        return $this;
    }

    public function & setDescription( $key ){
        $this->elements[ 'description' ] = array( 'key' => $key );
        return $this;
    }

    public function & addInfo( $key, $sufix ){
        $this->elements[ 'info' ][] = array( 'key' => $key, 'sufix' => $sufix );
        return $this;
    }

    public function & setValues( $values ){
        $this->values = is_string( $values ) ? json_decode( $values, true ) : $values;
        return $this;
    }

    public function __toString(){
        return $this->app->view->fetch( '@my/myvideosgrid.twig', array( 'elements' => $this->elements, 'values' => $this->values ) );
    }
}