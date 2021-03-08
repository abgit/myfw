<?php

class mypanel{

    /** @var mycontainer*/
    private $app;

    private ?string $name    = null;
    private array $keys      = array();
    private array $keyshtml  = array();
    private array $title     = array();
    private array $emptymsg  = array();
    private array $values    = array();
    private array $elements  = array();
    private bool $more       = false;
    private int $perpage     = 10;
    private int $offset      = 0;
    private int $size        = 6;
    private int $sizeoffset  = 0;
    private ?array $action   = null;
    private ?string $cdn     = null;

    public function __construct( $c ){
        $this->app = $c;
    }

    public function & setName( string $name ): mypanel{
        $this->name = $name;
        return $this;
    }
    
    public function & addKey( string $key, string $keyhtml ): mypanel{
        $this->keys[]     = $key;
        $this->keyshtml[] = $keyhtml;
        return $this;
    }

    public function & setSize( int $size, int $sizeoffset = 0 ): mypanel{
        $this->size       = $size;
        $this->sizeoffset = $sizeoffset;
        return $this;
    }

    public function & setTitle( string $label, string $icon = '', string $description = '' ):mypanel{
        $this->title = array( 'label' => $label, 'icon' => $icon, 'description' => $description );
        return $this;
    }

    public function & setAction( array $onclick ):mypanel{
        $this->action = $onclick;
        return $this;
    }

    public function & setEmptyMessage( string $message, $class = 'callout-info', $title = '' ):mypanel{
        $this->emptymsg = array( 'message' => $message, 'class' => $class, 'title' => $title );
        return $this;
    }

    public function & setValues( array $values ):mypanel{
        $this->values = $values;

        return $this;
    }

    public function & addThumb( string $key, $static = false, $size = 3, ?array $urlobj = null, $classkey = '', $default = '', $ratingkey = '' ):mypanel{
        $this->elements[ 'thumb' ] = array( 'key' => $key, 'static' => $static, 'size' => $size, 'urlobj' => $urlobj, 'classkey' => $classkey, 'default' => $default, 'ratingkey' => $ratingkey, 'ratingobj' => $this->app->rating->setName( $key ) );
        return $this;
    }

    public function & setCDN( string $cdn ):mypanel{
        $this->cdn = $cdn;
        return $this;
    }

    public function & addTitle( string $key, $static = false, ?array $urlobj = null, $maxsize = '' ):mypanel{
        $this->elements[ 'title' ] = array( 'key' => $key, 'static' => $static, 'urlobj' => $urlobj, 'maxsize' => $maxsize );
        return $this;
    }

    public function & addReference( string $key ):mypanel{
        $this->elements[ 'reference' ] = array( 'key' => $key );
        return $this;
    }

    public function & addDescription( string $key, $static = false, $onclick = '', $maxsize = '' ):mypanel{
        $this->elements[ 'description' ] = array( 'key' => $key, 'static' => $static, 'onclick' => $onclick, 'maxsize' => $maxsize );
        return $this;
    }

    public function & addBackground( string $key ):mypanel{
        $this->elements[ 'back' ] = array( 'key' => $key );
        return $this;
    }

    public function & addInfo( string $key, $prefix = '', $sufix = '', $iclass = '', $class = '', $defaultvalue = '', $defaultprefix = '', $defaultsufix = '', $defaultclass = '', $extrakey = false, $extrasufix = '', $depends = false ):mypanel{
        $this->elements[ 'info' ][ $key ] = array( 'key' => $key, 'type' => 0, 'prefix' => $prefix, 'sufix' => $sufix, 'iclass' => $iclass, 'class' => $class, 'defaultvalue' => $defaultvalue, 'defaultprefix' => $defaultprefix, 'defaultsufix' => $defaultsufix, 'defaultclass' => $defaultclass, 'extrakey' => $extrakey, 'extrasufix' => $extrasufix, 'depends' => $depends );
        return $this;
    }

    public function & addInfoLabel( string $key, string $label, string $iclass ):mypanel{
        $this->elements[ 'info' ][ $key ] = array( 'key' => $key, 'type' => 2, 'iclass' => $iclass, 'label' => $label );
        return $this;
    }

    public function & addProgress( string $key, ?string $class = 'success' ):mypanel{
        $this->elements[ 'progress' ] = array( 'key' => $key, 'class' => $class );
        return $this;
    }

    public function & ajaxUpdateProgress( string $key, $value ):mypanel{
        $this->app->ajax->css( '#ppb' . $key, 'width', $value . '%' );
        return $this;
    }

    public function & ajaxUpdateStatus( $key, $statuskey, $value ):mypanel{

        if( isset( $this->elements[ 'status' ] ) )
            foreach( $this->elements[ 'status' ] as $el )
                if( isset( $el[ 'key' ] ) && $el[ 'key' ] == $statuskey && isset( $el[ 'type' ] ) && $el[ 'type' ] == 1 && isset( $el[ 'sufix' ] ) )
                    $this->app->ajax->text( '#psiv' . $key . $statuskey, $value . $this->app->filters->label( $el[ 'sufix' ], $value ) );

        return $this;
    }

    public function & ajaxUpdateInfo( $panelkey, $infokey, $value, $extravalue = '' ):mypanel{
        
        // get prefix and extrasufix
        if( isset( $this->elements[ 'info' ][ $infokey ] ) )
            $value = $this->elements[ 'info' ][ $infokey ][ 'prefix' ] . $value . $this->elements[ 'info' ][ $infokey ][ 'sufix' ] . $extravalue . $this->elements[ 'info' ][ $infokey ][ 'extrasufix' ];
        
        $this->app->ajax->html( '#pic' . $infokey . $panelkey, $value )
                        ->removeClass( '#pi' . $infokey . $panelkey, 'hide' );
        return $this;
    }

    public function & ajaxHideInfo( string $panelkey, string $infokey ):mypanel{
        
        $this->app->ajax->html( '#pic' . $infokey . $panelkey, '' )
                        ->switchClass( '#pi' . $infokey . $panelkey, 'hide', 'hide' );
        return $this;
    }

    public function & ajaxMenuLabel( string $panelkey, string $value ):mypanel{
        
        $this->app->ajax->html( '#pms' . $panelkey, $value );

        return $this;
    }

    public function & addInfoThumb( string $key, $size = null, $onclick = '' ):mypanel{
        $this->elements[ 'info' ][ $key ] = array( 'key' => $key, 'type' => 1, 'size' => $size, 'onclick' => $onclick );
        return $this;
    }

    public function & setInfoFilter( string $key, string $filter ):mypanel{
        $this->elements[ 'info' ][ $key ][ 'filter' ] = $filter;
        return $this;
    }

    public function & addStatus( string $key, string $prefix = '', string $sufix = '' ):mypanel{
        $this->elements[ 'status' ][] = array( 'key' => $key, 'type' => 1, 'prefix' => $prefix, 'sufix' => $sufix );
        return $this;
    }

    public function & addStatusIcon( string $key, $icons, $depends = false, $keyclass = '' ):mypanel{
        $this->elements[ 'status' ][] = array( 'key' => $key, 'type' => 2, 'icons' => $icons, 'icon' => is_string( $icons ) ? $icons : false, 'depends' => $depends, 'keyclass' => $keyclass );
        return $this;
    }

    public function & addStatusInfo( string $key, $prefix = '', $sufix = '', $class = '', $defaultvalue = '', $defaultprefix = '', $defaultsufix = '', $defaultclass = '', $extrakey = false, $extrasufix = '', $classreplacekey = '' ):mypanel{
        $this->elements[ 'status' ][ $key ] = array( 'key' => $key, 'type' => 3, 'prefix' => $prefix, 'sufix' => $sufix, 'class' => $class, 'defaultvalue' => $defaultvalue, 'defaultprefix' => $defaultprefix, 'defaultsufix' => $defaultsufix, 'defaultclass' => $defaultclass, 'extrakey' => $extrakey, 'extrasufix' => $extrasufix, 'classreplacekey' => $classreplacekey );
        return $this;
    }

    public function & addStatusThumb( string $key ):mypanel{
        $this->elements[ 'status' ][ $key ] = array( 'key' => $key, 'type' => 4 );
        return $this;
    }

    public function & setStatusInfoFilter( string $key, string $filter ):mypanel{
        $this->elements[ 'status' ][ $key ][ 'filter' ] = $filter;
        return $this;
    }

    public function & ajaxStatusIconShow( string $key, string $id, bool $operation = true ):mypanel{
        $this->app->ajax->css( '#pani' . $id . $key, 'display', $operation ? 'inline' : 'none' );
        return $this;
    }

    public function & ajaxStatusIconShowClass( $classname, $operation = true ):mypanel{
        $this->app->ajax->css( '.panicon' . $classname, 'display', $operation ? 'inline' : 'none' );
        return $this;
    }

    public function & ajaxThumbChange( $src, $class ):mypanel{
        $this->app->ajax->attr( '.pt' . $class, 'src', ( is_string( $src ) && strlen( $src ) ) ? $src : ( isset( $this->elements[ 'thumb' ][ 'default' ] ) ? $this->elements[ 'thumb' ][ 'default' ] : '' ) );
        return $this;
    }

    public function & ajaxMenuShow( $index, $id, $operation = true ):mypanel{

        if( isset( $this->elements[ 'menu' ] ) ){
            foreach( $this->elements[ 'menu' ] as $menu ){
                if( $menu[ 'type' ] == 1 ){
                    foreach( $menu[ 'options' ] as $k => $option ){
                        if( isset( $option[ 'id' ] ) && $option[ 'id' ] == $index ){
                            $this->app->ajax->css( '#panm' . $id . $k, 'display', $operation ? 'block' : 'none' );
                            break;
                        }
                    }
                }
            }
        }

        return $this;
    }

    public function & ajaxMenuShowClass( $index, $class, $operation = true ):mypanel{
        $this->app->ajax->css( '.panmc' . $index . $class, 'display', $operation ? 'block' : 'none' );
        return $this;
    }

    public function & addStatusSeparator( $sep = '|' ):mypanel{
        $this->elements[ 'status' ][] = array( 'type' => 0, 'sep' => $sep );
        return $this;
    }

    public function & addButton( $label, $urlobj, $icon = 'icon-cog4', $id = '', $dependsLabelPrefix = null, $dependsLabelSufix = null, $dependsValueKey = null, $showdepends = '' ):mypanel{
        $this->elements[ 'menu' ][] = array( 'type' => 0, 'icon' => $icon, 'urlobj' => $urlobj, 'label' => $label, 'id' => $id, 'dependsLabelPrefix' => $dependsLabelPrefix, 'dependsLabelSufix' => $dependsLabelSufix, 'dependsValueKey' => $dependsValueKey, 'showdepends' => $showdepends );
        return $this;
    }

    public function & addMenu( $options, $icon = 'icon-cog4', $label = '' ):mypanel{
        $this->elements[ 'menu' ][] = array( 'type' => 1, 'icon' => $icon, 'options' => $options, 'label' => $label );
        return $this;
    }

    public function & setMenuItemDisabled( $menuindex, $subindex, $depends ):mypanel{

        if( isset( $this->elements[ 'menu' ][ $menuindex ][ 'options' ][ $subindex ] ) )
            $this->elements[ 'menu' ][ $menuindex ][ 'options' ][ $subindex ] += array( 'disabled' => true, 'disableddepends' => $depends );
        return $this;
    }

    public function & addToolbarButton( $label, $urlobj = '', $icon = 'icon-cog4', $color = '', $class = '', $htmlid = '' ):mypanel{
        $this->elements[ 'tmenu' ][] = array( 'type' => 0, 'icon' => $icon, 'urlobj' => $urlobj, 'label' => $label, 'color' => $color, 'class' => $class, 'htmlid' => $htmlid );
        return $this;
    }

    public function & addToolbarMenu( $options, $icon = 'icon-cog4', $label = '' ):mypanel{
        $this->elements[ 'tmenu' ][] = array( 'type' => 1, 'icon' => $icon, 'options' => $options, 'label' => $label );
        return $this;
    }

    public function & addToolbarMenuSelect( $id, $options, $icon = 'icon-cog4', $label = null ):mypanel{

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

    public function & addToolbarSeparator( $iterations = 1 ):mypanel{
        $this->elements[ 'tmenu' ][] = array( 'type' => 3, 'it' => $iterations );
        return $this;
    }

    public function & ajaxToolbarMenuSelected( $ctxmenuid, $index ):mypanel{

        if( isset( $this->elements[ 'tmenu' ][ $ctxmenuid ][ 'options' ] ) ){
            foreach( $this->elements[ 'tmenu' ][ $ctxmenuid ][ 'options' ] as $i => $arr ){
                if( $arr[ 'id' ] == $index ){
                    $this->app->ajax->visibility( '#meni' . $ctxmenuid . $arr[ 'id' ], true )
                                    ->attr( '#menl' . $ctxmenuid . $arr[ 'id' ], 'class', 'active' );

                    if( isset( $arr[ 'menu' ] ) ){
                        $this->app->ajax->text( '#menbl' . $ctxmenuid, $arr[ 'menu' ] );
                    }
                }else{
                    $this->app->ajax->visibility( '#meni' . $ctxmenuid . $arr[ 'id' ], false )
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
    public function & setMore( $onclick, $perpage = 10, $offset = 0, $label = 'more' ):mypanel{
        
        // reset counter
//        $this->app->session()->set( 'p' . $this->name . 'perpage', $perpage );
//        $this->app->session()->set( 'p' . $this->name . 'offset',  $offset );
        
        $this->perpage = $perpage;
        
        $this->more = array( 'to' => $onclick, 'label' => $label /*, 'offset' => $this->offset*/ );

        if( isset( $_POST[ 'os' ] ) ){
            $this->offset = intval( $_POST[ 'os' ] );
            $this->app->ajax->val( '#' . $this->name . 'moreos', $this->offset + $this->perpage );
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
    
    public function & resetOffset():mypanel{
        $this->offset = 0;
//        $this->app->session()->set( 'p' . $this->name . 'offset', 0 );
        return $this;
    }

    public function & addAjaxValues( $values, $append = true ):mypanel{

        if( !is_array( $values ) )
            return $this;

        $counter = count( $values );

        if( $counter ){
            $append ? $this->app->ajax->append( '#' . $this->name, $this->render( $values ) ) : $this->app->ajax->prepend( '#' . $this->name, $this->render( $values ) );
        }

        if( $this->perpage > $counter )
            $this->app->ajax->hide( '#' . $this->name . 'more' );

        return $this;
    }

    public function & ajaxRemove( $tag ):mypanel{
        $this->app->ajax->hide( '#' . $this->name . $tag );
        return $this;
    }

    public function & updateAjaxValues( $values ):mypanel{

        if( !is_array( $values ) )
            return $this;

        $counter = count( $values );

        if( $this->perpage > $counter )
            $this->app->ajax->hide( '#' . $this->name . 'more' );
        else
            $this->app->ajax->show( '#' . $this->name . 'more' );

        $this->app->ajax->html( '#' . $this->name, $this->render( $values ) );

        $this->resetOffset();

        return $this;
    }


    public function & show( string $htmlid ):mypanel{
        $this->app->ajax->html( $htmlid, $this->render() );
        return $this;
    }


    public function __toString():string{
        return $this->render();
    }


    private function render( $values = null ):string{

        return $this->app->view->fetch( '@my/mypanel.twig',
            array(
                'values'     => $values ?? $this->values,
                'elements'   => $this->elements,
                'name'       => $this->name,
                'emptymsg'   => $this->emptymsg,
                'cdn'        => $this->cdn,
                'title'      => $this->title,
                'more'       => $this->more,
                'offset'     => $this->offset + $this->perpage,
                'perpage'    => $this->perpage,
                'size'       => $this->size,
                'sizeoffset' => $this->sizeoffset,
                'allitems'   => is_null( $values ),
                'action'     => $this->action,
                'tags'       => array( $this->keys, $this->keyshtml ) ) );
    }
}