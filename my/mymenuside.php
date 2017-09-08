<?php

class mymenuside{

    private $elements = array();
    private $header;

    public function __construct(){
        $this->app = \Slim\Slim::getInstance();
    }

    public function & setHeader( $label, $icon = '' ){
        $this->header = array( 'label' => $label, 'icon' => $icon );
        return $this;
    }

    public function & addElement( $key, $label, $href = '', $onclick = '' ){
        $this->elements[ $key ] = array( 'key' => $key, 'label' => $label, 'href' => $href, 'onclick' => $onclick );
        return $this;
    }

    public function __toString(){
        return $this->render();
    }

    private function render(){
        return $this->app->render( '@my/mymenuside', array( 'elements' => $this->elements,
                                                            'header'   => $this->header ), null, null, null, false, false );

    }
}