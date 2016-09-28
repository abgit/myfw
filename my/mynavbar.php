<?php

class mynavbar{
    
    private $name;
    private $values   = array();
    private $elements = array();
    private $header   = array();
    private $cdn      = false;

    public function __construct( $name ){
        $this->name = $name;
        $this->app = \Slim\Slim::getInstance();
    }

    public function & setHeader( $logo, $logosecure = null, $href = '', $toogle = true ){
        $this->header = array( 'logo' => ( !is_null( $logosecure ) && $this->app->ishttps() ) ? $logosecure : $logo, 'href' => $href, 'toogle' => $toogle );
        return $this;
    }

    public function & setCDN( $cdn ){
        $this->cdn = $cdn;
        return $this;
    }

    public function & setText( $message, $icon = null, $thumb = null, $thumbclass = null ){
        $this->text = array( 'message' => $message, 'icon' => $icon, 'thumb' => $thumb, 'thumbclass' => $thumbclass );
        return $this;
    }
    
    public function & ajaxUpdateText( $message ){
        $this->app->ajax()->html( '#navbartxt', $message, true );
        return $this;
    }

    public function & addItem( $text, $onclick = '', $href = '', $class = '', $style = '' ){
        $this->elements[] = array( 'type' => 'item', 'text' => $text, 'onclick' => $onclick, 'href' => $href, 'class' => $class, 'style' => $style );
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

    public function & ajaxThumbChange( $thumb ){
        
        $this->app->ajax()->attr( '#navbarimg', 'src', $thumb );
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
                                                          'cdn'      => $this->cdn,
                                                          'elements' => $this->elements
                                                         ), null, null, null, false, false );
    }

}