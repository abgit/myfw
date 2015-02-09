<?php

class myinfo{
    
    private $name;
    private $values = array();
    private $elements = array();
    private $counter  = 0;
    private $emptymsg = 'No information to display';

    public function __construct( $name ){
        $this->name = $name;
        $this->app = \Slim\Slim::getInstance();
    }

    public function & addH4( $key ){
        $this->elements[ $key ] = array( 'key' => $key, 'type' => 'h4' );
        return $this;
    }

    public function & addH5( $key ){
        $this->elements[ $key ] = array( 'key' => $key, 'type' => 'h5' );
        return $this;
    }

    public function & setEmptyMessage( $emptymsg ){
        $this->emptymsg = $emptymsg;
        return $this;
    }

    public function & addSeparatorLine(){
        $this->elements[] = array( 'type' => 'sep' );
        return $this;
    }

    public function & addText( $key ){
        $this->elements[] = array( 'key' => $key, 'type' => 'text' );
        return $this;
    }

    public function & addTextImage( $key, $keytitle, $keyimage, $keyimagesec, $imagewidth = 200, $imageheight = 150 ){
        $this->elements[] = array( 'key' => $key, 'keyt' => $keytitle, 'keyi' => $this->app->ishttps() ? $keyimagesec : $keyimage, 'type' => 'textimage', 'imagewidth' => $imagewidth, 'imageheight' => $imageheight );
        return $this;
    }

    public function & addList( $key, $options ){
        $this->elements[] = array( 'key' => $key, 'options' => $options, 'type' => 'list' );
        return $this;
    }

    public function & setValues( $values ){
        if( is_array( $values ) )
            $this->values = $values;

        return $this;
    }

    public function __toString(){
        return $this->render();
    }

    private function render( $values = null ){
        return $this->app->render( '@my/myinfo', array( 'values'   => is_null( $values ) ? $this->values : $values,
                                                        'name'     => $this->name,
                                                        'elements' => $this->elements,
                                                        'values'   => $this->values,
                                                        'emptymsg' => $this->emptymsg
                                                         ), null, null, null, false, false );
    }
    
}