<?php

class myajax{
    
    private $out = array();
    
    public function & addComand( $name, $obj ){
        $this->out[ $name ][] = $obj;
        return $this;
    }
    
    public function & setCommand( $name, $obj ){
        $this->out[ $name ] = $obj;    
        return $this;
    }
    
    public function delCommand( $name ){
        if( isset( $this->out[ $name ] ) ){
            unset( $this->out[ $name ] );
            return true;
        }
        return false;
    }

    public function setMessageInfo( $msg, $header = 'Information', $life = 6000 ){

        if( is_array( $msg ) )
            $msg = '<ul><li>' . implode( '</li><li>', $msg ) . '</li></ul>';

        $this->out[ 'msg' ] = array( 'h' => $header, 'd' => $msg, 'l' => $life, 't' => 'growl-info' );
    }

    public function & setMessageOk( $msg, $header = null, $life = 6000 ){

        if( is_null( $header ) )
            $header = 'Success';

        if( is_array( $msg ) )
            $msg = '<ul><li>' . implode( '</li><li>', $msg ) . '</li></ul>';

        $this->out[ 'msg' ] = array( 'h' => $header, 'd' => $msg, 'l' => $life, 't' => 'growl-success' );
        return $this;
    }

    public function & setMessageWarning( $msg, $header = null, $life = 6000 ){

        if( is_null( $header ) )
            $header = ( is_array( $msg ) && count( $msg ) > 1 ) ? 'Warnings' : 'Warning';

        if( is_array( $msg ) )
            $msg = '<ul><li>' . implode( '</li><li>', $msg ) . '</li></ul>';

        $this->out[ 'msg' ] = array( 'h' => $header, 'd' => $msg, 'l' => $life, 't' => 'growl-warning' );
        return $this;
    }

    public function & setMessageError( $msg, $header = null, $life = 6000 ){

        if( is_null( $header ) )
            $header = ( is_array( $msg ) && count( $msg ) > 1 ) ? 'Errors found' : 'Error found';

        $msg = is_array( $msg ) ? ( count( $msg ) > 1 ?  ( '<ul><li>' . implode( '</li><li>', $msg ) . '</li></ul>' ) : implode( '', $msg ) ) : $msg;

        $this->out[ 'msg' ] = array( 'h' => $header, 'd' => $msg, 'l' => $life, 't' => 'growl-error' );
        return $this;
    }

    public function addFormCsrf( $element, $csrf ){
        $this->out[ 'cs' ][] = array( 'e' => $element, 'v' => $csrf );
    }

    public function setFormReset( $name ){
        $this->out[ 'fr' ] = array( 'f' => $name );
    }
    
    public function modalHide( $id ){
        $this->out[ 'mh' ] = array( 'i' => $id );
    }
    
    public function callAction( $action ){
        $this->out[ 'ca' ] = array( 'a' => $action );
    }

    public function focus( $element ){
        $this->out[ 'fu' ] = array( 'e' => $element );
    }

    public function append( $el, $html ){
        $this->out[ 'ap' ] = array( 'e' => $el, 'h' => $this->filter( $html ) );
    }

    public function prepend( $el, $html ){
        $this->out[ 'pp' ] = array( 'e' => $el, 'h' => $this->filter( $html ) );
    }

    public function & replacewith( $el, $html ){
        $this->out[ 'repw' ][] = array( 'e' => $el, 'h' => $this->filter( $html ) );
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

    public function & text( $el, $html ){
        $this->out[ 'tx' ][] = array( 'e' => $el, 'h' => $this->filter( $html ) );
        return $this;
    }

    public function & remove( $el ){
        $this->out[ 'rem' ][] = array( 'e' => $el );
        return $this;
    }

    public function & fadeOut( $el ){
        $this->out[ 'fo' ] = array( 'e' => $el );
        return $this;
    }

    public function & fadeIn( $el ){
        $this->out[ 'fi' ] = array( 'e' => $el );
        return $this;
    }

    public function filter( $s ){
        return trim( preg_replace( "@( )*[\\r|\\n|\\t]+( )*@", "", $s ) );
    }

    public function render(){
        print json_encode( $this->out );
    }

}