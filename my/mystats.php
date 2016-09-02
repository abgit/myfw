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

    public function & setID( $id ){
        $this->name = $id;
        return $this;
    }

    public function & setName( $name ){
        $this->name = $name;
        return $this;
    }

    public function & hideButton(){
        $this->hidebutton = true;
        return $this;
    }

    public function & addElement( $key, $label, $icon = false, $type = '', $onclick = '', $href = '', $percentage = '', $class = '', $islabel = false, $typekey = false, $percentagetype = 1, $percentagekey = false, $keytype = 1 ){
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

    public function & addAddonLabel( $name, $value, $prefix = true, $key = false, $keydefault = '' ){
        if( isset( $this->elements[ $name ] ) ){
            if( $prefix ){
                    $this->elements[ $name ][ 'addonlabelpre' ] = $value;
                    if( is_string( $key ) ){
                        $this->elements[ $name ][ 'addonlabelprekey' ] = $key;
                        $this->elements[ $name ][ 'addonlabelprekeydefault' ] = $keydefault;
                    }
            }else{
                    $this->elements[ $name ][ 'addonlabelpos' ] = $value;
                    if( is_string( $key ) ){
                        $this->elements[ $name ][ 'addonlabelposkey' ] = $key;
                        $this->elements[ $name ][ 'addonlabelposkeydefault' ] = $keydefault;
                    }
            }
        }
        return $this;
    }

    public function & setValues( $values ){
        $this->values = is_array( $values ) ? $values : json_decode( $values, true );

        return $this;
    }

    public function &updateAjaxValues( $values ){

        if( !is_array( $values ) )
            $values = json_decode( $values, true );

        foreach( $this->elements as $name => $el ){
            if( isset( $values[ $el[ 'key' ] ] ) )
                $this->app->ajax()->text( '#st' . $el[ 'key' ], $values[ $el[ 'key' ] ] );

            if( isset( $el[ 'percentagekey' ] ) && !empty( $el[ 'percentagekey' ] ) )
                $this->app->ajax()->css( '#stp' . $el[ 'key' ], 'width', $values[ $el[ 'key' ] ] . '%' );


            if( isset( $values[ $el[ 'key' ] ] ) && isset( $el[ 'addonlabelprekey' ] ) )
                $this->app->ajax()->text( '#st' . $el[ 'key' ] . 'lpre', $values[ $el[ 'addonlabelprekey' ] ] );

            if( isset( $values[ $el[ 'key' ] ] ) && isset( $el[ 'addonlabelposkey' ] ) )
                $this->app->ajax()->text( '#st' . $el[ 'key' ] . 'lpos', $values[ $el[ 'addonlabelposkey' ] ] );

        }
        return $this;
    }

    public function &updateAjaxValue( $key, $value, $valuepre = null, $valuepos = null ){

        $this->app->ajax()->text( '#st' . $key, $value );

        if( !is_null( $valuepre ) )
            $this->app->ajax()->text( '#st' . $key . 'lpre', $valuepre );

        if( !is_null( $valuepos ) )
            $this->app->ajax()->text( '#st' . $key . 'lpos', $valuepos );

        return $this;
    }

/*
    public function & changeValues( $values, $mode ){
        $mode == 1 ? $this->setValues( $values ) : $this->updateAjaxValues( $values );
        return $this;
    }
*/

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