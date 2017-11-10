<?php

class mymenuside{

    /** @var mycontainer*/
    private $app;

    private $elements = array();
    private $header;

    public function __construct( $c ){
        $this->app = $c;
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
        return $this->app->view->fetch( '@my/mymenuside.twig', array( 'elements' => $this->elements,
                                                                               'header'   => $this->header ) );

    }
}