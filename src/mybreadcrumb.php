<?php

class mybreadcrumb{

    /** @var mycontainer*/
    private $app;

    private $elements = array();

    public function __construct( $c ){
        $this->app = $c;
    }

    public function & addElement( $key, $label, $href = '' ){
        $this->elements[ $key ] = array( 'key' => $key, 'label' => $label, 'href' => $href );
        return $this;
    }

    public function __toString(){
        return $this->render();
    }

    private function render(){
        return $this->app->view->fetch( '@my/mybreadcrumb.twig', array( 'elements' => $this->elements ) );
    }
}