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
    private $offset;
    private $reltitle;
    private $relmessagekey;
    private $messagevars = array();

    public function __construct( $name ){
        $this->name = $name;
        $this->app = \Slim\Slim::getInstance();
    }

    public function & setClass( $class ){
        $this->classname = $class;
        return $this;
    }

    public function & setCloseButton( $onclick ){
        $this->closebutton = array( 'onclick' => $onclick );
        return $this;
    }
    
    public function & setHeader( $title, $subtitle = '', $custom = ''){
        $this->customheader = array( 'title' => $title, 'subtitle' => $subtitle, 'custom' => $custom );
        return $this;
    }

    public function & setSubHeader( $title, $subtitle ){
        $this->customsubheader = array( 'title' => $title, 'subtitle' => $subtitle );
        return $this;
    }

    public function & setOffset( $offset ){
        $this->offset = $offset;
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
        $this->app->ajax()->text( '#msg' . $this->name . 't', $this->app->ajax()->filter( $title ) );
        return $this;
    }

    public function & addMessage( $message, $thumb = false ){
        $this->elements[] = array( 'type' => 'message', 'text' => $message, 'thumb' => $thumb );
        return $this;
    }

    public function & addMessageTemplate( $message ){
        $this->elements[] = array( 'type' => 'messagetemplate', 'message' => $message );
        return $this;
    }

    public function & addVariables( $vars ){
        $this->messagevars = $this->messagevars + $vars;
        return $this;
    }

    public function & addCustom( $name, $obj ){
        $this->elements[] = array( 'type' => 'custom' , 'obj' => $obj, 'name' => $name );
        return $this;
    }

    public function & addLabelsList( $list, $header = '' ){
        $this->elements[] = array( 'type' => 'labelslist', 'list' => $list, 'header' => $header );
        return $this;
    }

    public function & addTitleMessageRelative( $title, $messagekey ){
        $this->reltitle = $title;
        $this->relmessagekey = $messagekey;
        return $this;
    }
    
    public function & setValues( $values ){

        if( is_string( $values ) )
            $val = json_decode( $values, true );

        if( json_last_error() !== JSON_ERROR_NONE ){
            $this->addMessage( $values );

        }elseif( isset( $val[ $this->relmessagekey ] ) ){
            $this->addTitle( $this->reltitle )
                 ->addMessage( $val[ $this->relmessagekey ] );
        }

        foreach( $this->elements as $el )
            if( $el[ 'type' ] == 'custom' )
                $el[ 'obj' ]->setValues( $values );

        return $this;
    }


    public function & addThumb( $key, $keyhttps ){
        $this->elements[] = array( 'type' => 'thumb', 'key' => $this->app->ishttps() ? $keyhttps : $key );
        return $this;
    }

    public function & addNewLine(){
        $this->elements[] = array( 'type' => 'nl' );
        return $this;
    }

    public function & ajaxUpdateMessage( $message ){
        $this->app->ajax()->text( '#msg' . $this->name . 'm', $this->app->ajax()->filter( $message ) );
        return $this;
    }

    public function & addSmall( $message ){
        $this->elements[] = array( 'type' => 'small', 'text' => $message );
        return $this;
    }

    public function & addButton( $label, $icon, $onclick, $class = '', $color = false, $colorbackground = false ){
        $this->elements[] = array( 'type' => 'button', 'label' => $label, 'icon' => $icon, 'onclick' => $onclick, 'class' => $class, 'color' => $color, 'colorbackground' => $colorbackground );
        return $this;
    }

    public function & pusherHide( $channel = false, $event = false ){
        $this->app->pusher()->remove( '#msg' . $this->name )->send( $channel, $event );
        return $this;
    }

    public function & pusherShow( $div, $channel = false, $event = false ){
        $this->app->pusher()->append( $div, $this->__toString() )->send( $channel, $event );
        return $this;
    }

    public function & ajaxHide(){
        $this->app->ajax()->remove( '#msg' . $this->name );
        return $this;
    }

    public function & ajaxShow( $div ){
        $this->app->ajax()->append( $div, $this->__toString() );
        return $this;
    }

    public function & ajaxReplace( $div ){
        $this->app->ajax()->html( $div, $this->__toString() );
        return $this;
    }

    public function & ajaxRefresh(){
        $this->app->ajax()->replacewith( '#msg' . $this->name, $this->__toString() );
        return $this;
    }
    
    public function __toString(){
        return $this->render();
    }

    private function render( $values = null ){
        return $this->app->render( '@my/mymessage', array( 'name'            => $this->name,
                                                           'classname'       => $this->classname,
                                                           'customheader'    => $this->customheader,
                                                           'customsubheader' => $this->customsubheader,
                                                           'video'           => $this->video,
                                                           'elements'        => $this->elements,
                                                           'closebutton'     => $this->closebutton,
                                                           'offset'          => $this->offset
                                                         ) + $this->messagevars, null, null, null, false, false );
    }

}