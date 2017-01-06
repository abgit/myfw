<?php

    use Mailgun\Mailgun;

    class mymailer{

        public function __construct(){

            $this->app    = \Slim\Slim::getInstance();
            $this->mg     = new Mailgun( $this->app->config( 'email.driver' ) === 'heroku' ? getenv( 'MAILGUN_API_KEY' ) : $this->app->config( 'email.mailgunkey' ) );
            $this->domain = $this->app->config( 'email.mailgundomain' );
        }

        public function sendinternal( $message, $subject = null, $templatestring = '', $vars = array() ){
            return $this->send( $this->app->config( 'email.from' ), $this->app->config( 'email.to' ), is_null( $subject ) ? $this->app->config( 'email.subject' ) : $subject, $message, $templatestring, $vars );
        }

        public function sendsystem( $to, $subject, $message, $templatestring = '', $vars = array() ){
            return $this->send( $this->app->config( 'email.from' ), $to, $subject, $message, $templatestring, $vars );
        }

        public function send( $from, $to, $subject, $html, $templatestring = '', $vars = array() ){

            if( is_array( $html ) )
                $html = json_encode( $html );

            // if we use a template file, assign text and optional vars to template and get render result
            if( $template = $this->app->config( 'email.template' ) )
                $html = $this->app->render( $template, array( 'content' => $html, 'templatestring' => $templatestring ) + $vars, null, null, 0, false, false );    

            // comput mailgun email header
            $email = array( 'from' => $from, 'to' => $to, 'subject' => $subject, 'html' => $html );

            // check if we have custom additional header variables
            if( ( $headers = $this->app->config( 'email.headers' ) ) && is_array( $headers ) )
                $email = $headers + $email;

            // send
            return $this->mg->sendMessage( $this->domain, $email );
        }
    }
