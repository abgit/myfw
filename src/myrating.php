<?php

class myrating{

    /** @var mycontainer*/
    private $app;

    private string $name;
    private array $elements = array();
    private ?float $value;
    private string $key;
    private array $action = array();
    private bool $showdiv = true;
    private array $labels = array();

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
        $this->value = $value;
        return $this;
    }

    public function & setAction( $urlobj ):myrating{
        $this->action = $urlobj;
        return $this;
    }

    public function & setValues( $values ): myrating{
        $values = is_string( $values ) ? json_decode($values, true, 512, JSON_THROW_ON_ERROR) : $values;
        $this->value = ( isset( $values[ $this->key ] ) && is_numeric( $values[ $this->key ] ) ) ? (float) $values[ $this->key ] : $this->value;
        return $this;
    }

    public function & setLabel( int $index, string $label, bool $alwaysShow = false ): myrating{
        $this->labels[ $index ] = array( 'label' => $label, 'alwaysshow' => $alwaysShow );
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
                                                                    'name'       => $this->name,
                                                                    'action'     => $this->action,
                                                                    'labels'     => $this->labels,
                                                                    'showdiv'    => $this->showdiv ) );
    }
}