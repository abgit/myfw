<?php

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;

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

    /** @var mycontainer*/
    private $app;

    public function __construct( $c ){

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
        $this->preventmsg       = 'undefined';

        $this->app = $c;
    }

    public function & setName( $formname ){
        $this->formname = $formname;
        $this->csrfname = $formname . 'csrf';
        return $this;
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

    public function & setModal( $title = '', $class = 'modal-lg', $icon = '', $static = true, $width = '', $closebutton = true, $pagewidth = 1210 ){
        $this->modal = array( 'id' => 'mod' . $this->formname, 'title' => $title, 'class' => $class, 'icon' => $icon, 'static' => $static, 'width' => $width, 'closebutton' => $closebutton, 'pagewidth' => $pagewidth );
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

    public function & addBitcoin( $name, $label = '', $help = '', $currencies = array(), $onchange = '', $decimal = 8 ){
        $this->elements[ $name ] = array( 'type' => 'bitcoin', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'rules' => array(), 'filters' => array(), 'options' => array(), 'help' => $help, 'currencies' => $currencies, 'onchange' => $onchange, 'decimal' => $decimal );
        $this->addFilter( $name, 'satoshi' );
        $this->addRule( $name, 'Invalid bitcoin amount', 'bitcoin' );
        if( is_string( $currencies ) && !empty( $currencies ) )
            $this->elements[ $currencies ] = array();
        return $this;
    }

    public function & addHidden( $name ){
        $htmlname = ( $name{0} == '@' ? substr( $name, 1 ) : $this->formname . $name );
        $name     = ( $name{0} == '@' ? substr( $name, 1 ) : $name );
        $this->elements[ $name ] = array( 'type' => 'hidden', 'valuetype' => 'simple', 'name' => $htmlname, 'label' => '', 'rules' => array(), 'filters' => array(), 'options' => array() );	
        return $this;
    }

    public function & addCheckbox( $name, $label, $help = '' ){
        $this->elements[ $name ] = array( 'type' => 'checkbox', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'rules' => array(), 'filters' => array(), 'options' => array(), 'help' => $help );	
        return $this;
    }

    public function & addTextarea( $name, $label, $help = '', $rows = 2 ){
        $this->elements[ $name ] = array( 'type' => 'textarea', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'rows' => $rows, 'rules' => array(), 'filters' => array(), 'options' => array(), 'help' => $help );
        return $this;
    }

    public function & addMarkdown( $name, $label, $picker_options ){

        if( isset( $this->app[ 'filestack.options'] ) )
            $picker_options = $picker_options + $this->app[ 'filestack.options'];

        $processing = $this->app->urlfor->action( 'myfwmarkdown' );

        $this->elements[ $name ] = array( 'type' => 'markdown', 'valuetype' => 'simple', 'pickeroptions' => json_encode( $picker_options, JSON_HEX_APOS ), 'processing' => $processing, 'name' => $name, 'label' => $label, 'rules' => array(), 'filters' => array(), 'options' => array() );
        return $this;
    }

    public function processMarkdown()
    {
        if( isset($_POST['img'] ) ) {
            return $this->app->ajax->markdown( $this->app->filters->filestackresize( $_POST['img'], isset( $this->app['markdown.width'] ) ? $this->app['markdown.width'] : 600, isset( $this->app['markdown.height'] ) ? $this->app['markdown.height'] : 300 ) );
        }

        return false;
    }

    public function & addPassword( $name, $label, $help = '' ){
        $this->elements[ $name ] = array( 'type' => 'password', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'rules' => array(), 'filters' => array(), 'options' => array(), 'help' => $help );
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
        $this->app->ajax->focus( '#' . $this->formname . $element );
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

    public function & addHeader( $title, $description = '', $icon = '', $descriptionclass = '', $align = '', $hr = false ){
        $this->elements[ 'hdr' . strtolower( $title ) ] = array( 'type' => 'formheader', 'title' => $title, 'description' => $description, 'descriptionclass' => $descriptionclass, 'icon' => $icon, 'rules' => array(), 'filters' => array(), 'align' => $align, 'hr' => $hr );
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

    public function & addStatic( $name, $label = '', $help = '', $showvalue = '', $prefix = '', $sufix = '', $replacelist = false, $clipboard = false ){

        if( $clipboard )
            $this->app->ajax->clipboard();

        $this->elements[ $name ] = array( 'type' => 'static', 'name' => $name, 'label' => $label, 'rules' => array(), 'filters' => array(), 'help' => $help, 'showvalue' => $showvalue, 'prefix' => $prefix, 'sufix' => $sufix, 'replacelist' => $replacelist, 'clipboard' => $clipboard );
        return $this;
    }    

    public function & addStaticMessage( $message = '', $title = '', $icon = '', $date = '' ){
        $this->elements[ 'smm' . $this->counter++ ] = array( 'type' => 'staticmessage', 'message' => $message, 'title' => $title, 'icon' => $icon, 'date' => $date, 'rules' => array(), 'filters' => array() );
        return $this;
    }    

    public function & addCustom( $name, $obj ){
        $this->elements[ $name ] = array( 'type' => 'custom', 'obj' => $obj, 'rules' => array(), 'filters' => array() );
        return $this;
    }

    public function & getCustom( $name){
        return isset( $this->elements[ $name ][ 'obj' ] ) ? $this->elements[ $name ][ 'obj' ] : null;
    }

    public function & addEmail( $name, $label = 'Email' ){
        $this->elements[ $name ] = array( 'type' => 'text', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'rules' => array( 'email' => 'Email is not valid' ), 'filters' => array() );
        return $this;
    }

    public function & addCameraTag( $name, $label = 'Video', $maxlength = null, $sources = '', $help = '' ){
        
        $expiration = time() + 1800;
        $signature  = $this->app->config[ 'cameratag.key' ] ? hash_hmac( 'sha1', $expiration, $this->app->config[ 'cameratag.key' ] ) : '';

        $this->elements[ $name ] = array( 'type' => 'cameratag', 'valuetype' => 'cameratag', 'name' => $name, 'label' => $label, 'appid' => $this->app->config[ 'cameratag.appid' ], 'maxlength' => $maxlength, 'appexpiration' => $expiration, 'appsignature' => $signature, 'sources' => $sources, 'help' => $help );

        $this->addRule( function() use ( $name ){

            if( isset( $_POST[ $this->formname . $name ][ 'video_uuid' ] ) && is_string( $_POST[ $this->formname . $name ][ 'video_uuid' ] ) ){

                if( empty( $_POST[ $this->formname . $name ][ 'video_uuid' ] ) )
                    return true;

                try{ 
                    $json = file_get_contents( 'https://cameratag.com/api/v10/videos/' . $_POST[ $this->formname . $name ][ 'video_uuid' ] . '.json?api_key=' . $this->app->config[ 'cameratag.key' ] );
                    $json = json_decode( $json, true );

                } catch (Exception $e ){
                    return 'Invalid video';
                }

                if( isset( $json[ 'state' ] ) && isset( $json[ 'uuid' ] ) && isset( $json[ 'app_uuid' ] ) && isset( $json[ 'type' ] ) && $json[ 'type' ] == 'Video' && $json[ 'uuid' ] == $_POST[ $this->formname . $name ][ 'video_uuid' ] && $json[ 'app_uuid' ] == $this->app->config[ 'cameratag.appid' ] )
                    return true;
            }

            return 'Invalid recording. Record another video.';
        });

        return $this;
    }


    public function & addCameraTagPhoto( $name, $label = 'Photo', $appid = null, $help = '' ){
        
        if( is_null( $appid ) )
            $appid = $this->app->config[ 'cameratag.appid' ];

        $expiration = time() + 1800;
        $signature  = $this->app->config[ 'cameratag.key' ] ? hash_hmac( 'sha1', $expiration, $this->app->config[ 'cameratag.key' ] ) : '';

        $this->elements[ $name ] = array( 'type' => 'cameratagphoto', 'valuetype' => 'cameratagphoto', 'name' => $name, 'label' => $label, 'appid' => $appid, 'appexpiration' => $expiration, 'appsignature' => $signature, 'help' => $help );

        $this->addRule( function() use ( $name, $appid ){

            if( isset( $_POST[ $this->formname . $name . '_uuid' ] ) && is_string( $_POST[ $this->formname . $name . '_uuid' ] ) ){

                if( empty( $_POST[ $this->formname . $name . '_uuid' ] ) )
                    return true;

                try{ 
                    $json = file_get_contents( 'https://cameratag.com/api/v10/photos/' . $_POST[ $this->formname . $name . '_uuid' ] . '.json?api_key=' . $this->app->config[ 'cameratag.key' ] );
                    $json = json_decode( $json, true );

                } catch (Exception $e ){
                    return 'Invalid photo';
                }

                if( isset( $json[ 'state' ] ) && isset( $json[ 'uuid' ] ) && isset( $json[ 'app_uuid' ] ) && isset( $json[ 'type' ] ) && $json[ 'type' ] == 'Photo' && $json[ 'uuid' ] == $_POST[ $this->formname . $name . '_uuid' ] && $json[ 'app_uuid' ] == $appid )
                    return true;
            }

            return 'Invalid recording. Retry with another photo.';
        });

        return $this;
    }


    public function & addCameraTagVideo( $name, $label = 'Video' ){
        $this->elements[ $name ] = array( 'type' => 'cameratagvideo', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'appid' => $this->app->config[ 'cameratag.appid' ], 'appcdn' => $this->app->config[ 'cameratag.appcdn' ] );
        return $this;
    }

    public function & addCameraTagImage( $name, $label = 'Image' ){
        $this->elements[ $name ] = array( 'type' => 'cameratagimage', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'appid' => $this->app->config[ 'cameratag.appid' ], 'appcdn' => $this->app->config[ 'cameratag.appcdn' ] );
        return $this;
    }
/*
    public function & addZiggeo( $name, $label = 'Video' ){
        $this->elements[ $name ] = array( 'type' => 'ziggeo', 'valuetype' => 'simple', 'name' => $name, 'label' => $label );

        $this->app->ajax()->Ziggeo( $this->formname . $name, '#' . $this->formname . $name . 'd' );
        return $this;
    }*/


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
                case 'reverse': foreach( $options as $k => $v )
                                    $res[ $v ] = $k;
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
        if( !is_null( $optionsFilter ) )
            $options = $this->filterOptions( $options, $optionsFilter );

        $rules[ 'selectvalid' ] = array( $label . ' is not valid', $options );

        $htmlname = ( $name{0} == '@' ? substr( $name, 1 ) : $this->formname . $name );
        $name     = ( $name{0} == '@' ? substr( $name, 1 ) : $name );

        $this->elements[ $name ] = array( 'type' => 'select', 'valuetype' => 'simple', 'name' => $htmlname, 'label' => $label, 'rules' => array(), 'filters' => array(), 'options' => $options, 'help' => $help );

        if( is_string( $options ) )
            $this->elements[ $options ] = array();

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


    public function processUploadcareThumb( Request $request, Response $response, $args ){
    
        if( isset( $_POST[ 'img' ] ) && is_string( $_POST[ 'img' ] ) ){

            // store cropped version and get new uuid
            try {
                $api = new Uploadcare\Api($this->app->config['uploadcare.key'], $this->app->config['uploadcare.secret']);
                $file = $api->createLocalCopy( $_POST[ 'img' ] );
                $uuid = $file->getUuid();
                $url  = $file->getUrl();
            } catch (Exception $e) {
            }


            $this->app->ajax->attr( '#uploadcarei' . $args[ 'fsid' ], 'src', $url );
            $this->app->ajax->val(  '#uploadcareh' . $args[ 'fsid' ], $uuid  );
        }
    }


    public function & addUploadcare( $name, $label, $width = '', $height = '', $picker_options = array(), $help = '', $thumbdefault = '' ){

        if( isset( $this->app[ 'uploadcare.options'] ) )
            $picker_options = $picker_options + $this->app[ 'uploadcare.options'];

        $processing = $this->app->urlfor->action( 'myfwuploadcare', array( 'fsid' => $this->formname . $name ) );

        $this->elements[ $name ] = array( 'type' => 'uploadcare', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'width' => $width, 'height' => $height, 'rules' => array(), 'filters' => array(), 'default' => empty( $thumbdefault ) ? $this->app->config[ 'uploadcare.default' ] : $thumbdefault, 'help' => $help, 'processing' => $processing, 'pickeroptions' => json_encode( $picker_options, JSON_HEX_APOS ) );

        $this->addRule( function() use ( $name ){

            if( isset( $_POST[ $this->formname . $name ] ) && is_string( $_POST[ $this->formname . $name ] ) ){

                if( empty( $_POST[ $this->formname . $name ] ) )
                    return true;

                    try {
                        $api = new Uploadcare\Api($this->app->config['uploadcare.key'], $this->app->config['uploadcare.secret']);
                        $file = $api->getFile($_POST[$this->formname . $name])->getUuid();

                        if (!empty($file)) {
                            return true;
                        }

                    } catch (Exception $e) {
                    }

            }

            return 'Invalid media uploadcare';
        });

        return $this;
    }


    public function processFilestackThumb( Request $request, Response $response, $args ){

        if( isset( $_POST[ 'img' ] ) && is_string( $_POST[ 'img' ] ) && strpos( $_POST[ 'img' ], 'https://cdn.filestackcontent.com/' ) === 0 ){
            $this->app->ajax->attr( '#filestacki' . $args[ 'fsid' ], 'src', $this->app->filters->filestack( $_POST[ 'img' ] ) );
        }
    }


    public function & addFilestack( $name, $label, $width = '', $height = '', $picker_options = array(), $help = '', $thumbdefault = '' ){

        if( isset( $this->app[ 'filestack.options'] ) )
            $picker_options = $picker_options + $this->app[ 'filestack.options'];

        $processing = $this->app->urlfor->action( 'myfwfilestack', array( 'fsid' => $this->formname . $name ) );

        $this->elements[ $name ] = array( 'type' => 'filestack', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'width' => $width, 'height' => $height, 'rules' => array(), 'filters' => array(), 'api' => $this->app->config[ 'filestack.api' ], 'default' => empty( $thumbdefault ) ? $this->app->config[ 'filestack.default' ] : $thumbdefault, 'help' => $help, 'processing' => $processing, 'pickeroptions' => json_encode( $picker_options, JSON_HEX_APOS ) );

        $this->addRule( function() use ( $name ){

            if( isset( $_POST[ $this->formname . $name ] ) && is_string( $_POST[ $this->formname . $name ] ) && ( empty( $_POST[ $this->formname . $name ] ) || strpos( $_POST[ $this->formname . $name ], 'https://cdn.filestackcontent.com/' ) === 0 ) ){

                if( empty( $_POST[ $this->formname . $name ] ) )
                    return true;

                $json = json_decode( file_get_contents( $this->app->filters->filestack( $_POST[ $this->formname . $name ], 'read', 'metadata', false ) ), true );

                if( isset( $json[ 'mimetype' ] ) )
                    return true;
            }

            return 'Invalid media';
        });
        return $this;
    }

    public function & addFilestackImage( $name, $label = '', $width = '', $height = '', $help = '' ){
        $this->elements[ $name ] = array( 'type' => 'filestackimage', 'valuetype' => 'simple', 'name' => $name, 'label' => $label, 'width' => $width, 'height' => $height, 'rules' => array(), 'filters' => array(), 'options' => array(), 'help' => $help );
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

    public function & addButton( $name, $label = null, $labelbutton = null, $onclick = '', $href = '', $icon = '' ){

        $this->elements[ $name ] = array( 'type' => 'button', 'name' => $name, 'onclick' => $onclick, 'label' => $label, 'labelbutton' => $labelbutton,  'rules' => array(), 'filters' => array(), 'href' => $href, 'icon' => $icon );
        return $this;
    }

    public function & addAjaxButton( $name, $labelbutton = null, $onclick = '', $href = '', $css = '', $close = false ){

        $this->elements[ $name ] = array( 'type' => 'ajaxbutton', 'isbutton' => true, 'name' => $name, 'close' => $close, 'onclick' => $onclick, 'labelbutton' => $labelbutton,  'rules' => array(), 'filters' => array(), 'href' => $href, 'css' => $css );
        return $this;
    }

    public function & deleteElement( $name ){
        if( isset( $this->elements[ $name ] ) )
            unset( $this->elements[ $name ] );

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

        if( empty( $name ) )
            $name = 'save';
        $this->elements[ $name ] = array( 'type' => 'ajax', 'isbutton' => true, 'name' => $name, 'position' => $position, 'css' => $css, 'label' => $label, 'rules' => array(), 'filters' => array(), 'options' => $options );
        $this->applyCsrf();

        return $this;
    }

    public function & setProperty( $name, $property, $value ){
        if( isset( $this->elements[ $name ] ) )
            $this->elements[ $name ][ $property ] = $value;
        return $this;
    }

    private function csrfreset(){

        $csrfnew = $this->getRandom( 8 );

        $this->app->session->set( $this->csrfname, $csrfnew );

        // add csrf to ajax
        $this->app->ajax->addFormCsrf( $this->csrfname, $csrfnew );
    
        return $csrfnew;
    }

    private function applyCsrf(){

        // csrf protection
        if( !$this->csrfinit ){

            // create csrf if not exists
            if( empty( $this->app->session->get( $this->csrfname ) ) )
                $this->csrfreset();

            $this->addRule( function(){

                $csrf = $this->app->session->get( $this->csrfname, '' );

                if( is_string( $csrf ) && !empty( $csrf ) && isset( $_POST[ $this->csrfname ] ) && $csrf === $_POST[ $this->csrfname ] ){

                    // update post for app::confirm
                    $_POST[ $this->csrfname ] = $this->csrfreset();

                    return true;
                }else{
                    $this->csrfreset();
                    return 'csrf protection';
                }
            });

            $this->csrfinit = true;
        }

    }


    public function & setSubmitMessage( $msg, $title = 'Sucess' ){

        if( $this->app->isajax )
            $this->app->ajax->msgOk( $msg, $title );

        $this->submitMessage = $msg;
        return $this;
    }

    public function & setWarningMessage( $msg, $title = 'Warning' ){

        if( $this->app->isajax )
            $this->app->ajax->msgWarning( $msg, $title );

        $this->warningMessage = $msg;
        return $this;
    }

    public function & setErrorMessage( $msg, $title = 'Errors found' ){

        if( $this->app->isajax )
            $this->app->ajax->msgError( $msg, $title );

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
            if( isset( $el[ 'type' ] ) && ( $el[ 'type' ] == 'submit' || $el[ 'type' ] == 'ajax' ) && isset( $_POST[ $this->formname . $n ] ) && ( empty( $button ) || $n == $button ) )
                return true;
        }
        return false;
    }

    public function & hide( $messageok = null, $messageerror = null ){

        if( $this->app->isajax ){
            $this->app->ajax->setFormReset( $this->formname );

            if( !empty( $this->modal ) ){
                $modal = $this->modal;
                $this->app->ajax->modalHide( $modal[ 'id' ] );
            }

            if( !empty( $messageok ) )
                $this->app->ajax->msgOk( $messageok );

            if( !empty( $messageerror ) )
                $this->app->ajax->msgError( $messageerror );
        }

        $this->hide = true;
        return $this;
    }

    public function & show(){

        // check special form element: transloadit
        $transloadit   = 0;
        $chatscroll    = 0;
        $pusherchannel = 0;
        $cameratag     = array();
        foreach( $this->elements as $n => $el ){
            if( isset( $el[ 'type' ] ) ){
                if( $el[ 'type' ] == 'transloadit' ){
                    $transloadit = 1;
                }
                if( $el[ 'type' ] == 'cameratag' || $el[ 'type' ] == 'cameratagphoto' || $el[ 'type' ] == 'cameratagvideo' || $el[ 'type' ] == 'cameratagimage' ){
                    $cameratag[] = $this->formname . $el[ 'name' ];
                }
                if( $el[ 'type' ] == 'custom' && is_a( $el[ 'obj' ], 'mychat' ) ){

                    /** @var mychat $obj */
                    $obj = $el[ 'obj' ];

                    //$transloadit   = $obj->getTransloadit();
                    $chatscroll    = '#' . $obj->getWindowId();
                    $pusherchannel = $obj->getPusherChannel();
                }
            }
        }

        // if modal undefined, create one
        if( ! isset( $this->modal['id'] ) )
            $this->setModal();

        $this->app->ajax->showForm( $this->formname, $this->app->ajax->filter( $this->__toString() ), $this->modal['id'], $transloadit, $chatscroll, $pusherchannel, $cameratag );
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
        }elseif( is_array( $values ) ){
            if( $append == false ){
                $this->valuesdefault = array();
            }

            foreach( $this->elements as $n => $el ){
                if( isset( $values[ $n ] ) ){
                    if( isset( $el[ 'obj' ] ) && method_exists( $el[ 'obj' ], 'setvalues' ) ){
                        $el[ 'obj' ]->setValues( $values[ $n ] );
                    }else{
                        $this->valuesdefault[ $n ] = $values[ $n ];
                    }
                }
            }
        }
        return $this;
    }

    public function & setDefaultValue( $elementname, $value ){
        $this->valuesdefault[ $elementname ] = $value;
        return $this;
    }

    public function & replaceDefaultValues( $values ){
        $this->setDefaultValues( $values, true );
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

                    if( ! is_callable( array( $this->app->rules, $rulename ) ) || ! call_user_func( array( $this->app->rules, $rulename ), $value, $ruleoptions, $values, $el ) ){

                        // add error message if message is not null
                        if( !is_null( $rulemessage ) )
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

        if( !$isvalid && $this->app->isajax )
            $this->app->ajax->msgError( $this->getErrors() );

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
                case 'array':       $values[ $n ] = ( isset( $_POST[ $this->formname . $n ] ) && is_array( $_POST[ $this->formname . $n ] ) ) ? $_POST[ $this->formname . $n ] : array();
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
                default:            continue;
            }

            if( $applyFilters && isset( $el[ 'filters' ] ) && is_array( $el[ 'filters' ] ) )
                foreach( $el[ 'filters' ] as $f )
                    if( is_callable( array( $this->app->filters, $f ) ) )
                        $values[ $n ] = call_user_func( array( $this->app->filters, $f ), $values[ $n ] );
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
                    if( is_callable( array( $this->app->filters, $f ) ) )
                        $v = call_user_func( array( $this->app->filters, $f ), $v );

            $this->elements[$n][ 'value' ] = $v;
        }

        return array(   'hide'          => $this->hide,
                        'submitted'     => $this->isSubmitted(),
                        'valid'         => $this->wasValid,
                        'validmsg'      => ( empty( $this->submitMessage ) ? 'Form submitted' : $this->submitMessage ),
                        'warningmsg'    => $this->warningMessage,
                        'preventmsg'    => $this->preventmsg,
                        'errors'        => $this->errors,
                        'valuesdefault' => $this->valuesdefault,
                        'name'          => $this->formname,
                        'action'        => $this->action,
                        'target'        => $this->target,
                        'elements'      => $this->elements,
                        'renderaction'  => $this->renderaction,
                        'rendersubmit'  => $this->rendersubmit,
                        'csrfname'      => $this->csrfname,
                        'csrf'          => $this->app->session->get( $this->csrfname, '' ),
                        'isajax'        => $this->app->isajax,
                        'closeb'        => $this->app->isajax && $this->closebutton,
                        'closeset'      => $this->closebuttonsettings,
                        'footer'        => $this->footer,
                        'ismodal'       => !empty( $this->modal ),
                        'modal'         => $this->modal );
    }

    public function __toString(){
        return $this->app->view->fetch( '@my/myform.twig', $this->obj() );
    }
}
