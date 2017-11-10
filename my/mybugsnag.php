<?php


class mybugsnag{

    /** @var Bugsnag\Configuration */
    private $bugsnag;

    public function __construct( $container ){

        if( !isset( $container[ 'bugsnag.token' ] ) )
            return;

        $this->bugsnag = Bugsnag\Client::make( $container[ 'bugsnag.token' ] );

        if( isset( $container[ 'bugsnag.releasestage' ] ) )
            $this->bugsnag->setReleaseStage( $container[ 'bugsnag.releasestage' ] );

        if( isset( $container[ 'bugsnag.hostname' ] ) )
            $this->bugsnag->setHostname( $container[ 'bugsnag.hostname' ] );

        if( isset( $container[ 'bugsnag.appversion' ] ) )
            $this->bugsnag->setAppVersion( $container[ 'bugsnag.appversion' ] );

        Bugsnag\Handler::register( $this->bugsnag );

        $this->bugsnag->registerCallback( function( \Bugsnag\Report $report ) use( $container ) {
            if( isset( $container[ 'bugsnag.user' ] ) )
                $report->setUser( $container[ 'bugsnag.user' ] );
        });

    }


    public function notifyException( $e ){
        if( !is_null( $this->bugsnag ) && gettype( $e ) == 'object' )
            return $this->bugsnag->notifyException( $e );

        return false;
    }


    public function notifyError( $msg, $procedure, $func ){
        if( !is_null( $this->bugsnag ) )
            return $this->bugsnag->notifyError( $msg, $procedure, $func );

        return false;
    }

}