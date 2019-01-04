<?php

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class mymessage{

    /** @var mycontainer*/
    private $app;

    private $name;
    private $classname;
    private $elements;
    private $customheader;
    private $customsubheader;
    private $video;
    private $offset;
    private $reltitle;
    private $relmessagekey;
    private $messagevars = array();
    private $messageprefix;
    private $messagesufix;
    private $values = array();
    private $tip = false;
    private $depends = '';

    public function __construct( $c ){
        $this->app = $c;
    }

    public function & setName( $name ){
        $this->name = $name;
        return $this;
    }

    public function & setClass( $class ){
        $this->classname = $class;
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

    public function & setDepends( $depends ){
        $this->depends = $depends;
        return $this;
    }

    public function & addTitle( $title ){
        $this->elements[] = array( 'type' => 'title' , 'text' => $title );
        return $this;
    }
    
    public function & ajaxUpdateTitle( $title ){
        $this->app->ajax->text( '#msg' . $this->name . 't', $this->app->ajax->filter( $title ) );
        return $this;
    }

    public function & addMessage( $message, $thumb = false ){
        $this->elements[] = array( 'type' => 'message', 'text' => $message, 'thumb' => $thumb );
        return $this;
    }

    public function & setMessageAddon( $prefix, $sufix = '' ){
        $this->messageprefix = $prefix;
        $this->messagesufix  = $sufix;
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

    public function & addTitleRelative( $title ){
        $this->reltitle = $title;
        return $this;
    }

    public function & addMessageRelative( $messagekey ){
        $this->relmessagekey = $messagekey;
        return $this;
    }

    public function & setTip(){

        $sufix = $this->app->config[ 'message.tip.sufix' ];

        $this->tip = array( 'id' => 'tip' . $this->name . $sufix, 'onclick' => $this->app->urlfor->ajax( 'myfwtip', array( 'tip' => 'tip' . $this->name ), '' ) );

        if( class_exists( 'Memcached' ) && $this->app->memcached->get( $this->tip[ 'id' ] ) !== 1 )
            $this->app->memcached->set( $this->tip[ 'id' ], 0 );

        return $this;
    }

    public function processTip( Request $request, Response $response, $args ){

        $sufix = $this->app->config[ 'message.tip.sufix' ];

        if( $this->app->memcached->get( $args[ 'tip' ] . $sufix ) === 0 )
            $this->app->memcached->set( $args[ 'tip' ] . $sufix, 1, time() + 172800 );

        return $response;
    }

    public function & setValues( $values ){

        $val = '';

        if( is_string( $values ) )
            $val = json_decode( $values, true );

        if( json_last_error() !== JSON_ERROR_NONE ){
            $this->addMessage( $values );

        }else{

            if( isset( $val[ $this->reltitle ] ) )
                $this->addTitle( $this->reltitle );

            if( isset( $val[ $this->relmessagekey ] ) )
                $this->addMessage( $val[ $this->relmessagekey ] );
        }

        foreach( $this->elements as $el )
            if( $el[ 'type' ] == 'custom' ) {
                $obj = $el['obj'];

                /** @var myobject $obj*/
                $obj->setValues($values);
            }

        $this->values = is_string( $values ) ? $val : $values;
        return $this;
    }


    public function & addThumb( $key ){
        $this->elements[] = array( 'type' => 'thumb', 'key' => $key );
        return $this;
    }

    public function & addNewLine(){
        $this->elements[] = array( 'type' => 'nl' );
        return $this;
    }

    public function & ajaxUpdateMessage( $message ){
        $this->app->ajax->text( '#msg' . $this->name . 'm', $this->app->ajax->filter( $message ) );
        return $this;
    }

    public function & addSmall( $message ){
        $this->elements[] = array( 'type' => 'small', 'text' => $message );
        return $this;
    }

    public function & addButton( $label, $icon, $urlobj, $class = '', $color = false, $colorbackground = false ){
        $this->elements[] = array( 'type' => 'button', 'label' => $label, 'icon' => $icon, 'urlobj' => $urlobj, 'class' => $class, 'color' => $color, 'colorbackground' => $colorbackground );
        return $this;
    }

    public function & pusherHide( $channel = false, $event = false ){
        $this->app->pusher->remove( '#msg' . $this->name )->send( $channel, $event );
        return $this;
    }

    public function & pusherShow( $div, $channel = false, $event = false ){
        $this->app->pusher->append( $div, $this->__toString() )->send( $channel, $event );
        return $this;
    }

    public function & ajaxHide(){
        $this->app->ajax->remove( '#msg' . $this->name );
        return $this;
    }

    public function & ajaxShow( $div ){
        $this->app->ajax->append( $div, $this->__toString() );
        return $this;
    }

    public function & ajaxReplace( $div ){
        $this->app->ajax->html( $div, $this->__toString() );
        return $this;
    }

    public function & ajaxRefresh(){
        $this->app->ajax->replacewith( '#msg' . $this->name, $this->__toString() );
        return $this;
    }
    
    public function __toString(){

        if( isset( $this->tip[ 'id' ] ) && class_exists( 'Memcached' ) && $this->app->memcached->get( $this->tip[ 'id' ] ) === 1 )
            return '';

        return $this->app->view->fetch( '@my/mymessage.twig', array( 'name'            => $this->name,
                                                                              'classname'       => $this->classname,
                                                                              'customheader'    => $this->customheader,
                                                                              'customsubheader' => $this->customsubheader,
                                                                              'video'           => $this->video,
                                                                              'elements'        => $this->elements,
                                                                              'messageprefix'   => $this->messageprefix,
                                                                              'messagesufix'    => $this->messagesufix,
                                                                              'offset'          => $this->offset,
                                                                              'tip'             => $this->tip,
                                                                              'depends'         => $this->depends,
                                                                              'values'          => $this->values ) );
    }

}