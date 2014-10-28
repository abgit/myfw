<?php

class mynavbar{
    
    private $name;
    private $values = array();
    private $elements = array();
    private $header = array();

    public function __construct( $name ){
        $this->name = $name;
        $this->app = \Slim\Slim::getInstance();
    }

    public function & setHeader( $logo, $logosecure, $href = '', $toogle = true ){
        $this->header = array( 'logo' => $this->app->ishttps() ? $logosecure : $logo, 'href' => $href, 'toogle' => $toogle );
        return $this;
    }
    
    public function & setText( $message, $icon ){
        $this->text = array( 'message' => $message, 'icon' => $icon );
        return $this;
    }

    public function & addItem( $text, $onclick, $href, $class = '' ){
        $this->elements[] = array( 'type' => 'item', 'text' => $text, 'onclick' => $onclick, 'href' => $href, 'class' => $class );
        return $this;
    }

    public function & addCustom( $obj ){
        $this->elements[] = array( 'type' => 'custom', 'obj' => $obj );
        return $this;
    }

    public function & addMenu( $label, $options ){
        $this->elements[] = array( 'type' => 'menu', 'label' => $label, 'options' => $options );
        return $this;
    }

    public function __toString(){
        return $this->render();
    }

    private function render( $values = null ){
        return $this->app->render( '@my/mynavbar', array( 'values'   => is_null( $values ) ? $this->values : $values,
                                                          'name'     => $this->name,
                                                          'header'   => $this->header,
                                                          'text'     => $this->text,
                                                          'elements' => $this->elements
                                                         ), null, null, null, false, false );
    }

}