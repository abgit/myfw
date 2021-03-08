<?php

class myprogress{

    /** @var mycontainer*/
    private $app;

    private string $name = '';
    private array $elements = array();
    private int $value = 0;
    private $onInit;
    private string $key = '';
    private string $keyhtml = '';
    private array $keys = array();
    private array $keyshtml = array();
    private bool $showdiv = true;
    private string $class = 'progress-done';
    private string $classActiveOn  = 'progress-running';
    private string $classActiveOff = 'progress-stopped';

    private ?string $titleFormatSingular = '';
    private string $titleFormatPlural = '';
    private bool $showTitle = false;
    private bool $showHeader = false;
    private int $height = 20;

    private bool $on = true;

    public function __construct( $c ){
        $this->app = $c;
    }

    public function & setOnInit( $fn ):myprogress{
        $this->onInit = $fn;
        return $this;
    }

    public function getClass():string{
        return $this->class;
    }

    public function getClassActive():string{
        return $this->on ? $this->classActiveOn : $this->classActiveOff;
    }

    public function getClassDone():string{
        return $this->on ? $this->class : $this->classActiveOff;
    }

    public function getActive():string{
        return $this->on ? 'active' : '';
    }

    public function & setHeight( $height ):myprogress{
        $this->height = $height;
        return $this;
    }

    public function & setKey( string $key, string $keyhtml ): myprogress{
        $this->key     = $key;
        $this->keyhtml = $keyhtml;
        $this->addKey( $key, $keyhtml );
        return $this;
    }

    public function & addKey( string $key, string $keyhtml ): myprogress{
        $this->keys[]     = $key;
        $this->keyshtml[] = $keyhtml;
        return $this;
    }

    public function & setTitleFormat( string $format ): myprogress{

        $this->showTitle = true;

        $e = explode( '!', $format );

        $this->titleFormatPlural   = $e[0] ?? '';
        $this->titleFormatSingular = $e[1] ?? $this->titleFormatPlural;

        return $this;
    }

    public function getTitleFormat( $unit ):string{
        return (int)$unit === 1 && !is_null( $this->titleFormatSingular )? $this->titleFormatSingular : $this->titleFormatPlural;
    }

    public function init( $args = null ): myprogress{
        call_user_func( $this->onInit, $args );
        return $this;
    }

    public function & setName( string $name ): myprogress{
        $this->name = $name;
        return $this;
    }

    public function & addStep( string $name, int $units, string $help = '', string $header = '' ): myprogress{

        if( !empty( $header ) ){
            $this->showHeader = true;
        }

        $this->elements[ $name ] = array( 'name' => $name, 'units' => $units, 'help' => $help, 'header' => $header );
        return $this;
    }

    public function getSteps():array{

        if( empty( $this->elements ) ){
            return array();
        }

        $unitsTotal = 0;

        foreach ( $this->elements as $name => $element ){

            // add start unit
            $this->elements[ $name ][ 'start' ] = 1+$unitsTotal;
            $this->elements[ $name ][ 'end' ]   =   $unitsTotal + $this->elements[ $name ][ 'units' ];

            $unitsTotal = $this->elements[ $name ][ 'end' ];
        }

        $widthTotal = 0;
        foreach ( $this->elements as $name => $element ){

            // add width
            $width = (int) ( $this->elements[ $name ][ 'units' ] * 100 / $unitsTotal );
            $this->elements[ $name ][ 'width' ] = $width;
            $widthTotal += $width;

            // check value
            if( $this->value >= $this->elements[ $name ][ 'start' ] && $this->value < $this->elements[ $name ][ 'end' ] ){

                $value      = $this->value - $this->elements[ $name ][ 'start' ] + 1;
                $percentage = (int) ( $value * 100 / $this->elements[ $name ][ 'units' ] );
                $unitsleft  = $this->elements[ $name ][ 'units' ] - $value;

                $this->elements[ $name ][ 'percentage' ] = $percentage;
                $this->elements[ $name ][ 'class' ]      = $this->getClassActive();
                $this->elements[ $name ][ 'active' ]     = $this->getActive();
                $this->elements[ $name ][ 'unitsleft' ]  = $unitsleft;
                $this->elements[ $name ][ 'title' ]      = sprintf( $this->getTitleFormat($unitsleft), $unitsleft );
                $this->elements[ $name ][ 'type' ]       = 1;

            }elseif( $this->value < $this->elements[ $name ][ 'start' ] ){
                $this->elements[ $name ][ 'percentage' ] = 0;
                $this->elements[ $name ][ 'class' ]      = '';
                $this->elements[ $name ][ 'active' ]     = '';
                $this->elements[ $name ][ 'unitsleft' ]  = $this->elements[ $name ][ 'units' ];
                $this->elements[ $name ][ 'title' ]      = '';
                $this->elements[ $name ][ 'type' ]       = 3;

            }elseif( $this->value > $this->elements[ $name ][ 'end' ] ) {
                $this->elements[ $name ][ 'percentage' ] = 100;
                $this->elements[ $name ][ 'class' ]      = $this->class;
                $this->elements[ $name ][ 'active' ]     = '';
                $this->elements[ $name ][ 'unitsleft' ]  = 0;
                $this->elements[ $name ][ 'title' ]      = '';
                $this->elements[ $name ][ 'type' ]       = 4;

            }elseif( $this->value == $this->elements[ $name ][ 'end' ] ) {
                $this->elements[ $name ][ 'percentage' ] = 100;
                $this->elements[ $name ][ 'class' ]      = $this->getClassDone();
                $this->elements[ $name ][ 'active' ]     = $this->getActive();
                $this->elements[ $name ][ 'unitsleft' ]  = 0;
                $this->elements[ $name ][ 'title' ]      = '';
                $this->elements[ $name ][ 'type' ]       = 4;
            }
        }

        // add width correction to last element so that total width is always 100%
        $this->elements[ $name ][ 'width' ] += 100 - $widthTotal;

        return $this->elements;
    }

    public function & setValue( $value ): myprogress{
        $this->value = abs( $value );
        $this->on    = $value >= 0;
        return $this;
    }

    public function & setOn( bool $on ): myprogress{
        $this->on = $on;
        return $this;
    }

    public function & ajaxRefresh( $value ): myprogress{

        $this->setValue( $value );

        $this->showdiv = false;

        $this->app->ajax->html( '#' . $this->name, $this->__toString() );

        return $this;
    }

    public function __toString():string {
        return $this->app->view->fetch( '@my/myprogress.twig', array( 'elements'   => array_values( $this->getSteps() ),
                                                                      'name'       => $this->name,
                                                                      'showdiv'    => $this->showdiv,
                                                                      'showtitle'  => $this->showTitle,
                                                                      'showheader' => $this->showHeader,
                                                                      'height'     => $this->height,
                                                                      'tags'       => array( array( $this->key ) + $this->keys, array( $this->keyhtml ) + $this->keyshtml ) ) );
    }
}