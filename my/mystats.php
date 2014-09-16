<?php

class mystats{

    private $name;
    private $values = array();
    private $items;

    public function __construct( $name ){
        $this->name = $name;
        $this->app = \Slim\Slim::getInstance();
    }

    public function & setName( $name ){
        $this->name = $name;
        return $this;
    }

    public function & setValues( $values ){
        if( is_array( $values ) )
            $this->values = $values;

        return $this;
    }

    public function & addElement( $key, $label, $icon = 'icon-cog4', $type = 'info', $onclick = '', $href = '', $percentage = 100 ){
        $this->elements[ $key ] = array( 'key' => $key, 'icon' => $icon, 'href' => $href, 'onclick' => $onclick, 'label' => $label, 'type' => $type, 'percentage' => $percentage );
        return $this;
    }

    public function & addAddon( $name, $value, $prefix = true ){
        if( isset( $this->elements[ $name ] ) ){
            if( $prefix ){
                $this->elements[ $name ][ 'addonpre' ] = $value;
            }else{
                $this->elements[ $name ][ 'addonpos' ] = $value;            
            }
        }
        return $this;
    }

    public function &updateAjaxValues( $values ){
        foreach( $this->elements as $name => $el ){
            if( isset( $values[ $el[ 'key' ] ] ) )
                $this->app->ajax()->text( '#st' . $el[ 'key' ], $values[ $el[ 'key' ] ] );
        }
        return $this;
    }

    public function __toString(){
        return $this->render();
    }

    private function render( $values = null ){
        return $this->app->render( '@my/mystats', array( 'values'   => is_null( $values ) ? $this->values : $values,
                                                         'name'     => $this->name,
                                                         'elements' => $this->elements
                                                         ), null, null, null, false, false );

    }
}