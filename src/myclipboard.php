<?php

class myclipboard{

    /** @var mycontainer*/
    private $app;

    private $label;
    private $values;
    private $image;
    private $classname = '';

    public function __construct( $c ){
        $this->app = $c;
    }

    public function & setLabel( $label ){
        $this->label = $label;
        return $this;
    }

    public function & setClass( $classname ){
        $this->classname = $classname;
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
        $this->app->ajax->clipboard( $this->classname );

        return $this->app->view->fetch( '@my/myclipboard.twig', array( 'label' => $this->label, 'classname' => $this->classname, 'values' => $this->values, 'image' => $this->image ) );
    }
}