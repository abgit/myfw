<?php

class mygrid{

    /** @var mycontainer*/
    private $app;

    private string $name;
    private string $key = '';
    private string $keyhtml = '';
    private array $cols;
    private array $labels;
    private array $more   = array();
    private array $values = array();
    private array $title = array();
    private string $emptymsg = 'No elements to display';
    private array $buttons = array();
    private array $buttonsbottom = array();
    private array $actions = array();
    private array $modal = array();
    private string $orderby = '';
    private bool $orderbya = false;
    private ?mymenu $menuhtml = null;
    private ?string $rowclass = null;
    private ?string $rowclassdepends = null;
    private int $perpage = 10;
    private string $titlekey = '';
    private array $keys = array();
    private array $keyshtml = array();
    private bool $classxs  = false;
    private bool $classsm  = false;
    private ?myform $form   = null;
    private bool $showheaders = true;

    public  $onInit;
    public  $onMore;
    public  $onRefresh;

    public function __construct( $c ){
        $this->app  = $c;
    }

    public function & setName( $name ): mygrid
    {
        $this->name = $name;
        return $this;
    }
    
    public function & setKey( string $key, string $keyhtml ): mygrid
    {
        $this->key     = $key;
        $this->keyhtml = $keyhtml;
        $this->addKey( $key, $keyhtml );
        return $this;
    }

    public function & addKey( string $key, string $keyhtml ): mygrid
    {
        $this->keys[]     = $key;
        $this->keyshtml[] = $keyhtml;
        return $this;
    }

    public function & setTitle( $label, $icon = '', $description = '' ): mygrid
    {
        $this->title[ 'label' ] = $label;
        $this->title[ 'icon' ]  = $icon;
        $this->title[ 'description' ]  = $description;
        return $this;
    }

    public function & setTitleKey( $key ): mygrid
    {
        $this->titlekey = $key;
        return $this;
    }

    public function & showHeaders( $showheaders ): mygrid
    {
        $this->showheaders = $showheaders;
        return $this;
    }

    public function & setEmptyMessage( $emptymsg ): mygrid
    {
        $this->emptymsg = $emptymsg;
        return $this;
    }
    
    public function & setW( $k, $width ): mygrid
    {
        $widths = $this->app->config[ 'grid.widths' ];

        if( isset( $this->labels[ $k ] ) ) {
            $this->labels[$k]['width'] = $widths[$width] ?? $width;
        }

        return $this;
    }

    public function & setWidth(): mygrid{

        $arg = func_get_args();
        $i   = 0;
        foreach( $this->labels as $k => $el ){
            if( isset( $arg[ $i ] ) ) {
                $this->labels[$k]['width'] = $arg[$i];
            }
            $i++;
        }
        return $this;
    }

    public function & setOrderby( $col, $asc ): mygrid{
        $this->orderby  = $col;
        $this->orderbya = $asc;
        return $this;
    }

    public function addAction( $name, $call ): mygrid
    {
        $this->actions[ $name ] = $call;
        return $this;
    }

    public function __call( $name, $arguments ){

        if( isset( $this->actions[ $name ] ) ){
            return call_user_func_array( $this->actions[ $name ], $arguments );
        }

        return null;
    }

    public function & addToolbarButton( $label, $icon, $onclick, $class = 'info', $id = '' ): mygrid
    {
        $this->buttons[] = array( 'label' => $label, 'icon' => $icon, 'onclick' => $onclick, 'class' => $class, 'id' => $id );
        return $this;
    }

    public function & addToolbarButtonBottom( $label, $icon, $onclick, $class = 'info', $id = '' ): mygrid
    {
        $this->buttonsbottom[] = array( 'label' => $label, 'icon' => $icon, 'onclick' => $onclick, 'class' => $class, 'id' => $id );
        return $this;
    }


    public function & addSimple( $key, $kval, $label = '', $align = '', $truncate = '' ): mygrid{

        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'simple', 'truncate' => $truncate );
        return $this;
    }

    public function & addDescription( $key, $kval, $label = '', $align = '', $inline = false, $truncate = 36 ): mygrid{

        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'description', 'inline' => $inline, 'truncate' => $truncate );
        return $this;
    }

    public function & addH4( $key, $kval, $label = '', $align = '', $kval2 = false, $prefix2 = false ): mygrid{

        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'h4', 'kval2' => $kval2, 'prefix2' => $prefix2 );
        return $this;
    }

    public function & addSpan( $key, $kval, $label = '', $align = '' ): mygrid {

        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'span' );
        return $this;
    }

    public function & addThumb( $key, $kval, $label = '', $urlobj = '', $default = '', $kvalclass = '' ): mygrid
    {

        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label );
        }

        if( empty( $default ) ) {
            $default = $this->app->config['panel.defaultmini'];
        }

        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'kvalclass' => $kvalclass, 'type' => 'thumb', 'urlobj' => $urlobj, 'default' => $default );
        return $this;
    }

    public function & addAgo( $key, $kval, $label, $dateonly = false, $keycustomdate = '' ): mygrid
    {

        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'ago', 'dateonly' => $dateonly, 'keycustomdate' => $keycustomdate );
        return $this;
    }

    public function & addProgress( $key, $kval, $label, $class = '', $big = true ): mygrid
    {

        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'progress', 'class' => $class, 'big' => $big );
        return $this;
    }

    public function & addUrl( $key, $kval, $label, $urlobj = false, $align = 'left', $truncate = '' ): mygrid
    {

        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'url', 'urlobj' => $urlobj, 'truncate' => $truncate );
        return $this;
    }

    public function & addFixed( $key, $kval, $label, $options, $default = array(), $align = 'center' ): mygrid
    {

        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'fixed', 'options' => $options, 'default' => $default );
        return $this;
    }

    public function & addInfo( $key, $kval, $label = '', $title = '', $align = 'left', $depends = false, $dependsnot = false ): mygrid
    {

        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => $align  );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'info', 'title' => $title, 'depends' => $depends, 'dependsnot' => $dependsnot );
        return $this;
    }

    public function & addImage( $key, $kval, $label, $cdn = '', $sufix = '', $width = 20, $height = 15 ): mygrid
    {

        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'image', 'cdn' => $cdn, 'sufix' => $sufix, 'width' => $width, 'height' => $height);
        return $this;
    }

    public function & addLabel( $key, $kval, $label = '', $classreplacedefault = '', $classreplace = array(), $customkey = false, $replaceval = false, $sufix = '' ): mygrid
    {

        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'kval' => $kval, 'type' => 'label', 'replaceval' => $replaceval, 'classreplace' => $classreplace, 'classreplacedefault' => $classreplacedefault, 'classreplacekey' => $customkey, 'sufix' => $sufix );
        return $this;
    }

    public function & addBr( $key ): mygrid
    {
        $this->cols[ $key ][] = array( 'key' => $key, 'type' => 'br' );
        return $this;
    }

    public function & addSpace( $key ): mygrid
    {
        $this->cols[ $key ][] = array( 'key' => $key, 'type' => 'space' );
        return $this;
    }

    public function & setMenu( mymenu $obj ): mygrid
    {
        $this->menuhtml = $obj;
        return $this;
    }

    public function getMenu(): ?mymenu
    {
        return $this->menuhtml;
    }

    public function & addMenu( $key, $options, $label = '', $icon = '', $align = '', $depends = false, $buttons = array() ): mygrid
    {

        if( !isset( $this->labels[ $key ] ) ){
            $this->labels[ $key ] = array( 'key' => $key, 'label' => $label, 'align' => empty( $align ) ? 'center' : $align );
        }
        $this->cols[ $key ][] = array( 'key' => $key, 'type' => 'menu', 'icon' => $icon, 'options' => $options, 'depends' => $depends, 'buttons' => $buttons );

        return $this;
    }

    public function & setMenuItemDisabled( $index, $depends ): mygrid
    {
    
        foreach( $this->cols as $col => $list ){
            foreach( $list as $k => $c){
                if( $c[ 'type' ] === 'menu' && isset( $this->cols[ $col ][ $k ][ 'options' ][ $index ] ) ){
                    $this->cols[ $col ][ $k ][ 'options' ][ $index ] += array( 'disabled' => true, 'disableddepends' => $depends );
                }
            }
        }
        return $this;
    }

    public function & ajaxSetMenuItemDisabled( $key, $id, $menu = 'menutools' ): mygrid
    {

        if( isset( $this->cols[ $menu ][0][ 'options' ] ) ){
            foreach( $this->cols[ $menu ][0][ 'options' ] as $i => $options ){
                if( isset( $options[ 'id' ] ) && $options[ 'id' ] == $id ){
                    $this->app->ajax->attr( '#' . $key . 'm' . $i, 'class', 'disabled' );
                    return $this;
                }
            }
        }

        return $this;
    }

    public function & ajaxThumbChange( $src, $class ): mygrid
    {
        $this->app->ajax->attr( '.gt' . $class, 'src', ( is_string( $src ) && $src !== '') ? $src : $this->app->config[ 'grid.thumb' ] );
        return $this;
    }


    public function & setAction( $key, $urlobj ): mygrid
    {
        if( isset( $this->labels[ $key ] ) ) {
            $this->labels[$key]['urlobj'] = $urlobj;
        }
        return $this;
    }

    public function & setValues( $values ): mygrid
    {

        $this->values = is_array( $values ) ? $values : json_decode( $values, true );

        if( is_string( $this->titlekey ) && isset( $values[ $this->titlekey ] ) ) {
            $this->title['labelkey'] = $values[$this->titlekey];
        }

        $this->app->session->set( $this->name . 'gridinit', false );

        return $this;
    }

    public function & setClassXS( $el ): mygrid
    {

        $this->classxs = true;

        if( !is_array( $el ) ) {
            $el = array($el);
        }

        foreach( $this->cols as $k => $e ){
            if( !in_array( $k, $el, false ) ){
                $this->setClass( $k, 'hidden-xs' );
            }
        }

        return $this;
    }

    public function & setClassSM( $el ): mygrid
    {

        $this->classsm = true;

        if( !is_array( $el ) ) {
            $el = array($el);
        }

        foreach( $this->cols as $k => $e ){
            if( !in_array( $k, $el, false ) ){
                $this->setClass( $k, 'hidden-sm' );
            }
        }

        return $this;
    }

    public function & setClass( $el, $class ): mygrid
    {

        if( !is_array( $el ) ) {
            $el = array($el);
        }

        foreach( $el as $e ){
            if( isset( $this->labels[ $e ] ) ){
                $this->labels[ $e ][ 'class' ] = ( isset( $this->labels[ $e ][ 'class' ] ) && $this->labels[$e]['class'] !== '') ? ( $this->labels[ $e ][ 'class' ] . ' ' . $class ) : $class;
            }
        }
        return $this;
    }

    public function & setClassDepends( string $class, string $depends ): mygrid
    {
        $this->rowclass = $class;
        $this->rowclassdepends = $depends;
        return $this;
    }

    public function & setRowClass( $key, $kval, $class, $dependkey = false ): mygrid
    {

        if( isset( $this->cols[ $key ] ) ){
            foreach( $this->cols[ $key ] as $index => $subrow ){
                if( isset( $this->cols[ $key ][ $index ][ 'kval' ] ) && $this->cols[ $key ][ $index ][ 'kval' ] === $kval ) {
                    $this->cols[$key][$index]['class'] = array('list' => $class, 'key' => $dependkey);
                }
            }
        }
        return $this;
    }


    public function & setRowReplace( $key, $kval, $replace ): mygrid
    {

        if( isset( $this->cols[ $key ] ) ){
            foreach( $this->cols[ $key ] as $index => $subrow ){
                if( isset( $this->cols[ $key ][ $index ][ 'kval' ] ) && $this->cols[ $key ][ $index ][ 'kval' ] === $kval ) {
                    $this->cols[$key][$index]['replace'] = $replace;
                }
            }
        }
        return $this;
    }

    public function & setTruncate( $key, $kval, $truncate ): mygrid
    {

        if( isset( $this->cols[ $key ] ) ){
            foreach( $this->cols[ $key ] as $index => $subrow ){
                if( isset( $this->cols[ $key ][ $index ][ 'kval' ] ) && $this->cols[ $key ][ $index ][ 'kval' ] === $kval ) {
                    $this->cols[$key][$index]['truncate'] = $truncate;
                }
            }
        }
        return $this;
    }

    public function & addAddon( $key, $kval, $value, $prefix = true, $valuesingular = null, $isorder = null ): mygrid
    {

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

    public function & refreshAjaxValue( $value ): mygrid
    {

        if( is_string( $value ) ) {
            $value = json_decode($value, true);
        }

        return $this->refreshAjaxValues( array( $value ) );
    }

    public function & refreshAjaxValues( $values ): mygrid
    {

        if( is_string( $values ) ) {
            $values = json_decode($values, true);
        }

        if( is_array( $values ) ){
            foreach( $values as $row ){
                if( isset( $row[ $this->key ] ) ) {
                    $this->app->ajax->replacewith('#' . $this->name . $row[$this->key], $this->render(array($row)));
                }
            }
        }

        return $this;
    }

    public function & ajaxAddReplaceValues( $values ): mygrid
    {

        if( is_string( $values ) ) {
            $values = json_decode($values, true);
        }

        $this->app->ajax->hide( '#' . $this->name . 'empty' );

        foreach( $values as $row ){
            if( isset( $row[ $this->key ] ) ) {
                $this->app->ajax->addreplacewith('#' . $this->name . $row[$this->key], $this->render(array($row)), '#' . $this->name);
            }
        }

        return $this;    
    }

    public function & ajaxRefresh( $values ): mygrid
    {

        if( is_string( $values ) ) {
            $values = json_decode($values, true);
        }

        if( empty( $values ) ) {
            $values = array();
        }
        
        $this->app->ajax->html( '#' . $this->name, $this->render( $values, true ) );

        if( $this->app->rules->isdecimal( count( $values ) / $this->getLimit() ) ) {
            $this->app->ajax->remove('#' . $this->name . 'more');
        }

        return $this;    
    }

    public function & deleteAjaxValue( $key ): mygrid
    {
        $this->app->ajax->hideTableRow( '#' . $this->name . $key, '#' . $this->name );
        return $this;
    }

    public function & addAjaxValue( $value ): mygrid
    {
        if( is_string( $value ) ) {
            $value = json_decode($value, true);
        }

        return $this->addAjaxValues( array( $value ), false );
    }

    public function & addAjaxValues( $values, $append = true ): mygrid
    {

        if( !is_array( $values ) ) {
            return $this;
        }

        $counter = count( $values );

        if( $counter ){
            $append ? $this->app->ajax->append( '#' . $this->name, $this->render( $values ) ) : $this->app->ajax->prepend( '#' . $this->name, $this->render( $values ) );

            $this->app->ajax->hide( '#' . $this->name . 'empty' );
        }

        if( $this->perpage > $counter ) {
            $this->app->ajax->remove('#' . $this->name . 'more');
        }

        return $this;
    }

    public function & addAjaxPage( $values ): mygrid
    {
        $this->addAjaxValues( $values );
        if( !empty( $values ) ) {
            $this->pageIncrement();
        }
    
        return $this;
    }

    public function & pageIncrement(): mygrid
    {
        $this->app->session->set( $this->name . 'gridinit', false );

        $g = $this->app->session->get( $this->name . 'gridpage' );

        $this->app->session->set( $this->name . 'gridpage', 1 + $g );

        return $this;
    }

    public function & setMore( $onclick, $perpage = 10, $label = 'more' ): mygrid{

        $this->more    = array( 'onclick' => $onclick, 'label' => $label );
        $this->perpage = $perpage;

        return $this;
    }

    public function getPage():int{

        if( $this->app->session->get( $this->name . 'gridinit', true ) === true ){
            return 0;
        }
        return 1 + $this->app->session->get( $this->name . 'gridpage', 0 );
    }

    public function getLimit():int{

        $resetall = false;

        if( $this->app->session->get( $this->name . 'gridinit' ) === true ){

            $resetall = $this->app->session->get( $this->name . 'gridresetall' );
        }

        return ( $resetall === true ) ? $this->perpage : ( $this->perpage + $this->perpage * $this->app->session->get( $this->name . 'gridpage' ) );
    }

    public function getOffset():int{
        return $this->getPage() * $this->getLimit();
    }

    public function & pageReset( $resetall = true ): mygrid
    {
        $this->app->session->set( $this->name . 'gridinit',     true );
        $this->app->session->set( $this->name . 'gridresetall', $resetall );
        return $this;
    }


    public function & setModal( $title, $class = 'modal-lg', $icon = 'icon-paragraph-justify2', $static = true, $width = '' ): mygrid
    {
        $this->modal = array( 'formid' => 'mygf' . $this->name, 'title' => $title, 'class' => $class, 'icon' => $icon, 'static' => $static, 'width' => $width );
        return $this;
    }

    public function & hide(): mygrid
    {
        $this->modalform()->hide();
        return $this;
    }

    public function init(): mygrid
    {
        call_user_func($this->onInit);
        return $this;
    }

    public function & setOnInit( $fn ):mygrid{
        $this->onInit = $fn;
        return $this;
    }

    public function more(): mygrid
    {
        call_user_func($this->onMore);
        return $this;
    }

    public function & setOnMore( $fn ):mygrid{
        $this->onMore = $fn;
        return $this;
    }

    public function refresh(): mygrid
    {
        call_user_func($this->onRefresh);
        return $this;
    }

    public function & setOnRefresh( $fn ):mygrid{
        $this->onRefresh = $fn;
        return $this;
    }

    public function modalform():myform{

        if( is_null( $this->form ) ) {
            $this->form = $this->app->form;
        }

        if( isset( $this->modal[ 'formid' ] ) ){
            $this->form
                ->setName( $this->modal[ 'formid' ] )
                ->setModal( $this->modal[ 'title' ], $this->modal[ 'class' ], $this->modal[ 'icon' ], $this->modal[ 'static' ], $this->modal[ 'width' ] );
        }

        return $this->form->addAjax()
                          ->addCustom( $this->name, $this );
    }

    public function show( $htmlid = null ): ?myform
    {
        if( is_null( $htmlid ) ){
            return $this->modalform()->show();
        }
        $this->app->ajax->html( $htmlid, $this->render() );

        return null;
    }

    public function __toString():string{
        return $this->render();
    }

    private function render( $customvalues = null, $renderempty = false ): string{

        $values      = is_null( $customvalues ) ? $this->values : $customvalues;
        $valuestotal = count( $values );

        if( is_null( $this->classxs ) ) {
            $this->setClass(array_keys(array_slice($this->cols, 1, -1)), 'hidden-xs');
        }

        if( is_null( $this->classsm ) ) {
            $this->setClass(array_keys(array_slice($this->cols, 2, -2)), 'hidden-sm');
        }

        return $this->app->view->fetch( '@my/mygrid.twig', array( 'name'            => $this->name,
                                                                  'key'             => $this->key,
                                                                  'keyhtml'         => $this->keyhtml,
                                                                  'labels'          => $this->labels,
                                                                  'allitems'        => is_null( $customvalues ),
                                                                  'emptyitem'       => ( is_null( $customvalues ) || ( $renderempty && empty( $customvalues ) ) ),
                                                                  'values'          => $values,
                                                                  'more'            => $this->more,
                                                                  'moreshow'        => ( $valuestotal > 0 && !$this->app->rules->isdecimal( $valuestotal / $this->perpage ) ),
                                                                  'title'           => $this->title,
                                                                  'emptymsg'        => $this->emptymsg,
                                                                  'buttons'         => $this->buttons,
                                                                  'buttonsbottom'   => $this->buttonsbottom,
                                                                  'perpage'         => $this->perpage,
                                                                  'orderby'         => $this->orderby,
                                                                  'orderbya'        => $this->orderbya,
                                                                  'menuhtml'        => $this->menuhtml,
                                                                  'rowclass'        => $this->rowclass,
                                                                  'rowclassdepends' => $this->rowclassdepends,
                                                                  'cols'            => $this->cols,
                                                                  'showheaders'     => $this->showheaders,
                                                                  'tags'            => array( array( $this->key ) + $this->keys, array( $this->keyhtml ) + $this->keyshtml ) ) );
    }
}