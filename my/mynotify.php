<?php

class mynotify{

    private $name;
    private $keys = array();
    private $title;
    private $icon = 'icon-paragraph-justify2';
    private $values = array();
    private $buttonright;
    private $buttonleft;
    private $itemtitle;
    private $itemthumb;
    private $itemdescription;
    private $itemmore;
    private $itemlabel;
    private $counter = null;
    private $emptymsg = 'No elements to display';
    private $unreadkey = false;
    private $allitems = true;
    private $onshow;

    public function __construct( $name = 'n' ){
        $this->name = $name;
        $this->app = \Slim\Slim::getInstance();
    }

    public function & setName( $name ){
        $this->name = $name;
        return $this;
    }
    
    public function & setKeys( $keys ){
        $this->keys = $keys;
        return $this;
    }

    public function & setEmptyMessage( $emptymsg ){
        $this->emptymsg = $emptymsg;
        return $this;
    }

    public function & setUnreadKey( $key ){
        $this->unreadkey = $key;
        return $this;
    }

    public function & setTitle( $label ){
        $this->title = $label;
        return $this;
    }

    public function & setIcon( $icon ){
        $this->icon = $icon;
        return $this;
    }

    public function & setOnShow( $action ){
        $this->onshow = $action;
        return $this;
    }

    public function & setValues( $values, $counter ){

        if( !is_array( $values ) )
            $this->values = json_decode( $values, true );

       $this->counter = $counter;

        return $this;
    }

    public function & addButtonLeft( $label, $onclick = '', $href = '', $icon = 'icon-cog4' ){
        $this->buttonleft = array( 'icon' => $icon, 'href' => $href, 'onclick' => $onclick, 'label' => $label );
        return $this;
    }

    public function & addButtonRight( $label, $onclick = '', $href = '', $icon = 'icon-cog4' ){
        $this->buttonright = array( 'icon' => $icon, 'href' => $href, 'onclick' => $onclick, 'label' => $label );
        return $this;
    }

    public function & setItemTitle( $key, $replace = false ){
        $this->itemtitle = array( 'key' => $key, 'replace' => $replace );
        return $this;
    }

    public function & setItemLabel( $key, $prefix = false, $sufix = false  ){
        $this->itemlabel = array( 'key' => $key, 'prefix' => $prefix, 'sufix' => $sufix );
        return $this;
    }

    public function & setItemThumb( $key, $classkey = '' ){
        $this->itemthumb = array( 'key' => $key, 'classkey' => $classkey );
        return $this;
    }

    public function & setItemDescription( $key ){
        $this->itemdescription = array( 'key' => $key );
        return $this;
    }

    public function & setItemAction( $onclick ){
        $this->itemaction = array( 'onclick' => $onclick );
        return $this;
    }

    public function & setItemMore( $label, $onclick = '', $href = '' ){
        $this->itemmore = array( 'href' => $href, 'onclick' => $onclick, 'label' => $label );
        return $this;
    }

    public function & ajaxUpdate( $values, $counter ){

        $this->allitems = false;

        $this->setValues( $values, $counter );

        $this->app->ajax()->notifyUpdate( '#' . $this->name, $this->__toString(), $this->counter );
        return $this;
    }


    public function & ajaxUpdateThumb( $class, $value ){
        $this->app->ajax()->attr( '.notifthumb' . $class, 'src', $value );
        return $this;
    }


    public function & pusherUpdate( $values, $counter, $channel = null, $event = null ){

        $this->allitems = false;

        $this->setValues( $values, $counter );

        $this->app->pusher()->notifyUpdate( '#' . $this->name, $this->__toString(), $this->counter )->send( is_null( $channel ) ? $this->app->config( 'pusher.channel' ) : $channel, is_null( $event ) ? $this->app->config( 'pusher.event' ) : $event );
        return $this;
    }

    public function __toString(){
        return $this->render();
    }

    private function render( $values = null ){
        return $this->app->render( '@my/mynotify', array( 'values'          => is_null( $values ) ? $this->values : $values,
                                                          'name'            => $this->name,
                                                          'keys'            => $this->keys,
                                                          'title'           => $this->title,
                                                          'icon'            => $this->icon,
                                                          'buttonright'     => $this->buttonright,
                                                          'buttonleft'      => $this->buttonleft,
                                                          'itemtitle'       => $this->itemtitle,
                                                          'itemlabel'       => $this->itemlabel,
                                                          'itemthumb'       => $this->itemthumb,
                                                          'itemdescription' => $this->itemdescription,
                                                          'itemaction'      => $this->itemaction,
                                                          'itemmore'        => $this->itemmore,
                                                          'counter'         => $this->counter,
                                                          'emptymsg'        => $this->emptymsg,
                                                          'unreadkey'       => $this->unreadkey,
                                                          'onshow'          => $this->onshow,
                                                          'allitems'        => $this->allitems
                                                         ), null, null, null, false, false );

    }
}