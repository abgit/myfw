<?php

class mymessage{
    
    private $name;
    private $id;
    private $classname;
    private $elements;
    private $closebutton;
    private $customheader;
    private $customsubheader;
    private $video;

    public function __construct( $name ){
        $this->name = $name;
        $this->app = \Slim\Slim::getInstance();
    }

    public function & setID( $id ){
        $this->id = $id;
        return $this;
    }

    public function & setClass( $class ){
        $this->classname = $class;
        return $this;
    }

    public function & setCloseButton( $onclick ){
        $this->closebutton = array( 'onclick' => $onclick );
        return $this;
    }
    
    public function & setHeader( $title, $subtitle, $custom ){
        $this->customheader = array( 'title' => $title, 'subtitle' => $subtitle, 'custom' => $custom );
        return $this;
    }

    public function & setSubHeader( $title, $subtitle ){
        $this->customsubheader = array( 'title' => $title, 'subtitle' => $subtitle );
        return $this;
    }

    public function & setVideo( $src, $name, $class, $width, $height, $iframe = true, $thumb = '' ){
        $this->video = array( 'src' => $src, 'name' => $name, 'class' => $class, 'width' => $width, 'height' => $height, 'iframe' => $iframe, 'thumb' => $thumb );
        return $this;
    }

    public function & addTitle( $title ){
        $this->elements[] = array( 'type' => 'title' , 'text' => $title );
        return $this;
    }
    
    public function & ajaxUpdateTitle( $title ){
        $this->app->ajax()->text( '#' . $this->id . 't', $this->app->ajax()->filter( $title ) );
        return $this;
    }

    public function & addMessage( $message ){
        $this->elements[] = array( 'type' => 'message', 'text' => $message );
        return $this;
    }

    public function & ajaxUpdateMessage( $message ){
        $this->app->ajax()->text( '#' . $this->id . 'm', $this->app->ajax()->filter( $message ) );
        return $this;
    }

    public function & addSmall( $message ){
        $this->elements[] = array( 'type' => 'small', 'text' => $message );
        return $this;
    }

    public function & addButton( $label, $icon, $onclick, $class = 'info', $color = false, $colorbackground = false ){
        $this->elements[] = array( 'type' => 'button', 'label' => $label, 'icon' => $icon, 'onclick' => $onclick, 'class' => $class, 'color' => $color, 'colorbackground' => $colorbackground );
        return $this;
    }


    public function __toString(){
        return $this->render();
    }

    private function render( $values = null ){
        return $this->app->render( '@my/mymessage', array( 'name'            => $this->name,
                                                           'id'              => $this->id,
                                                           'classname'       => $this->classname,
                                                           'customheader'    => $this->customheader,
                                                           'customsubheader' => $this->customsubheader,
                                                           'video'           => $this->video,
                                                           'elements'        => $this->elements,
                                                           'closebutton'     => $this->closebutton
                                                         ), null, null, null, false, false );
    }

}