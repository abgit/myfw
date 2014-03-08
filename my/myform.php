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
    private $wasSubmitted;
    private $wasValid;
    private $renderaction;
    private $rendersubmit;
    private $customRules;
    private $disabled;

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
        $this->action           = '';
        $this->wasSubmitted     = false;
        $this->wasValid         = null;
        $this->renderaction     = true;
        $this->rendersubmit     = true;
        $this->customRules      = array();
        $this->disabled         = array();
        $this->csrfname         = $name . 'csrf';

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

    public function & addText( $name, $label = '', $rules = array(), $filters = array(), $options = array() ){
        $this->elements[ $name ] = array( 'type' => 'text', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'rules' => $rules, 'filters' => $filters, 'options' => $options );
        return $this;
    }

    public function & addHidden( $name, $rules = array(), $filters = array(), $options = array() ){
        $this->elements[ $name ] = array( 'type' => 'hidden', 'valuetype' => 'simple', 'name' => $name, 'label' => '', 'rules' => $rules, 'filters' => $filters, 'options' => $options );	
        return $this;
    }

    public function & addCheckbox( $name, $label, $rules = array(), $filters = array(), $options = array() ){
        $this->elements[ $name ] = array( 'type' => 'checkbox', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'rules' => $rules, 'filters' => $filters, 'options' => $options );	
        return $this;
    }

    public function & addTextarea( $name, $label, $rules = array(), $filters = array(), $options = array() ){
        $this->elements[ $name ] = array( 'type' => 'textarea', 'valuetype' => 'simple', 'name' => $name, 'label' => $label,  'rules' => $rules, 'filters' => $filters, 'options' => $options );
        return $this;
    }

    public function & addPassword( $name, $label, $rules = array(), $filters = array(), $options = array() ){
        $this->elements[ $name ] = array( 'type' => 'password', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'rules' => $rules, 'filters' => $filters, 'options' => $options );
        return $this;
    }

    // special elements
    public function & addEmail( $name, $label = 'Email', $rules = array(), $filters = array() ){
        $rules = array_merge( $rules, array( 'email' => 'Email is not valid' ) );
        $this->elements[ $name ] = array( 'type' => 'text', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'rules' => $rules, 'filters' => $filters );	
        return $this;
    }

    public function & addMonth( $name, $label, $rules = array(), $filters = array() ){
        $options = array();
        foreach (range(1, 12) as $number)
            $options[ sprintf('%02d', $number ) ] = sprintf('%02d', $number ) . ' - ' . date("F", mktime(0, 0, 0, $number, 10));

        return $this->addSelect( $name, $label, $rules, $filters, $options );
    }

    public function & addYear( $name, $label, $rules = array(), $filters = array() ){
        $options = array();
        foreach( range( date("Y"), date("Y") + 20 ) as $number)
            $options[ $number ] = $number;

        return $this->addSelect( $name, $label, $rules, $filters, $options );
    }

    public function & addSelect( $name, $label, $rules = array(), $filters = array(), $options = array(), $optionsFilter = null ){

        // filter types
        if( isset( $optionsFilter['type'] ) ){
            switch( $optionsFilter['type'] ){
                case 'implode': foreach( $options as $o => $arr )
                                    $options[ $o ] = implode( ' ', $arr );
                                break;
                case 'explode': $options = explode( $optionsFilter['delimiter'], $options );
                                break;
                case 'for':     $options = array();
                                for( $i = $optionsFilter['min']; $i<=$optionsFilter['max']; $i += $optionsFilter['step'] )
                                    $options[ $i ] = $i;
                                break;
            }
        }

        $rules[ 'selectvalid' ] = array( $label . ' is not valid', $options );
        $this->elements[ $name ] = array( 'type' => 'select', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'rules' => $rules, 'filters' => $filters, 'options' => $options );	
        return $this;
    }

    public function & addMultiple( $name, $label, $rules = array(), $filters = array(), $options = array(), $optionsFilter = null ){
        $this->elements[ $name ] = array( 'type' => 'multiple', 'valuetype' => 'multiple', 'name' => $name, 'label' => $label, 'rules' => $rules, 'filters' => $filters, 'options' => $options );	
        return $this;
    }

    public function & addCheckboxgroup( $name, $label, $rules = array(), $filters = array(), $options = array(), $settings = array() ){
//		$rules[ 'checkboxgroupvalid' ] = array( $label . ' is not valid', $options );
        $this->elements[ $name ] = array( 'type' => 'checkboxgroup', 'valuetype' => 'group', 'name' => $name, 'label' => $label, 'rules' => $rules, 'filters' => $filters, 'options' => $options, 'settings' => $settings );		
        return $this;
    }

    public function & addImageupload( $name, $label, $rules = array(), $filters = array(), $options = array() ){

        // default options
        $options = $options + array( 'template_id' => '', 'width' => 0, 'height' => 0, 'type' => 'simple', 'steps' => array() );

        $app = \Slim\Slim::getInstance();

        $params = json_encode(array(
            'auth' => array(
                'key'       => $app->config( 'transloadit.s' ),
                'expires'   => gmdate('Y/m/d H:i:s+00:00', strtotime('+1 hour'))
                ),
                'steps' => array(
                    'cfiles' => array( 
                        'path' => $app->client()->getTag() . '/${file.id}-${file.url_name}'
                    ),
                    'ofile' => array( 
                        'path' => $app->client()->getTag() . '/${file.id}-${file.url_name}'
                    )
                ) + $options[ 'steps' ],
                'template_id' => $options['template_id']
        ), JSON_UNESCAPED_SLASHES );

        $this->imageuploadorder = isset( $this->imageuploadorder ) ? 1+$this->imageuploadorder : 1;
        $this->elements[ $name ] = array( 'type' => 'imageupload', 'valuetype' => $options['type'], 'name' => $name, 'label' => $label, 'rules' => $rules, 'filters' => $filters, 'options' => array( 'order' => $this->imageuploadorder, 'params' => $params, 'signature' => hash_hmac('sha1', $params, $app->config('transloadit.s') ), 'width' => $options['width'], 'height' => $options['height'] ) );
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

    public function & addCaptcha( $name = 'captcha' ){
        $this->elements[ $name ] = array( 'type' => 'captcha', 'valuetype' => 'simple', 'name' => $name, 'rules' => array( 'required' => 'Security code is required', 'captcha' => 'Security code is not valid' ), 'filters' => array() );
        return $this;
    }

    public function & disable( $name ){
        if( isset( $this->elements[ $name ] ) )
            $this->elements[ $name ][ 'disabled' ] = true;
        return $this;
    }

    public function & readonly( $name ){
        if( isset( $this->elements[ $name ] ) )
            $this->elements[ $name ][ 'readonly' ] = true;
        return $this;
    }

    public function & setAction( $action ){
        $this->action = $action;
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
        \Slim\Slim::getInstance()->session()->set( 'captcha_string', $rand );

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

    // add grid for debugging purposes
    private function addgrid($tmpimg, $width2, $height2, $iscale, $color) {
        $lwid = floor($iscale*3/2);
        imagesetthickness($tmpimg, $lwid);
        for ($x = 4; $x < $width2-$lwid; $x+=$lwid*2)
            imageline($tmpimg, $x, 0, $x, $height2-1, $color);
        for ($y = 4; $y < $height2-$lwid; $y+=$lwid*2)
            imageline($tmpimg, 0, $y, $width2-1, $y, $color);
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

        // $this->addgrid($tmpimg, $width2, $height2, $iscale, $col); // debug
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

    public function & addSubmit( $label = null, $name = null, $position = '', $options = array() ){

        if( empty( $name ) )  $name = 'save';
        if( empty( $label ) ) $label = 'Save';
        $this->elements[ $name ] = array( 'type' => 'submit', 'name' => $name, 'position' => $position, 'label' => $label, 'rules' => array(), 'filters' => array(), 'options' => $options );

        // csrf protection
        if( !$this->csrfinit ){

            $csrf = $this->app->session()->get( $this->csrfname, '' );

            $this->addRule( function() use( $csrf ){
                return ( is_string( $csrf ) && !empty( $csrf ) && isset( $_POST[ $this->csrfname ] ) && $csrf === $_POST[ $this->csrfname ] ) ? true : 'csrf protection';
            });

            $this->app->session()->set( $this->csrfname, $this->getRandom( 8 ) );
            $this->csrfinit = true;
		}

        return $this;
    }

    public function & setSubmitMessage( $msg ){
        $this->submitMessage = $msg;
        return $this;
    }

    public function & setWarningMessage( $msg ){
        $this->warningMessage = $msg;
        return $this;
    }

    public function & setErrorMessage( $msg ){
        $this->errors[] = $msg;
        $this->wasValid = false;
        return $this;
    }

    public function isSubmitted( $button = '' ){

        foreach( $this->elements as $n => $el ){
            if( $el[ 'type' ] == 'submit' && isset( $_POST[ $this->formname . $n ] ) && ( empty( $button ) || $n == $button ) )
                return ($this->wasSubmitted = true );
        }
        return ($this->wasSubmitted=false);
    }

    public function & hide(){
        $this->hide = true;
        return $this;
    }

    public function & setDefaultValues( $values ){

        // check if values is object
        if( is_object( $values ) ){
            $val = array();
            foreach( $this->elements as $n => $el )
                if( isset( $values->$n ) )
                    $val[ $n ] = $values->$n;

            $this->valuesdefault = $val;
        }else{
            $this->valuesdefault = $values;
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

    public function addRule(){
	
        // add validation if form is submitted only
        if( ! $this->isSubmitted() )
            return false;

        $this->customRules[] = func_get_args();
    }

    public function isSubmittedAndValid(){
        return ( $this->isSubmitted() && $this->isValid() );
    }

    public function onSubmittedAndValid( $callback ){
        if( ! $this->isSubmittedAndValid() )
            return false;
        return call_user_func( $callback );
    }

    public function isValid( $values = null ){

        // get values
        if( ! is_array( $values ) )
            $values = $this->getValues( false );

        foreach( $this->elements as $n => $el ){

            $value = isset( $values[ $n ] ) ? $values[ $n ] : '';

            // cycle element rules
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

        return ( $this->wasValid = empty( $this->errors ) );
    }

    public function getValues( $applyFilters = true ){

        // check if form is submitted
        if( !$this->wasSubmitted )
            return $this->valuesdefault;

        $values = array();
        foreach( $this->elements as $n => $el ){

            // get values only from some element types and if not disabled
            if( !isset( $el[ 'valuetype' ] ) || ( isset( $el['disabled'] ) && $el['disabled'] == true ) )
                continue;

            // get value for this element
            switch( $el[ 'valuetype' ] ){
                case 'simple':  $values[ $n ] = isset( $_POST[ $this->formname . $n ] ) ? $_POST[ $this->formname . $n ] : '';
                                break;
                case 'multiple':$values[ $n ] = ( isset( $_POST[ $this->formname . $n ] ) && is_array( $_POST[ $this->formname . $n ] ) ) ? implode( ';', $_POST[ $this->formname . $n ] ) : ''; 
                                break;
                case 'group':   $v = array();
                                foreach( $el['options'] as $o => $val )
                                    if( isset( $_POST[ $this->formname . $n . $o ] ) && $_POST[ $this->formname . $n . $o ] == 'on' )
                                        $v[] = $o;
                                $values[ $n ] = implode( ';', $v );
                                break;
                default:        continue;
            }

            if( $applyFilters && is_array( $el[ 'filters' ] ) )
                foreach( $el[ 'filters' ] as $f )
                    if( is_callable( array( 'myfilters', $f ) ) )
                        $values[ $n ] = call_user_func( array( 'myfilters', $f ), $values[ $n ] );
        }

        return $values;
    }

    public function getValue( $key, $default = null ){
        $vals = $this->getValues();
        return ( isset( $vals[ $key ] ) ? $vals[ $key ] : $default );
    }

    public function obj(){

        // get values
        $values = $this->getValues( false );

        foreach( $this->elements as $n => $el ){		
            $this->elements[$n][ 'value' ] = isset( $values[ $n ] ) ? $values[ $n ] : '';
            if( $el[ 'type' ] == 'captcha' ) $this->initCaptcha();
        }

        return array(   'hide'          => $this->hide,
                        'submitted'     => $this->wasSubmitted,
                        'valid'         => $this->wasValid,
                        'validmsg'      => ( empty( $this->submitMessage ) ? 'Form submitted' : $this->submitMessage ),
                        'warningmsg'    => $this->warningMessage,
                        'errors'        => $this->errors,
                        'name'          => $this->formname,
                        'action'        => $this->action,
                        'elements'      => $this->elements,
                        'renderaction'  => $this->renderaction,
                        'rendersubmit'  => $this->rendersubmit,
                        'csrfname'      => $this->csrfname,
                        'csrf'          => $this->app->session()->get( $this->csrfname, '' )
                        );
    }

    public function __toString(){
        return \Slim\Slim::getInstance()->render( '@my/myform', $this->obj(), null, null, APP_CACHEAPC, false, false );
    }
}
