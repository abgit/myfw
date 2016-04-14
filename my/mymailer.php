<?php

    use Mailgun\Mailgun;

    class mymailer{

        public function __construct(){

            $this->app    = \Slim\Slim::getInstance();
            $this->mg     = new Mailgun( $this->app->config( 'email.driver' ) === 'heroku' ? getenv( 'MAILGUN_API_KEY' ) : $this->app->config( 'email.mailgunkey' ) );
            $this->domain = $this->app->config( 'email.mailgundomain' );
        }

        public function sendinternal( $message, $subject = null ){
            return $this->send( $this->app->config( 'email.from' ), $this->app->config( 'email.to' ), is_null( $subject ) ? $this->app->config( 'email.subject' ) : $subject, $message );
        }

        public function sendsystem( $to, $subject, $message, $mailgunoptions = array() ){
            return $this->send( $this->app->config( 'email.from' ), $to, $subject, $message, $mailgunoptions );
        }

        public function send( $from, $to, $subject, $text, $mailgunoptions = array() ){

            // log
            $this->app->log()->debug( "mymailer::send,to:" . $to . ',subject:' . $subject );

            // if we use a template file, assign text and optional vars to template and get render result
            if( $template = $this->app->config( 'email.template' ) ){
                if( !($tag = $this->app->config( 'email.templatetag' ) ) ){
                    $tag = 'content';
                }
                $html = $this->app->render( $template, is_array( $text ) ? $text : array( $tag => $text ), null, null, 0, false, false );    
            }else{
                $html = $text;
            }

            // comput mailgun email header
            $email = array( 'from' => $from, 'to' => $to, 'subject' => $subject, 'text' => is_string( $text ) ? $text : '', 'html' => $html ) + $mailgunoptions;

            // check if we have custom additional header variables
            if( ( $headers = $this->app->config( 'email.headers' ) ) && is_array( $headers ) )
                $email = $headers + $email;

            // send
            return $this->mg->sendMessage( $this->domain, $email );
        }
    }
