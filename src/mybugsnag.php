<?php


class mybugsnag{

    /** @var Bugsnag\Configuration */
    private $bugsnag = null;

    public function __construct( $container ){

        if( !isset( $container[ 'bugsnag.token' ] ) )
            return;

        $this->bugsnag = Bugsnag\Client::make( $container[ 'bugsnag.token' ] );

        if( isset( $container[ 'bugsnag.releasestage' ] ) && is_string( $container[ 'bugsnag.releasestage' ] ) )
            $this->bugsnag->setReleaseStage( $container[ 'bugsnag.releasestage' ] );

        if( isset( $container[ 'bugsnag.hostname' ] ) && is_string( $container[ 'bugsnag.hostname' ] ) )
            $this->bugsnag->setHostname( $container[ 'bugsnag.hostname' ] );

        if( isset( $container[ 'bugsnag.appversion' ] ) && is_string( $container[ 'bugsnag.appversion' ] ) )
            $this->bugsnag->setAppVersion( $container[ 'bugsnag.appversion' ] );

        if( isset( $container[ 'bugsnag.user' ] ) ) {
            $this->bugsnag->registerCallback(function (\Bugsnag\Report $report) use ($container) {
                $report->setUser( $container['bugsnag.user'] );
            });
        }

        $this->bugsnag->setAutoCaptureSessions(true);

        Bugsnag\Handler::register( $this->bugsnag );

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