<?php

class myclipboard{

    /** @var mycontainer*/
    private $app;

    private $label;
    private $values;
    private $image;

    public function __construct( $c ){
        $this->app = $c;
    }

    public function & setLabel( $label ){
        $this->label = $label;
        return $this;
    }

    public function & setValues( $values ){
        $this->values = $values;
        return $this;
    }

    public function & isImage( $width, $height ){
        $this->image = array( 'width' => $width, 'height' => $height );
        return $this;
    }

    public function __toString(){
        $this->app->ajax->clipboard();

        return $this->app->view->fetch( '@my/myclipboard.twig', array( 'label' => $this->label, 'values' => $this->values, 'image' => $this->image ) );
    }
}