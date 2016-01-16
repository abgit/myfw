<?php

class myinfo{
    
    private $name;
    private $values = array();
    private $elements = array();
    private $counter  = 0;
    private $emptymsg = 'No information to display';
    private $profile = false;
    private $meta = array();

    public function __construct( $name ){
        $this->name = $name;
        $this->app = \Slim\Slim::getInstance();
    }

    public function & addH4( $key ){
        $this->elements[ $key ] = array( 'key' => $key, 'type' => 'h4' );
        return $this;
    }

    public function & addH5( $key ){
        $this->elements[ $key ] = array( 'key' => $key, 'type' => 'h5' );
        return $this;
    }

    public function & setEmptyMessage( $emptymsg ){
        $this->emptymsg = $emptymsg;
        return $this;
    }

    public function & addSeparatorLine(){
        $this->elements[] = array( 'type' => 'sl' );
        return $this;
    }

    public function & addSeparator(){
        $this->elements[] = array( 'type' => 'se' );
        return $this;
    }

    public function & addText( $key ){
        $this->elements[] = array( 'key' => $key, 'type' => 'text' );
        return $this;
    }

    public function & addTitle( $label, $icon = 'icon-stack' ){
        $this->elements[] = array( 'type' => 'ti', 'label' => $label, 'icon' => $icon );
        return $this;
    }

    public function & addCustom( $name, $obj, $title = '' ){
        $this->elements[ $name ] = array( 'obj' => $obj, 'title' => $title, 'type' => 'custom' );
        return $this;
    }

    public function & addTextImage( $key, $keytitle, $keyimage, $keyimagesec, $imagewidth = 200, $imageheight = 150 ){
        $this->elements[] = array( 'key' => $key, 'keyt' => $keytitle, 'keyi' => $this->app->ishttps() ? $keyimagesec : $keyimage, 'type' => 'textimage', 'imagewidth' => $imagewidth, 'imageheight' => $imageheight );
        return $this;
    }

    public function & setProfile( $size ){
        $this->meta = array( 'size' => $size );
        return $this;
    }

    public function & setProfileThumb( $key, $size = 150, $cdn = '', $onclick = '' ){
        $this->profile[ 'thumb' ] = array( 'key' => $key, 'size' => $size, 'cdn' => $cdn, 'onclick' => $onclick );
        return $this;
    }

    public function & setProfileDescription( $keytitle, $keysubtitle ){
        $this->profile[ 'desc' ] = array( 'keytitle' => $keytitle, 'keysubtitle' => $keysubtitle );
        return $this;
    }

    public function & setProfileDescriptionImage( $key, $cdn, $sufix, $width, $height ){
        $this->profile[ 'descimg' ] = array( 'key' => $key, 'cdn' => $cdn, 'sufix' => $sufix, 'width' => $width, 'height' => $height );
        return $this;
    }

    public function & addProfileIcon( $icon, $href, $key, $prefix, $sufix, $hrefkey = false, $depends = false ){
        $this->profile[ 'icons' ][] = array( 'icon' => $icon, 'href' => $href, 'key' => $key, 'prefix' => $prefix, 'sufix' => $sufix, 'hrefkey' => $hrefkey, 'depends' => $depends );
        return $this;
    }

    public function & addList( $key, $options ){
        $this->elements[] = array( 'key' => $key, 'options' => $options, 'type' => 'list' );
        return $this;
    }

    public function & setValues( $values ){
        $this->values = is_array( $values ) ? $values : json_decode( $values, true );

        foreach( $this->elements as $n => $el ){
            if( is_string( $n ) && isset( $this->values[ $n ] ) && isset( $el[ 'obj' ] ) && method_exists( $el[ 'obj' ], 'setvalues' ) )
                $el[ 'obj' ]->setValues( $this->values[ $n ] );
        }

        return $this;
    }

    public function __toString(){
        return $this->render();
    }

    private function render( $values = null ){
        return $this->app->render( '@my/myinfo', array( 'values'   => is_null( $values ) ? $this->values : $values,
                                                        'name'     => $this->name,
                                                        'elements' => $this->elements,
                                                        'values'   => $this->values,
                                                        'emptymsg' => $this->emptymsg,
                                                        'profile'  => $this->profile,
                                                        'meta'     => $this->meta
                                                         ), null, null, null, false, false );
    }
    
}