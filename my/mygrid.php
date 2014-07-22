<?php

class mygrid{
    
    private $name     = null;
    private $key      = null;
    private $cols     = null;
    private $labels   = null;
    private $more     = null;
    private $values   = null;
    private $keyhtml  = null;
    private $menu     = 0;
    private $title    = null;
    private $emptymsg = null;
    private $buttons  = array();
    
    public function __construct( $name ){
        $this->name = $name;
        $this->app = \Slim\Slim::getInstance();
    }
    
    public function & setKey( $key, $keyhtml = 'KEY' ){
        $this->key     = $key;
        $this->keyhtml = $keyhtml;
        return $this;
    }
    
    public function & setTitle( $label, $icon = 'icon-stack' ){
        $this->title = array( 'label' => $label, 'icon' => $icon );
        return $this;
    }

    public function & setEmptyMessage( $emptymsg ){
        $this->emptymsg = $emptymsg;
        return $this;
    }
    
    public function & setWidth(){

        $arg = func_get_args();
        $i   = 0;
        foreach( $this->labels as $k => $el ){
            if( isset( $arg[ $i ] ) )
                $this->labels[ $k ][ 'width' ] = $arg[$i];
            $i++;
        }
        return $this;
    }
    
    public function & addToolbarButton( $label, $icon, $onclick, $class = 'info' ){
        $this->buttons[] = array( 'label' => $label, 'icon' => $icon, 'onclick' => $onclick, 'class' => $class );
        return $this;
    }
    
    public function & addSimple( $key, $kval, $label, $align = '' ){
        $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'simple');
        return $this;
    }

    public function & addH4( $key, $kval, $label, $align = '' ){
        $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'h4');
        return $this;
    }

    public function & addSpan( $key, $kval, $label, $align = '' ){
        $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'span' );
        return $this;
    }

    public function & addAgo( $key, $kval, $label, $align = '' ){
        $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'ago' );
        return $this;
    }

    public function & addUrl( $key, $kval, $label, $onclick, $align = 'text-left'){
        $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'url', 'onclick' => $onclick );
        return $this;
    }

    public function & addFixed( $key, $kval, $label, $options, $default = array(), $align = 'text-center' ){
        $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'fixed', 'options' => $options, 'default' => $default );
        return $this;
    }

    public function & addMenu( $options, $label = 'Tools', $icon = 'icon-cog4', $classtype = 'primary', $align = 'text-center' ){
        $key = 'm' . $this->menu++;
        $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align  );
        $this->cols[ $key ][] = array( 'key' => $key, 'type' => 'menu', 'icon' => $icon, 'classtype' => $classtype, 'options' => $options );
        return $this;
    }

    public function & setValues( $values ){
        $this->values = $values;
        return $this;
    }

    public function & refreshAjaxValues( $values ){

        if( !is_array( $values ) || !isset( $values[0] ) )
            $values = array( $values );

        foreach( $values as $row ){
            if( isset( $row[ $this->key ] ) )
                $this->app->ajax()->replacewith( '#' . $row[ $this->key ], $this->render( array( $row ) ) );
        }

        return $this;
    }

    public function & addAjaxValue( $values ){

        if( !is_array( $values ) || !isset( $values[0] ) )
            $values = array( $values );

        $values = array_reverse( $values );

        $counter = false;
        foreach( $values as $row ){
            if( isset( $row[ $this->key ] ) ){
                $this->app->ajax()->prepend( '#' . $this->name, $this->render( $values ) );
                $counter = true;
            }
        }

        if( $counter )
            $this->app->ajax()->remove( '#' . $this->name . 'empty' );

        return $this;
    }

    public function & setMore( $onclick, $label = 'load more' ){
        $this->more = array( 'onclick' => $onclick, 'label' => $label );
        return $this;
    }
    
    public function __toString(){
        return $this->render();
    }

    private function render( $values = null ){
        return $this->app->render( '@my/mygrid', array( 'name'     => $this->name,
                                                        'key'      => $this->key,
                                                        'keyhtml'  => $this->keyhtml,
                                                        'labels'   => $this->labels,
                                                        'allitems' => is_null( $values ),
                                                        'values'   => is_null( $values ) ? $this->values : $values,
                                                        'more'     => $this->more,
                                                        'title'    => $this->title,
                                                        'emptymsg' => $this->emptymsg,
                                                        'buttons'  => $this->buttons,
                                                        'cols'     => $this->cols ), null, null, APP_CACHEAPC, false, false );        
    }
}