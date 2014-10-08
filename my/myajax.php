<?php

class myajax{
    
    private $out = array();
    
    public function & msg( $msg, $header = null, $args = array() ){

        if( !is_null( $header ) ){
            $args[ 'header' ] = $header;
        }

        if( !isset( $args[ 'life' ] ) ){
            $args[ 'life' ] = 6000;
        }

        $this->out[ 'mg' ][] = array( 'd' => is_array( $msg ) ? ( count( $msg ) > 1 ? ( '<ul><li>' . implode( '</li><li>', $msg ) . '</li></ul>' ) : implode( '', $msg ) ) : $msg, 'a' => $args );
        return $this;
    }

    public function & msgInfo( $msg, $header = 'Information', $args = array() ){
        return $this->msg( $msg, $header, array( 'theme' => 'growl-info' ) + $args );
    }

    public function & msgOk( $msg, $header = 'Success', $args = array() ){
        return $this->msg( $msg, $header, array( 'theme' => 'growl-success' ) + $args );
    }

    public function & msgWarning( $msg, $header = null, $args = array() ){
        return $this->msg( $msg, is_null( $header ) ? ( ( is_array( $msg ) && count( $msg ) > 1 ) ? 'Warnings' : 'Warning' ) : $header, array( 'theme' => 'growl-warning' ) + $args );
    }

    public function & msgError( $msg, $header = null, $args = array() ){
        return $this->msg( $msg, is_null( $header ) ? ( ( is_array( $msg ) && count( $msg ) > 1 ) ? 'Errors found' : 'Error found' ) : $header, array( 'theme' => 'growl-error' ) + $args );
    }

    public function & addFormCsrf( $element, $csrf ){
        $this->out[ 'cs' ][] = array( 'e' => $element, 'v' => $csrf );
        return $this;
    }

    public function & setFormReset( $name ){
        $this->out[ 'fr' ] = array( 'f' => $name );
        return $this;
    }
    
    public function & showForm( $formname, $html, $modal, $transloadit = 0 ){
        $this->out[ 'fs' ] = array( 'f' => $formname, 'h' => $html, 's' => $modal, 't' => $transloadit );
        return $this;
    }
    
    public function & modalHide( $id ){
        $this->out[ 'mh' ] = array( 'i' => $id );
        return $this;
    }
    
    public function & callAction( $action ){
        $this->out[ 'ca' ][] = array( 'a' => $action );
        return $this;
    }

    public function & focus( $element ){
        $this->out[ 'fu' ] = array( 'e' => $element );
        return $this;
    }

    public function & confirm( $url, $msg, $title, $help, $mode ){
        $this->out[ 'co' ] = array( 'u' => $url, 'm' => $msg, 't' => $title, 'h' => $help, 'o' => $mode );
        return $this;
    }

    public function & redirect( $url, $ms = 1000 ){
        $this->out[ 'rd' ] =  array( 'u' => $url, 'm' => $ms );
        return $this;
    }

    public function & append( $el, $html ){
        $this->out[ 'ap' ][] = array( 'e' => $el, 'h' => $this->filter( $html ) );
        return $this;
    }

    public function & prepend( $el, $html ){
        $this->out[ 'pp' ][] = array( 'e' => $el, 'h' => $this->filter( $html ) );
        return $this;
    }

    public function & replacewith( $el, $html ){
        $this->out[ 'rp' ][] = array( 'e' => $el, 'h' => $this->filter( $html ) );
        return $this;
    }

    public function & html( $el, $html ){
        $this->out[ 'ht' ][] = array( 'e' => $el, 'h' => $this->filter( $html ) );
        return $this;
    }

    public function & attr( $el, $property, $value ){
        $this->out[ 'at' ][] = array( 'e' => $el, 'p' => $property, 'v' => $value );
        return $this;
    }

    public function & visibility( $el, $mode ){
        $this->out[ 'vi' ][] = array( 'e' => $el, 'm' => $mode ? 'visible' : 'hidden' );
        return $this;
    }

    public function & text( $el, $html ){
        $this->out[ 'tx' ][] = array( 'e' => $el, 'h' => $this->filter( $html ) );
        return $this;
    }

    public function & val( $el, $html ){
        $this->out[ 'va' ][] = array( 'e' => $el, 'h' => $this->filter( $html ) );
        return $this;
    }

    public function & css( $el, $property, $value ){
        $this->out[ 'cc' ][] = array( 'e' => $el, 'p' => $property, 'v' => $value );
        return $this;
    }

    public function & remove( $el ){
        $this->out[ 'rm' ][] = array( 'e' => $el );
        return $this;
    }

    public function & hide( $el ){
        $this->out[ 'hi' ][] = array( 'e' => $el );
        return $this;
    }

    public function & hideTableRow( $el, $table, $emptymsg ){
        $this->out[ 'hr' ][] = array( 'e' => $el, 't' => $table, 'm' => $emptymsg );
        return $this;
    }

    public function & show( $el ){
        $this->out[ 'sh' ][] = array( 'e' => $el );
        return $this;
    }

    public function & fadeOut( $el ){
        $this->out[ 'fo' ][] = array( 'e' => $el );
        return $this;
    }

    public function & fadeIn( $el ){
        $this->out[ 'fi' ][] = array( 'e' => $el );
        return $this;
    }

    public function filter( $s ){
        return trim( preg_replace( "@( )*[\\r|\\n|\\t]+( )*@", "", $s ) );
    }

    public function render(){
        print json_encode( $this->out );
    }

}