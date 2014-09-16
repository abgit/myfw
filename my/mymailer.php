<?php

    use Mailgun\Mailgun;

    class mymailer{

        public function __construct(){

            $this->app    = \Slim\Slim::getInstance();
            $this->mg     = new Mailgun( $this->app->config( 'email.mailgunkey' ) );
            $this->domain = $this->app->config( 'email.mailgundomain' );
        }

        public function sendinternal( $message, $subject = null ){
            return $this->send( $this->app->config( 'email.from' ), $this->app->config( 'email.to' ), is_null( $subject ) ? $this->app->config( 'email.subject' ) : $subject, $message );
        }

        public function sendsystem( $to, $subject, $message ){
            return $this->send( $this->app->config( 'email.from' ), $to, $subject, $message );
        }

        public function send( $from, $to, $subject, $text, $templatevars = array() ){

            // log
            $this->app->log()->debug( "mymailer::send,to:" . $to . ',subject:' . $subject );

            // if we use a template file, assign text and optional vars to template and get render result
            if( $template = $this->app->config( 'email.template' ) ){
                if( !($tag = $this->app->config( 'email.templatetag' ) ) ){
                    $tag = 'content';
                }
                $html = $this->app->render( $template, $templatevars + array( $tag => $text ), null, null, APP_CACHEAPC, false, false );    
            }else{
                $html = $text;
            }

            // comput mailgun email header
            $email = array( 'from' => $from, 'to' => $to, 'subject' => $subject, 'text' => $text, 'html' => $html );

            // check if we have custom additional header variables
            if( ( $headers = $this->app->config( 'email.headers' ) ) && is_array( $headers ) )
                $email = $headers + $email;

            // send
            return $this->mg->sendMessage( $this->domain, $email );
        }
    }
