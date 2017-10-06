<?php

class mynavbar{
    
    private $name;
    private $values   = array();
    private $elements = array();
    private $header   = array();
    private $cdn      = false;
    private $text     = '';

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
    
    public function & ajaxUpdateText( $message, $htmlid = 'navbartxt' ){
        $this->app->ajax()->html( '#' . $htmlid, $message, true );
        return $this;
    }

    public function & addItem( $id, $text, $urlobj = '', $class = '', $style = '' ){
        $this->elements[ $id ] = array( 'type' => 'item', 'id' => $id, 'text' => $text, 'urlobj' => $urlobj, 'class' => $class, 'style' => $style );
        return $this;
    }

    public function & addCustom( $id, $obj ){
        $this->elements[ $id ] = array( 'type' => 'custom', 'id' => $id, 'obj' => $obj );
        return $this;
    }

    public function & addMenu( $id, $label, $options, $thumb = '', $htmlid = '', $thumbid = '' ){
        $this->elements[ $id ] = array( 'type' => 'menu', 'id' => $id, 'label' => $label, 'options' => $options, 'thumb' => $thumb, 'htmlid' => $htmlid, 'thumbid' => $thumbid );
        return $this;
    }

    public function & setActive( $id ){

        if( isset( $this->elements[ $id ] ) ){
            $this->elements[ $id ][ 'active' ] = true;
            return $this;
        }

        foreach( $this->elements as $elid => $el ){
            if( isset( $el[ 'options' ] ) ){
                foreach( $el[ 'options' ] as $k => $opt ){
                    if( isset( $opt[ 'id' ] ) && $opt[ 'id' ] == $id ){
                        $this->elements[ $elid ][ 'active' ] = true;
                        $this->elements[ $elid ][ 'options' ][ $k ][ 'active' ] = true;
                        return $this;
                    }
                }
            }
        }

        return $this;
    }

    public function & ajaxThumbChange( $thumb, $htmlid = 'navbarimg' ){
        
        $this->app->ajax()->attr( '#' . $htmlid, 'src', $thumb );
        return $this;
    }

    public function __toString(){
        return $this->render();
    }

    public function render( $values = null ){
        return $this->app->render( '@my/mynavbar', array( 'values'   => is_null( $values ) ? $this->values : $values,
                                                          'name'     => $this->name,
                                                          'header'   => $this->header,
                                                          'text'     => $this->text,
                                                          'cdn'      => $this->cdn,
                                                          'elements' => $this->elements
                                                         ), null, null, null, false, false );
    }

}