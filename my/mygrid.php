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
    private $title    = array();
    private $emptymsg = 'No elements to display';
    private $buttons  = array();
    private $classes  = array();
    private $actions  = array();
    private $modal    = null;
    private $orderby  = false;
    private $orderbya = null;
    private $menuhtml = null;
    private $rowclass = false;
    private $rowclassdepends = false;
    private $perpage  = 10;
    private $titlekey = false;
    
    public function __construct( $name = 'g' ){
        $this->name = $name;
        $this->app = \Slim\Slim::getInstance();
    }

    public function & setID( $id ){
        $this->name = $id;
        return $this;
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


    public function & setTitle( $label, $icon = 'icon-stack' ){
        $this->title[ 'label' ] = $label;
        $this->title[ 'icon' ]  = $icon;
        return $this;
    }

    public function & setTitleKey( $key ){
        $this->titlekey = $key;
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

    public function & setOrderby( $col, $asc = false ){
        $this->orderby  = $col;
        $this->orderbya = $asc;
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
    
    public function & addSimple( $key, $kval, $label = '', $align = '', $truncate = '' ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'simple', 'truncate' => $truncate );
        return $this;
    }

    public function & addDescription( $key, $kval, $label = '', $align = '', $inline = false ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'description', 'inline' => $inline );
        return $this;
    }

    public function & addH4( $key, $kval, $label, $align = '', $kval2 = false, $prefix2 = false ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'h4', 'kval2' => $kval2, 'prefix2' => $prefix2 );
        return $this;
    }

    public function & addSpan( $key, $kval, $label = '', $align = '' ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'span' );
        return $this;
    }

    public function & addThumb( $key, $kval, $label = '', $onclick = '', $default = '' ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'thumb', 'onclick' => $onclick, 'default' => $default );
        return $this;
    }

    public function & addAgo( $key, $kval, $label, $dateonly = false, $keycustomdate = '' ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'ago', 'dateonly' => $dateonly, 'keycustomdate' => $keycustomdate );
        return $this;
    }

    public function & addProgress( $key, $kval, $label, $class = '' ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'progress', 'class' => $class );
        return $this;
    }

    public function & addUrl( $key, $kval, $label, $onclick = false, $bold = false, $align = 'left', $truncate = '' ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'url', 'onclick' => $onclick, 'bold' => $bold, 'truncate' => $truncate );
        return $this;
    }

    public function & addFixed( $key, $kval, $label, $options, $default = array(), $align = 'center' ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'fixed', 'options' => $options, 'default' => $default );
        return $this;
    }

    public function & addInfo( $key, $kval, $label = '', $title = '', $align = 'left', $depends = false, $dependsnot = false ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align  );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'info', 'title' => $title, 'depends' => $depends, 'dependsnot' => $dependsnot );
        return $this;
    }

    public function & addImage( $key, $kval, $label, $cdn = '', $sufix = '', $width = 20, $height = 15 ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'image', 'cdn' => $cdn, 'sufix' => $sufix, 'width' => $width, 'height' => $height);
        return $this;
    }

    public function & addLabel( $key, $kval, $label = '', $classreplacedefault = '', $classreplace = array(), $customkey = false, $replaceval = false ){
        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'label', 'replaceval' => $replaceval, 'classreplace' => $classreplace, 'classreplacedefault' => $classreplacedefault, 'classreplacekey' => $customkey );
        return $this;
    }

    public function & addBr( $key ){
        $this->cols[ $key ][] = array( 'key' => $key, 'type' => 'br' );
        return $this;
    }

    public function & addSpace( $key ){
        $this->cols[ $key ][] = array( 'key' => $key, 'type' => 'space' );
        return $this;
    }

    public function & setMenu( $obj ){
        $this->menuhtml = $obj;
        return $this;
    }

    public function getMenu(){
        return $this->menuhtml;
    }

    public function & addMenu( $options, $label = 'Tools', $icon = 'icon-cog4', $align = 'center' ){
        $key = 'menutools';
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

    public function & ajaxSetMenuItemDisabled( $key, $id, $menu = 'menutools' ){

        if( isset( $this->cols[ $menu ][0][ 'options' ] ) ){
            foreach( $this->cols[ $menu ][0][ 'options' ] as $i => $options ){
                if( isset( $options[ 'id' ] ) && $options[ 'id' ] == $id ){
                    $this->app->ajax()->attr( '#' . $key . 'm' . $i, 'class', 'disabled' );
                    return $this;
                }
            }
        }

        return $this;
    }

    public function & setAction( $key, $onclick ){
        if( isset( $this->labels[ $key ] ) )
            $this->labels[ $key ][ 'onclick' ] = $onclick;
        return $this;
    }

    public function & setValues( $values ){
        $this->values = is_array( $values ) ? $values : json_decode( $values, true );

        if( is_string( $this->titlekey ) && isset( $values[ $this->titlekey ] ) )
            $this->title[ 'labelkey' ] = $values[ $this->titlekey ];

        $this->app->session()->set( $this->name . 'gridinit', false );

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

    public function & setClassDepends( $class, $depends ){
        $this->rowclass = $class;
        $this->rowclassdepends = $depends;
        return $this;
    }

    public function & setRowClass( $key, $kval, $class, $dependkey = false ){

        if( isset( $this->cols[ $key ] ) ){
            foreach( $this->cols[ $key ] as $index => $subrow ){
                if( isset( $this->cols[ $key ][ $index ][ 'kval' ] ) && $this->cols[ $key ][ $index ][ 'kval' ] == $kval )
                    $this->cols[ $key ][ $index ][ 'class' ] = array( 'list' => $class, 'key' => $dependkey );
            }
        }
        return $this;
    }


    public function & setRowReplace( $key, $kval, $replace ){

        if( isset( $this->cols[ $key ] ) ){
            foreach( $this->cols[ $key ] as $index => $subrow ){
                if( isset( $this->cols[ $key ][ $index ][ 'kval' ] ) && $this->cols[ $key ][ $index ][ 'kval' ] == $kval )
                    $this->cols[ $key ][ $index ][ 'replace' ] = $replace;
            }
        }
        return $this;
    }

    public function & addAddon( $key, $kval, $value, $prefix = true, $valuesingular = null, $isorder = null ){

        foreach( $this->cols[ $key ] as $index => $subrow ){
            if( isset( $this->cols[ $key ][ $index ][ 'kval' ] ) && $this->cols[ $key ][ $index ][ 'kval' ] == $kval ){
                $this->cols[ $key ][ $index ][ $prefix ? 'addonpre' : 'addonpos' ] = $value;

                if( !$prefix ){
                    $this->cols[ $key ][ $index ][ 'addonpossing' ]  = empty( $valuesingular ) ? $value : $valuesingular;
                    $this->cols[ $key ][ $index ][ 'addonposorder' ] = !empty( $isorder );
                }
            }
        }

        return $this;
    }

    public function & refreshAjaxValue( $value ){

        if( is_string( $value ) )
            $value = json_decode( $value, true );

        return $this->refreshAjaxValues( array( $value ) );
    }

    public function & refreshAjaxValues( $values ){

        if( is_string( $values ) )
            $values = json_decode( $values, true );
        
        foreach( $values as $row ){
            if( isset( $row[ $this->key ] ) )
                $this->app->ajax()->replacewith( '#' . $this->name . $row[ $this->key ], $this->render( array( $row ) ) );
        }

        return $this;
    }

    public function & ajaxAddReplaceValues( $values ){

        if( is_string( $values ) )
            $values = json_decode( $values, true );

        $this->app->ajax()->hide( '#' . $this->name . 'empty' );

        foreach( $values as $row ){
            if( isset( $row[ $this->key ] ) )
                $this->app->ajax()->addreplacewith( '#' . $this->name . $row[ $this->key ], $this->render( array( $row ) ), '#' . $this->name );
        }

        return $this;    
    }

    public function & ajaxRefresh( $values ){

        if( is_string( $values ) )
            $values = json_decode( $values, true );

        if( empty( $values ) )
            $values = array();
        
        $this->app->ajax()->html( '#' . $this->name, $this->render( $values, true ) );

        return $this;    
    }

    public function & deleteAjaxValue( $key ){
        $this->app->ajax()->hideTableRow( '#' . $this->name . $key, '#' . $this->name );
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

            $this->app->ajax()->hide( '#' . $this->name . 'empty' );
        }

        if( $this->perpage > $counter )
            $this->app->ajax()->remove( '#' . $this->name . 'more' );

        return $this;
    }

    public function & addAjaxPage( $values ){
        $this->addAjaxValues( $values );
        if( !empty( $values ) )
            $this->pageIncrement();
    
        return $this;
    }

    private function pageIncrement(){
        $this->app->session()->set( $this->name . 'gridinit', false );

        $g = $this->app->session()->get( $this->name . 'gridpage' );

        $this->app->session()->set( $this->name . 'gridpage', 1 + $g );
    }

    public function & setMore( $onclick, $perpage = 10, $label = 'more' ){

        $this->more = array( 'onclick' => $onclick, 'label' => $label );
        $this->perpage = $perpage;

        return $this;
    }

    public function getPage(){

        if( $this->app->session()->get( $this->name . 'gridinit', true ) === true ){
            return 0;
        }
        return 1 + $this->app->session()->get( $this->name . 'gridpage', 0 );
    }

    public function getLimit(){

        if( $this->app->session()->get( $this->name . 'gridinit' ) === true ){

            $resetall = $this->app->session()->get( $this->name . 'gridresetall' );
            return ( $resetall === true ) ? $this->perpage : ( $this->perpage + $this->perpage * $this->app->session()->get( $this->name . 'gridpage' ) );
        }

        return $this->perpage;
    }

    public function getOffset(){
        return $this->getPage() * $this->getLimit();
    }

    public function & pageReset( $resetall = true ){
        $this->app->session()->set( $this->name . 'gridinit',     true );
        $this->app->session()->set( $this->name . 'gridresetall', $resetall );
        return $this;
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
                             ->addCustom( $this->name, $this )
                             ->show();
        }
        $this->app->ajax()->html( $htmlid, $this->render() );
    }

    private function render( $customvalues = null, $renderempty = false ){

        $values      = is_null( $customvalues ) ? $this->values : $customvalues;
        $valuestotal = count( $values );

        return $this->app->render( '@my/mygrid', array( 'name'            => $this->name,
                                                        'key'             => $this->key,
                                                        'keyhtml'         => $this->keyhtml,
                                                        'labels'          => $this->labels,
                                                        'allitems'        => is_null( $customvalues ),
                                                        'emptyitem'       => ( is_null( $customvalues ) || ( $renderempty && empty( $customvalues ) ) ),
                                                        'values'          => $values,
                                                        'more'            => $this->more,
                                                        'moreshow'        => ( $valuestotal > 0 && !myrules::isdecimal( $valuestotal / $this->perpage ) ),
                                                        'title'           => $this->title,
                                                        'emptymsg'        => $this->emptymsg,
                                                        'buttons'         => $this->buttons,
                                                        'perpage'         => $this->perpage,
                                                        'orderby'         => $this->orderby,
                                                        'orderbya'        => $this->orderbya,
                                                        'menuhtml'        => $this->menuhtml,
                                                        'rowclass'        => $this->rowclass,
                                                        'rowclassdepends' => $this->rowclassdepends,
                                                        'cols'            => $this->cols ), null, null, 0, false, false );        
    }
}