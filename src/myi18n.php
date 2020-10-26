<?php

class myi18n{

    /** @var mycontainer*/
    private $app;

    private string $lang = 'en_US';
    private bool $session = false;
    private string $codeset = 'UTF-8';
    private string $domain = 'myfw';
    private string $path;

    public function __construct( $c ){
        $this->app = $c;
    }

    // session: read from session if exists
    public function & setLang( $lang, $session = false, $updatesession = false ): myi18n
    {
        $this->lang = $session ? $this->app->session->get( 'i18n.session', $lang ) : $lang;

        if( $updatesession ){
            $this->app->session->set( 'i18n.session', $this->lang );
        }
        $this->updatebind();
        return $this;
    }

    public function & setPath( $path ): myi18n
    {
        $this->path = $path;
        $this->updatebind();
        return $this;
    }

    public function & setCodeset( $codeset ): myi18n
    {
        $this->codeset = $codeset;
        $this->updatebind();
        return $this;
    }

    public function & setDomain( $domain ): myi18n
    {
        $this->domain = $domain;
        $this->updatebind();
        return $this;
    }

    private function & updatebind(): myi18n
    {
        putenv( 'LC_ALL=' . $this->lang );
        setlocale( LC_ALL, $this->lang );
        bindtextdomain( $this->domain, $this->path );
        bind_textdomain_codeset( $this->domain, $this->codeset );
        textdomain( $this->domain );
        return $this;
    }

    public function _n( $s, $p = null, $i = null, $o1 = null, $o2 = null ){

        // singular/plural
        if( is_string( $p ) && is_numeric( $i ) ){

            if( (int)$i === 1 ){
                $str = gettext( $s );
                $arr = is_null( $o1 ) ? array("") : ( is_array( $o1 ) ? $o1 : array( $o1 ) );
            }else{
                $str = gettext( $p );
                $arr = is_null( $o2 ) ? ( is_null( $o1 ) ? array("") : ( is_array( $o1 ) ? $o1 : array( $o1 ) ) ) : ( is_array( $o2 ) ? $o2 : array( $o2 ) );
            }

        // simple with/without variables
        }else{
            $str = gettext( $s );
            $arr = is_array( $p ) ? $p : array("");
        }

        array_unshift( $arr, $str );

        return sprintf(...$arr);
    }     
}