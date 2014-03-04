<?php

    use Mailgun\Mailgun;

    class mymailer{

        public function __construct(){

            $this->app = \Slim\Slim::getInstance();
            $this->mg = new Mailgun( $this->app->config( 'email.mailgunkey' ) );
            $this->domain = $this->app->config( 'email.mailgundomain' );
        }

        public function sendinternal( $message ){
            return $this->send( $this->app->config( 'email.from' ), $this->app->config( 'email.to' ), $this->app->config( 'email.subject' ), $message );
        }

        public function sendsystem( $to, $subject, $message ){
            return $this->send( APP_EMAIL, $to, $subject, $message );
        }

        public function send( $from, $to, $subject, $text ){

            $this->app = \Slim\Slim::getInstance();
            $this->app->log()->debug( "mymailer::send,to:" . $to . ',subject:' . $subject );

            $html = file_get_contents( dirname( __FILE__ ) . '/mymailer.tpl' );
            $html = str_replace( '#msgheader#',  date("j / M"), $html );
            $html = str_replace( '#msgtitle#',   $subject, $html );
            $html = str_replace( '#msgcontent#', nl2br( preg_replace( '@((http://|https://)(www.)?([^ ]{4,300}))@', '<a href="\0" target="_blank">\4</a>', $text ) ), $html );

            return $this->mg->sendMessage( $this->domain, array(    'from'          => $from, 
                                                                    'to'            => $to,
                                                                    'subject'       => $subject, 
                                                                    'text'          => $text,
                                                                    'html'          => $html ) );
        }
    }
