<?php

    use Mailgun\Mailgun;

    class mymailgun{

        /** @var mycontainer */
        private $app;

        /** @var \Mailgun\Mailgun  */
        private $mg;

        private $domain;

        public function __construct( $c ){

            $this->app    = $c;
            $this->mg     = Mailgun::create( $this->app->config[ 'mailgun.driver' ] === 'heroku' ? getenv( 'MAILGUN_API_KEY' ) : $this->app->config[ 'mailgun.mailgunkey' ] );
            $this->domain = $this->app->config[ 'mailgun.mailgundomain' ];
        }

        public function sendinternal( $message, $subject = null, $templatestring = '', $vars = array() ){
            return $this->send( $this->app->config[ 'mailgun.from' ], $this->app->config[ 'mailgun.to' ], is_null( $subject ) ? $this->app->config[ 'mailgun.subject' ] : $subject, $message, $templatestring, $vars );
        }

        public function sendsystem( $to, $subject, $message, $templatestring = '', $vars = array() ){
            return $this->send( $this->app->config[ 'mailgun.from' ], $to, $subject, $message, $templatestring, $vars );
        }

        public function send( $from, $to, $subject, $html, $templatestring = '', $vars = array() ){

            if( is_array( $html ) )
                $html = json_encode( $html );

            // if we use a template file, assign text and optional vars to template and get render result
            if( $template = $this->app->config[ 'mailgun.template' ] )
                $html = $this->app->view->fetch( $template, array( 'content' => $html, 'templatestring' => $templatestring ) + $vars );

            // comput mailgun email header
            $email = array( 'from' => $from, 'to' => $to, 'subject' => $subject, 'html' => $html );

            // check if we have custom additional header variables
            if( ( $headers = $this->app->config[ 'mailgun.headers' ] ) && is_array( $headers ) )
                $email = $headers + $email;

            // send
            return $this->mg->messages()->send( $this->domain, $email );
        }
    }