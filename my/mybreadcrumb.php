<?php

class mybreadcrumb{

    private $elements = array();

    public function __construct(){
        $this->app = \Slim\Slim::getInstance();
    }

    public function & addElement( $key, $label, $href = '' ){
        $this->elements[ $key ] = array( 'key' => $key, 'label' => $label, 'href' => $href );
        return $this;
    }

    public function __toString(){
        return $this->render();
    }

    private function render(){
        return $this->app->render( '@my/mybreadcrumb', array( 'elements' => $this->elements ), null, null, null, false, false );

    }
}