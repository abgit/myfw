<?php

class myrating{

    /** @var mycontainer*/
    private $app;

    private string $name;
    private array $elements = array();
    private ?float $value = 0;
    private string $key = '';
    private array $action = array();
    private array $action1 = array();
    private array $action2 = array();
    private array $action3 = array();
    private array $action4 = array();
    private array $action5 = array();
    private bool $showdiv = true;
    private array $labels = array();
    private array $keys = array();
    private array $keyshtml = array();
    private array $othervalues = array();

    public function __construct( $c ){
        $this->app = $c;
    }

    public function & setKey( string $key ): myrating{
        $this->key = $key;
        return $this;
    }

    public function & setName( string $name ): myrating{
        $this->name = $name;
        return $this;
    }

    public function & setValue( $value ): myrating{
        $this->value = $value >= 5 ? 5 : ( $value <= 0 ? 0 : $value );
        return $this;
    }

    public function & setAction( $urlobj ):myrating{
        $this->action = $urlobj;
        return $this;
    }

    public function & setAction1( $urlobj ):myrating{
        $this->action1 = $urlobj;
        return $this;
    }

    public function & setAction2( $urlobj ):myrating{
        $this->action2 = $urlobj;
        return $this;
    }

    public function & setAction3( $urlobj ):myrating{
        $this->action3 = $urlobj;
        return $this;
    }
    public function & setAction4( $urlobj ):myrating{
        $this->action4 = $urlobj;
        return $this;
    }
    public function & setAction5( $urlobj ):myrating{
        $this->action5 = $urlobj;
        return $this;
    }

    public function & setValues( $values ): myrating{
        if( is_string( $values ) ){
            $values = json_decode($values, true, 512, JSON_THROW_ON_ERROR);
        }

        if( isset( $values[ $this->key ] ) && is_numeric( $values[ $this->key ] ) ){
            $this->value = (float) $values[ $this->key ];
        }

        return $this;
    }

    public function & setOtherValues( $values ): myrating{
        $this->othervalues = $values;
        return $this;
    }

    public function & setLabel( int $index, string $label, bool $alwaysShow = false ): myrating{
        $this->labels[ $index ] = array( 'label' => $label, 'alwaysshow' => $alwaysShow );
        return $this;
    }

    public function & addKey( string $key, string $keyhtml ): myrating{
        $this->keys[]     = $key;
        $this->keyshtml[] = $keyhtml;
        return $this;
    }

    public function & ajaxRefresh( $values ): myrating{

        $this->setValues( $values );

        $this->showdiv = false;

        $this->app->ajax->html( '#' . $this->name . 'pagid', $this->__toString() );

        return $this;
    }


    public function __toString():string {
        return $this->app->view->fetch( '@my/myrating.twig', array( 'elements'   => $this->elements,
                                                                    'value'      => $this->value,
                                                                    'othervalues'=> $this->othervalues,
                                                                    'name'       => $this->name,
                                                                    'action'     => $this->action,
                                                                    'action1'    => $this->action1,
                                                                    'action2'    => $this->action2,
                                                                    'action3'    => $this->action3,
                                                                    'action4'    => $this->action4,
                                                                    'action5'    => $this->action5,
                                                                    'labels'     => $this->labels,
                                                                    'showdiv'    => $this->showdiv,
                                                                    'tags'       => array( $this->keys, $this->keyshtml ) ) );
    }
}