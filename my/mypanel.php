<?php

class mypanel{

    private $name;
    private $key;
    private $keyhtml;
    private $title;
    private $emptymsg = 'No elements to display';
    private $values   = array();
    private $elements = array();
    private $more     = false;

    public function __construct( $name = 'p' ){
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

    public function & setTitle( $label, $icon = 'icon-stack' ){
        $this->title = array( 'label' => $label, 'icon' => $icon );
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

    public function & addThumb( $key, $keyhttps ){
        $this->elements[ 'thumb' ] = array( 'key' => $this->app->ishttps() ? $keyhttps : $key );
        return $this;
    }

    public function & addTitle( $key ){
        $this->elements[ 'title' ] = array( 'key' => $key );
        return $this;
    }

    public function & addReference( $key ){
        $this->elements[ 'reference' ] = array( 'key' => $key );
        return $this;
    }

    public function & addDescription( $key ){
        $this->elements[ 'description' ] = array( 'key' => $key );
        return $this;
    }

    public function & addInfo( $key, $prefix = '', $sufix = '', $class = '', $defaultvalue = '', $defaultprefix = '', $defaultsufix = '', $defaultclass = '' ){
        $this->elements[ 'info' ][ $key ] = array( 'key' => $key, 'prefix' => $prefix, 'sufix' => $sufix, 'class' => $class, 'defaultvalue' => $defaultvalue, 'defaultprefix' => $defaultprefix, 'defaultsufix' => $defaultsufix, 'defaultclass' => $defaultclass );
        return $this;
    }

    public function & setInfoFilter( $key, $filter ){
        $this->elements[ 'info' ][ $key ][ 'filter' ] = $filter;
        return $this;
    }

    public function & addStatus( $key ){
        $this->elements[ 'status' ][] = array( 'key' => $key, 'type' => 1 );
        return $this;
    }

    public function & addStatusIcon( $key, $icons ){
        $this->elements[ 'status' ][] = array( 'key' => $key, 'type' => 2, 'icons' => $icons );
        return $this;
    }

    public function & addStatusSeparator( $sep = '|' ){
        $this->elements[ 'status' ][] = array( 'type' => 0, 'sep' => $sep );
        return $this;
    }

    public function & addButton( $label, $onclick = '', $href = '', $icon = 'icon-cog4', $color = '' ){
        $this->elements[ 'menu' ][] = array( 'type' => 0, 'icon' => $icon, 'href' => $href, 'onclick' => $onclick, 'label' => $label, 'color' => $color );
        return $this;
    }

    public function & addMenu( $options, $icon = 'icon-cog4', $label = '' ){
        $this->elements[ 'menu' ][] = array( 'type' => 1, 'icon' => $icon, 'options' => $options, 'label' => $label );
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

    public function & addToolbarMenuSelect( $options, $icon = 'icon-cog4', $label = '', $id = '' ){
        $id = empty( $id ) ? $this->name . 'sel' : $id;
        $this->elements[ 'tmenu' ][ $id ] = array( 'type' => 2, 'icon' => $icon, 'options' => $options, 'label' => $label, 'id' => $id );
        return $this;
    }

    public function & setToolbarMenuSelected( $index, $ctxmenuid = '' ){
        $ctxmenuid = empty( $ctxmenuid ) ? $this->name . 'sel' : $ctxmenuid;
    
        if( $this->app->request->isAjax() ){
            if( isset( $this->elements[ 'tmenu' ][ $ctxmenuid ][ 'options' ] ) )
                foreach( $this->elements[ 'tmenu' ][ $ctxmenuid ][ 'options' ] as $i => $arr )
                    $this->app->ajax()->visibility( '#meni' . $ctxmenuid . $i, ( $i == $index ) ? true : false );
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
        $this->app->session()->set( 'p' . $this->name . 'perpage', $perpage );
        $this->app->session()->set( 'p' . $this->name . 'offset',  $offset );
        
        $this->more = array( 'onclick' => $onclick, 'label' => $label );

        return $this;
    }

    public function getPerPage(){
        return intval( $this->app->session()->get( 'p' . $this->name . 'perpage', 10 ) );
    }

    public function getOffset( $increase = false ){

        $newoffset = intval( $this->app->session()->get( 'p' . $this->name . 'offset', 0 ) ) + $this->getPerPage();

        $this->app->session()->set( 'p' . $this->name . 'offset', $newoffset );

        return $newoffset;
    }
    
    public function & resetOffset(){
        $this->app->session()->set( 'p' . $this->name . 'offset', 0 );
        return $this;
    }

    public function &addAjaxValues( $values, $append = true ){

        if( !is_array( $values ) )
            return $this;

        $counter = count( $values );

        if( $counter ){
            $append ? $this->app->ajax()->append( '#' . $this->name, $this->render( $values ) ) : $this->app->ajax()->prepend( '#' . $this->name, $this->render( $values ) );
        }

        if( $this->getPerPage() > $counter )
            $this->app->ajax()->hide( '#' . $this->name . 'more' );

        return $this;
    }

    public function & updateAjaxValues( $values ){

        if( !is_array( $values ) )
            return $this;

        $counter = count( $values );

        if( $this->getPerPage() > $counter )
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
        return $this->app->render( '@my/mypanel', array( 'values'   => is_null( $values ) ? $this->values : $values,
                                                         'elements' => $this->elements,
                                                         'name'     => $this->name,
                                                         'key'      => $this->key,
                                                         'keyhtml'  => $this->keyhtml,
                                                         'emptymsg' => $this->emptymsg,
                                                         'more'     => $this->more,
                                                         'perpage'  => $this->getPerPage(),
                                                         'allitems' => is_null( $values )
                                                         ), null, null, null, false, false );
    }
}