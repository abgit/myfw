<?php


    class myurlfor{

        /** @var mycontainer*/
        private $app;

        public function __construct( $c ){
            $this->app = $c;
        }

        public function action( $action, $options = array() ): ?string
        {
            try {
                return $this->app->router->pathFor($action, $options);
            }catch (Exception $e){
                return '';
            }
        }

        public function ajax( $action, $options = array(), $msg = false ): string
        {
            return "myfwsubmit('" . $this->action( $action, $options ) . "'" . ( is_string( $msg ) ? ( ",'" . $msg . "'" ) : '' ) . ")";
        }

        public function ajaxObj( $action, $options = array(), $msg = false ): array
        {
            return array( 'obj' => 'urlajax',
                          'url' => $this->action( $action, $options ),
                          'msg' => $msg );
        }

        public function obj( $action, $options = array(), $target = '' ): array
        {
            return array( 'obj'    => 'url',
                          'url'    => $this->action( $action, $options ),
                          'target' => $target );
        }

        public function external( $url, $target = '' ): array
        {
            return array( 'obj'    => 'url',
                          'url'    => $url,
                          'target' => $target );
        }

        public function href( $key, $target = '_blank' ): array
        {
            return array( 'obj'    => 'url',
                          'urlkey' => $key,
                          'target' => $target );
        }

        public function redir( $action, $options = array() ): array
        {
            return array( 'obj'    => 'redir',
                          'url'    => $this->action( $action, $options ) );
        }

        public function multiple( $urls, $code, $keys, $default ): array
        {
            return array( 'obj'     => 'urls',
                          'urls'    => $urls,
                          'code'    => $code,
                          'keys'    => $keys,
                          'default' => $default );
        }

        public function ajaxForm( $formname, $action, $submitbutton = '', $msg = 'Loading ...', $delay = 0 ): array
        {
            return array( 'obj'          => 'urlsubmit',
                          'formname'     => $formname,
                          'submitbutton' => $formname . $submitbutton,
                          'msg'          => $msg,
                          'action'       => $action,
                          'delay'        => (int)$delay);
        }


        public function urlobj( $val, $valuearray = array(), $tags = array() ): string
        {

            if( is_array( $val ) && isset( $val[ 'obj' ] ) ){
                switch( $val[ 'obj' ] ){
                    case 'url':
                    case 'urlsubmit':
                    case 'urlajax':
                    case 'redir':     return $this->_urlobj( $val, $valuearray, $tags );
                    case 'urls':      return $this->_urlobjmultiple( $val, $valuearray );
                }
            }
            return '';
        }

        public function _urlobj( $val, $valuearray = array(), $tags = array() ): string
        {
            if( is_array( $val ) && isset( $val[ 'obj' ] ) ){

                if( isset( $val[ 'url' ] ) ){
                    $url = ( !empty( $valuearray ) && !empty( $tags ) ) ? $this->app->filters->replaceurl( $val[ 'url' ], $valuearray, $tags ) : $val[ 'url' ];
                }elseif( isset( $val[ 'urlkey' ] ) ) {
                    $url = $valuearray[ $val[ 'urlkey' ] ] ?? null;
                }else{
                    $url = null;
                }

                switch( $val[ 'obj' ] ){
                    case 'urlajax':   return "onclick=\"myfwsubmit('" . $url . ( is_string( $val[ 'msg' ] ) ? "','" . $val[ 'msg' ] : '' ) . "')\"";
                    case 'urlsubmit': return "onclick=\"myfwformsubmit('" . $val[ 'formname' ] . "','','','" . $val[ 'submitbutton' ] . "','" . $val[ 'msg' ] . "','" . $val[ 'action' ] . "'," . $val[ 'delay' ] . ")\"";
                    case 'url':       return 'href="' . $url . '"' . ( ( isset( $val[ 'target' ] ) && !empty( $val[ 'target' ] ) ) ? ' target="' . $val[ 'target' ] . '"' : '' );
                    case 'redir':     return "onclick=\"myfwredir('" . $url . "')\"";
                }
            }
            return '';
        }

        public function _urlobjmultiple( $url, $values ): string
        {

            if( is_array( $url ) ){
                $keys               = $url[ 'keys' ];
                $urlmultiplekey     = $url[ 'code' ];
                $urlmultipledefault = $url[ 'default' ];
                $url                = $url[ 'urls' ];

                if( isset( $values[ $urlmultiplekey ] ) ){
                    foreach( $url as $i => $urlobj ){
                        if( (string)$i === (string)$values[$urlmultiplekey]){
                            return $this->_urlobj( $urlobj, $values, $keys );
                        }
                    }
                }

                if( is_string( $urlmultipledefault ) ) {
                    return $this->_urlobj($urlmultipledefault, $values, $keys);
                }
            }
            return '';
        }

    }
