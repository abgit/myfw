<?php

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

class myfw{

    /** @var mycontainer */
    private $container;

    public function __construct( \Slim\App $app ){

        /** @var mycontainer $container */
        $container = $this->container = $app->getContainer();

        $container['ipaddress'] = function () {
            return $_SERVER['REMOTE_ADDR'];
        };

        // services
        $container['config'] = function ($c) {
            return new myconfig($c);
        };
        $container['ajax'] = function () {
            return new myajax();
        };
        $container['auth0'] = function ($c) {
            return new myauth0($c);
        };
        $container['db'] = function ($c) {
            return new mydb($c);
        };
        $container['bugsnag'] = function ($c) {
            return new mybugsnag($c);
        };
        $container['memcached'] = function ($c) {
            return new mymemcached($c);
        };
        $container['i18n'] = function () {
            return new myi18n();
        };
        $container['urlfor'] = function ($c) {
            return new myurlfor($c);
        };
        $container['filters'] = function ($c) {
            return new myfilters($c);
        };
        $container['rules'] = function ($c) {
            return new myrules($c);
        };
        $container['blockchain'] = function ($c) {
            return new myblockchain($c);
        };
        $container['list'] = function ($c) {
            return new mylist($c);
        };
        $container['pusher'] = function ($c) {
            return new mypusher($c);
        };
        $container['breadcrumb'] = function ($c) {
            return new mybreadcrumb($c);
        };
        $container['mailgun'] = function ($c) {
            return new mymailgun($c);
        };
        $container['notify'] = function ($c) {
            return new mynotify($c);
        };
        $container['nexmo'] = function ($c) {
            return new mynexmo($c);
        };
        $container['social'] = function ($c) {
            return new mysocial($c);
        };
        $container['otp'] = function ($c) {
            return new myotp($c);
        };
        $container['client'] = function ($c) {
            return new myclient($c);
        };
        $container['cidr'] = function ($c) {
            return new mycidr($c);
        };


        // objects
        $container['calendar'] = $container->factory(function ($c) {
            return new mycalendar($c);
        });
        $container['videosgrid'] = $container->factory(function ($c) {
            return new myvideosgrid($c);
        });
        $container['infopage'] = $container->factory(function ($c) {
            return new myinfopage($c);
        });
        $container['chat'] = $container->factory(function ($c) {
            return new mychat($c);
        });
        $container['clipboard'] = $container->factory(function ($c) {
            return new myclipboard($c);
        });
        $container['grid'] = $container->factory(function ($c) {
            return new mygrid($c);
        });
        $container['menu'] = $container->factory(function ($c) {
            return new mymenu($c);
        });
        $container['menuside'] = $container->factory(function ($c) {
            return new mymenuside($c);
        });
        $container['message'] = $container->factory(function ($c) {
            return new mymessage($c);
        });
        $container['navbar'] = $container->factory(function ($c) {
            return new mynavbar($c);
        });
        $container['panel'] = $container->factory(function ($c) {
            return new mypanel($c);
        });
        $container['stats'] = $container->factory(function ($c) {
            return new mystats($c);
        });
        $container['form'] = $container->factory(function ($c) {
            return new myform($c);
        });


        $app->add(new \Slim\Middleware\Session([
            'name'        => 'mysession',
            'autorefresh' => true,
            'lifetime'    => '20 minutes'
        ]));

        $container['session'] = function () {
            ( new mysession( [ 'name'        => 'mysession',
                               'autorefresh' => true,
                               'lifetime'    => '20 minutes' ] ) )->start();

            return new \SlimSession\Helper;
        };


        $container['confirm'] = function ($c) {
            return new myconfirm($c);
        };

        $container->extend('view', function ( $view, $container ) {

            /** @var \Slim\Views\Twig $view */
            if (!is_a($view, '\Slim\Views\Twig')) {
                return $view;
            }

            /** @var Twig_Environment $env */
            $env = $view->getEnvironment();

            $env->addFunction(new Twig_Function('_',
                function ($s, $v = array("")) {
                    if (!is_array($v)) {
                        $v = array($v);
                    }
                    array_unshift($v, gettext($s));
                    return call_user_func_array('sprintf', $v);
                }));

            $env->addFunction(new Twig_Function('d', 'var_export'));

            $env->addFunction(new Twig_Function('c', function ($value) use ($container) {
                return $container->config[$value];
            }, array('is_safe' => array('html'))));


            $env->addFilter(new Twig_Filter('cdn', array($container->filters, 'cdn')
                , array('is_safe' => array('html'))));

            $env->addFilter(new Twig_Filter('urlobj', array($container->urlfor, 'urlobj')
                , array('is_safe' => array('html'))));

            $env->addFilter(new Twig_Filter('*',
                function ($f) use ($container) {
                    if (is_callable(array($container->filters, $f))) {
                        $args = func_get_args();
                        array_shift($args);
                        return call_user_func_array(array($container->filters, $f), $args);
                    }
                    return '';
                }
            ));
            /*
                            $view->addFunction( new Twig_SimpleFunction( 'urlFor',
                                function( $action, $params = array() ){
                                    try{
                                        return $this->urlFor( $action, is_array( $params ) ? $params : array( $params ) );
                                    }catch( RuntimeException $e ){
                                        return '';
                                    };
                            }));
            */
            $env->addExtension(new Twig_Extension_StringLoader());
            $env->addExtension(new Aptoma\Twig\Extension\MarkdownExtension(new Aptoma\Twig\Extension\MarkdownEngine\MichelfMarkdownEngine()));

            /** @var Twig_Loader_Filesystem $loader */
            $loader = $env->getLoader();
            $loader->addPath(__DIR__, 'my');

            return $view;
        });

//        $app->get( '/verify/{h:cf[a-f0-9]{32}}[/{twotoken:[0-9]{6}}]', 'myconfirm:processRequest' )
//            ->setName( 'myfwconfirm' );

        $app->post( '/verify/{h:cf[a-f0-9]{32}}[/{twotoken:.*}]', 'myconfirm:processRequest' )
            ->setName( 'myfwconfirm' );


        $app->post( '/myfw/tip/{tip:tip[a-zA-Z0-9]{1,20}}', 'mymessage:processTip' )
            ->setName( 'myfwtip' );

        $app->post( '/myfw/filestack/{fsid:[a-zA-Z0-9]{1,20}}', 'myform:processFilestackThumb' )
            ->setName( 'myfwfilestack' );

        $app->post( '/myfw/markdown', 'myform:processMarkdown' )
            ->setName( 'myfwmarkdown' );

        $container['filestack.policy'] = function ($c) {
            return base64_encode('{"expiry":' . strtotime( 'first day of next month midnight' ) . ',"call":["pick","store"]}' );
        };

        $container['filestack.signature'] = function ($c) {
            return hash_hmac( 'sha256', $c['filestack.policy'], $c->config[ 'filestack.secret' ] );
        };

        $app->post( '/pusher/auth', 'mypusher:checkEndpoint' );
    }

    /** @throws myexception */
    public function __invoke( Request $request, Response $response, $next) {

        $container = $this->container;

        $container['isajax'] = function () use ($request) {
            return ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest');
        };

        $container['isreferer'] = function () use ($container, $request) {

            $hostname = $container->config['app.hostname'];
            $referer  = $request->getHeaderLine('Referer');

            return ( $request->isPost() && !empty( $hostname ) && !empty( $referer ) && strpos( strtolower( $referer ), strtolower( $hostname ) ) !== false );
        };

        if ( isset( $container->config[ 'app.ratelimit' ] ) && $container->config[ 'app.ratelimit' ] === true && !$container->memcached->rateisvalid() ) {
            throw new myexception(myexception::RATELIMIT,
                'Too much requests. Please wait ' . $container->memcached->ratelocktimeout() . 's and try again.');
        }

        if ( isset( $container->config[ 'app.cidr' ] ) && php_sapi_name() !== 'cli' && !empty( $container->config[ 'app.cidr' ] ) && !$container->cidr->match( $_SERVER['REMOTE_ADDR'], $container->config[ 'app.cidr' ] ) ) {
            throw new myexception(myexception::FORBIDDEN,
                'IP ' . $_SERVER['REMOTE_ADDR'] . ' not in APP cidr whitelist.' );
        }

        // myconfirm method to populate $_POST
        $xconfirm = $request->getHeaderLine('X-Confirm');

        if( !empty( $xconfirm ) && empty( $_POST ) ){
            $hashid = $container->session->get( $xconfirm, false );
            if( is_string( $hashid ) ){
                $hash = $container->session->get( $hashid, false );
                if( isset( $hash['postvars'] ) ){
                    $_POST = $hash['postvars'];
                }
            }
        }

        /** @var Response $response */
        $response = $next($request, $response);

        if( !$container->pusher->empty() )
            $container->pusher->sendall();

        // check debug mode
        if( isset( $container->config[ 'app.debug' ] ) && $container->config[ 'app.debug' ] === true ) {
            $response = $response->withAddedHeader('X-myfw-page', sprintf("%.3f", defined('APP_START' ) ? (float)microtime(true) - APP_START : 0 ) );
            $response = $response->withAddedHeader('X-myfw-db', $container->db->getDebugsCounter() );
        }

        return $container->isajax ? $response->withJson( $container->ajax->obj() ) : $response;
    }

}

    // debug function alias
    function d($x){
        die(var_export($x));
    }

