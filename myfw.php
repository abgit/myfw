<?php

    define( "APP_START",         round( microtime( true ) * 1000 ) );
    define( "APP_WEBMODE",       !isset( $_SERVER['argc'] ) );
    define( "APP_CACHEAPC",      0 );
    define( "APP_CACHEREDIS",    1 );

    // slim warnings on cron
    if( ! APP_WEBMODE ){
        if( ! isset( $_SERVER['REQUEST_METHOD'] ) ) $_SERVER['REQUEST_METHOD'] = '';
        if( ! isset( $_SERVER['REMOTE_ADDR'] ) )    $_SERVER['REMOTE_ADDR'] = '';
        if( ! isset( $_SERVER['REQUEST_URI'] ) )    $_SERVER['REQUEST_URI'] = '';
        if( ! isset( $_SERVER['SERVER_NAME'] ) )    $_SERVER['SERVER_NAME'] = '';
        if( ! isset( $_SERVER['SERVER_PORT'] ) )    $_SERVER['SERVER_PORT'] = '';
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

        private $forms       = array();
        private $mailer      = null;
        private $client      = null;
        private $cart        = null;
        private $tplobj      = null;
        private $pdodb       = null;
        private $renderinit  = false;
        private $redisro     = null;
        private $redisrw     = null;
        private $rules       = null;
        private $vtotal      = null;
        private $session     = null;
        private $bcloud      = null;
        private $transloadit = null;
        private $loginit     = null;
        private $i18n        = null;
        private $cache       = null;

        public function __construct( $arr = array() ){
            parent::__construct( $arr );
        }

        public function setConditions( $cond ){
            \Slim\Route::setDefaultConditions( $cond );
        }

        public function log(){
            if( $this->config( 'log.init' ) && is_null( $this->loginit ) ){
                $this->config( 'log.writer', new mylog() );
                $this->log->setEnabled( true );
                $this->log->setLevel( $this->config( 'log.level' ) );
                $this->loginit = true;
            }

            return $this->log;
        }

        public function rules(){
            if( is_null( $this->rules ) )
				$this->rules = new myrules();
			return $this->rules;
		}

		// get form object
		public function form( $formname = 'f' ){
			if( ! isset( $this->forms[ $formname ] ) )
				$this->forms[ $formname ] = new myform( $formname );
			return $this->forms[ $formname ];
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

		public function client(){
			if( is_null( $this->client ) && ( $client = $this->config( 'client.global' ) ) )
				$this->client = new $client();
			return $this->client;
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

        // show template
        public function render( $tpl, $vars = array(), $cacheid = null, $cachettl = null, $cachetype = APP_CACHEAPC, $display = true, $printFooter = true ){

            if( !$this->renderinit ){

                // load view
                $this->view( new \Slim\Views\Twig() );
                $this->view->parserDirectory = __DIR__ . '/my/vendor/twig/twig/lib/Twig';
            }

            // get intance to create custom filters
            $env = $this->view()->getInstance();

            if( !$this->renderinit ){
                $env->getExtension('core')->setDateFormat( 'F j, Y' );
                $env->addFunction( new Twig_SimpleFunction( '_', function($s,$v=array("")){if(!is_array($v))$v=array($v);array_unshift($v,gettext($s));return call_user_func_array('sprintf', $v );}));
                $env->addFunction( new Twig_SimpleFunction( '_n', '_n' ) );  
                $env->addFilter( new Twig_SimpleFilter( '*', function( $f, $args ){ return is_callable( array( 'myfilters', $f ) ) ? call_user_func( array( 'myfilters', $f ), $args ) : ''; }, array('is_safe' => array('html') ) ) );

                if( $this->config( 'templates.cachepath' ) )
                    $env->setCache( $this->config( 'templates.cachepath' ) );

                // add system path
                $env->getLoader()->addPath( __DIR__ . '/my/', 'my' );

                // add global tpl obj
                if( $this->config( 'templates.global' ) )
                    $env->addGlobal( 'tpl', $this->tpl() );

                $this->renderinit = true;
            }

            $output = $env->render( $tpl . '.tpl', $vars );

            if( $this->config( 'templates.srcpreg' ) )
                $output = preg_replace( '~(href|src)=(["\'])(?!#)(/)?(?!http(s)?://)([^"\']+)(' . $this->config( 'templates.srcpregext' ). ')(["\'])~i', '$1="' . $this->config( 'templates.srcpregdomain' ) . '$5$6"', $output);

            // optionally add to cache
            if( !is_null( $cacheid ) && $this->request()->isGet() && !$this->ishttps() && empty( $this->forms ) ){
                $this->cache()->set( $cachetype, $this->request()->getScheme() . 'tpl' . $cacheid, $output, $cachettl );
            }

            if( $display == false ){
                return $output . $this->printFooter( $printFooter, 'O' );
            }

            if( $display == true ){
                echo $output, $this->printFooter( $printFooter, 'O' );                    
            }
		}

		// try to render cache if available
		public function renderCached( $cacheid, $cachetype = APP_CACHEAPC, $printFooter = true ){

			$cacheid = $this->request()->getScheme() . 'tpl' . $cacheid;

			// check if cache is supported and cache timer is active for this specific page
			if( $this->request()->isGet() && !$this->ishttps() && $this->cache()->exists( $cachetype, $cacheid ) ){
				echo $this->cache()->get( $cachetype, $cacheid ), $this->printFooter( $printFooter, 'C' );
				return true;
			}
			return false;
		}

        public function renderAjax( $arr = array() ){
            if( $this->request->isAjax() ){
                print( json_encode( $arr ) );
                return true;
            }
            return false;
        }

		private function printFooter( $print = true, $appendString = '' ){
			return $print ? "\n<!-- " . ( ( round(microtime(true) * 1000)-APP_START) /1000 ) . 's on s' . substr( $_SERVER["SERVER_NAME"], 3, 2 ) . ' ' . $appendString . ' -->' : '';
		}

		public function ishttps(){
			return ( ( isset( $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] ) && $_SERVER[ 'HTTP_X_FORWARDED_PROTO' ] == 'https' ) || $this->request()->getScheme() === 'https' );
		}
		
		public function saveFile( $path, $content, $minimize = false ){

            $filesaved = $app->config( 'savefile.enable' ) !== false ? file_put_contents( $path, $minimize ? str_replace(array("\r", "\n", "\t", "\v"), '', $content ) : $content ) : false;

			$this->log()->debug( "myfw::saveFile,path:" . $path . ',saved:' . intval( $filesaved ) );

			return $filesaved;
		}
		
		public function cron( $match, $callback ){
			global $argv;

			return ( is_array( $argv ) && isset( $argv[1] ) && is_string( $argv[1] ) && is_string( $match ) && $argv[1] === $match ) ? call_user_func( $callback ) : false;
		}

        public function run(){
            return APP_WEBMODE ? parent::run() : false;
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

	// set session authentication
	function islogged() {
		$app = \Slim\Slim::getInstance();
		if ( ! $app->client()->isLogged() )
			$app->redirect( '/login' . $app->request()->getResourceUri() );
	}

	// check https protocol
	function ishttps(){
		$app = \Slim\Slim::getInstance();
		if( $app->config( 'ishttps.enable' ) !== false && ! $app->ishttps() )
			$app->redirect( "https://" . $app->config( 'app.hostname' ) . $_SERVER['REQUEST_URI'] );
	}

	// check http protocol
	function ishttp(){
		$app = \Slim\Slim::getInstance();
		if( $app->config( 'ishttp.enable' ) !== false && $app->ishttps() )
			$app->redirect( "http://" . $app->config( 'app.hostname' ) . $_SERVER['REQUEST_URI'] );
	}

    // check ajax post
    function isajax(){
		$app = \Slim\Slim::getInstance();
        if( $app->config( 'isajax.enable' ) !== false && $app->request->isAjax() )
            $app->pass();
    }
