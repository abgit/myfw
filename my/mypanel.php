<?php

class mypanel{

    private $name;
    private $key;
    private $keyhtml;
    private $title;
    private $emptymsg   = 'No elements to display';
    private $values     = array();
    private $elements   = array();
    private $more       = false;
    private $perpage    = 10;
    private $offset     = 0;
    private $size       = 6;
    private $sizeoffset = 0;
    private $action     = false;
    private $idmenusel  = 0;
    private $cdn        = false;

    public function __construct( $name = 'p' ){
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
        return $this;
    }

    public function & setSize( $size, $sizeoffset = 0 ){
        $this->size       = $size;
        $this->sizeoffset = $sizeoffset;
        return $this;
    }

    public function & setTitle( $label, $icon = 'icon-stack' ){
        $this->title = array( 'label' => $label, 'icon' => $icon );
        return $this;
    }

    public function & setAction( $onclick ){
        $this->action = $onclick;
        return $this;
    }

    public function & setEmptyMessage( $emptymsg ){
        $this->emptymsg = $emptymsg;
        return $this;
    }

    public function & setValues( $values ){
        if( is_array( $values ) )
            $this->values = $values;

        return $this;
    }

    public function & addThumb( $key, $keyhttps = null, $static = false, $size = 3, $onclick = '', $classkey = '', $default = '' ){
        $this->elements[ 'thumb' ] = array( 'key' => ( !is_null( $keyhttps ) && $this->app->ishttps() ) ? $keyhttps : $key, 'static' => $static, 'size' => $size, 'onclick' => $onclick, 'classkey' => $classkey, 'default' => $default );
        return $this;
    }
    
    public function & setCDN( $cdn ){
        $this->cdn = $cdn;
        return $this;
    }

    public function & addTitle( $key, $static = false, $onclick = '', $maxsize = '' ){
        $this->elements[ 'title' ] = array( 'key' => $key, 'static' => $static, 'onclick' => $onclick, 'maxsize' => $maxsize );
        return $this;
    }

    public function & addReference( $key ){
        $this->elements[ 'reference' ] = array( 'key' => $key );
        return $this;
    }

    public function & addDescription( $key, $static = false, $onclick = '', $maxsize = '' ){
        $this->elements[ 'description' ] = array( 'key' => $key, 'static' => $static, 'onclick' => $onclick, 'maxsize' => $maxsize );
        return $this;
    }

    public function & addBackground( $key, $keyhttps = null ){
        $this->elements[ 'back' ] = array( 'key' => ( !is_null( $keyhttps ) && $this->app->ishttps() ) ? $keyhttps : $key );
        return $this;
    }

    public function & addInfo( $key, $prefix = '', $sufix = '', $iclass = '', $class = '', $defaultvalue = '', $defaultprefix = '', $defaultsufix = '', $defaultclass = '', $extrakey = false, $extrasufix = '', $depends = false ){
        $this->elements[ 'info' ][ $key ] = array( 'key' => $key, 'type' => 0, 'prefix' => $prefix, 'sufix' => $sufix, 'iclass' => $iclass, 'class' => $class, 'defaultvalue' => $defaultvalue, 'defaultprefix' => $defaultprefix, 'defaultsufix' => $defaultsufix, 'defaultclass' => $defaultclass, 'extrakey' => $extrakey, 'extrasufix' => $extrasufix, 'depends' => $depends );
        return $this;
    }

    public function & addProgress( $key ){
        $this->elements[ 'progress' ] = array( 'key' => $key );
        return $this;
    }

    public function & ajaxUpdateProgress( $key, $value ){
        $this->app->ajax()->css( '#ppb' . $key, 'width', $value . '%' );
        return $this;
    }

    public function & ajaxUpdateStatus( $key, $statuskey, $value ){

        if( isset( $this->elements[ 'status' ] ) )
            foreach( $this->elements[ 'status' ] as $el )
                if( isset( $el[ 'key' ] ) && $el[ 'key' ] == $statuskey && isset( $el[ 'type' ] ) && $el[ 'type' ] == 1 && isset( $el[ 'sufix' ] ) )
                    $this->app->ajax()->text( '#psiv' . $key . $statuskey, $value . myfilters::label( $el[ 'sufix' ], $value ) );

        return $this;
    }

    public function & ajaxUpdateInfo( $panelkey, $infokey, $value, $extravalue = '' ){
        
        // get prefix and extrasufix
        if( isset( $this->elements[ 'info' ][ $infokey ] ) )
            $value = $this->elements[ 'info' ][ $infokey ][ 'prefix' ] . $value . $this->elements[ 'info' ][ $infokey ][ 'sufix' ] . $extravalue . $this->elements[ 'info' ][ $infokey ][ 'extrasufix' ];
        
        $this->app->ajax()->html( '#pic' . $infokey . $panelkey, $value )
                          ->removeClass( '#pi' . $infokey . $panelkey, 'hide' );
        return $this;
    }

    public function & ajaxHideInfo( $panelkey, $infokey ){
        
        $this->app->ajax()->html( '#pic' . $infokey . $panelkey, '' )
                          ->switchClass( '#pi' . $infokey . $panelkey, 'hide', 'hide' );
        return $this;
    }

    public function & ajaxMenuLabel( $panelkey, $value ){
        
        $this->app->ajax()->html( '#pms' . $panelkey, $value );

        return $this;
    }

    public function & addInfoThumb( $key, $size = null, $onclick = '' ){
        $this->elements[ 'info' ][ $key ] = array( 'key' => $key, 'type' => 1, 'size' => $size, 'onclick' => $onclick );
        return $this;
    }

    public function & setInfoFilter( $key, $filter ){
        $this->elements[ 'info' ][ $key ][ 'filter' ] = $filter;
        return $this;
    }

    public function & addStatus( $key, $prefix = '', $sufix = '' ){
        $this->elements[ 'status' ][] = array( 'key' => $key, 'type' => 1, 'prefix' => $prefix, 'sufix' => $sufix );
        return $this;
    }

    public function & addStatusIcon( $key, $icons, $depends = false, $keyclass = '' ){
        $this->elements[ 'status' ][] = array( 'key' => $key, 'type' => 2, 'icons' => $icons, 'icon' => is_string( $icons ) ? $icons : false, 'depends' => $depends, 'keyclass' => $keyclass );
        return $this;
    }

    public function & addStatusInfo( $key, $prefix = '', $sufix = '', $class = '', $defaultvalue = '', $defaultprefix = '', $defaultsufix = '', $defaultclass = '', $extrakey = false, $extrasufix = '', $classreplacekey = '' ){
        $this->elements[ 'status' ][ $key ] = array( 'key' => $key, 'type' => 3, 'prefix' => $prefix, 'sufix' => $sufix, 'class' => $class, 'defaultvalue' => $defaultvalue, 'defaultprefix' => $defaultprefix, 'defaultsufix' => $defaultsufix, 'defaultclass' => $defaultclass, 'extrakey' => $extrakey, 'extrasufix' => $extrasufix, 'classreplacekey' => $classreplacekey );
        return $this;
    }

    public function & setStatusInfoFilter( $key, $filter ){
        $this->elements[ 'status' ][ $key ][ 'filter' ] = $filter;
        return $this;
    }

    public function & ajaxStatusIconShow( $key, $id, $operation = true ){
        $this->app->ajax()->css( '#pani' . $id . $key, 'display', $operation ? 'inline' : 'none' );
        return $this;
    }

    public function & ajaxStatusIconShowClass( $classname, $operation = true ){
        $this->app->ajax()->css( '.panicon' . $classname, 'display', $operation ? 'inline' : 'none' );
        return $this;
    }

    public function & ajaxThumbChange( $src, $class ){
        $this->app->ajax()->attr( '.pt' . $class, 'src', ( is_string( $src ) && strlen( $src ) ) ? $src : ( isset( $this->elements[ 'thumb' ][ 'default' ] ) ? $this->elements[ 'thumb' ][ 'default' ] : '' ) );
        return $this;
    }

    public function & ajaxMenuShow( $index, $id, $operation = true ){

        if( isset( $this->elements[ 'menu' ] ) ){
            foreach( $this->elements[ 'menu' ] as $menu ){
                if( $menu[ 'type' ] == 1 ){
                    foreach( $menu[ 'options' ] as $k => $option ){
                        if( isset( $option[ 'id' ] ) && $option[ 'id' ] == $index ){
                            $this->app->ajax()->css( '#panm' . $id . $k, 'display', $operation ? 'block' : 'none' );
                            break;
                        }
                    }
                }
            }
        }

        return $this;
    }

    public function & ajaxMenuShowClass( $index, $class, $operation = true ){
        $this->app->ajax()->css( '.panmc' . $index . $class, 'display', $operation ? 'block' : 'none' );
        return $this;
    }

    public function & addStatusSeparator( $sep = '|' ){
        $this->elements[ 'status' ][] = array( 'type' => 0, 'sep' => $sep );
        return $this;
    }

    public function & addButton( $label, $onclick = '', $href = '', $icon = 'icon-cog4', $color = '', $id = '', $depends = false, $dependsLabelPrefix = '', $dependsLabelSufix = '', $dependsValueKey = '', $showdepends = '' ){
        $this->elements[ 'menu' ][] = array( 'type' => 0, 'icon' => $icon, 'href' => $href, 'onclick' => $onclick, 'label' => $label, 'color' => $color, 'id' => $id, 'depends' => $depends, 'dependsLabelPrefix' => $dependsLabelPrefix, 'dependsLabelSufix' => $dependsLabelSufix, 'dependsValueKey' => $dependsValueKey, 'showdepends' => $showdepends );
        return $this;
    }

    public function & addMenu( $options, $icon = 'icon-cog4', $label = '' ){
        $this->elements[ 'menu' ][] = array( 'type' => 1, 'icon' => $icon, 'options' => $options, 'label' => $label );
        return $this;
    }

    public function & setMenuItemDisabled( $menuindex, $subindex, $depends ){

        if( isset( $this->elements[ 'menu' ][ $menuindex ][ 'options' ][ $subindex ] ) )
            $this->elements[ 'menu' ][ $menuindex ][ 'options' ][ $subindex ] += array( 'disabled' => true, 'disableddepends' => $depends );
        return $this;
    }

    public function & addToolbarButton( $label, $onclick = '', $href = '', $icon = 'icon-cog4', $color = '', $class = '' ){
        $this->elements[ 'tmenu' ][] = array( 'type' => 0, 'icon' => $icon, 'href' => $href, 'onclick' => $onclick, 'label' => $label, 'color' => $color, 'class' => $class );
        return $this;
    }

    public function & addToolbarMenu( $options, $icon = 'icon-cog4', $label = '' ){
        $this->elements[ 'tmenu' ][] = array( 'type' => 1, 'icon' => $icon, 'options' => $options, 'label' => $label );
        return $this;
    }

    public function & addToolbarMenuSelect( $id, $options, $icon = 'icon-cog4', $label = null ){

        if( is_null( $label ) ){
            $label = 'menu';

            foreach( $options as $op ){
                if( isset( $op[ 'selected' ] ) && $op[ 'selected' ] === true ){
                    if( isset( $op[ 'menu' ] ) ){
                        $label = $op[ 'menu' ];
                    }
                    break;
                }
            }
        }

        $this->elements[ 'tmenu' ][ $id ] = array( 'type' => 2, 'icon' => $icon, 'options' => $options, 'label' => $label, 'id' => $id, 'idhtml' => $this->name . 'sel' . $id );
        return $this;
    }

    public function & addToolbarSeparator( $iterations = 1 ){
        $this->elements[ 'tmenu' ][] = array( 'type' => 3, 'it' => $iterations );
        return $this;
    }

    public function & ajaxToolbarMenuSelected( $ctxmenuid, $index ){

        if( isset( $this->elements[ 'tmenu' ][ $ctxmenuid ][ 'options' ] ) ){
            foreach( $this->elements[ 'tmenu' ][ $ctxmenuid ][ 'options' ] as $i => $arr ){
                if( $arr[ 'id' ] == $index ){
                    $this->app->ajax()->visibility( '#meni' . $ctxmenuid . $arr[ 'id' ], true )
                                      ->attr( '#menl' . $ctxmenuid . $arr[ 'id' ], 'class', 'active' );

                    if( isset( $arr[ 'menu' ] ) ){
                        $this->app->ajax()->text( '#menbl' . $ctxmenuid, $arr[ 'menu' ] );
                    }
                }else{
                    $this->app->ajax()->visibility( '#meni' . $ctxmenuid . $arr[ 'id' ], false )
                                      ->attr( '#menl' . $ctxmenuid . $arr[ 'id' ], 'class', '' );
                }
            }
        }

        return $this;
    }
/*
    public function & ajaxToolbarMenuLabel( $label, $ctxmenuid, $selectid = null ){

        $this->app->ajax()->text( '#menbl' . $ctxmenuid, $label );

        if( !is_null( $selectid ) )
            $this->ajaxToolbarMenuSelected( $ctxmenuid, $selectid );

        return $this;
    }
*/
    public function & setMore( $onclick, $perpage = 10, $offset = 0, $label = 'more' ){
        
        // reset counter
//        $this->app->session()->set( 'p' . $this->name . 'perpage', $perpage );
//        $this->app->session()->set( 'p' . $this->name . 'offset',  $offset );
        
        $this->perpage = $perpage;
        
        $this->more = array( 'to' => $onclick, 'label' => $label /*, 'offset' => $this->offset*/ );

        if( isset( $_POST[ 'os' ] ) ){
            $this->offset = intval( $_POST[ 'os' ] );
            $this->app->ajax()->val( '#' . $this->name . 'moreos', $this->offset + $this->perpage );
        }else{
            $this->offset  = $offset;        
        }

        return $this;
    }


//    public function getPerPage(){
//        return intval( $this->perpage /*$this->app->session()->get( 'p' . $this->name . 'perpage', 10 )*/ );
//    }

    public function getOffset( /*$increase = false*/ ){

//        $newoffset = intval( $this->app->session()->get( 'p' . $this->name . 'offset', 0 ) ) + $this->getPerPage();

//        $this->app->session()->set( 'p' . $this->name . 'offset', $newoffset );

//        if( $increase )
//            $this->offset += $this->perpage;

        return $this->offset;
    }
    
    public function & resetOffset(){
        $this->offset = 0;
//        $this->app->session()->set( 'p' . $this->name . 'offset', 0 );
        return $this;
    }

    public function &addAjaxValues( $values, $append = true ){

        if( !is_array( $values ) )
            return $this;

        $counter = count( $values );

        if( $counter ){
            $append ? $this->app->ajax()->append( '#' . $this->name, $this->render( $values ) ) : $this->app->ajax()->prepend( '#' . $this->name, $this->render( $values ) );
        }

        if( $this->perpage > $counter )
            $this->app->ajax()->hide( '#' . $this->name . 'more' );

        return $this;
    }

    public function & ajaxRemove( $tag ){
        $this->app->ajax()->hide( '#' . $this->name . $tag );
        return $this;
    }

    public function & updateAjaxValues( $values ){

        if( !is_array( $values ) )
            return $this;

        $counter = count( $values );

        if( $this->perpage > $counter )
            $this->app->ajax()->hide( '#' . $this->name . 'more' );
        else
            $this->app->ajax()->show( '#' . $this->name . 'more' );

        $this->app->ajax()->html( '#' . $this->name, $this->render( $values ) );

        $this->resetOffset();

        return $this;
    }


    public function __toString(){
        return $this->render();
    }

    public function show( $htmlid ){
        $this->app->ajax()->html( $htmlid, $this->render() );
    }

    private function render( $values = null ){
        return $this->app->render( '@my/mypanel', array( 'values'     => is_null( $values ) ? $this->values : $values,
                                                         'elements'   => $this->elements,
                                                         'name'       => $this->name,
                                                         'key'        => $this->key,
                                                         'keyhtml'    => $this->keyhtml,
                                                         'emptymsg'   => $this->emptymsg,
                                                         'cdn'        => $this->cdn,
                                                         'more'       => $this->more,
                                                         'offset'     => $this->offset + $this->perpage,
                                                         'perpage'    => $this->perpage,
                                                         'size'       => $this->size,
                                                         'sizeoffset' => $this->sizeoffset,
                                                         'allitems'   => is_null( $values ),
                                                         'action'     => $this->action,
                                                         'tags'       => array( array( $this->key ), array( $this->keyhtml ) )
                                                         ), null, null, null, false, false );
    }
}