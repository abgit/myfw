<?php

class mystats{

    /** @var mycontainer*/
    private $app;

    private $name;
    private $align;
    private $elements = array();
    private $values = array();
    private $hidebutton = false;

    public  $onInit;
    public  $onInitValues;
    public  $onRefresh;
    public  $onRefreshValues;

    public function __construct( $c ){
        $this->app = $c;
    }

    public function & setName( $name ): mystats{
        $this->name = $name;
        return $this;
    }

    public function & hideButton(): mystats{
        $this->hidebutton = true;
        return $this;
    }

    public function & addElement( $key, $label, $icon = false, $type = '', $onclick = '', $href = '', $percentage = '', $class = '', $islabel = false, $typekey = false, $percentagetype = 1, $percentagekey = false, $keytype = 1 ): mystats{
        $this->elements[ $key ] = array( 'element' => 'standard', 'key' => $key, 'icon' => $icon, 'href' => $href, 'onclick' => $onclick, 'label' => $label, 'type' => $type, 'percentage' => $percentage, 'class' => $class, 'islabel' => $islabel, 'typekey' => $typekey, 'percentagetype' => $percentagetype, 'percentagekey' => $percentagekey, 'keytype' => $keytype );
        return $this;
    }

    public function & addElementLabel( $key, $label, $options, $defaultlabel = '-', $defaultclass = 'label-default' ): mystats{
        $this->elements[ $key ] = array( 'element' => 'label', 'key' => $key, 'label' => $label, 'options' => $options, 'defaultlabel' => $defaultlabel, 'defaultclass' => $defaultclass  );
        return $this;
    }

    public function & setReplace( $key, $replace, $default ): mystats{
        if( isset( $this->elements[ $key ] ) ){
            $this->elements[ $key ][ 'replace' ]        = $replace;
            $this->elements[ $key ][ 'replacedefault' ] = $default;
        }
        return $this;
    }

    public function & setClass( $key, $replace, $default, $customkey = false ): mystats{
        if( isset( $this->elements[ $key ] ) ){
            $this->elements[ $key ][ 'classreplace' ]        = $replace;
            $this->elements[ $key ][ 'classreplacedefault' ] = $default;
            $this->elements[ $key ][ 'classreplacekey' ]     = $customkey;
        }
        return $this;
    }

    public function & addAddon( $name, $value, $prefix = true ): mystats{
        if( isset( $this->elements[ $name ] ) ){
            if( $prefix ){
                $this->elements[ $name ][ 'addonpre' ] = $value;
            }else{
                $this->elements[ $name ][ 'addonpos' ] = $value;            
            }
        }
        return $this;
    }

    public function & addAddonLabel( $name, $value, $prefix = true, $key = false, $keydefault = '' ): mystats{
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


    public function & init(): mystats
    {
        if( is_callable( $this->onInit ) ) {
            call_user_func($this->onInit);
        }
        if( is_callable( $this->onInitValues ) ) {
            $this->setValues( call_user_func( $this->onInitValues ) );
        }
        return $this;
    }

    public function & setOnInit( $fn ):mystats{
        $this->onInit = $fn;
        return $this;
    }

    public function & setOnInitValues( $fn ):mystats{
        $this->onInitValues = $fn;
        return $this;
    }

    public function & refresh(): mystats
    {
        if( is_callable( $this->onRefresh ) ) {
            call_user_func($this->onRefresh);
        }
        if( is_callable( $this->onRefreshValues ) ) {
            $this->updateAjaxValues( call_user_func($this->onRefreshValues) );
        }
        return $this;
    }

    public function & setOnRefresh( $fn ):mystats{
        $this->onRefresh = $fn;
        return $this;
    }

    public function & setOnRefreshValues( $fn ):mystats{
        $this->onRefreshValues = $fn;
        return $this;
    }

    public function & setValues( $values ): mystats{
        $this->values = is_array( $values ) ? $values : json_decode( $values, true );

        return $this;
    }

    public function & setAlign( $align ): mystats{
        $this->align = $align;
        return $this;
    }

    public function &updateAjaxValues( $values, $pusherChannel = false ): mystats{

        if( !is_array( $values ) )
            $values = json_decode( $values, true );

        $obj = $pusherChannel ? $this->app->pusher : $this->app->ajax;

        foreach( $this->elements as $name => $el ){
            if( isset( $values[ $el[ 'key' ] ] ) )
                $obj->text( '#st' . $el[ 'key' ], $values[ $el[ 'key' ] ] );

            if( isset( $el[ 'percentagekey' ] ) && !empty( $el[ 'percentagekey' ] ) )
                $obj->css( '#stp' . $el[ 'key' ], 'width', $values[ $el[ 'key' ] ] . '%' );

            if( isset( $values[ $el[ 'key' ] ] ) && isset( $el[ 'addonlabelprekey' ] ) )
                $obj->text( '#st' . $el[ 'key' ] . 'lpre', $values[ $el[ 'addonlabelprekey' ] ] );

            if( isset( $values[ $el[ 'key' ] ] ) && isset( $el[ 'addonlabelposkey' ] ) )
                $obj->text( '#st' . $el[ 'key' ] . 'lpos', $values[ $el[ 'addonlabelposkey' ] ] );
        }

        if( $pusherChannel )
            $obj->send( $pusherChannel, null );

        return $this;
    }

    public function &updateAjaxValue( $key, $value, $valuepre = null, $valuepos = null ): mystats{

        $this->app->ajax->text( '#st' . $key, $value );

        if( !is_null( $valuepre ) )
            $this->app->ajax->text( '#st' . $key . 'lpre', $valuepre );

        if( !is_null( $valuepos ) )
            $this->app->ajax->text( '#st' . $key . 'lpos', $valuepos );

        return $this;
    }

    public function __toString():string{
        return $this->render();
    }

    private function render( $values = null ):string{
        return $this->app->view->fetch( '@my/mystats.twig', array( 'values'     => is_null( $values ) ? $this->values : $values,
                                                                            'name'       => $this->name,
                                                                            'elements'   => $this->elements,
                                                                            'hidebutton' => $this->hidebutton,
                                                                            'align'      => $this->align ) );
    }
}