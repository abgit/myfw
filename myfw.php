<?php

    use Aptoma\Twig\Extension\MarkdownExtension;
    use Aptoma\Twig\Extension\MarkdownEngine;

    define( "APP_WEBMODE", isset( $_SERVER['HTTP_HOST'] ) );

    // slim warnings on cron
    if( ! APP_WEBMODE ){
        if( ! isset( $_SERVER['REQUEST_METHOD'] ) ) $_SERVER['REQUEST_METHOD'] = '';
        if( ! isset( $_SERVER['REMOTE_ADDR'] ) )    $_SERVER['REMOTE_ADDR']    = '';
        if( ! isset( $_SERVER['REQUEST_URI'] ) )    $_SERVER['REQUEST_URI']    = '';
        if( ! isset( $_SERVER['SERVER_NAME'] ) )    $_SERVER['SERVER_NAME']    = '';
        if( ! isset( $_SERVER['SERVER_PORT'] ) )    $_SERVER['SERVER_PORT']    = '';
    }

    // init slim
    require_once __DIR__ . '/my/vendor/autoload.php';
    \Slim\Slim::registerAutoloader();

    // framework autoload
    spl_autoload_register( function( $class ) {
        if( strpos( $class, 'my' ) === 0 )
            include_once __DIR__ . '/my/' . $class . '.php';
    });

    // my framework
    class myfw extends \Slim\Slim{

        public  $client      = false;

        private $forms       = array();
        private $modals      = array();
        private $ajax        = null;
        private $mailer      = null;
        private $cart        = null;
        private $tplobj      = null;
        private $pdodb       = null;
        private $renderinit  = false;
        private $redisro     = null;
        private $redisrw     = null;
        private $rules       = null;
        private $filters     = null;
        private $vtotal      = null;
        private $session     = null;
        private $bcloud      = null;
        private $transloadit = null;
        private $loginit     = null;
        private $i18n        = null;
        private $cache       = null;
        private $cacheable   = null;
        private $isajaxmode  = null;
        private $isratemode  = null;
        private $auth        = null;
        private $onlogincall = null;
        private $objuserredir= null;
        private $objuserlogg = null;
        private $otp         = null;
        private $grids       = null;
        private $panel       = null;
        private $notify      = null;
        private $stats       = null;
        private $info        = null;
        private $message     = null;
        private $navbar      = null;
        private $uuid        = false;
        private $blockchain  = null;
        private $blockcypher = null;
        private $auth0       = null;
        private $on2Fcall    = null;
        private $bef2Fcall   = null;
        private $onsmscall   = null;
        private $chats       = null;
        private $ishttps     = null;
        private $sms         = null;
        private $calendar    = null;
        private $menu        = null;
        private $pusher      = null;
        private $memcached   = null;
        private $intercom    = null;

        public  $onDBError   = null;

        public function __construct( $arr = array() ){
            parent::__construct( $arr );
            $this->hook( 'slim.before.dispatch', function(){
                $this->cacheable = null;
            });
            $this->hook( 'slim.after.dispatch', function(){
//                if( $this->config( 'app.israte' ) !== false )
//                    $this->memcached()->ratemonodelete();

                if( $this->isajaxmode === true )
                    $this->ajax()->render();

                $this->isajaxmode = null;
            });
            $this->post( '/myfwconfirm/:h(/:twotoken)(/)', 'islogged', function( $h, $twotoken = '' ){

                $obj = $this->session()->get( $h, false );

                if( isset( $obj[ 'uri' ] ) && isset( $obj[ 'method' ] ) ){

                    if( isset( $obj[ '2f' ] ) && $obj[ '2f' ] ){
                        if ( !$this->rules()->twofactortoken( $twotoken ) || call_user_func( $this->on2Fcall, $twotoken ) !== true )
                            return $this->ajax()->msgWarning( 'Token is not valid.' )->render();

                        $this->ajax()->confirmDialogClose();
                    }

                    if( isset( $obj[ '2s' ] ) && $obj[ '2s' ] ){
                        if ( !$this->rules()->smspin( $twotoken ) || call_user_func( $this->onsmscall, intval( $twotoken ) ) !== true )
                            return $this->ajax()->msgWarning( 'Pin is not valid.' )->render();

                        $this->ajax()->confirmDialogClose();
                    }

                    $route = $this->router->getMatchedRoutes( $obj[ 'method' ], $obj[ 'uri' ], true );

                    if( isset( $route[0] ) ){
                        $this->session()->set( $h . 'confirm', 1 );

                        if( isset( $obj[ 'postvars' ] ) )
                            $_POST = $obj[ 'postvars' ];

                        return $route[0]->dispatch();
                    }
                }

                $this->notFound();

            })->name( 'myfwconfirm' )->conditions( array( 'h' => 'cf[a-f0-9]{32}' ) );
        }

        public function setConditions( $cond ){
            \Slim\Route::setDefaultConditions( $cond );
        }

        public function config($name, $value = null){

            if (is_array($name)) {
                if (true === $value) {
                    $this->container['settings'] = array_merge_recursive($this->container['settings'], $name);
                } else {
                    $this->container['settings'] = array_merge($this->container['settings'], $name);
                }
            } elseif (func_num_args() === 1) {
                if( isset( $this->container['settings'][$name] ) ){

                    if( !is_string( $this->container['settings'][$name] ) )
                        return $this->container['settings'][$name];

                    preg_match("/([^$@#][a-zA-Z0-9]+[-]{1})([$@#][a-zA-Z0-9_]+.*)/", $this->container['settings'][$name], $vars );
                    if( is_array( $vars ) && !empty( $vars ) ){
                        $setting = $vars[ 2 ];
                        $prefix  = $vars[ 1 ];
                    }else{
                        $setting = $this->container['settings'][$name];
                        $prefix  = '';
                    }

                    switch( $setting{0} ){
                        case '@': list( $all, $variable, $sufix ) = $this->configparse( $setting );
                                  $var = $this->getenvconfigvar( $variable );
                                  return is_null( $var ) ? null : ( $prefix . $var . $sufix );

                        case '#': list( $all, $variable, $sufix ) = $this->configparse( $setting );
                                  $var = $this->getenvconfigvar( $variable );
                                  return is_null( $var ) ? null : ( $prefix . $this->configdecrypt( $var ) . $sufix );

                        case '!': list( $all, $variable, $sufix ) = $this->configparse( $setting );
                                  return $prefix . $this->configdecrypt( $variable ) . $sufix;

                        case '$': list( $all, $variable, $sufix ) = $this->configparse( $setting );
                                  return $prefix . $this->session()->get( $variable ) . $sufix;

                        default: return $setting;
                    }
                }
                return null;
            } else {
                $settings = $this->container['settings'];
                $settings[$name] = $value;
                $this->container['settings'] = $settings;
            }
        }

        private function configparse( $name ){
            preg_match("/([a-zA-Z0-9_]+)(.*)/", substr( $name, 1 ), $vars );
            return $vars;
        }

        private function getenvconfigvar( $name ){

            $var = getenv( $name );
            return $var === false ? null : $var;
        }

        public function configencrypt( $plain, $key = null ) {

            $key = substr( is_null( $key ) ? $this->container['settings'][ 'app.mc' ] : $key, 0, 32 );

            mt_srand( (double) microtime() * 1000000 );
            $iv = mcrypt_create_iv( mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC ), MCRYPT_RAND );

            $value = mcrypt_encrypt( MCRYPT_RIJNDAEL_256, $key, $plain, MCRYPT_MODE_CBC, $iv );

            return rtrim( base64_encode( serialize( [ $iv, $value ] ) ), "\0\3" );
        }

        public function configdecrypt( $encoded, $key = null ){

            $key = substr( is_null( $key ) ? $this->container['settings'][ 'app.mc' ] : $key, 0, 32 );

            list( $iv, $value ) = unserialize( base64_decode( $encoded ) );
            return rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, $key, $value, MCRYPT_MODE_CBC, $iv ), "\0");
        }

        public function log(){
            if( $this->config( 'log.init' ) && is_null( $this->loginit ) ){
                $this->config( 'log.writer', new mylog() );
                $this->log->setEnabled( true );
                $this->log->setLevel( $this->config( 'log.level' ) );
                $this->loginit = true;
            }else{
                $this->log->setEnabled( false );
            }

            return $this->log;
        }

        public function setcacheable(){
            $this->cacheable = true;
        }

        public function setajaxmode(){
            $this->isajaxmode = true;
        }

        public function setratemonomode(){
            $this->isratemode = true;
        }

        public function iscacheable(){
            return $this->cacheable === true;
        }

        public function rules(){
            if( is_null( $this->rules ) )
				$this->rules = new myrules();
			return $this->rules;
		}

        public function filters(){
            if( is_null( $this->filters ) )
				$this->filters = new myfilters();
			return $this->filters;
		}

        public function auth0(){
            if( is_null( $this->auth0 ) )
				$this->auth0 = new myauth0();
			return $this->auth0;
		}

        public function sms(){
            if( is_null( $this->sms ) )
				$this->sms = new mysms();
			return $this->sms;
		}

		// get form object
		public function form( $formname = 'f' ){
			if( ! isset( $this->forms[ $formname ] ) )
				$this->forms[ $formname ] = new myform( $formname );
			return $this->forms[ $formname ];
		}

		public function grid( $name = 'g' ){
			if( ! isset( $this->grids[ $name ] ) )
				$this->grids[ $name ] = new mygrid( $name );
			return $this->grids[ $name ];
		}

		public function chat( $name ){
			if( ! isset( $this->chats[ $name ] ) )
				$this->chats[ $name ] = new mychat( $name );
			return $this->chats[ $name ];
		}

		public function menu( $name ){
			if( ! isset( $this->menu[ $name ] ) )
				$this->menu[ $name ] = new mymenu( $name );
			return $this->menu[ $name ];
		}

		public function calendar( $name = 'c' ){
			if( ! isset( $this->calendar[ $name ] ) )
				$this->calendar[ $name ] = new mycalendar( $name );
			return $this->calendar[ $name ];
		}

		public function notify( $name = 'n' ){
			if( ! isset( $this->notify[ $name ] ) )
				$this->notify[ $name ] = new mynotify( $name );
			return $this->notify[ $name ];
		}

		public function stats( $name = 'n' ){
			if( ! isset( $this->stats[ $name ] ) )
				$this->stats[ $name ] = new mystats( $name );
			return $this->stats[ $name ];
		}

		public function modal( $id ){
			if( ! isset( $this->modals[ $id ] ) )
				$this->modals[ $id ] = new mymodal( $id );
			return $this->modals[ $id ];
		}

		public function blockchain(){
			if( ! isset( $this->blockchain ) )
				$this->blockchain = new myblockchain();
			return $this->blockchain;
		}

		public function pusher(){
			if( ! isset( $this->pusher ) )
				$this->pusher = new mypusher();
			return $this->pusher;
		}

		public function blockcypher(){
			if( ! isset( $this->blockcypher ) )
				$this->blockcypher = new myblockcypher();
			return $this->blockcypher;
		}

		public function info( $id = 'in' ){
			if( ! isset( $this->info[ $id ] ) )
				$this->info[ $id ] = new myinfo( $id );
			return $this->info[ $id ];
		}

		public function panel( $id = 'p' ){
			if( ! isset( $this->panel[ $id ] ) )
				$this->panel[ $id ] = new mypanel( $id );
			return $this->panel[ $id ];
		}

		public function navbar( $id = 'n' ){
			if( ! isset( $this->navbar[ $id ] ) )
				$this->navbar[ $id ] = new mynavbar( $id );
			return $this->navbar[ $id ];
		}

		public function message( $id = 'ms' ){
			if( ! isset( $this->message[ $id ] ) )
				$this->message[ $id ] = new mymessage( $id );
			return $this->message[ $id ];
		}

		public function ajax(){
			if( ! isset( $this->ajax ) )
				$this->ajax = new myajax();
			return $this->ajax;
		}

		public function mailer(){
			if( is_null( $this->mailer ) )
				$this->mailer = new mymailer();
			return $this->mailer;
		}

		public function tpl(){
			if( is_null( $this->tplobj ) && ( $tplobj = $this->config( 'templates.global' ) ) ){
				$this->tplobj = new $tplobj();
            }
			return $this->tplobj;
		}

		public function db(){
			if( is_null( $this->pdodb ) )
				$this->pdodb = new mydb();
			return $this->pdodb;
		}

        public function onLogin( $callback ){
            $this->onlogincall  = $callback;
        }
        
        public function setUUID( $uuid ){
            $this->uuid = $uuid;
        }
        
        public function getUUID(){
            return $this->uuid;
        }
        
        public function isLogged(){

//            if( $this->objuserlogg === true )
//                return true;

//            if( $forceloginifanonimous == false )
//                return false;

//            $func = $this->onlogincall;
            return call_user_func( $this->onlogincall ) === true;


//            if( is_string( $func ) || !$func ){
//                $this->objuserredir = $func;
//                return false;
//            }

//            $this->objuserlogg = true;
//            return true;
        }
        
        public function getuserredir(){
            return $this->objuserredir;
        }

		public function i18n(){
			if( is_null( $this->i18n ) )
				$this->i18n = new myi18n();
			return $this->i18n;
		}

		public function cart(){
			if( is_null( $this->cart ) )
				$this->cart = new mycart();
			return $this->cart;
		}

		public function cache(){
			if( is_null( $this->cache ) )
				$this->cache = new mycache();
			return $this->cache;
		}

		public function memcached(){
			if( is_null( $this->memcached ) )
				$this->memcached = new mymemcached();
			return $this->memcached;
		}

		public function intercom(){
			if( is_null( $this->intercom ) )
				$this->intercom = new myintercom();
			return $this->intercom;
		}

		public function session(){
			if( is_null( $this->session ) )
				$this->session = new mysession();
			return $this->session;
		}

		public function vtotal(){
			if( is_null( $this->vtotal ) )
				$this->vtotal = new myvtotal();
			return $this->vtotal;
		}

		public function bcloud(){
			if( is_null( $this->bcloud ) )
				$this->bcloud = new mybcloud();
			return $this->bcloud;
		}

        public function transloadit(){
            if( is_null( $this->transloadit ) )
                $this->transloadit = new mytransloadit();
            return $this->transloadit;
        }

        public function auth(){
            if( is_null( $this->auth ) )
                $this->auth = new myauth();
            return $this->auth;
        }
        
        public function otp(){
            if( is_null( $this->otp ) )
                $this->otp = new myotp();
            return $this->otp;
        }

        public function confirmSMS( $msg = null, $help = null, $title = null, $confirmByDefault = false ){
            return $this->confirm( $msg, $help, $title, '', 1, false, true, $confirmByDefault );
        }

        public function confirmToken( $msg = null, $help = null, $title = null, $confirmByDefault = false ){
            return $this->confirm( $msg, $help, $title, '', 1, true, false, $confirmByDefault );
        }

        public function confirm( $msg = null, $help = null, $title = null, $description = '', $mode = 1, $twofactor = false, $sms = false, $confirmByDefault = false ){

            if( empty( $msg ) )   $msg   = 'Do you confirm your action ?';
            if( empty( $help ) )  $help  = '';
            if( empty( $title ) ) $title = 'Confirmation';

            $postvars = ( isset( $_POST ) ? $_POST : array() );
            foreach( $postvars as $k => $val )
                if( strpos( $k, 'csrf' ) )
                    unset( $postvars[ $k ] );
 
            $route = $this->router->getCurrentRoute();
            $hash  = 'cf' . md5( json_encode( array( $route->getName(), $route->getParams() ) + $postvars ) );

            if( $this->session()->get( $hash . 'confirm', false ) === 1 ){
                $this->session()->delete( $hash . 'confirm' );
                $this->session()->delete( $hash );
                return true;
            }

            $call = ( isset( $this->bef2Fcall ) && is_callable( $this->bef2Fcall ) ) ? call_user_func( $this->bef2Fcall ) : null;

            if( $confirmByDefault === true && $call === false ){
                $this->session()->delete( $hash . 'confirm' );
                $this->session()->delete( $hash );
                return true;
            }

            if( $twofactor && ( $call === false || is_null( $call ) ) ){
                $twofactor = false;
            }

            $uri    = $this->request->getResourceUri();
            $method = $this->request->getMethod();

            $pin   = ( $twofactor == true or $sms == true );
            $pinlabel = '';
            $pinhelp  = '';

            if( $twofactor == true ){
                $pinlabel = 'Two-factor authentication token';
                $pinhelp  = '';
            }elseif( $sms == true ){
                $pinlabel = 'SMS authentication pin';
                $pinhelp  = 'This action requires an sms token. An sms was sent.';
            }

            $this->session()->set( $hash, array( 'uri' => $uri, 'method' => $method, '2f' => intval( $twofactor ), '2s' => intval( $sms ), 'postvars' => $_POST ) );
            $this->ajax()->confirm( $this->urlfor( 'myfwconfirm', array( 'h' => $hash ) ), $msg, $title, $description, $help, $mode, $pin, $pinlabel, $pinhelp )->render();
            $this->stop();
        }

        public function on2Factor( $callback ){
            $this->on2Fcall = $callback;
        }

        public function onDBError( $callback ){
            $this->onDBError = $callback;
        }

        public function before2Factor( $callback ){
            $this->bef2Fcall = $callback;
        }

        public function getBefore2Factor(){
            return call_user_func( $this->bef2Fcall );
        }

        public function onSMS( $callback ){
            $this->onsmscall = $callback;
        }

        // show template
        public function render( $tpl, $vars = array(), $cacheid = null, $cachettl = null, $cachetype = 0, $display = true, $printFooter = true ){

            if( !$this->renderinit ){

                // load view
                $this->view( new \Slim\Views\Twig() );
                $this->view->parserDirectory = __DIR__ . '/my/vendor/twig/twig/lib/Twig';
            }

            // get intance to create custom filters
            $env = $this->view()->getInstance();

            if( !$this->renderinit ){
                $env->getExtension('core')->setDateFormat( 'F j, Y' );

                $env->addFunction( new Twig_SimpleFunction( '_',
                    function( $s, $v = array( "" ) ){
                        if( !is_array( $v ))
                            $v = array( $v );
                            array_unshift( $v, gettext( $s ) );
                            return call_user_func_array( 'sprintf', $v );
                        }));

                $env->addFunction( new Twig_SimpleFunction( '_n', '_n' ) );
                $env->addFunction( new Twig_SimpleFunction( 'd', 'var_export' ) );

                $env->addFunction( new Twig_SimpleFunction( 'c', function( $value ) {
                    return $this->config( $value );
                }, array( 'is_safe' => array( 'html' ) ) ) );

                $env->addFilter( new Twig_SimpleFilter( 'cdn', array( 'myfilters', 'cdn' )
                , array( 'is_safe' => array( 'html' ) ) ) );
                
                $env->addFilter( new Twig_SimpleFilter( '*',
                    function( $f  ){
                        if( is_callable( array( 'myfilters', $f ) ) ){
                            $args = func_get_args();
                            array_shift( $args );
                            return call_user_func_array( array( 'myfilters', $f ), $args );
                        }
                        return '';
                    }
                ) );

                $env->addFunction( new Twig_SimpleFunction( 'urlFor',
                    function( $action, $params = array() ){
                        try{
                            return $this->urlFor( $action, is_array( $params ) ? $params : array( $params ) );
                        }catch( RuntimeException $e ){
                            return '';
                        };
                }));

                $env->addExtension( new Twig_Extension_StringLoader() );

                $engine = new MarkdownEngine\MichelfMarkdownEngine();

                $env->addExtension(new MarkdownExtension($engine));

                if( $this->config( 'templates.cachepath' ) )
                    $env->setCache( $this->config( 'templates.cachepath' ) );

                // add system path
                $env->getLoader()->addPath( __DIR__ . '/my/', 'my' );

                $this->renderinit = true;
            }

            $output = $env->render( $tpl . '.tpl', $vars );

            // optionally add to cache
//            if( ( !is_null( $cacheid ) || $this->iscacheable() ) && $this->request()->isGet() && !$this->ishttps() && empty( $this->forms ) ){
//                $this->cache()->set( $cachetype, 'tpl' . $this->request()->getPath() . $cacheid, $output, $cachettl );
//            }

            if( $display == false ){
                return $output;// . $this->printFooter( $printFooter, 'O' );
            }

            if( $display == true ){
                echo $output; //, $this->printFooter( $printFooter, 'O' );                    
            }
		}

		// try to render cache if available
		public function renderCached( $cacheid = null, $cachetype = 0, $printFooter = true ){

			$cacheid = 'tpl' . $this->request()->getPath() . $cacheid;

			// check if cache is supported and cache timer is active for this specific page
			if( $this->request()->isGet() && !$this->ishttps() && $this->cache()->exists( $cachetype, $cacheid ) ){
				echo $this->cache()->get( $cachetype, $cacheid ), $this->printFooter( $printFooter, 'C' );
				return true;
			}
			return false;
		}
        
        public function urlForAjax( $action, $options = array(), $msg = false ){
            return "myfwsubmit('" . $this->urlFor( $action, $options ) . "'" . ( is_string( $msg ) ? ( ",'" . $msg . "'" ) : '' ) . ")";
        }

        public function urlForAjaxActions( $urls, $code, $keys, $default ){
            return array( 'type' => 'ajaxactions', 'urls' => $urls, 'code' => $code, 'keys' => $keys, 'default' => $default );
        }

        public function urlForAjaxForm( $formname, $action, $submitbutton = '', $msg = 'Loading ...', $delay = 0 ){
            return "myfwformsubmit('" . $formname . "','','','" . $formname . $submitbutton . "','" . $msg . "','" . $action . "'," . intval( $delay ) .  ")";
        }

        public function urlForWindow( $action, $options = array() ){
            return "myfwopen('http://" . $this->config( 'app.hostname' ) . $this->urlFor( $action, $options ) . "')";
        }

		private function printFooter( $print = true, $appendString = '' ){
			return $print ? "\n<!-- " . round( microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 2 ) . 's ' . $appendString . ' -->' : '';
		}

		public function ishttps(){
            if( is_null( $this->ishttps ) )
    			$this->ishttps = ( ( isset( $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] ) && $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] === 'https' ) || $this->request()->getScheme() === 'https' );

            return $this->ishttps;
		}
		
		public function saveFile( $path, $content, $minimize = false ){

            $filesaved = $app->config( 'savefile.enable' ) !== 0 ? file_put_contents( $path, $minimize ? str_replace(array("\r", "\n", "\t", "\v"), '', $content ) : $content ) : false;

			$this->log()->debug( "myfw::saveFile,path:" . $path . ',saved:' . intval( $filesaved ) );

			return $filesaved;
		}
		
		public function cron( $match, $callback = '' ){
			global $argv;

            if( ! is_array( $argv ) )
                return false;

			if( is_string( $match ) && isset( $argv[1] ) && is_string( $argv[1] ) && $argv[1] === $match && is_callable( $callback ) )
                return call_user_func( $callback );

            if( is_callable( $match ) )
                return call_user_func( $match );
		}

        public function run(){
            
            if( APP_WEBMODE )
                parent::run();
            
            if( !is_null( $this->pusher ) )
                $this->pusher->sendall();
        }

        public function redirectjs( $url, $close = false ){
            $this->render( '@my/jsredir', array( 'url' => $url, 'winclose' => $close ) );
            $this->stop();
        }
	}
	
	// debug function alias
	function d( $x ){
		die( var_export( $x ) );
	}

    function _n( $s, $p = null, $i = null, $o1 = null, $o2 = null ){

        // singular/plural
        if( is_string( $p ) && is_numeric( $i ) ){

            if( intval( $i ) === 1 ){
                $str = gettext( $s );
                $arr = is_null( $o1 ) ? array("") : ( is_array( $o1 ) ? $o1 : array( $o1 ) );
            }else{
                $str = gettext( $p );
                $arr = is_null( $o2 ) ? ( is_null( $o1 ) ? array("") : ( is_array( $o1 ) ? $o1 : array( $o1 ) ) ) : ( is_array( $o2 ) ? $o2 : array( $o2 ) );
            }

        // simple with/without variables
        }else{
            $str = gettext( $s );
            $arr = is_array( $p ) ? $p : array("");
        }

        array_unshift( $arr, $str );

        return call_user_func_array( 'sprintf', $arr );
    }        

    function vksprintf( $string = '', $vars = array() ) {
        if ( is_string( $string ) && is_array( $vars ) && count( $vars ) ) {
            foreach( $vars as $key => $value )
                $string = str_replace( '#' . $key, $value, $string );
        }
        return $string;
    }

    function ip_in_range( $ip, $range ) {
        if ( strpos( $range, '/' ) == false ){
            $range .= '/32';
        }

        // $range is in IP/CIDR format eg 127.0.0.1/24
        list( $range, $netmask ) = explode( '/', $range, 2 );
        $range_decimal = ip2long( $range );
        $ip_decimal = ip2long( $ip );
        $wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
        $netmask_decimal = ~ $wildcard_decimal;
        return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
    }

    function ip_in_rangelist( $ip, $rangelist ){
        foreach( explode( ';', $rangelist ) as $range ){
            if( ip_in_range( $ip, $range ) ){
                return true;
            }
        }
        return false;
    }

    function iscidr(){
        $app = \Slim\Slim::getInstance();

        if( !ip_in_rangelist( $app->request->getIp(), $app->config( 'app.cidr' ) ) )
            $app->pass();
    }

    // set session authentication
    function islogged() {
        $app = \Slim\Slim::getInstance();

        if( !$app->isLogged() ){
            $app->request->isAjax() ? $app->ajax()->msgWarning( 'Redirecting ...', 'Session expired', array( 'openDuration' => 0, 'sticky' => true ) )->redirect( $app->config( 'app.logouturl' ) )->render() : $app->redirect( $app->config( 'app.logouturl' ) );
            $app->stop();
        }
    }

    // check https protocol
    function ishttps(){
        $app = \Slim\Slim::getInstance();
        if( $app->config( 'ishttps.enable' ) !== 0 && !$app->ishttps() )
            $app->redirect( "https://" . $app->config( 'app.hostname' ) . $_SERVER['REQUEST_URI'] );
    }

    // check http protocol
    function ishttp(){
        $app = \Slim\Slim::getInstance();
        if( $app->config( 'ishttp.enable' ) !== 0 && $app->ishttps() )
            $app->redirect( "http://" . $app->config( 'app.hostname' ) . $_SERVER['REQUEST_URI'] );
    }

    // check if referrer exists and belong to current host
    function isreferrer(){
        $app = \Slim\Slim::getInstance();
        if( strpos( strtolower( $app->request->getReferer() ), strtolower( $app->config( 'app.hostname' ) ) ) === false )
            $app->pass();
    }

    // check ajax post
    function isajax(){
        $app = \Slim\Slim::getInstance();
        if( $app->config( 'isajax.enable' ) !== 0 && !$app->request->isAjax() )
            $app->pass();
        else
            $app->setajaxmode();            
    }

    function iscache(){
		$app = \Slim\Slim::getInstance();
        if( $app->config( 'iscache.enable' ) !== 0 ){
            $app->renderCached() ? $app->stop() : $app->setcacheable();
        }
    }

    // check developmentmode
    function isdevelopment(){
        $app = \Slim\Slim::getInstance();
        if( $app->config( 'app.isdevelopment' ) !== true )
            $app->pass();
    }

    function israte() {
        $app = \Slim\Slim::getInstance();

        if( $app->config( 'app.israte' ) !== false && !$app->memcached()->ratevalid() ){
            if( $app->request->isAjax() ){
                $app->ajax()->msgWarning( 'Too much requests. Please wait ' . $app->memcached()->ratelocktimeout() . 's and try again.', 'Rate limit protection' )->render();
            }
            $app->stop();
        }
    }
