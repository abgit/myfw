<?php

class myi18n{
    
    private $lang = 'en_US';
    private $session = false;
    private $path;
    private $codeset = 'UTF-8';
    private $domain = 'myfw';

    public function __contruct(){
        $this->app = \Slim\Slim::getInstance();
    }

    // session: read from session if exists
    public function setLang( $lang, $session = false, $updatesession = false ){
        $this->lang = $session ? $this->app->session()->get( 'i18n.session', $lang ) : $lang;

        if( $updatesession ){
            $this->app->session()->set( 'i18n.session', $this->lang );
        }
        $this->updatebind();
    }

    public function setPath( $path ){
        $this->path = $path;
        $this->updatebind();
    }

    public function setCodeset( $codeset ){
        $this->codeset = $codeset;
        $this->updatebind();
    }

    public function setDomain( $domain ){
        $this->domain = $domain;
        $this->updatebind();
    }

    private function updatebind(){
        putenv( 'LC_ALL=' . $this->lang );
        setlocale( LC_ALL, $this->lang );
        bindtextdomain( $this->domain, $this->path );
        bind_textdomain_codeset( $this->domain, $this->codeset );
        textdomain( $this->domain );
    }

}