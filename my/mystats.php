<?php

class mystats{

    private $name;
    private $values = array();
    private $items;
    private $hidebutton = false;

    public function __construct( $name ){
        $this->name = $name;
        $this->app = \Slim\Slim::getInstance();
    }

    public function & setName( $name ){
        $this->name = $name;
        return $this;
    }

    public function & hideButton(){
        $this->hidebutton = true;
        return $this;
    }

    public function & addElement( $key, $label, $icon = false, $type = '', $onclick = '', $href = '', $percentage = 100, $class = '', $islabel = false, $typekey = false, $percentagetype = 1, $percentagekey = false, $keytype = 1 ){
        $this->elements[ $key ] = array( 'key' => $key, 'icon' => $icon, 'href' => $href, 'onclick' => $onclick, 'label' => $label, 'type' => $type, 'percentage' => $percentage, 'class' => $class, 'islabel' => $islabel, 'typekey' => $typekey, 'percentagetype' => $percentagetype, 'percentagekey' => $percentagekey, 'keytype' => $keytype );
        return $this;
    }

    public function & setReplace( $key, $replace, $default ){
        if( isset( $this->elements[ $key ] ) ){
            $this->elements[ $key ][ 'replace' ]        = $replace;
            $this->elements[ $key ][ 'replacedefault' ] = $default;
        }
        return $this;
    }

    public function & setClass( $key, $replace, $default, $customkey = false ){
        if( isset( $this->elements[ $key ] ) ){
            $this->elements[ $key ][ 'classreplace' ]        = $replace;
            $this->elements[ $key ][ 'classreplacedefault' ] = $default;
            $this->elements[ $key ][ 'classreplacekey' ]     = $customkey;
        }
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

    public function & addAddonLabel( $name, $value, $prefix = true, $key = false ){
        if( isset( $this->elements[ $name ] ) ){
            if( $prefix ){
                    $this->elements[ $name ][ 'addonlabelpre' ] = $value;
                    if( is_string( $key ) )
                        $this->elements[ $name ][ 'addonlabelprekey' ] = $key;
            }else{
                    $this->elements[ $name ][ 'addonlabelpos' ] = $value;
                    if( is_string( $key ) )
                        $this->elements[ $name ][ 'addonlabelposkey' ] = $key;
            }
        }
        return $this;
    }

    public function & setValues( $values ){
        $this->values = is_array( $values ) ? $values : json_decode( $values, true );
        return $this;
    }

    public function &updateAjaxValues( $values ){
        foreach( $this->elements as $name => $el ){
            if( isset( $values[ $el[ 'key' ] ] ) )
                $this->app->ajax()->text( '#st' . $el[ 'key' ], $values[ $el[ 'key' ] ] );
        }
        return $this;
    }

    public function & changeValues( $values, $mode ){
        $mode == 1 ? $this->setValues( $values ) : $this->updateAjaxValues( $values );
        return $this;
    }

    public function __toString(){
        return $this->render();
    }

    private function render( $values = null ){
        return $this->app->render( '@my/mystats', array( 'values'     => is_null( $values ) ? $this->values : $values,
                                                         'name'       => $this->name,
                                                         'elements'   => $this->elements,
                                                         'hidebutton' => $this->hidebutton
                                                         ), null, null, null, false, false );

    }
}