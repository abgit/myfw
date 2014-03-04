<?php

    // cart session class management
    class mycart{

        private $maxelements = 10;
        private $app;

        public function __construct(){

            $this->app = \Slim\Slim::getInstance();
            $this->app->session()->setcheck( 'cart', array() );
        }

        public function clear(){
            return $this->app->session()->set( 'cart', array() );
        }

        public function getItems(){
            return $this->app->session()->get( 'cart', array() );
        }

        public function getTotalItems(){
            return count( $this->getItems() );
        }

        public function getTotal(){
            $total = 0;
            foreach( $this->getItems() as $tag => $item )
                $total += isset( $item[ 'total' ][ 'value' ] ) ? $item[ 'total' ][ 'value' ] : 0;
            return $total;
        }

        public function addItem( $id, $label ){

            if( $this->getTotalItems() == $this->maxelements )
                return false;

            $this->app->session()->setcheck( array( 'cart', $id ), array() );
            $this->app->session()->set( array( 'cart', $id, 'tag' ),   $id );
            $this->app->session()->set( array( 'cart', $id, 'label' ), $label );
		}

        public function isItem( $id ){
            return $this->app->session()->exists( array( 'cart', $id ) );
        }

        public function isItemValue( $id, $value, $property ){
            return $this->app->session()->get( array( 'cart', $id, $property, 'value' ), '' ) === $value;
        }

        public function addExtra( $id, $value ){
            return $this->app->session()->set( array( 'cartextra', $id ), $value );
        }

        public function getExtra( $id ){
            return $this->app->session()->set( array( 'cartextra', $id ), '' );
        }

        public function removeItem( $id ){
            return $this->app->session()->delete( array( 'cart', $id ) );
        }

        public function updateItemProperty( $id, $property, $value, $options = array() ){
            $this->app->session()->set( array( 'cart', $id, $property, 'value' ),   $value );
            $this->app->session()->set( array( 'cart', $id, $property, 'options' ), $options );
        }

        public function getItemPropertyValue( $id, $property ){
            return $this->app->session()->get( array( 'cart', $id, $property, 'value' ), null );
        }

        public function checkItemProperty( $property ){
            foreach( $this->getItems() as $item ){
                if( ! isset( $item[ $property ] ) || empty( $item[ $property ] ) )
                    return false;
            }
            return true;
        }
    }
