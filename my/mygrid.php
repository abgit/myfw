<?php

class mygrid{
    
    private $name     = null;
    private $key      = null;
    private $cols     = null;
    private $labels   = null;
    private $more     = false;
    private $values   = null;
    private $keyhtml  = null;
    private $menu     = 0;
    private $title    = null;
    private $emptymsg = 'No elements to display';
    private $buttons  = array();
    private $classes  = array();
    private $actions  = array();
    private $modal    = null;
    private $tags     = array();
    
    public function __construct( $name = 'g' ){
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
        $this->addKey( $key, $keyhtml );
        return $this;
    }

    public function & addKey( $key, $keyhtml ){
        $this->tags[0][] = $key;
        $this->tags[1][] = $keyhtml;
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

    public function addAction( $name, $call ){
        $this->actions[ $name ] = $call;
    }

    public function __call( $name, $arguments ){

        if( isset( $this->actions[ $name ] ) ){
            return call_user_func_array( $this->actions[ $name ], $arguments );
        }
    }
    
    public function & addToolbarButton( $label, $icon, $onclick, $class = 'info' ){
        $this->buttons[] = array( 'label' => $label, 'icon' => $icon, 'onclick' => $onclick, 'class' => $class );
        return $this;
    }
    
    public function & addSimple( $key, $kval, $label = '', $align = '' ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'simple');
        return $this;
    }

    public function & addH4( $key, $kval, $label, $align = '' ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'h4');
        return $this;
    }

    public function & addSpan( $key, $kval, $label = '', $align = '' ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'span' );
        return $this;
    }

    public function & addThumb( $key, $kval, $kvals, $label = '', $onclick = '' ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $this->app->ishttps() ? $kvals : $kval, 'type' => 'thumb', 'onclick' => $onclick );
        return $this;
    }

    public function & addAgo( $key, $kval, $label, $align = '' ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'ago' );
        return $this;
    }

    public function & addUrl( $key, $kval, $label, $onclick, $bold = false, $align = 'text-left' ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'url', 'onclick' => $onclick, 'bold' => $bold );
        return $this;
    }

    public function & addFixed( $key, $kval, $label, $options, $default = array(), $align = 'text-center' ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'fixed', 'options' => $options, 'default' => $default );
        return $this;
    }

    public function & addMenu( $options, $label = 'Tools', $icon = 'icon-cog4', $align = 'text-center' ){
        $key = 'm' . $this->menu++;
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align  );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'type' => 'menu', 'icon' => $icon, 'options' => $options );
        return $this;
    }

    public function & setMenuItemDisabled( $index, $depends ){
    
        foreach( $this->cols as $col => $list ){
            foreach( $list as $k => $c){
                if( $c[ 'type' ] == 'menu' && isset( $this->cols[ $col ][ $k ][ 'options' ][ $index ] ) ){
                    $this->cols[ $col ][ $k ][ 'options' ][ $index ] += array( 'disabled' => true, 'disableddepends' => $depends );
                }
            }
        }
        return $this;
    }

    public function & ajaxSetMenuItemDisabled( $key, $index ){
        $this->app->ajax()->attr( '#' . $key . 'm' . $index, 'class', 'disabled' );
        return $this;
    }

    public function & setValues( $values ){
        if( is_array( $values ) )
            $this->values = $values;

        return $this;
    }

    public function & setClass( $el, $class ){

        if( !is_array( $el ) )
            $el = array( $el );

        foreach( $el as $e ){
            if( isset( $this->labels[ $e ] ) ){
                $this->labels[ $e ][ 'class' ] = $class;
            }
        }
        return $this;
    }



    public function & setRowClass( $key, $kval, $class, $dependkey = false ){

        foreach( $this->cols[ $key ] as $index => $subrow ){
            if( $this->cols[ $key ][ $index ][ 'kval' ] == $kval )
                $this->cols[ $key ][ $index ][ 'class' ] = array( 'list' => $class, 'key' => $dependkey );
        }
        return $this;
    }


    public function & setRowReplace( $key, $kval, $replace ){
        foreach( $this->cols[ $key ] as $index => $subrow ){
            if( $this->cols[ $key ][ $index ][ 'kval' ] == $kval )
                $this->cols[ $key ][ $index ][ 'replace' ] = $replace;
        }
        return $this;
    }

    public function & addAddon( $key, $kval, $value, $prefix = true ){

        if( $prefix )
            foreach( $this->cols[ $key ] as $index => $subrow )
                if( $this->cols[ $key ][ $index ][ 'kval' ] == $kval )
                    $this->cols[ $key ][ $index ][ 'addonpre' ] = $value;
        else
            foreach( $this->cols[ $key ] as $index => $subrow )
                if( $this->cols[ $key ][ $index ][ 'kval' ] == $kval )
                    $this->cols[ $key ][ $index ][ 'addonpos' ] = $value;

        return $this;
    }


    public function & refreshAjaxValue( $value ){
        return $this->refreshAjaxValues( array( $value ) );
    }

    public function & refreshAjaxValues( $values ){

        if( !is_array( $values ) )
            return $this;

        foreach( $values as $row ){
            if( isset( $row[ $this->key ] ) )
                $this->app->ajax()->replacewith( '#' . $row[ $this->key ], $this->render( array( $row ) ) );
        }

        return $this;
    }

    public function & deleteAjaxValue( $key ){
        $this->app->ajax()->hideTableRow( '#' . $key, '#' . $this->name . 'table', '#' . $this->name . 'empty' );
        return $this;
    }

    public function & addAjaxValue( $value ){
        return $this->addAjaxValues( array( $value ), false );
    }

    public function & addAjaxValues( $values, $append = true ){

        if( !is_array( $values ) )
            return $this;

        $counter = count( $values );

        if( $counter ){
            $append ? $this->app->ajax()->append( '#' . $this->name, $this->render( $values ) ) : $this->app->ajax()->prepend( '#' . $this->name, $this->render( $values ) );
        }

        if( $counter )
            $this->app->ajax()->hide( '#' . $this->name . 'empty' );

        if( $this->getPerPage() > $counter )
            $this->app->ajax()->remove( '#' . $this->name . 'more' );

        return $this;
    }

    public function & setMore( $onclick, $perpage = 10, $offset = 0, $label = 'more' ){
        
        // reset counter
        $this->app->session()->set( $this->name . 'perpage', $perpage );
        $this->app->session()->set( $this->name . 'offset',  $offset );
        
        $this->more = array( 'onclick' => $onclick, 'label' => $label );

        return $this;
    }

    public function getPerPage(){
        return intval( $this->app->session()->get( $this->name . 'perpage', 10 ) );
    }

    public function getOffset( $increase = false ){

        $newoffset = intval( $this->app->session()->get( $this->name . 'offset', 0 ) ) + $this->getPerPage();

        $this->app->session()->set( $this->name . 'offset', $newoffset );

        return $newoffset;
    }

    public function __toString(){
        return $this->render();
    }

    public function & setModal( $title, $class = 'modal-lg', $icon = 'icon-paragraph-justify2', $static = true, $width = '' ){
        $this->modal = array( 'formid' => 'mygf' . $this->name, 'title' => $title, 'class' => $class, 'icon' => $icon, 'static' => $static, 'width' => $width );
        return $this;
    }

    public function show( $htmlid = null ){

        if( is_null( $htmlid ) ){
            return $this->app->form( $this->modal[ 'formid' ] )
                             ->addAjax()
                             ->setModal( $this->modal[ 'title' ], $this->modal[ 'class' ], $this->modal[ 'icon' ], $this->modal[ 'static' ], $this->modal[ 'width' ] )
                             ->addGrid( $this )
                             ->show();
        }
        $this->app->ajax()->html( $htmlid, $this->render() );
    }

    private function render( $values = null ){
        return $this->app->render( '@my/mygrid', array( 'name'     => $this->name,
                                                        'key'      => $this->key,
                                                        'keyhtml'  => $this->keyhtml,
                                                        'tags'     => $this->tags,
                                                        'labels'   => $this->labels,
                                                        'allitems' => is_null( $values ),
                                                        'values'   => is_null( $values ) ? $this->values : $values,
                                                        'more'     => $this->more,
                                                        'title'    => $this->title,
                                                        'emptymsg' => $this->emptymsg,
                                                        'buttons'  => $this->buttons,
                                                        'perpage'  => $this->getPerPage(),
                                                        'cols'     => $this->cols ), null, null, APP_CACHEAPC, false, false );        
    }
}