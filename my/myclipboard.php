<?php

class myclipboard{

    private $label;
    private $values;
    private $image;

    public function __construct(){
        $this->app = \Slim\Slim::getInstance();
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
        return $this->render();
    }

    private function render(){

        $this->app->ajax()->clipboard();

        return $this->app->render( '@my/myclipboard', array( 'label' => $this->label, 'values' => $this->values, 'image' => $this->image ), null, null, null, false, false );

    }
}