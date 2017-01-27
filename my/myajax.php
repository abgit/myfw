<?php

class myajax{
    
    protected $out = array();
    
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

    public function & msgInfo( $msg, $header, $args = array() ){
        return $this->msg( $msg, empty( $header ) ? 'Information' : $header, array( 'theme' => 'growl-info' ) + $args );
    }

    public function & msgOk( $msg, $header = null, $args = array() ){
        return $this->msg( $msg, empty( $header ) ? 'Success' : $header, array( 'theme' => 'growl-success' ) + $args );
    }

    public function & msgWarning( $msg, $header = null, $args = array() ){
        return $this->msg( $msg, empty( $header ) ? ( ( is_array( $msg ) && count( $msg ) > 1 ) ? 'Warnings' : 'Warning' ) : $header, array( 'theme' => 'growl-warning' ) + $args );
    }

    public function & msgError( $msg, $header = null, $args = array() ){
        return $this->msg( $msg, empty( $header ) ? ( ( is_array( $msg ) && count( $msg ) > 1 ) ? 'Errors found' : 'Error found' ) : $header, array( 'theme' => 'growl-error' ) + $args );
    }

    public function & addFormCsrf( $element, $csrf ){
        $this->out[ 'cs' ][] = array( 'e' => $element, 'v' => $csrf );
        return $this;
    }

    public function & setFormReset( $name ){
        $this->out[ 'fr' ] = array( 'f' => $name );
        return $this;
    }
    
    public function & showForm( $formname, $html, $modal, $transloadit = 0, $chatscroll = 0, $pusher = 0, $webcameratag = array() ){
        $this->out[ 'fs' ] = array( 'f' => $formname, 'h' => $html, 's' => $modal, 't' => $transloadit, 'c' => $chatscroll, 'p' => $pusher, 'w' => empty( $webcameratag ) ? false : $webcameratag );
        return $this;
    }
    
    public function & modalHide( $id ){
        $this->out[ 'mh' ][] = array( 'i' => $id );
        return $this;
    }
    
    public function & callAction( $action ){
        $this->out[ 'ca' ][] = array( 'a' => $action );
        return $this;
    }

    public function & pusherSubscribe( $key, $channel, $event, $encryption, $region, $replace, $replaceWith ){
        $this->out[ 'pu' ] = array( 'k' => $key, 'c' => $channel, 'v' => $event, 'y' => $encryption, 'r' => $region, 's' => $replace, 'w' => $replaceWith );
        return $this;
    }

    public function & chatAdd( $element, $message, $boxScroll ){
        $this->out[ 'ch' ][] = array( 'e' => $element, 'm' => $this->filter( $message ), 'h' => $boxScroll );
        return $this;
    }

    public function & cameraTag(){
        $this->out[ 'ct' ] = 1;
        return $this;
    }

    public function & Ziggeo( $hidden, $div ){
        $this->out[ 'zi' ][] = array( 'h' => $hidden, 'd' => $div );
        return $this;
    }

    public function & focus( $element ){
        $this->out[ 'fu' ] = array( 'e' => $element );
        return $this;
    }

    public function & confirm( $url, $msg, $title, $description, $help, $mode, $requirepin, $pinLabel, $pinHelp ){
        $this->out[ 'co' ] = array( 'u' => $url, 'm' => $msg, 'd' => $description, 't' => $title, 'h' => $help, 'o' => $mode, 'f' => intval( $requirepin ), 'l' => $pinLabel, 'p' => $pinHelp );
        return $this;
    }

    public function & confirmDialogClose(){
        $this->out[ 'cd' ] = array();
        return $this;
    }

    public function & redirect( $url, $ms = 1000 ){
        $this->out[ 'rd' ] =  array( 'u' => $url, 'm' => $ms );
        return $this;
    }

    public function & preloadImages( $images ){
        $this->out[ 'pl' ] =  array( 'i' => $images );
        return $this;
    }

    public function & append( $el, $html, $removefirst = false ){
        $this->out[ 'ap' ][] = array( 'e' => $el, 'h' => $this->filter( $html ), 'r' => $removefirst );
        return $this;
    }

    public function & appendTextArea( $el, $html ){
        $this->out[ 'ta' ][] = array( 'e' => $el, 'h' => $html );
        return $this;
    }

    public function & calendar( $el ){
        $this->out[ 'cr' ][] = array( 'e' => $el );
        return $this;
    }

    public function & calendarEventAdd( $el, $event_title, $event_start, $event_end, $event_id, $event_color ){
        $this->out[ 'ci' ][] = array( 'e' => $el, 't' => $event_title, 's' => $event_start, 'f' => $event_end, 'i' => $event_id, 'c' => $event_color );
        return $this;
    }

    public function & calendarEventRemove( $el, $event_id ){
        $this->out[ 'cx' ][] = array( 'e' => $el, 'i' => $event_id );
        return $this;
    }

    public function & calendarRefresh( $el ){
        $this->out[ 'cf' ][] = array( 'e' => $el );
        return $this;
    }

    public function & notifyUpdate( $el, $html, $counter ){
        $this->out[ 'nu' ][] = array( 'e' => $el, 'h' => $this->filter( $html ), 'c' => intval( $counter ) );
        return $this;
    }

    public function & prependTextArea( $el, $html ){
        $this->out[ 'tp' ][] = array( 'e' => $el, 'h' => $html );
        return $this;
    }

    public function & prepend( $el, $html, $removelast = false ){
        $this->out[ 'pp' ][] = array( 'e' => $el, 'h' => $this->filter( $html ), 'r' => $removelast );
        return $this;
    }

    public function & replacewith( $el, $html ){
        $this->out[ 'rp' ][] = array( 'e' => $el, 'h' => $this->filter( $html ) );
        return $this;
    }

    public function & addreplacewith( $el, $html, $grid, $prepend = true, $removelast = true ){
        $this->out[ 'ar' ][] = array( 'e' => $el, 'h' => $this->filter( $html ), 'g' => $grid, 'p' => $prepend, 'r' => $removelast );
        return $this;
    }

    public function & html( $el, $html, $escape = false ){
        $this->out[ 'ht' ][] = array( 'e' => $el, 'h' => $this->filter( $html, $escape ) );
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

    public function & switchClass( $el, $classdel, $classadd ){
        $this->out[ 'sc' ][] = array( 'e' => $el, 'd' => $classdel, 'a' => $classadd );
        return $this;    
    }

    public function & removeClass( $el, $class ){
        $this->out[ 'rc' ][] = array( 'e' => $el, 'c' => $class );
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

    public function & hideTableRow( $el, $table ){
        $this->out[ 'hr' ][] = array( 'e' => $el, 't' => $table );
        return $this;
    }

    public function & show( $el, $duration = 400 ){
        $this->out[ 'sh' ][] = array( 'e' => $el, 'd' => $duration );
        return $this;
    }

    public function & load( $el ){
        $this->out[ 'ld' ][] = array( 'e' => $el );
        return $this;
    }

    public function & display( $el, $bool = true ){
        $this->out[ 'di' ][] = array( 'e' => $el, 'b' => $bool );
        return $this;
    }

    public function & fadeOut( $el, $duration = 400 ){
        $this->out[ 'fo' ][] = array( 'e' => $el, 'd' => $duration );
        return $this;
    }

    public function & fadeIn( $el, $duration = 400 ){
        $this->out[ 'fi' ][] = array( 'e' => $el, 'd' => $duration );
        return $this;
    }

    public function & scrollBottom( $el ){
        $this->out[ 'sb' ][] = array( 'e' => $el );
        return $this;
    }

    public function filter( $s, $escape = false ){
        return trim( preg_replace( "@( )*[\r\n\t]+( )*@", " ", $escape === false ? $s : htmlspecialchars( $s ) ) );
    }

    public function & timeout( $action, $ms, $mode = 0 ){
        $this->out[ 'ti' ] = array( 'a' => $action, 't' => $ms, 'm' => $mode ? 1 : 0 );
        return $this;
    }

    public function & canceltimeout( $mode = 0 ){
        $this->out[ 'tr' ] = array( 'm' => $mode ? 1 : 0 );
        return $this;
    }

    public function & interval( $action, $ms, $mode = 0 ){
        $this->out[ 'ii' ] = array( 'a' => $action, 't' => $ms, 'm' => $mode ? 1 : 0 );
        return $this;
    }

    public function & cancelinterval( $mode = 0 ){
        $this->out[ 'ir' ] = array( 'm' => $mode ? 1 : 0 );
        return $this;
    }

    public function & login(){
        $this->out[ 'lo' ] = array();
        return $this;
    }

    public function & drift( $id, $name, $avatar, $custom = array() ){
        $this->out[ 'df' ] = array( 'i' => $id, 'a' => array( 'name' => $this->filter( $name, true ), 'avatarUrl' => $avatar ) + $custom );
        return $this;    
    }

    public function & markdown( $url ){
        $this->out[ 'md' ] = array( 'u' => $url );
        return $this;
    }

    public function render(){
        print json_encode( $this->out );
    }

}