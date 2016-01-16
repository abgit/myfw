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

    public function & addThumb( $key, $keyhttps = null, $static = false, $size = 3, $onclick = '' ){
        $this->elements[ 'thumb' ] = array( 'key' => ( !is_null( $keyhttps ) && $this->app->ishttps() ) ? $keyhttps : $key, 'static' => $static, 'size' => $size, 'onclick' => $onclick );
        return $this;
    }
    
    public function & setCDN( $cdn ){
        $this->cdn = $cdn;
        return $this;
    }

    public function & addTitle( $key, $static = false, $onclick = '' ){
        $this->elements[ 'title' ] = array( 'key' => $key, 'static' => $static, 'onclick' => $onclick );
        return $this;
    }

    public function & addReference( $key ){
        $this->elements[ 'reference' ] = array( 'key' => $key );
        return $this;
    }

    public function & addDescription( $key, $static = false, $onclick = '' ){
        $this->elements[ 'description' ] = array( 'key' => $key, 'static' => $static, 'onclick' => $onclick );
        return $this;
    }

    public function & addBackground( $key, $keyhttps = null ){
        $this->elements[ 'back' ] = array( 'key' => ( !is_null( $keyhttps ) && $this->app->ishttps() ) ? $keyhttps : $key );
        return $this;
    }

    public function & addInfo( $key, $prefix = '', $sufix = '', $class = '', $defaultvalue = '', $defaultprefix = '', $defaultsufix = '', $defaultclass = '', $extrakey = false, $extrasufix = '', $depends = false ){
        $this->elements[ 'info' ][ $key ] = array( 'key' => $key, 'type' => 0, 'prefix' => $prefix, 'sufix' => $sufix, 'class' => $class, 'defaultvalue' => $defaultvalue, 'defaultprefix' => $defaultprefix, 'defaultsufix' => $defaultsufix, 'defaultclass' => $defaultclass, 'extrakey' => $extrakey, 'extrasufix' => $extrasufix, 'depends' => $depends );
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

    public function & addStatusIcon( $key, $icons, $depends = false ){
        $this->elements[ 'status' ][] = array( 'key' => $key, 'type' => 2, 'icons' => $icons, 'icon' => is_string( $icons ) ? $icons : false, 'depends' => $depends );
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

    public function & ajaxMenuShow( $index, $id, $operation = true ){
        $this->app->ajax()->css( '#panm' . $id . $index, 'display', $operation ? 'block' : 'none' );
        return $this;
    }

    public function & addStatusSeparator( $sep = '|' ){
        $this->elements[ 'status' ][] = array( 'type' => 0, 'sep' => $sep );
        return $this;
    }

    public function & addButton( $label, $onclick = '', $href = '', $icon = 'icon-cog4', $color = '', $id = '', $depends = false, $dependsLabelPrefix = '', $dependsLabelSufix = '', $dependsValueKey = '' ){
        $this->elements[ 'menu' ][] = array( 'type' => 0, 'icon' => $icon, 'href' => $href, 'onclick' => $onclick, 'label' => $label, 'color' => $color, 'id' => $id, 'depends' => $depends, 'dependsLabelPrefix' => $dependsLabelPrefix, 'dependsLabelSufix' => $dependsLabelSufix, 'dependsValueKey' => $dependsValueKey );
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

    public function & addToolbarMenuSelect( $options, $icon = 'icon-cog4', $label = '' ){
        $id = $this->name . 'sel' . $this->idmenusel++;
        $this->elements[ 'tmenu' ][ $id ] = array( 'type' => 2, 'icon' => $icon, 'options' => $options, 'label' => $label, 'id' => $id );
        return $this;
    }

    public function & addToolbarSeparator( $iterations = 1 ){
        $this->elements[ 'tmenu' ][] = array( 'type' => 3, 'it' => $iterations );
        return $this;
    }

    public function & setToolbarMenuSelected( $index, $ctxmenuid = '' ){
        $ctxmenuid = empty( $ctxmenuid ) ? $this->name . 'sel' : $ctxmenuid;
    
        if( $this->app->request->isAjax() ){
            if( isset( $this->elements[ 'tmenu' ][ $ctxmenuid ][ 'options' ] ) )
                foreach( $this->elements[ 'tmenu' ][ $ctxmenuid ][ 'options' ] as $i => $arr )
                    $this->app->ajax()->visibility( '#meni' . $ctxmenuid . $i, ( $i == $index ) ? true : false )->attr( '#menl' . $ctxmenuid . $i, 'class', ( $i == $index ) ? 'active' : '' );
        }else{
                foreach( $this->elements[ 'tmenu' ][ $ctxmenuid ][ 'options' ] as $i => $arr ){
                    $this->elements[ 'tmenu' ][ $ctxmenuid ][ 'options' ][ $i ][ 'selected' ] = ( $i == $index ) ? true : false;
                }
        }

        return $this;
    }

    public function & setToolbarMenuLabel( $label, $ctxmenuid = '' ){
        $ctxmenuid = empty( $ctxmenuid ) ? $this->name . 'sel' : $ctxmenuid;
        
        $this->app->ajax()->text( '#menbl' . $ctxmenuid, $label );
        return $this;
    }

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