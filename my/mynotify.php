<?php

class mynotify{

    private $name;
    private $key = '';
    private $keyhtml = ' ';
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

    public function __construct( $name = 'n' ){
        $this->name = $name;
        $this->app = \Slim\Slim::getInstance();
    }

    public function & setName( $name ){
        $this->name = $name;
        return $this;
    }
    
    public function & setKey( $key, $keyhtml = 'KEY' ){
        $this->key     = $key;
        $this->keyhtml = $keyhtml;
        return $this;
    }

    public function & setEmptyMessage( $emptymsg ){
        $this->emptymsg = $emptymsg;
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

    public function & setCounter( $i ){
        $this->counter = $i;
        return $this;
    }

    public function & setValues( $values ){

        if( is_array( $values ) ){
            $this->values = $values;

            if( is_null( $this->counter ) )
                $this->counter = count( $values );
        }
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

    public function & setItemThumb( $key, $keyhttps ){
        $this->itemthumb = array( 'key' => $this->app->ishttps() ? $keyhttps : $key );
        return $this;
    }

    public function & setItemDescription( $key ){
        $this->itemdescription = array( 'key' => $key );
        return $this;
    }

    public function & setItemAction( $onclick, $href = '' ){
        $this->itemaction = array( 'onclick' => $onclick, 'href' => $href );
        return $this;
    }

    public function & setItemMore( $label, $onclick = '', $href = '' ){
        $this->itemmore = array( 'href' => $href, 'onclick' => $onclick, 'label' => $label );
        return $this;
    }

    public function & ajaxClearCounter(){
        $this->ajaxUpdateCounter(0);
        return $this;
    }

    public function & ajaxUpdateCounter( $c ){
        $c = intval( $c );
        $this->app->ajax()->text( '#' . $this->name . 'counter', $c ? $c : '' );
        return $this;
    }

    public function __toString(){
        return $this->render();
    }

    private function render( $values = null ){
        return $this->app->render( '@my/mynotify', array( 'values'   => is_null( $values ) ? $this->values : $values,
                                                          'name'     => $this->name,
                                                          'key'      => $this->key,
                                                          'keyhtml'  => $this->keyhtml,
                                                          'title'       => $this->title,
                                                          'icon'        => $this->icon,
                                                          'buttonright' => $this->buttonright,
                                                          'buttonleft'  => $this->buttonleft,
                                                          'itemtitle'    => $this->itemtitle,
                                                          'itemlabel'    => $this->itemlabel,
                                                          'itemthumb'    => $this->itemthumb,
                                                          'itemdescription' => $this->itemdescription,
                                                          'itemaction'      => $this->itemaction,
                                                          'itemmore'        => $this->itemmore,
                                                          'counter'         => $this->counter,
                                                          'emptymsg'        => $this->emptymsg
                                                         ), null, null, null, false, false );

    }
}