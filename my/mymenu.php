<?php

class mymenu{
    
    private $elements = array();
    private $idmenusel  = 0;
    private $name;

    public function __construct( $name ){
        $this->name = $name;
        $this->app = \Slim\Slim::getInstance();
    }

    public function & addButton( $label, $onclick = '', $href = '', $icon = 'icon-cog4', $color = '', $class = '' ){
        $this->elements[] = array( 'type' => 0, 'icon' => $icon, 'href' => $href, 'onclick' => $onclick, 'label' => $label, 'color' => $color, 'class' => $class );
        return $this;
    }

    public function & addMenu( $options, $icon = 'icon-cog4', $label = '' ){
        $this->elements[] = array( 'type' => 1, 'icon' => $icon, 'options' => $options, 'label' => $label );
        return $this;
    }

    public function & addMenuSelect( $options, $icon = 'icon-cog4', $label = '' ){
        $id = $this->name . 'sel' . $this->idmenusel++;
        $this->elements[ $id ] = array( 'type' => 2, 'icon' => $icon, 'options' => $options, 'label' => $label, 'id' => $id );
        return $this;
    }

    public function & addSeparator( $iterations = 1 ){
        $this->elements[] = array( 'type' => 3, 'it' => $iterations );
        return $this;
    }

    public function & setMenuSelected( $index, $ctxmenuid = '' ){
        $ctxmenuid = empty( $ctxmenuid ) ? $this->name . 'sel' : $ctxmenuid;
    
        if( $this->app->request->isAjax() ){
            if( isset( $this->elements[ $ctxmenuid ][ 'options' ] ) )
                foreach( $this->elements[ $ctxmenuid ][ 'options' ] as $i => $arr )
                    $this->app->ajax()->visibility( '#meni' . $ctxmenuid . $i, ( $i == $index ) ? true : false )
                                      ->attr( '#menl' . $ctxmenuid . $i, 'class', ( $i == $index ) ? 'active' : '' );
        }else{
                foreach( $this->elements[ $ctxmenuid ][ 'options' ] as $i => $arr ){
                    $this->elements[ $ctxmenuid ][ 'options' ][ $i ][ 'selected' ] = ( $i == $index ) ? true : false;
                }
        }

        return $this;
    }

    public function & setMenuLabel( $label, $ctxmenuid = '' ){
        $ctxmenuid = empty( $ctxmenuid ) ? $this->name . 'sel' : $ctxmenuid;
        
        $this->app->ajax()->text( '#menbl' . $ctxmenuid, $label );
        return $this;
    }

    public function __toString(){
        return $this->render();
    }

    private function render( $values = null ){
        return $this->app->render( '@my/mymenu', array( 'name'     => $this->name,
                                                        'elements' => $this->elements
                                                         ), null, null, null, false, false );
    }
    
}