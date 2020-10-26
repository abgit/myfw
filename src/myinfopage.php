<?php

class myinfopage{

    /** @var mycontainer*/
    private $app;

    private $name;
    private $values   = array();
    private $elements = array();
    private $emptymsg = 'No information to display';
    private $profile  = false;
    private $meta     = array();
    private $keys     = array();
    private $keyshtml = array();

    public function __construct( $c ){
        $this->app = $c;
    }

    public function & setName( $name ){
        $this->name = $name;
        return $this;
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

    public function & addTitle( $key, $label = '', $icon = '' ){
        $this->elements[] = array( 'type' => 'ti', 'label' => $label, 'key' => $key, 'icon' => $icon );
        return $this;
    }

    public function & addCustom( $name, $obj, $title = '', $description = '' ){
        $this->elements[ $name ] = array( 'obj' => $obj, 'title' => $title, 'type' => 'custom', 'description' => $description );
        return $this;
    }

    public function & addTextImage( $key, $keytitle, $keyimage = '', $imagewidth = 200, $imageheight = 150, $defaulttext = '' ){
        $this->elements[] = array( 'key' => $key, 'keyt' => $keytitle, 'keyi' => $keyimage, 'type' => 'textimage', 'imagewidth' => $imagewidth, 'imageheight' => $imageheight, 'defaulttext' => $defaulttext );
        return $this;
    }

    public function & setProfile( $size ){
        $this->meta = array( 'size' => $size );
        return $this;
    }

    public function & setProfileThumb( $key, $size = 150, $default = '', $cdn = '', $href = '', $onclick = '' ){
        $this->profile[ 'thumb' ] = array( 'key' => $key, 'size' => $size, 'default' => $default, 'cdn' => $cdn, 'href' => $href, 'onclick' => $onclick );
        return $this;
    }

    public function & setProfileDescription( $keytitle, $keysubtitle ){
        $this->profile[ 'desc' ] = array( 'keytitle' => $keytitle, 'keysubtitle' => $keysubtitle );
        return $this;
    }

    public function & setProfileHead( $key ){
        $this->profile[ 'head' ] = array( 'key' => $key );
        return $this;
    }

    public function & setProfileText( $key, $prefix = '', $sufix = '', $default = '', $sufixsingular = '' ){
        $this->profile[ 'text' ] = array( 'key' => $key, 'prefix' => $prefix, 'sufix' => $sufix, 'default' => $default, 'sufixsingular' => $sufixsingular );
        return $this;
    }

    public function & setProfileString( $key ){
        $this->profile[ 'string' ] = array( 'key' => $key );
        return $this;
    }

    public function & setProfileMenu( $obj ){
        $this->profile[ 'menu' ] = array( 'obj' => $obj );
        return $this;
    }
    
    public function & setProfileDescriptionImage( $key, $cdn, $sufix, $width, $height ){
        $this->profile[ 'descimg' ] = array( 'key' => $key, 'cdn' => $cdn, 'sufix' => $sufix, 'width' => $width, 'height' => $height );
        return $this;
    }

    public function & addProfileIcon( $icon, $href, $hrefkey = false, $hrefsufix = false ){
        $this->profile[ 'icons' ][] = array( 'icon' => $icon, 'href' => $href, 'hrefkey' => $hrefkey, 'hrefsufix' => $hrefsufix );
        return $this;
    }
    public function & addProfileCustom( $name, $obj ){
        $this->profile[ 'custom' ][] = array( 'name' => $name, 'obj' => $obj );
        return $this;
    }

    public function & setKey( $key, $keyhtml ){
        $this->keys[]     = $key;
        $this->keyshtml[] = $keyhtml;
        return $this;
    }

    public function & addProfileCameraTag( $key ){

        $expiration = time() + 1800;
        $signature  = $this->app->config[ 'cameratag.key' ] ? hash_hmac( 'sha1', $expiration, $this->app->config[ 'cameratag.key' ] ) : '';

        $this->elements[] = array( 'type' => 'cameratag', 'key' => $key, 'appcdn' => $this->app->config[ 'cameratag.appcdn' ], 'signature' => $signature, 'expiration' => $expiration );
        return $this;
    }

    public function & addProfileFileStackMovie( $key ){
        $this->elements[] = array( 'type' => 'filestackmovie', 'key' => $key );
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
        return $this->app->view->fetch( '@my/myinfopage.twig', array( 'values'   => is_null( $values ) ? $this->values : $values,
                                                                               'name'     => $this->name,
                                                                               'elements' => $this->elements,
                                                                               'emptymsg' => $this->emptymsg,
                                                                               'profile'  => $this->profile,
                                                                               'meta'     => $this->meta,
                                                                               'tags'     => array( $this->keys, $this->keyshtml ) ) );
    }
    
}