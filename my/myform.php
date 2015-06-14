<?php

class myform{

    private $formname;
    private $elements;
    private $counter;
    private $valuesdefault;
    private $errors;
    private $errorsCustom;
    private $submitMessage;
    private $warningMessage;
    private $hide;
    private $action;
    private $target;
    private $wasValid;
    private $renderaction;
    private $rendersubmit;
    private $customRules;
    private $disabled;
    private $preventmsg;
    private $isajax = false;
    private $modal = array();
    private $closebutton = true;
    private $closebuttonsettings;
    private $footer = false;

    private $csrfname;
    private $csrfinit = false;
	
    // captcha
    private	$font;
    private $signature;
    private $perturbation;
    private $imgwid;
    private $imghgt;
    private $numcirc;
    private $numlines;
    private $ncols;

    private $app;

    public function __construct( $name ){

        $this->formname         = $name;
        $this->elements         = array();
        $this->counter          = 1;
        $this->valuesdefault    = array();
        $this->errors           = array();
        $this->errorsCustom     = array();
        $this->submitMessage    = '';
        $this->warningMessage   = '';
        $this->hide             = false;
        $this->action           = $_SERVER['REQUEST_URI'];
        $this->target           = false;
        $this->wasValid         = null;
        $this->renderaction     = true;
        $this->rendersubmit     = true;
        $this->customRules      = array();
        $this->disabled         = array();
        $this->csrfname         = $name . 'csrf';
        $this->preventmsg       = 'undefined';

        // captcha
        $this->font             = dirname( __FILE__ ) . '/myform.ttf';	
        $this->signature        = ". -     /   _   (   +";
        $this->perturbation     = 1.0; // bigger numbers give more distortion; 1 is standard
        $this->imgwid           = 200; // image width, pixels
        $this->imghgt           = 100; // image height, pixels
        $this->numcirc          = 4;   // number of wobbly circles
        $this->numlines         = 4;   // number of lines
        $this->ncols            = 20;  // foreground or background cols

        $this->app = \Slim\Slim::getInstance();
        $this->isajax = $this->app->request->isAjax();
    }

    public function & clear(){
        $this->__construct( $this->formname );
        return $this;
    }

    public function & clearPost(){
        $_POST = array();
        return $this;
    }

    public function & getName(){
        return $this->formname;
    }

    public function & setModal( $title, $class = 'modal-lg', $icon = 'icon-paragraph-justify2', $static = true, $width = '', $closebutton = true ){
        $this->modal = array( 'id' => 'mod' . $this->formname, 'title' => $title, 'class' => $class, 'icon' => $icon, 'static' => $static, 'width' => $width, 'closebutton' => $closebutton );
        return $this;
    }

    public function & hideCloseButton(){
        $this->closebutton = false;
        return $this;
    }

    public function & setCloseButton( $label, $class = 'default' ){
        $this->closebuttonsettings = array( 'label' => $label, 'class' => $class );
        return $this;
    }

    public function & addText( $name, $label = '', $help = '', $bitcoin = false ){
        $this->elements[ $name ] = array( 'type' => 'text', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'rules' => array(), 'filters' => array(), 'options' => array(), 'help' => $help, 'bitcoin' => $bitcoin );
        return $this;
    }

    public function & addBitcoin( $name, $label = '', $help = '', $currencies = array( 'USD', 'EUR' ) ){
        $this->elements[ $name ] = array( 'type' => 'bitcoin', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'rules' => array(), 'filters' => array(), 'options' => array(), 'help' => $help, 'currencies' => $currencies );
        return $this;
    }

    public function & addHidden( $name ){
        $htmlname = ( $name{0} == '@' ? substr( $name, 1 ) : $this->formname . $name );
        $name     = ( $name{0} == '@' ? substr( $name, 1 ) : $name );
        $this->elements[ $name ] = array( 'type' => 'hidden', 'valuetype' => 'simple', 'name' => $htmlname, 'label' => '', 'rules' => array(), 'filters' => array(), 'options' => array() );	
        return $this;
    }

    public function & addCheckbox( $name, $label ){
        $this->elements[ $name ] = array( 'type' => 'checkbox', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'rules' => array(), 'filters' => array(), 'options' => array() );	
        return $this;
    }

    public function & addTextarea( $name, $label, $help = '', $rows = 2 ){
        $this->elements[ $name ] = array( 'type' => 'textarea', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'rows' => $rows, 'rules' => array(), 'filters' => array(), 'options' => array(), 'help' => $help );
        return $this;
    }

    public function & addPassword( $name, $label ){
        $this->elements[ $name ] = array( 'type' => 'password', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'rules' => array(), 'filters' => array(), 'options' => array() );
        return $this;
    }

    public function & addBitcoinQrCode( $name, $label, $help = '', $width = 200, $key = false, $acc = '' ){
        $this->elements[ $name ] = array( 'type' => 'bitcoinqrcode', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'width' => $width, 'rules' => array(), 'filters' => array(), 'options' => array(), 'help' => $help, 'key' => $key, 'acc' => $acc );
        return $this;
    }


    public function & addStaticImage( $name, $label, $help = '', $width = '', $height = '', $align = '' ){
        $htmlname = ( $name{0} == '@' ? substr( $name, 1 ) : $this->formname . $name );
        $name     = ( $name{0} == '@' ? substr( $name, 1 ) : $name );
        $this->elements[ $name ] = array( 'type' => 'staticimage', 'valuetype' => 'simple', 'name' => $htmlname, 'label' => $label, 'width' => $width, 'height' => $height, 'rules' => array(), 'filters' => array(), 'options' => array(), 'align' => $align, 'help' => $help );
        return $this;
    }

    public function & addStaticMovie( $name, $label, $help = '', $width = '', $height = ''  ){
        $this->elements[ $name ] = array( 'type' => 'staticmovie', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'width' => $width, 'height' => $height, 'rules' => array(), 'filters' => array(), 'options' => array(), 'help' => $help );
        return $this;
    }

    public function & focus( $element ){
        $this->app->ajax()->focus( '#' . $this->formname . $element );
        return $this;
    }

    public function & addGroup( $size = 2, $total = null ){
        
        switch( $size ){
            case 4: $css = 'col-md-3 col-xs-6'; break;
            case 3: $css = 'col-md-4 col-xs-6'; break;
            default:$css = 'col-md-6 col-xs-9'; break;
        }

        if( is_null( $total ) )
            $total = $size;

        $this->elements[ 'grp' . $this->counter++ ] = array( 'type' => 'formgroup', 'total' => $total, 'css' => $css, 'rules' => array(), 'filters' => array() );
        return $this;
    }

    public function & addHeader( $title, $description = '', $icon = 'icon-books' ){
        $this->elements[ 'hdr' . $this->counter++ ] = array( 'type' => 'formheader', 'title' => $title, 'description' => $description, 'icon' => $icon, 'rules' => array(), 'filters' => array() );
        return $this;
    }

    public function & setFooter(){
        $this->footer = true;
        return $this;
    }

    public function & addMessage( $title, $description = '', $css = 'info', $buttonlabel = '', $buttononclick = '', $buttonhref = '', $buttonicon = '', $buttoncss = '' ){
        $this->elements[ 'mss' . $this->counter++ ] = array( 'type' => 'message', 'title' => $title, 'description' => $description, 'rules' => array(), 'filters' => array(), 'css' => $css, 'buttonlabel' => $buttonlabel, 'buttononclick' => $buttononclick, 'buttonhref' => $buttonhref, 'buttonicon' => $buttonicon, 'buttoncss' => $buttoncss );
        return $this;
    }    

    public function & addStatic( $name, $label = '', $help = '', $showvalue = true ){
        $this->elements[ $name ] = array( 'type' => 'static', 'name' => $name, 'label' => $label, 'rules' => array(), 'filters' => array(), 'help' => $help, 'showvalue' => $showvalue );
        return $this;
    }    

    public function & addStaticMessage( $message = '', $title = '', $icon = '', $date = '' ){
        $this->elements[ 'smm' . $this->counter++ ] = array( 'type' => 'staticmessage', 'message' => $message, 'title' => $title, 'icon' => $icon, 'date' => $date, 'rules' => array(), 'filters' => array() );
        return $this;
    }    

    public function & addGrid( $obj ){
        $name = is_string( $obj ) ? $obj : ( 'gri' . $this->counter++ );
        $this->elements[ $name ] = array( 'type' => 'grid', 'obj' => $obj );
        return $this;
    }    

    public function & addStatistics( $obj ){
        $name = is_string( $obj ) ? $obj : ( 'sts' . $this->counter++ );
        $this->elements[ $name ] = array( 'type' => 'statistics', 'obj' => $obj );
        return $this;
    }    

    public function & setGrid( $name, $obj ){
        if( isset( $this->elements[ $name ] ) )
            $this->elements[ $name ] = $obj;
        return $this;
    }

    public function & addCustom( $obj ){
        $this->elements[ 'ctm' . $this->counter++ ] = array( 'type' => 'custom', 'obj' => $obj, 'rules' => array(), 'filters' => array() );
        return $this;
    }    

    public function & addCalendar( $id, $onclick = '', $onclickloadingmsg = '' ){
        $this->elements[ $id ] = array( 'type' => 'calendar', 'id' => 'cal' . $this->formname . $id, 'ce' => $onclick, 'cm' => $onclickloadingmsg, 'rules' => array(), 'filters' => array() );

        if( $this->isajax )
            $this->app->ajax()->calendar( '#cal' . $this->formname . $id );
            
        return $this;
    }    

    public function & addChat( $id, $urlmsg, $urlupdate = null, $currentelementid = null, $options = array(), $wait = array() ){

        if( !empty( $urlupdate ) )
            $this->app->ajax()->interval( $urlupdate, 4000, 1 ); 

        if( is_int( $currentelementid ) ){
            $this->app->session()->set( 'myfwchat' . $id, $currentelementid );
        }

        // default options
        $options = $options + array( 'template_id' => '', 'width' => 0, 'height' => 0, 'steps' => array() );

        if( $this->app->config( 'transloadit.driver' ) === 'heroku' ){
            $this->apikey    = getenv( 'TRANSLOADIT_AUTH_KEY' );
            $this->apisecret = getenv( 'TRANSLOADIT_SECRET_KEY' );
        }else{
            $this->apikey    = $this->app->config( 'transloadit.k' );
            $this->apisecret = $this->app->config( 'transloadit.s' );
        }        

        $params = array( 'auth' => array( 'key'     => $this->apikey,
                                          'expires' => gmdate('Y/m/d H:i:s+00:00', strtotime('+1 hour') ) ) );

        if( isset( $options[ 'template_id' ] ) && !empty( $options[ 'template_id' ] ))
            $params[ 'template_id' ] = $options[ 'template_id' ];

        if( isset( $options[ 'steps' ] ) && !empty( $options[ 'steps' ] ))
            $params[ 'steps' ] = $options[ 'steps' ];

        $params = json_encode( $params, JSON_UNESCAPED_SLASHES );

        $this->elements[ $id ] = array( 'type' => 'chat', 'id' => $id, 'wait' => $wait, 'url' => $urlmsg, 'rules' => array(), 'filters' => array(), 'options' => array( 'params' => $params, 'signature' => hash_hmac('sha1', $params, $this->apisecret ) ) );
        return $this;
    }

    // special elements
    public function & addEmail( $name, $label = 'Email' ){
        $this->elements[ $name ] = array( 'type' => 'text', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'rules' => array( 'email' => 'Email is not valid' ), 'filters' => array() );
        return $this;
    }

    public function & addMonth( $name, $label ){
        $options = array();
        foreach (range(1, 12) as $number)
            $options[ sprintf('%02d', $number ) ] = sprintf('%02d', $number ) . ' - ' . date("F", mktime(0, 0, 0, $number, 10));

        return $this->addSelect( $name, $label, $options );
    }

    public function & addYear( $name, $label ){
        $options = array();
        foreach( range( date("Y"), date("Y") + 20 ) as $number)
            $options[ $number ] = $number;

        return $this->addSelect( $name, $label, $options );
    }


    public function & addDay( $name, $label, $labelnow = 'now', $labeltomorrow = 'tomorrow', $days = 10 ){

        $now = new DateTime();
        $schedule = array( 0 => $labelnow, 1 => $labeltomorrow );

        if( $days > 1 ){
            $schedule[ 2 ] = $now->modify( '+2 day' )->format( 'd F' );

            for($i = 3; $i < $days; $i++ )
                $schedule[ $i ] = $now->modify( '+1 day' )->format( 'd F' );
        }

        return $this->addSelect( $name, $label, $schedule );
    }


    private function & filterOptions( & $options, $optionsFilter = array() ){

        // options filter types
        if( isset( $optionsFilter[ 'type' ] ) ){
            $res = array();
            switch( $optionsFilter[ 'type' ] ){
                case 'implode': foreach( $options as $o => $arr )
                                    $res[ $o ] = implode( ' ', $arr );
                                break;
                case 'explode': $res = isset( $optionsFilter[ 'delimiter' ] ) ? explode( $optionsFilter[ 'delimiter' ], $options ) : array();
                                break;
                case 'list' :   foreach( explode( ';', $options ) as $el ){
                                    $pat = explode( ',', $el );
                                    if( isset( $pat[ 0 ] ) && isset( $pat[ 1 ] ) )
                                        $res[ $pat[ 0 ] ] = $pat[ 1 ];
                                }
                                break;
                case 'for':     if( isset( $optionsFilter[ 'min' ] ) && isset( $optionsFilter[ 'max' ] ) && isset( $optionsFilter[ 'step' ] ) )
                                    for( $i = $optionsFilter[ 'min' ]; $i<=$optionsFilter[ 'max' ]; $i += $optionsFilter[ 'step' ] )
                                        $res[ $i ] = $i;
                                break;
            }
            return $res;
        }

        return $options;
    }

    public function & addSelect( $name, $label, $options = array(), $optionsFilter = null, $help = '' ){
        $options = $this->filterOptions( $options, $optionsFilter );
        $rules[ 'selectvalid' ] = array( $label . ' is not valid', $options );

        $htmlname = ( $name{0} == '@' ? substr( $name, 1 ) : $this->formname . $name );
        $name     = ( $name{0} == '@' ? substr( $name, 1 ) : $name );
        $this->elements[ $name ] = array( 'type' => 'select', 'valuetype' => 'simple', 'name' => $htmlname, 'label' => $label, 'rules' => array(), 'filters' => array(), 'options' => $options, 'help' => $help );
        return $this;
    }

    public function & addMultiple( $name, $label, $options = array(), $optionsFilter = null, $help = '' ){
        $options = $this->filterOptions( $options, $optionsFilter );
        $this->elements[ $name ] = array( 'type' => 'multiple', 'valuetype' => 'multiple', 'name' => $name, 'label' => $label, 'rules' => array(), 'filters' => array(), 'options' => $options, 'help' => $help );
        return $this;
    }

    public function & addCheckboxgroup( $name, $label, $options = array(), $optionsFilter = null, $settings = array(), $help = '' ){
        $options = $this->filterOptions( $options, $optionsFilter );
        $this->elements[ $name ] = array( 'type' => 'checkboxgroup', 'valuetype' => 'group', 'name' => $name, 'label' => $label, 'rules' => array(), 'filters' => array(), 'options' => $options, 'settings' => $settings, 'help' => $help );
        return $this;
    }

    public function & addTransloadit( $name, $label, $options = array(), $settings = array(), $help = '' ){

        // default options
        $options = $options + array( 'template_id' => '', 'width' => 0, 'height' => 0, 'steps' => array(), 'mode' => 'image' );

        if( $this->app->config( 'transloadit.driver' ) === 'heroku' ){
            $this->apikey    = getenv( 'TRANSLOADIT_AUTH_KEY' );
            $this->apisecret = getenv( 'TRANSLOADIT_SECRET_KEY' );
        }else{
            $this->apikey    = $this->app->config( 'transloadit.k' );
            $this->apisecret = $this->app->config( 'transloadit.s' );
        }        

        $params = array( 'auth' => array( 'key'     => $this->apikey,
                                          'expires' => gmdate('Y/m/d H:i:s+00:00', strtotime('+1 hour') ) ) );

        if( isset( $options[ 'template_id' ] ) && !empty( $options[ 'template_id' ] ))
            $params[ 'template_id' ] = $options[ 'template_id' ];

        if( isset( $options[ 'steps' ] ) && !empty( $options[ 'steps' ] ))
            $params[ 'steps' ] = $options[ 'steps' ];

        $params = json_encode( $params, JSON_UNESCAPED_SLASHES );

        $this->elements[ $name ] = array( 'type' => 'transloadit', 'valuetype' => 'transloadit', 'name' => $name, 'label' => $label, 'rules' => array(), 'filters' => array(), 'settings' => $settings, 'options' => array( 'params' => $params, 'signature' => hash_hmac('sha1', $params, $this->apisecret ), 'width' => $options['width'], 'height' => $options['height'], 'mode' => $options[ 'mode' ] ), 'help' => $help );
        return $this;
    }

    public function & addSeparator(){
        $this->elements[ 'sep' . $this->counter++ ] = array( 'type' => 'separator', 'rules' => array(), 'filters' => array() );
        return $this;
    }

    public function & addSeparatorLine(){
        $this->elements[ 'sep' . $this->counter++ ] = array( 'type' => 'separatorline', 'rules' => array(), 'filters' => array() );
        return $this;
    }

    public function & addSeparatorHR(){
        $this->elements[ 'sep' . $this->counter++ ] = array( 'type' => 'separatorhr', 'rules' => array(), 'filters' => array() );
        return $this;
    }

    public function & addCaptcha( $name = 'captcha' ){
        $this->elements[ $name ] = array( 'type' => 'captcha', 'valuetype' => 'simple', 'name' => $name, 'rules' => array( 'required' => 'Security code is required', 'captcha' => 'Security code is not valid' ), 'filters' => array() );
        return $this;
    }

    public function & disable( $name ){
        if( isset( $this->elements[ $name ] ) )
            $this->elements[ $name ][ 'disabled' ] = true;
        return $this;
    }

    public function & disableAll(){
        foreach( $this->elements as $name => $el )
            $this->elements[ $name ][ 'disabled' ] = true;
        return $this;
    }

    public function & addAddon( $name, $value, $prefix = true ){
        if( isset( $this->elements[ $name ] ) ){
            if( $prefix === true ){
                $this->elements[ $name ][ 'addonpre' ] = $value;
            }elseif( $prefix === false ){
                $this->elements[ $name ][ 'addonpos' ] = $value;
            }elseif( is_null( $prefix ) ){
                $this->elements[ $name ][ 'addonend' ] = $value;
            }
        }
        return $this;
    }

    public function & prevent( $name ){
        if( isset( $this->elements[ $name ] ) )
            $this->elements[ $name ][ 'prevent' ] = true;
        return $this;
    }

    public function & preventAll( $except = array() ){
        foreach( $this->elements as $name => $el )
            if( ! in_array( $name, $except ) )
                $this->prevent( $name );
        return $this;
    }

    public function & readonly( $name ){
        if( isset( $this->elements[ $name ] ) )
            $this->elements[ $name ][ 'readonly' ] = true;
        return $this;
    }

    public function & setPlaceholder( $name, $placeholder ){
        if( isset( $this->elements[ $name ] ) )
            $this->elements[ $name ][ 'placeholder' ] = $placeholder;
        return $this;
    }

    public function & setAction( $action ){
        $this->action = $action;
        return $this;
    }

    public function & setTarget( $target ){
        $this->target = $target;
        return $this;
    }

    public function & renderaction( $enable = true ){
        $this->renderaction = $enable;
        return $this;
    }

    public function & rendersubmit( $enable = true ){
        $this->rendersubmit = $enable;
        return $this;
    }

    private function getRandom( $len ){

        // generate a letter random string
        $rand = "";

        // some easy-to-confuse letters taken out C/G I/l Q/O h/b 
        $letters = "ABDEFHKLMNOPRSTUVWXZabdefghikmnopqrstuvwxyz";

        for ($i = 0; $i < $len; ++$i)
            $rand .= substr($letters, rand(0,strlen($letters)-1), 1);

        return $rand;
    }

    private function initCaptcha(){

        // create the hash for the random number
        $rand = $this->getRandom( 5 );

        // save random value in session
        $this->app->session()->set( 'captcha_string', $rand );

        return $rand;
    }

    public function showCaptcha(){

        $rand = $this->initCaptcha();

        $image = $this->warped_text_image($this->imgwid, $this->imghgt, $rand);
        $this->add_text($image, $this->signature);

        // send several headers to make sure the image is not cached taken directly from the PHP Manual
        // Date in the past  
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");  
        header("Cache-Control: no-store, no-cache, must-revalidate");  
        header("Cache-Control: post-check=0, pre-check=0", false);  
        header("Pragma: no-cache");      
        header('Content-type: image/png'); 

        // send the image to the browser 
        imagepng($image); 

        // destroy the image to free up the memory 
        imagedestroy($image); 
    }

    private function frand(){
        return 0.0001*rand(0,9999);
    }

    // wiggly random line centered at specified coordinates
    private function randomline($img, $col, $x, $y) {

        $theta = ($this->frand()-0.5)*M_PI*0.7;
        $len = rand($this->imgwid*0.4,$this->imgwid*0.7);
        $lwid = rand(0,2);
        $k = $this->frand()*0.6+0.2; $k = $k*$k*0.5;
        $phi = $this->frand()*6.28;
        $step = 0.5;
        $dx = $step*cos($theta);
        $dy = $step*sin($theta);
        $n = $len/$step;
        $amp = 1.5*$this->frand()/($k+5.0/$len);
        $x0 = $x - 0.5*$len*cos($theta);
        $y0 = $y - 0.5*$len*sin($theta);
        $ldx = round(-$dy*$lwid);
        $ldy = round($dx*$lwid);
        for ($i = 0; $i < $n; ++$i) {
            $x = $x0+$i*$dx + $amp*$dy*sin($k*$i*$step+$phi);
            $y = $y0+$i*$dy - $amp*$dx*sin($k*$i*$step+$phi);
            imagefilledrectangle($img, $x, $y, $x+$lwid, $y+$lwid, $col);
        }
    }

    // amp = amplitude (<1), num=numwobb (<1)
    private function imagewobblecircle($img, $xc, $yc, $r, $wid, $amp, $num, $col){
        $dphi = 1;
        if ($r > 0)
            $dphi = 1/(6.28*$r);

        $woffs = rand(0,100)*0.06283;
        for ($phi = 0; $phi < 6.3; $phi += $dphi) {
            $r1 = $r * (1-$amp*(0.5+0.5*sin($phi*$num+$woffs)));
            $x = $xc + $r1*cos($phi);
            $y = $yc + $r1*sin($phi);
            imagefilledrectangle($img, $x, $y, $x+$wid, $y+$wid, $col);
        }
    }

    // make a distorted copy from $tmpimg to $img. $wid,$height apply to $img,
    // $tmpimg is a factor $iscale bigger.
    private function distorted_copy($tmpimg, $img, $width, $height, $iscale){
        $numpoles = 3;
        
        // make an array of poles AKA attractor points
        //  global $perturbation;
        for ($i = 0; $i < $numpoles; ++$i) {
            do {
                $px[$i] = rand(0, $width);
            } while ($px[$i] >= $width*0.3 && $px[$i] <= $width*0.7);

            do {
                $py[$i] = rand(0, $height);
            } while ($py[$i] >= $height*0.3 && $py[$i] <= $height*0.7);

            $rad[$i] = rand($width*0.4, $width*0.8);
            $tmp = -$this->frand()*0.15-0.15;
            $amp[$i] = $this->perturbation * $tmp;
        }

        // get img properties bgcolor
        $bgcol = imagecolorat($tmpimg, 1, 1);
        $width2 = $iscale*$width;
        $height2 = $iscale*$height;

        // loop over $img pixels, take pixels from $tmpimg with distortion field
        for ($ix = 0; $ix < $width; ++$ix)
            for ($iy = 0; $iy < $height; ++$iy) {
                $x = $ix;
                $y = $iy;
            for ($i = 0; $i < $numpoles; ++$i) {
                $dx = $ix - $px[$i];
                $dy = $iy - $py[$i];
                if ($dx == 0 && $dy == 0)
                    continue;
                $r = sqrt($dx*$dx + $dy*$dy);
                if ($r > $rad[$i])
                    continue;
                $rscale = $amp[$i] * sin(3.14*$r/$rad[$i]);
                $x += $dx*$rscale;
                $y += $dy*$rscale;
            }
            $c = $bgcol;
            $x *= $iscale;
            $y *= $iscale;
            if ($x >= 0 && $x < $width2 && $y >= 0 && $y < $height2)
                $c = imagecolorat($tmpimg, $x, $y);
            imagesetpixel($img, $ix, $iy, $c);
        }
    }

    private function warped_text_image($width, $height, $string){

        // internal variablesinternal scale factor for antialias
        $iscale = 3;

        // initialize temporary image
        $width2 = $iscale*$width;
        $height2 = $iscale*$height;
        $tmpimg = imagecreate($width2, $height2);
        $bgColor = imagecolorallocatealpha ($tmpimg, 252, 252, 252, 0);
        $col = imagecolorallocate($tmpimg, 0, 0, 0);

        // init final image
        $img = imagecreate($width, $height);
        imagepalettecopy($img, $tmpimg);    
        imagecopy($img, $tmpimg, 0,0 ,0,0, $width, $height);

        // put straight text into $tmpimage
        $fsize = $height2*0.25;
        $bb = imageftbbox($fsize, 0, $this->font, $string);
        $tx = $bb[4]-$bb[0];
        $ty = $bb[5]-$bb[1];
        $x = floor($width2/2 - $tx/2 - $bb[0]);
        $y = round($height2/2 - $ty/2 - $bb[1]);
        imagettftext($tmpimg, $fsize, 0, $x, $y, -$col, $this->font, $string);

        // warp text from $tmpimg into $img
        $this->distorted_copy($tmpimg, $img, $width, $height, $iscale);

        // add wobbly circles (spaced)
        //  global $numcirc;
        for ($i = 0; $i < $this->numcirc; ++$i) {
            $x = $width * (1+$i) / ($this->numcirc+1);
            $x += (0.5-$this->frand())*$width/$this->numcirc;
            $y = rand($height*0.1, $height*0.9);
            $r = $this->frand();
            $r = ($r*$r+0.2)*$height*0.2;
            $lwid = rand(0,2);
            $wobnum = rand(1,4);
            $wobamp = $this->frand()*$height*0.01/($wobnum+1);
            $this->imagewobblecircle($img, $x, $y, $r, $lwid, $wobamp, $wobnum, $col);
        }

        // add wiggly lines
        for ($i = 0; $i < $this->numlines; ++$i) {
            $x = $width * (1+$i) / ($this->numlines+1);
            $x += (0.5-$this->frand())*$width/$this->numlines;
            $y = rand($height*0.1, $height*0.9);
            $this->randomline($img, $col, $x, $y);
        }
        return $img;
    }

    private function add_text($img, $string){
        $cmtcol = imagecolorallocatealpha ($img, 128, 0, 0, 64);
        imagestring($img, 5, 10, imagesy($img)-20, $string, $cmtcol);
    }

    public function & addButton( $name, $label = null, $labelbutton = null, $onclick = '', $href = '' ){

        $this->elements[ $name ] = array( 'type' => 'button', 'name' => $name, 'onclick' => $onclick, 'label' => $label, 'labelbutton' => $labelbutton,  'rules' => array(), 'filters' => array(), 'href' => $href );
        return $this;
    }

    public function & addAjaxButton( $name, $labelbutton = null, $onclick = '', $href = '', $css = '' ){

        $this->elements[ $name ] = array( 'type' => 'ajaxbutton', 'isbutton' => true, 'name' => $name, 'onclick' => $onclick, 'labelbutton' => $labelbutton,  'rules' => array(), 'filters' => array(), 'href' => $href, 'css' => $css );
        return $this;
    }


    public function & addSubmit( $label = null, $name = null, $position = '', $options = array() ){

        if( empty( $name ) )  $name = 'save';
        if( empty( $label ) ) $label = 'Save';
        $this->elements[ $name ] = array( 'type' => 'submit', 'isbutton' => true, 'name' => $name, 'position' => $position, 'label' => $label, 'rules' => array(), 'filters' => array(), 'options' => $options );
        $this->applyCsrf();

        return $this;
    }

    public function & addAjax( $label = null, $name = null, $css = 'btn-success', $position = '', $options = array() ){

        if( empty( $name ) )  $name = 'save';
        $this->elements[ $name ] = array( 'type' => 'ajax', 'isbutton' => true, 'name' => $name, 'position' => $position, 'css' => $css, 'label' => $label, 'rules' => array(), 'filters' => array(), 'options' => $options );
        $this->applyCsrf();

        return $this;
    }

    private function applyCsrf(){

        // csrf protection
        if( !$this->csrfinit ){

            $csrfname = $this->csrfname;
            $csrf     = $this->app->session()->get( $csrfname, '' );
            $csrfnew  = $this->getRandom( 8 );

            $this->addRule( function() use( $csrf, $csrfname ){
                return ( is_string( $csrf ) && !empty( $csrf ) && isset( $_POST[ $csrfname ] ) && $csrf === $_POST[ $csrfname ] ) ? true : 'csrf protection';
            });


            $this->app->session()->set( $csrfname, $csrfnew );
            $this->csrfinit = true;
		
            // add csrf to ajax
            $this->app->ajax()->addFormCsrf( $csrfname, $csrfnew );
        }

    }


    public function & setSubmitMessage( $msg, $title = 'Sucess' ){

        if( $this->isajax )
            $this->app->ajax()->msgOk( $msg, $title );

        $this->submitMessage = $msg;
        return $this;
    }

    public function & setWarningMessage( $msg, $title = 'Warning' ){

        if( $this->isajax )
            $this->app->ajax()->msgWarning( $msg, $title );

        $this->warningMessage = $msg;
        return $this;
    }

    public function & setErrorMessage( $msg, $title = 'Errors found' ){

        if( $this->isajax )
            $this->app->ajax()->msgError( $msg, $title );

        $this->errors[] = $msg;
        $this->wasValid = false;
        return $this;
    }
    
    public function & setHelp( $element, $message ){
        if( isset( $this->elements[ $element ] ) )
            $this->elements[ $element ][ 'help' ] = $message;
        return $this;
    }

    public function isSubmitted( $button = '' ){

        foreach( $this->elements as $n => $el ){
            if( ( $el[ 'type' ] == 'submit' || $el[ 'type' ] == 'ajax' ) && isset( $_POST[ $this->formname . $n ] ) && ( empty( $button ) || $n == $button ) )
                return true;
        }
        return false;
    }

    public function & hide(){

        if( $this->isajax ){
            $this->app->ajax()->setFormReset( $this->formname );

            if( !empty( $this->modal ) ){
                $modal = $this->modal;
                $this->app->ajax()->modalHide( $modal[ 'id' ] );
            }
        }

        $this->hide = true;
        return $this;
    }

    public function & show(){

        // check special form element: transloadit
        $transloadit = 0;
        foreach( $this->elements as $n => $el ){
            if( $el[ 'type' ] == 'transloadit' || $el[ 'type' ] == 'chat' ){
                $transloadit = 1;
                break;
            }
        }

        // if modal undefined, create one
        if( ! isset( $this->modal['id'] ) )
            $this->setModal( 'Form' );

        $this->app->ajax()->showForm( $this->formname, $this->app->ajax()->filter( $this->__toString() ), $this->modal['id'], $transloadit );
        return $this;
    }

    public function & setDefaultValues( $values, $append = false ){

        // check if values is object
        if( is_object( $values ) ){
            $val = array();
            foreach( $this->elements as $n => $el )
                if( isset( $values->$n ) )
                    $val[ $n ] = $values->$n;

            $this->valuesdefault = $append ? ( $this->valuesdefault + $val ) : $val;
        }else{
            $this->valuesdefault = $append ? ( $this->valuesdefault + $values ) : $values;
        }
        return $this;
    }

    public function & setDefaultValue( $elementname, $value ){
        $this->valuesdefault[ $elementname ] = $value;
        return $this;
    }

    public function & setDefault( $callback ){

        if( ! $this->isSubmitted() && is_array( $res = call_user_func( $callback ) ) )
            $this->setDefaultValues( $res );

        return $this;
    }

    public function & addRule(){
	
        $args = func_get_args();

        if( !isset( $args[0] ) )
            return $this;

        // add validation if form is submitted only
        if( is_string( $args[0] ) ){

            if( isset( $args[1] ) && isset( $args[2] ) ){
                $element  = array_shift($args);
                $message  = array_shift($args);
                $rulename = array_shift($args);
                $ruleopt  = array_shift($args);
                
                $this->elements[ $element ][ 'rules' ][ $rulename ] = array( $message, $ruleopt );
            }

        }else{

//            if( $this->isSubmitted() )
                $this->customRules[] = $args;
        }

        return $this;
    }

    public function isSubmittedAndValid( $button = '' ){
        return ( $this->isSubmitted( $button ) && $this->isValid() );
    }

    public function onSubmittedAndValid( $callback ){
        if( ! $this->isSubmittedAndValid() )
            return false;
        return call_user_func( $callback );
    }

    public function isValid( $values = null ){

        // get values
        if( ! is_array( $values ) )
            $values = $this->getValues( false, false );

        foreach( $this->elements as $n => $el ){

            $value = isset( $values[ $n ] ) ? $values[ $n ] : '';

            // cycle element rules
            if( isset( $el[ 'rules' ] ) ){
                foreach( $el[ 'rules' ] as $rulename => $rinfo ){

                    // check if info is string or array
                    $rulemessage = is_array( $rinfo ) ? $rinfo[0] : $rinfo;
                    $ruleoptions = is_array( $rinfo ) && isset( $rinfo[1] ) ? $rinfo[1] : null;

                    // if element is not required and value is empty do not validate
                    if( empty( $value ) && !isset( $el[ 'rules' ][ 'required' ] ) )
                        continue;

                    if( ! is_callable( array( 'myrules', $rulename ) ) || ! call_user_func( array( 'myrules', $rulename ), $value, $ruleoptions, $values, $el ) ){
                        $this->errors[ $n ] = $rulemessage;
                        break;
                    }
                }
            }
        }

        foreach( $this->customRules as $rinfo ){

            $rulecallback = array_shift( $rinfo );
			
            $counter = 0;
            foreach( $rinfo as $elname ){
                if( isset( $this->errors[ $elname ] ) ){
                    $counter++;
                }
            }

            if( $counter == 0 ){
                $res = call_user_func( $rulecallback );
                if( is_string( $res ) ){
                    if( isset( $rinfo[0] ) ){
                        $this->errors[ $rinfo[0] ] = $res;
                    }else{
                        $this->errors[ '' ] = $res;
                    }
                }
            }
        }

        $isvalid = ( $this->wasValid = empty( $this->errors ) );

        if( !$isvalid && $this->isajax )
            $this->app->ajax()->msgError( $this->getErrors() );

        return $isvalid;
    }

    public function getErrors(){
        return $this->errors;
    }

    public function getValues( $applyFilters = true, $includeDisabled = false, $ungroup = false ){

        // check if form is submitted
        if( ! $this->isSubmitted() )
            return $this->valuesdefault;

        $values = array();
        foreach( $this->elements as $n => $el ){

            // get values only from some element types and if not disabled
            if( !isset( $el[ 'valuetype' ] ) || ( $includeDisabled == false && isset( $el['disabled'] ) && $el['disabled'] == true ) )
                continue;

            // get value for this element
            switch( $el[ 'valuetype' ] ){
                case 'simple':      $values[ $n ] = isset( $_POST[ $this->formname . $n ] ) ? $_POST[ $this->formname . $n ] : '';
                                    break;
                case 'multiple':    $values[ $n ] = ( isset( $_POST[ $this->formname . $n ] ) && is_array( $_POST[ $this->formname . $n ] ) ) ? implode( ';', $_POST[ $this->formname . $n ] ) : ''; 
                                    break;
                case 'group':       if( $ungroup ){
                                        foreach( $el['options'] as $o => $val )
                                            if( isset( $_POST[ $this->formname . $n . $o ] ) && $_POST[ $this->formname . $n . $o ] == 'on' )
                                                $values[ $o ] = 1;
                                    }else{
                                        $v = array();
                                        foreach( $el['options'] as $o => $val )
                                            if( isset( $_POST[ $this->formname . $n . $o ] ) && $_POST[ $this->formname . $n . $o ] == 'on' )
                                                $v[] = $o;
                                        $values[ $n ] = implode( ';', $v );
                                    }
                                    break;
                case 'transloadit': if( isset( $_POST[ $this->formname . $n ] ) ){
                                        $this->app->transloadit()->requestAssembly( $res, $_POST[ $this->formname . $n ] );
                                        $values[ $n ] = $res;
                                    }
                                    break;
                default:            continue;
            }

            if( $applyFilters && is_array( $el[ 'filters' ] ) )
                foreach( $el[ 'filters' ] as $f )
                    if( is_callable( array( 'myfilters', $f ) ) )
                        $values[ $n ] = call_user_func( array( 'myfilters', $f ), $values[ $n ] );
        }

        return $values;
    }


    public function & addFilter( $element, $filter ){
        if( isset( $this->elements[ $element ] ) )
            $this->elements[ $element ][ 'filters' ][] = $filter;
        return $this;
    }


    public function & addFilterHtml( $element, $filter ){
        if( isset( $this->elements[ $element ] ) )
            $this->elements[ $element ][ 'filtershtml' ][] = $filter;
        return $this;
    }


    public function getValue( $key, $default = null ){
        $vals = $this->getValues();
        return ( isset( $vals[ $key ] ) ? $vals[ $key ] : $default );
    }

    public function obj(){

        // get values
        $values = $this->getValues( false, true );

        foreach( $this->elements as $n => $el ){
            
            $v = isset( $values[ $n ] ) ? $values[ $n ] : '';
            
            if( isset( $el[ 'filtershtml' ] ) )
                foreach( $el[ 'filtershtml' ] as $f )
                    if( is_callable( array( 'myfilters', $f ) ) )
                        $v = call_user_func( array( 'myfilters', $f ), $v );

            $this->elements[$n][ 'value' ] = $v;
            if( $el[ 'type' ] == 'captcha' )
                $this->initCaptcha();
        }

        return array(   'hide'          => $this->hide,
                        'submitted'     => $this->isSubmitted(),
                        'valid'         => $this->wasValid,
                        'validmsg'      => ( empty( $this->submitMessage ) ? 'Form submitted' : $this->submitMessage ),
                        'warningmsg'    => $this->warningMessage,
                        'preventmsg'    => $this->preventmsg,
                        'errors'        => $this->errors,
                        'name'          => $this->formname,
                        'action'        => $this->action,
                        'target'        => $this->target,
                        'elements'      => $this->elements,
                        'renderaction'  => $this->renderaction,
                        'rendersubmit'  => $this->rendersubmit,
                        'csrfname'      => $this->csrfname,
                        'csrf'          => $this->app->session()->get( $this->csrfname, '' ),
                        'isajax'        => $this->isajax,
                        'closeb'        => $this->isajax && $this->closebutton,
                        'closeset'      => $this->closebuttonsettings,
                        'footer'        => $this->footer,
                        'ismodal'       => !empty( $this->modal ),
                        'modal'         => $this->modal
                        );
    }

    public function __toString(){
        return $this->app->render( '@my/myform', $this->obj(), null, null, APP_CACHEAPC, false, false );
    }
}
