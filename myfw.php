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
                $env->addFilter( new Twig_SimpleFilter( 'm', function($string,$showSymbol=true){ return ( ( $showSymbol ? '&euro; ' : '' ) . round($string, 2) ); }, array('is_safe' => array('html') ) ) );
                $env->addFilter( new Twig_SimpleFilter( 'url', function($string){ return myrules::fhost( $string); } ) );
                $env->addFilter( new Twig_SimpleFilter( 'order', function($string){ switch( intval( $string ) ){case 1: return '1st'; case 2: return '2nd'; case 3: return '3rd'; default: return $string . 'th'; } } ) );
                $env->addFilter( new Twig_SimpleFilter( 't', function($string,$chars=10,$rep='...'){ return $chars > strlen($string) ? $string : substr($string, 0, $chars) . $rep; } ) );
                $env->addFilter( new Twig_SimpleFilter( 'rnumber', function($string){$string=(0+str_replace(",","",$string));if(!is_numeric($string))return false;if($string>1000000000000)return round(($string/1000000000000),1).' trillion';elseif($string>1000000000)return round(($string/1000000000),1).' billion';elseif($string>1000000)return round(($string/1000000),1).' million';elseif($string>1000)return round(($string/1000),1).' thousand';return number_format($string);}));
                $env->addFilter( new Twig_SimpleFilter( 'bcloudname', function($string){ $b = new mybcloud(); $b = $b->getCategoriesList(); return isset( $b[$string] ) ? $b[$string][0] : 'unknown'; } ) );
                $env->addFilter( new Twig_SimpleFilter( 'gravatar', function( $email, $s = 80, $d = 'mm', $r = 'g' ){ return '//www.gravatar.com/avatar/' . md5( strtolower( trim( $email ) ) ) . "?s=$s&d=$d&r=$r"; } ) );
                $env->addFilter( new Twig_SimpleFilter( 'md5', function($string){return md5( $string );}));
                $env->addFilter( new Twig_SimpleFilter( 'alphaindex', function($string){return md5( $string );}));
                $env->addFilter( new Twig_SimpleFilter( 'floatval', function($string){return is_array($string) ? array_map( 'floatval', $string ) : floatval( $string );}));
                $env->addFilter( new Twig_SimpleFilter( 'intval', function($string){return is_array($string) ? array_map( 'intval', $string ) : intval( $string );}));
                $env->addFilter( new Twig_SimpleFilter( 'statecolor', function($string){if ( intval( $string) > 0 ){return '#090';}if( intval( $string) < 0 ){return '#F00';}return '#960';}));
                $env->addFilter( new Twig_SimpleFilter( 'extension', function($string){return strtolower(pathinfo($string, PATHINFO_EXTENSION));}));
                $env->addFilter( new Twig_SimpleFilter( 'domain', function($string){$url=parse_url($string); return isset($url['host'])?$url['host']:'';}));
                $env->addFilter( new Twig_SimpleFilter( 'xss', function($string){$obj = new myrules(); return $obj->xss( $string );}, array( 'is_safe' => array( 'html' ) ) ) );
                $env->addFilter( new Twig_SimpleFilter( 'ago', function($datetime, $full = 0){$now = new DateTime;$ago = new DateTime($datetime);$diff = $now->diff($ago);$diff->w = floor($diff->d / 7);$diff->d -= $diff->w * 7;$string = array( 'y' => 'year', 'm' => 'month', 'w' => 'week', 'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second' );foreach ($string as $k => &$v) {if ($diff->$k) {$v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');} else {unset($string[$k]);}}if (!$full) $string = array_slice($string, 0, 1);return $string ? implode(', ', $string) . ' ago' : 'just now';}));
                $env->addFunction( new Twig_SimpleFunction( '_', function($s,$v=array("")){if(!is_array($v))$v=array($v);array_unshift($v,gettext($s));return call_user_func_array('sprintf', $v );}));
                $env->addFunction( new Twig_SimpleFunction( '_n', '_n' ) );  

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
                $output = preg_replace( '~(href|src)=(["\'])(?!#)(/)?(?!http(s)?://)([^ ]+)(' . $this->config( 'templates.srcpregext' ). ')(["\'])~i', '$1="' . $this->config( 'templates.srcpregdomain' ) . '$5$6"', $output);

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
