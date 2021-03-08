<?php

use \Slim\App;
use \Slim\Http\Request;
use \Slim\Http\Response;

use \Slim\Middleware\Session;
use \Slim\Views\Twig;
use \SlimSession\Helper;

use \Twig\Environment;
use \Twig\Loader\LoaderInterface;
use \Twig\TwigFilter;
use \Twig\TwigFunction;
use \Twig\Extension\StringLoaderExtension;

// global time constants
define( 'SEC_SECOND',       1 );
define( 'SEC_MINUTE',      60 );
define( 'SEC_HOUR',      3600 );
define( 'SEC_DAY',      86400 );
define( 'SEC_WEEK',    604800 );
define( 'SEC_MONTH',  2592000 );


class myfw{

    /** @var mycontainer */
    private $container;

    public function __construct( App $app ){

        /** @var mycontainer $container */
        $container = $this->container = $app->getContainer();

        // services
        $container['config'] = static function ($c) {
            return new myconfig($c);
        };
        $container['ajax'] = static function () {
            return new myajax();
        };
        $container['auth0'] = static function ($c) {
            return new myauth0($c);
        };
        $container['db'] = static function ($c) {
            return new mydb($c);
        };
        $container['bugsnag'] = static function ($c) {
            return new mybugsnag($c);
        };
        $container['memcached'] = static function ($c) {
            return new mymemcached($c);
        };
        $container['redis'] = static function ($c) {
            return new myredis($c);
        };
        $container['i18n'] = static function ($c) {
            return new myi18n($c);
        };
        $container['urlfor'] = static function ($c) {
            return new myurlfor($c);
        };
        $container['filters'] = static function ($c) {
            return new myfilters($c);
        };
        $container['rules'] = static function ($c) {
            return new myrules($c);
        };
        $container['blockchain'] = static function ($c) {
            return new myblockchain($c);
        };
        $container['list'] = static function ($c) {
            return new mylist($c);
        };
        $container['breadcrumb'] = static function ($c) {
            return new mybreadcrumb($c);
        };
        $container['mailgun'] = static function ($c) {
            return new mymailgun($c);
        };
        $container['notify'] = static function ($c) {
            return new mynotify($c);
        };
        $container['nexmo'] = static function ($c) {
            return new mynexmo($c);
        };
        $container['social'] = static function ($c) {
            return new mysocial($c);
        };
        $container['client'] = static function ($c) {
            return new myclient($c);
        };
        $container['cidr'] = static function ($c) {
            return new mycidr($c);
        };


        // objects
        $container['pusher'] = $container->factory(static function ($c) {
            return new mypusher($c);
        });

        $container['calendar'] = $container->factory(static function ($c) {
            return new mycalendar($c);
        });
        $container['media'] = $container->factory(static function ($c) {
            return new mymedia($c);
        });
        $container['rating'] = $container->factory(static function ($c) {
            return new myrating($c);
        });
        $container['progress'] = $container->factory(static function ($c) {
            return new myprogress($c);
        });
        $container['infopage'] = $container->factory(static function ($c) {
            return new myinfopage($c);
        });
        $container['chat'] = $container->factory(static function ($c) {
            return new mychat($c);
        });
        $container['clipboard'] = $container->factory(static function ($c) {
            return new myclipboard($c);
        });
        $container['grid'] = $container->factory(static function ($c) {
            return new mygrid($c);
        });
        $container['menu'] = $container->factory(static function ($c) {
            return new mymenu($c);
        });
        $container['menuside'] = $container->factory(static function ($c) {
            return new mymenuside($c);
        });
        $container['message'] = $container->factory(static function ($c) {
            return new mymessage($c);
        });
        $container['navbar'] = $container->factory(static function ($c) {
            return new mynavbar($c);
        });
        $container['panel'] = $container->factory(static function ($c) {
            return new mypanel($c);
        });
        $container['stats'] = $container->factory(static function ($c) {
            return new mystats($c);
        });
        $container['form'] = $container->factory(static function ($c) {
            return new myform($c);
        });

        if( PHP_SAPI !== 'cli') {
            $app->add(new Session([
                'name'        => 'mysession',
                'autorefresh' => true,
                'secure'      => true,
                'lifetime'    => '60 minutes'
            ]));

            $container['session'] = static function () {
                ( new mysession( [ 'name'        => 'mysession',
                                   'autorefresh' => true,
                                   'secure'      => true,
                                   'lifetime'    => '60 minutes' ] ) )->start();

                return new Helper;
            };
        }


        $container['confirm'] = static function ($c) {
            return new myconfirm($c);
        };

        $container->extend('view', static function ($view, $container ) {

            /** @var Twig $view */
            if (!is_a($view, '\Slim\Views\Twig')) {
                return $view;
            }

            /** @var Environment $env */
            $env = $view->getEnvironment();

            $env->addFunction(new TwigFunction('_',
                static function ($s, $v = array('')) {
                    if (!is_array($v)) {
                        $v = array($v);
                    }
                    array_unshift($v, gettext($s));
                    return sprintf(...$v);
                }));

            $env->addFunction(new TwigFunction('d', 'var_export'));

            $env->addFunction(new TwigFunction('c', static function ($value) use ($container) {
                return $container->config[$value];
            }, array('is_safe' => array('html'))));


//            $env->addFilter(new TwigFilter('cdn', array($container->filters, 'cdn')
//                , array('is_safe' => array('html'))));

            $env->addFilter(new TwigFilter('htmlpurifier', array($container->filters, 'htmlpurifier')
                , array('is_safe' => array('html'))));

            $env->addFilter(new TwigFilter('urlobj', array($container->urlfor, 'urlobj')
                , array('is_safe' => array('html'))));


            $env->addFilter(new TwigFilter('autolink',
                static function ($f){
                    return preg_replace("/http[s]?:\/\/[a-zA-Z0-9.\-\/?#=&]+/", "<a href=\"$0\" target=\"_blank\">$0</a>", $f);
                }, array( 'pre_escape'=>'html', 'is_safe' => array('html'))));


            $env->addFilter(new TwigFilter('*',
                static function ($f) use ($container) {
                    if (is_callable(array($container->filters, $f))) {
                        $args = func_get_args();
                        array_shift($args);
                        return call_user_func_array(array($container->filters, $f), $args);
                    }
                    return '';
                }
            ));

            $env->addExtension(new StringLoaderExtension());
//            $env->addExtension(new Aptoma\Twig\Extension\MarkdownExtension(new Aptoma\Twig\Extension\MarkdownEngine\MichelfMarkdownEngine()));

            /** @var LoaderInterface $loader */
            $loader = $env->getLoader();
            $loader->addPath(__DIR__, 'my');

            return $view;
        });

        $app->post( '/verify/{h:cf[a-f0-9]{32}}[/{twotoken:.*}]', 'myconfirm:processRequest' )
            ->setName( 'myfwconfirm' );


        $app->post( '/myfw/tip/{tip:tip[a-zA-Z0-9]{1,20}}', 'mymessage:processTip' )
            ->setName( 'myfwtip' );

//        $app->post( '/myfw/filestack/{fsid:[a-zA-Z0-9]{1,20}}', 'myform:processFilestackThumb' )
//            ->setName( 'myfwfilestack' );

        $app->post( '/myfw/uploadcare/{fsid:.*}', 'myform:processUploadcareThumb' )
            ->setName( 'myfwuploadcare' );

//        $app->post( '/myfw/markdown', 'myform:processMarkdown' )
//            ->setName( 'myfwmarkdown' );

//        $container['filestack.policy'] = static fn() => base64_encode('{"expiry":' . strtotime( 'first day of next month midnight' ) . ',"call":["pick","store"]}' );
//        $container['filestack.signature'] = static fn($c) => hash_hmac( 'sha256', $c['filestack.policy'], $c->config[ 'filestack.secret' ] );

    }

    /** @throws myexception */
    public function __invoke( Request $request, Response $response, $next) {

        $container = $this->container;

        $container['isajax'] = static function () use ($request) {
            return ($request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest');
        };

        $container['isreferer'] = static function () use ($container, $request) {

            $hostname = $container->config['app.hostname'];
            $referer  = $request->getHeaderLine('Referer');

            return ( $request->isPost() && !empty( $hostname ) && !empty( $referer ) && stripos($referer, $hostname) !== false );
        };

        if ( PHP_SAPI !== 'cli' && isset( $container->config[ 'app.ratelimit' ] ) && $container->config[ 'app.ratelimit' ] === true && !$container->redis->rateisvalid() ) {
            throw new myexception(myexception::RATELIMIT,
                'Too much requests. Please wait ' . $container->redis->ratelocktimeout() . 's and try again.');
        }

        if ( PHP_SAPI !== 'cli' && !empty( $container->config[ 'app.cidr' ] ) && !$container->cidr->match( $_SERVER['REMOTE_ADDR'], $container->config[ 'app.cidr' ] ) ) {
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

        //if( !$container->pusher->empty() ) {
        //    $container->pusher->sendall();
        //}

        // check debug mode
        if( isset( $container->config[ 'app.debug' ] ) && $container->config[ 'app.debug' ] === true ) {
            $myresponse = $response->withAddedHeader('x-myfw-page', sprintf("%.3f", defined('APP_START' ) ? (float)microtime(true) - APP_START : 0 ) );
            $myresponse = $myresponse->withAddedHeader('x-myfw-d', $container->db->getDebugsCounter() );
            return $container->isajax ? $myresponse->withJson( $container->ajax->obj() ) : $myresponse;
        }

        return $container->isajax ? $response->withJson( $container->ajax->obj() ) : $response;
    }

}

    // debug function alias
    function d($x){
        die(var_export($x));
    }

    function in_arrayi( string $needle, array $haystack ): array{
        return in_array(strtolower($needle), array_map('strtolower', $haystack));
    }

    function array_avg( array $array ): array{
        $num = count( $array );

        $array = array_filter( $array, function($v){ return is_string($v) || is_numeric($v); } );

        $res = array_count_values($array);

        foreach ($res as $k => $val) {
            $res[$k] = array( 'value' => $k, 'count' => $val, 'percentage' => round($val / $num * 100, 2) );
        }

        return $res;

    }

    function array_avg_desc( array $array ):array{
        $array = array_values( array_avg( $array ) );

        array_multisort(array_column($array, 'percentage'), SORT_DESC, $array);

        return $array;
    }

    function array_avg_min( array $array, float $min ): array{
        $res = array();

        foreach( array_avg( $array ) as $k => $val ){
            if( $val[ 'percentage' ] >= $min ){
                $res[] = $val[ 'value' ];
            }
        }

        return $res;
    }

    function array_one( array $array ):array{

        $res = array();
        foreach ( $array as $el ){
            if( is_array( $el ) ){
                $res = array_merge( $res, array_one( $el ) );
            }else{
                $res[] = $el;
            }
        }
        return $res;
    }