<?php

class mymedia{

    /** @var mycontainer*/
    private $app;

    private string $name;
    private array $elements = array();
    private array $values = array();
    private $onInit;
    public  $onInitValues;
    public  $onRefresh;
    public  $onRefreshValues;
    private string $key;
    private string $keyhtml;
    private array $keys = array();
    private array $keyshtml = array();
    private bool $showdiv = true;
    private array $emptymsg = array();
    private bool $ismultiple = true;

    public function __construct( $c ){
        $this->app = $c;
    }

    public function & init(): mymedia
    {
        if( is_callable( $this->onInit ) ) {
            call_user_func($this->onInit);
        }
        if( is_callable( $this->onInitValues ) ) {
            $this->setValues( call_user_func( $this->onInitValues ) );
        }
        return $this;
    }

    public function & setOnInit( $fn ):mymedia{
        $this->onInit = $fn;
        return $this;
    }

    public function & setOnInitValues( $fn ):mymedia{
        $this->onInitValues = $fn;
        return $this;
    }

    public function & refresh(): mymedia
    {
        if( is_callable( $this->onRefresh ) ) {
            call_user_func($this->onRefresh);
        }
        if( is_callable( $this->onRefreshValues ) ) {
            $this->ajaxRefresh( call_user_func($this->onRefreshValues) );
        }
        return $this;
    }

    public function & setOnRefresh( $fn ):mymedia{
        $this->onRefresh = $fn;
        return $this;
    }

    public function & setOnRefreshValues( $fn ):mymedia{
        $this->onRefreshValues = $fn;
        return $this;
    }

    public function & setKey( string $key, string $keyhtml ): mymedia
    {
        $this->key     = $key;
        $this->keyhtml = $keyhtml;
        $this->addKey( $key, $keyhtml );
        return $this;
    }

    public function & setMultiple( bool $ismultiple ):mymedia{
        $this->ismultiple = $ismultiple;
        return $this;
    }

    public function & addKey( string $key, string $keyhtml ): mymedia
    {
        $this->keys[]     = $key;
        $this->keyshtml[] = $keyhtml;
        return $this;
    }

    public function & setName( string $name ): mymedia{
        $this->name = $name;
        return $this;
    }

    public function & setTitle( $keytitle, $urlobj ): mymedia
    {
        $this->elements[ 'title' ] = array( 'keytitle' => $keytitle, 'urlobj' => $urlobj );
        return $this;
    }

    public function & setThumb( $key, $class = 'mediaimg' ): mymedia{
        $this->elements[ 'thumb' ] = array( 'key' => $key, 'class' => $class );
        return $this;
    }

    public function & setVideo( $key, $class = 'mediavideo' ): mymedia{
        $this->elements[ 'video' ] = array( 'key' => $key, 'class' => $class );
        return $this;
    }

    public function & setButton( $key, $urlobj, $label ): mymedia{
        $this->elements[ 'button' ] = array( 'key' => $key, 'urlobj' => $urlobj, 'label' => $label );
        return $this;
    }

    public function & setRating( $key, $obj ): mymedia{
        $this->elements[ 'rating' ] = array( 'key' => $key, 'obj' => $obj );
        return $this;
    }

    public function & setDescription( $key, $mini = true ): mymedia
    {
        $this->elements[ 'description' ] = array( 'key' => $key, 'mini' => $mini );
        return $this;
    }

    public function & setInfo( $key, $class = null ): mymedia
    {
        $this->elements[ 'info' ] = array( 'key' => $key, 'class' => $class );
        return $this;
    }

    public function & setFixed( $kval, $options ): mymedia
    {
        $this->elements[ 'fixed' ] = array( 'kval' => $kval, 'options' => $options );
        return $this;
    }

    public function & setSmall( $key, $prefix = null ): mymedia
    {
        $this->elements[ 'small' ] = array( 'key' => $key, 'prefix' => $prefix );
        return $this;
    }

    public function & setSmallFixed( $key, $prefix, $options ): mymedia
    {
        $this->elements[ 'smallfixed' ] = array( 'key' => $key, 'prefix' => $prefix, 'options' => $options );
        return $this;
    }

    public function & setMenu( $key, $options, $label = '' ): mymedia{

        $this->elements[ 'menu' ] = array( 'key' => $key, 'label' => $label, 'options' => $options );
        return $this;
    }

    public function & setValues( $values ): mymedia
    {
        $this->values = is_string( $values ) ? json_decode($values, true, 512, JSON_THROW_ON_ERROR) : $values;
        return $this;
    }

    public function & ajaxRefresh( $values ): mymedia{

        $this->setValues( $values );

        $this->showdiv = false;

        $this->app->ajax->html( '#' . $this->name . 'div', $this->__toString() );

        return $this;
    }

    public function & setEmptyMessage( $message, $class = 'callout-info', $title = '' ): mymedia{
        $this->emptymsg = array( 'message' => $message, 'class' => $class, 'title' => $title );
        return $this;
    }

    public function __toString():string {
        return $this->app->view->fetch( '@my/mymedia.twig', array( 'elements'   => $this->elements,
                                                                   'values'     => $this->values,
                                                                   'name'       => $this->name,
                                                                   'showdiv'    => $this->showdiv,
                                                                   'emptymsg'   => $this->emptymsg,
                                                                   'ismultiple' => $this->ismultiple,
                                                                   'tags'     => array( array( $this->key ) + $this->keys, array( $this->keyhtml ) + $this->keyshtml ) ) );
    }
}