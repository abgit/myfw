<?php

    class mybcloud{
	
        public function __construct(){
            $this->app = \Slim\Slim::getInstance();
        }

        public function getInfo( & $info, $url ){
		
            $info = array();

            $endpoint = "http://thor.brightcloud.com:80/rest/uris/". urlencode( $url );

            // Establish an OAuth Consumer based on read credentials
            $consumer = new OAuthConsumer( $this->app->config( 'bcloud.k' ), $this->app->config( 'bcloud.s' ), NULL);

            // Setup OAuth request - Use NULL for OAuthToken parameter
            $request = OAuthRequest::from_consumer_and_token($consumer, NULL, "GET", $endpoint, NULL);

            // Sign the constructed OAuth request using HMAC-SHA1 - Use NULL for OAuthToken parameter
            $request->sign_request(new OAuthSignatureMethod_HMAC_SHA1(), $consumer, NULL);

            // Extract OAuth header from OAuth request object and keep it handy in a variable
            $oauth_header = $request->to_header();

            // Initialize a cURL session
            $curl = curl_init($endpoint);  

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);  
            curl_setopt($curl, CURLOPT_FAILONERROR, false);  
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            // Include OAuth Header as part of HTTP headers in the cURL request
            curl_setopt($curl, CURLOPT_HTTPHEADER, array($oauth_header));   

            // Make OAuth-signed request to the BCWS server and get hold of server response
            $response = curl_exec($curl);  

            // Close cURL session
            curl_close($curl);  

            if( $response ){				
                $xml = simplexml_load_string( $response );
                if( intval( $xml->response->status ) == 200 ){

                    $info = array(  'categoryid'    => intval( $xml->response->categories->cat->catid ),
                                    'categoryname'  => 'Real Estate',
                                    'score'         => intval( $xml->response->bcri ) );
                }
            }

            return ! empty( $info );
        }

        public function getCategoriesList(){

            return array( 
                 1=>array("Real Estate","Information on renting, buying, or selling real estate or properties.  Tips on buying or selling a home.  Real estate agents, rental or relocation services, and property improvement."),
                 2=>array("Computer and Internet Security","Computer/Internet security, security discussion groups."),
                 3=>array("Financial Services","Banking services and other types of financial information, such as loans, accountancy, actuaries, banks, mortgages, and general insurance companies. Does not include sites that offer market information, brokerage or trading services."),
                 4=>array("Business and Economy","Business firms, corporate websites , business information, economics, marketing, management, and entrepreneurship."),
                 5=>array("Computer and Internet Info","General computer and Internet sites, technical information. SaaS sites and other URLs that deliver internet services."),
                 6=>array("Auctions","Sites that support the offering and purchasing of goods between individuals as their main purpose. Does not include classified advertisements."),
                 7=>array("Shopping","Department stores, retail stores, company catalogs and other sites that allow online consumer or business shopping and the purchase of goods and services."),
                 8=>array("Cult and Occult","Methods, means of instruction, or other resources to interpret, affect or influence real events through the use of astrology, spells, curses, magic powers, satanic or supernatural beings. Includes horoscope sites."),
                 9=>array("Travel","Airlines and flight booking agencies. Travel planning, reservations, vehicle rentals, descriptions of travel destinations, or promotions for hotels or casinos. Car rentals."),
                10=>array("Abused Drugs","Discussion or remedies for illegal, illicit, or abused drugs such as heroin, cocaine, or other street drugs. Information on legal highs': glue sniffing, misuse of prescription drugs or abuse of other legal substances."),
                11=>array("Adult and Pornography","Sexually explicit material for the purpose of arousing a sexual or prurient interest. Adult products including sex toys, CD-ROMs, and videos. Online groups, including newsgroups and forums, that are sexually explicit in nature. Erotic stories and textual descriptions of sexual acts. Adult services including videoconferencing, escort services, and strip clubs. Sexually explicit art."),
                12=>array("Home and Garden","Home issues and products, including maintenance, home safety, decor, cooking, gardening, home electronics, design, etc."),
                13=>array("Military","Information on military branches, armed services, and military history."),
                14=>array("Social Networking","These are social networking sites that have user communities where users interact, post messages, pictures, and otherwise communicate. These sites were formerly part of Personal Sites and Blogs but have been removed to this new category to provide differentiation and more granular policy."),
                15=>array("Dead Sites","These are dead sites that do not respond to http queries. Policy engines should usually treat these as Uncategorized' sites."),
                16=>array("Individual Stock Advice and Tools","Promotion and facilitation of securities trading and management of investment assets. Also includes information on financial investment strategies, quotes, and news."),
                17=>array("Training and Tools","Distance education and trade schools, online courses, vocational training, software training, skills training."),
                18=>array("Dating","Dating websites focused on establishing personal relationships."),
                19=>array("Sex Education","Information on reproduction, sexual development, safe sex practices, sexually transmitted diseases, sexuality, birth control, sexual development, tips for better sex as well as products used for sexual enhancement, and contraceptives."),
                20=>array("Religion","Conventional or unconventional religious or quasi-religious subjects, as well as churches, synagogues, or other houses of worship."),
                21=>array("Entertainment and Arts","Motion pictures, videos, television, music and programming guides, books, comics, movie theatres, galleries, artists or reviews on entertainment. Performing arts (theatre, vaudeville, opera, symphonies, etc.). Museums, galleries, artist sites (sculpture, photography, etc.)."),
                22=>array("Personal sites and Blogs","Personal websites posted by individuals or groups, as well as blogs."),
                23=>array("Legal","Legal websites, law firms, discussions and analysis of legal issues."),
                24=>array("Local Information","City guides and tourist information, including restaurants, area/regional information, and local points of interest."),
                25=>array("Streaming Media","Sales, delivery, or streaming of audio or video content, including sites that provide downloads for such viewers."),
                26=>array("Job Search","Assistance in finding employment, and tools for locating prospective employers, or employers looking for employees."),
                27=>array("Gambling","Gambling or lottery web sites that invite the use of real or virtual money. Information or advice for placing wagers, participating in lotteries, gambling, or running numbers. Virtual casinos and offshore gambling ventures. Sports picks and betting pools. Virtual sports and fantasy leagues that offer large rewards or request significant wagers. Hotel and Resort sites that do not enable gambling on the site are categorized in Travel or Local Information."),
                28=>array("Translation","URL and language translation sites that allow users to see URL pages in other languages. These sites can also allow users to circumvent filtering as the target page's content is presented within the context of the translator's URL. These sites were formerly part of Proxy Avoidance and Anonymizers, but have been removed to this new category to provide differentiation and more granular policy."),
                29=>array("Reference and Research","Personal, professional, or educational reference material, including online dictionaries, maps, census, almanacs, library catalogues, genealogy, and scientific information."),
                30=>array("Shareware and Freeware","Software, screensavers, icons, wallpapers, utilities, ringtones. Includes downloads that request a donation, and open source projects."),
                31=>array("Peer to Peer","Peer to peer clients and access. Includes torrents, music download programs."),
                32=>array("Marijuana","Marijuana use, cultivation, history, culture, legal issues."),
                33=>array("Hacking","Illegal or questionable access to or the use of communications equipment/software. Development and distribution of programs that may allow compromise of networks and systems. Avoidance of licensing and fees for computer programs and other systems."),
                34=>array("Games","Game playing or downloading, video games, computer games, electronic games, tips, and advice on games or how to obtain cheat codes. Also includes sites dedicated to selling board games as well as journals and magazines dedicated to game playing. Includes sites that support or host online sweepstakes and giveaways. Includes fantasy sports sites that also host games or game-playing."),
                35=>array("Philosophy and Political Advocacy","Politics, philosophy, discussions, promotion of a particular viewpoint or stance in order to further a cause."),
                36=>array("Weapons","Sales, reviews, or descriptions of weapons such as guns, knives or martial arts devices, or provide information on their use, accessories, or other modifications."),
                37=>array("Pay to Surf","Sites that pay users in the form of cash or prizes, for clicking on or reading specific links, email, or web pages."),
                38=>array("Hunting and Fishing","Sport hunting, gun clubs, and fishing."),
                39=>array("Society","A variety of topics, groups, and associations relevant to the general populace, broad issues that impact a variety of people, including safety, children, societies, and philanthropic groups."),
                40=>array("Educational Institutions","Pre-school, elementary, secondary, high school, college, university, and vocational school and other educational content and information,including enrollment, tuition, and syllabus."),
                41=>array("Online Greeting cards","Online Greeting card sites."),
                42=>array("Sports","Team or conference web sites, international, national, college, professional scores and schedules; sports-related online magazines or newsletters, fantasy sports and virtual sports leagues."),
                43=>array("Swimsuits & Intimate Apparel","Swimsuits, intimate apparel or other types of suggestive clothing."),
                44=>array("Questionable","Tasteless humor, 'get rich quick' sites, and sites that manipulate the browser user experience or client in some unusual, unexpected, or suspicious manner."),
                45=>array("Kids","Sites designed specifically for children and teenagers."),
                46=>array("Hate and Racism","Sites that support content and languages or hate crime and racism such as Nazi, neo-Nazi, Ku Klux Klan, etc."),
                47=>array("Personal Storage","Online storage and posting of files, music, pictures, and other data."),
                48=>array("Violence","Sites that advocate violence, depictions, and methods, including game/comic violence and suicide."),
                49=>array("Keyloggers and Monitoring","Downloads and discussion of software agents that track a user's keystrokes or monitor their web surfing habits."),
                50=>array("Search Engines","Search interfaces using key words or phrases. Returned results may include text, websites, images, videos, and files."),
                51=>array("Internet Portals","Web sites that aggregate a broader set of Internet content and topics, and which typically serve as the starting point for an end user."),
                52=>array("Web Advertisements","Advertisements, media, content, and banners."),
                53=>array("Cheating","Sites that support cheating and contain such materials, including free essays, exam copies, plagiarism, etc."),
                54=>array("Gross","Vomit and other bodily functions, bloody clothing, etc."),
                55=>array("Web based email","Sites offering web based email and email clients."),
                56=>array("Malware Sites","Malicious content including executables, drive-by infection sites, malicious scripts, viruses, trojans, and code."),
                57=>array("Phishing and Other Frauds","Phishing, pharming, and other sites that pose as a reputable site, usually to harvest personal information from a user. These sites are typically quite short-lived, so examples don?t last long. Please contact us if you need fresh data."),
                58=>array("Proxy Avoidance and Anonymizers","Proxy servers and other methods to gain access to URLs in any way that bypasses URL filtering or monitoring. Web-based translation sites that circumvent filtering."),
                59=>array("Spyware and Adware","Spyware or Adware sites that provide or promote information gathering or tracking that is unknown to, or without the explicit consent of, the end user or the organization, also unsolicited advertising popups and programs that may be installed on a user's computer."),
                60=>array("Music","Music sales, distribution, streaming, information on musical groups and performances, lyrics, and the music business."),
                61=>array("Government","Information on government, government agencies and government services such as taxation, public, and emergency services. Also includes sites that discuss or explain laws of various governmental entities. Includes local, county, state, and national government sites."),
                62=>array("Nudity","Nude or seminude depictions of the human body. These depictions are not necessarily sexual in intent or effect, but may include sites containing nude paintings or photo galleries of artistic nature. This category also includes nudist or naturist sites that contain pictures of nude individuals."),
                63=>array("News and Media","Current events or contemporary issues of the day. Also includes radio stations and magazines, newspapers online, headline news sites, newswire services, personalized news services, and weather sites"),
                64=>array("Illegal","Criminal activity, how not to get caught, copyright and intellectual property violations, etc."),
                65=>array("Content Delivery Networks","Delivery of content and data for third parties, including ads, media, files, images, and video."),
                66=>array("Internet Communications","Internet telephony, messaging, VoIP services and related businesses."),
                67=>array("Bot Nets","These are URLs, typically IP addresses, which are determined to be part of a Bot network, from which network attacks are launched. Attacks may include SPAM messages, DOS, SQL injections, proxy jacking, and other unsolicited contacts."),
                68=>array("Abortion","Abortion topics, either pro-life or pro-choice."),
                69=>array("Health and Medicine","General health, fitness, well-being, including traditional and non-traditional methods and topics. Medical information on ailments, various conditions, dentistry, psychiatry, optometry, and other specialties. Hospitals and doctor offices. Medical insurance. Cosmetic surgery."),
                71=>array("SPAM URLs","URLs contained in SPAM."),
                74=>array("Dynamically Generated Content","Domains that generate content dynamically based on arguments to their URL or other information (like geo-location) on the incoming web request."),
                75=>array("Parked Domains","Parked domains are URLs which host limited content or click-through ads which may generate revenue for the hosting entities but generally do not contain content useful to the end user. Also includes Under Construction, folders, and web server default home pages."),
                76=>array("Alcohol and Tobacco","Sites that provide information on, promote, or support the sale of alcoholic beverages or tobacco products and associated paraphernalia."),
                78=>array("Image and Video Search","Photo and image searches, online photo albums/digital photo exchange, image hosting."),
                79=>array("Fashion and Beauty","Fashion or glamour magazines, beauty, clothes, cosmetics, style."),
                80=>array("Recreation and Hobbies","Information, associations, forums and publications on recreational pastimes such as collecting, kit airplanes, outdoor activities such as hiking, camping, rock climbing, specific arts, craft, or techniques; animal and pet related information, including breed-specifics, training, shows and humane societies."),
                81=>array("Motor Vehicles","Car reviews, vehicle purchasing or sales tips, parts catalogs. Auto trading, photos, discussion of vehicles including motorcycles, boats, cars, trucks and RVs. Journals and magazines on vehicle modifications."),
                82=>array("Web Hosting","Free or paid hosting services for web pages and information concerning their development, publication and promotion.")
            );
        }
    }

class OAuthException extends Exception {
}

class OAuthConsumer {
	  public $key;
	  public $secret;

	  function __construct($key, $secret, $callback_url=NULL) {
	    $this->key = $key;
	    $this->secret = $secret;
	    $this->callback_url = $callback_url;
	  }

	  function __toString() {
	    return "OAuthConsumer[key=$this->key,secret=$this->secret]";
	  }
}

	class OAuthToken {
	  // access tokens and request tokens
	  public $key;
	  public $secret;

	  /**
	   * key = the token
	   * secret = the token secret
	   */
	  function __construct($key, $secret) {
	    $this->key = $key;
	    $this->secret = $secret;
	  }

	  /**
	   * generates the basic string serialization of a token that a server
	   * would respond to request_token and access_token calls with
	   */
	  function to_string() {
	    return "oauth_token=" .
	           OAuthUtil::urlencode_rfc3986($this->key) .
	           "&oauth_token_secret=" .
	           OAuthUtil::urlencode_rfc3986($this->secret);
	  }

	  function __toString() {
	    return $this->to_string();
	  }
}

class OAuthSignatureMethod {
	  public function check_signature(&$request, $consumer, $token, $signature) {
	    $built = $this->build_signature($request, $consumer, $token);
	    return $built == $signature;
	  }
}

class OAuthSignatureMethod_HMAC_SHA1 extends OAuthSignatureMethod {
	  function get_name() {
	    return "HMAC-SHA1";
	  }

	  public function build_signature($request, $consumer, $token) {
	    $base_string = $request->get_signature_base_string();
	    $request->base_string = $base_string;
	
	    $key_parts = array(
	      $consumer->secret,
	      ($token) ? $token->secret : ""
	    );

	    $key_parts = OAuthUtil::urlencode_rfc3986($key_parts);
	    $key = implode('&', $key_parts);

	    return base64_encode(hash_hmac('sha1', $base_string, $key, true));
	  }
}

class OAuthSignatureMethod_PLAINTEXT extends OAuthSignatureMethod {
	  public function get_name() {
	    return "PLAINTEXT";
	  }

	  public function build_signature($request, $consumer, $token) {
	    $sig = array(
	      OAuthUtil::urlencode_rfc3986($consumer->secret)
	    );

	    if ($token) {
	      array_push($sig, OAuthUtil::urlencode_rfc3986($token->secret));
	    } else {
	      array_push($sig, '');
	    }

	    $raw = implode("&", $sig);
	    // for debug purposes
	    $request->base_string = $raw;

	    return OAuthUtil::urlencode_rfc3986($raw);
	  }
}

class OAuthSignatureMethod_RSA_SHA1 extends OAuthSignatureMethod {
	  public function get_name() {
	    return "RSA-SHA1";
	  }

	  protected function fetch_public_cert(&$request) {
	    // not implemented yet, ideas are:
	    // (1) do a lookup in a table of trusted certs keyed off of consumer
	    // (2) fetch via http using a url provided by the requester
	    // (3) some sort of specific discovery code based on request
	    //
	    // either way should return a string representation of the certificate
	    throw Exception("fetch_public_cert not implemented");
	  }

	  protected function fetch_private_cert(&$request) {
	    // not implemented yet, ideas are:
	    // (1) do a lookup in a table of trusted certs keyed off of consumer
	    //
	    // either way should return a string representation of the certificate
	    throw Exception("fetch_private_cert not implemented");
	  }

  public function build_signature(&$request, $consumer, $token) {
	    $base_string = $request->get_signature_base_string();
	    $request->base_string = $base_string;

	    // Fetch the private key cert based on the request
	    $cert = $this->fetch_private_cert($request);

	    // Pull the private key ID from the certificate
	    $privatekeyid = openssl_get_privatekey($cert);

	    // Sign using the key
	    $ok = openssl_sign($base_string, $signature, $privatekeyid);

	    // Release the key resource
	    openssl_free_key($privatekeyid);

	    return base64_encode($signature);
	  }

	  public function check_signature(&$request, $consumer, $token, $signature) {
	    $decoded_sig = base64_decode($signature);

	    $base_string = $request->get_signature_base_string();

	    // Fetch the public key cert based on the request
	    $cert = $this->fetch_public_cert($request);

	    // Pull the public key ID from the certificate
	    $publickeyid = openssl_get_publickey($cert);

	    // Check the computed signature against the one passed in the query
	    $ok = openssl_verify($base_string, $decoded_sig, $publickeyid);

	    // Release the key resource
	    openssl_free_key($publickeyid);

	    return $ok == 1;
	  }
}

class OAuthRequest {
	  private $parameters;
	  private $http_method;
	  private $http_url;
	  // for debug purposes
	  public $base_string;
	  public static $version = '1.0';
	  public static $POST_INPUT = 'php://input';

	  function __construct($http_method, $http_url, $parameters=NULL) {
	    @$parameters or $parameters = array();
	    $this->parameters = $parameters;
	    $this->http_method = $http_method;
	    $this->http_url = $http_url;
	  }


	  /**
	   * attempt to build up a request from what was passed to the server
	   */
	  public static function from_request($http_method=NULL, $http_url=NULL, $parameters=NULL) {
	    $scheme = (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] != "on")
	              ? 'http'
	              : 'https';
	    @$http_url or $http_url = $scheme .
                              '://' . $_SERVER['HTTP_HOST'] .
                              ':' .
                              $_SERVER['SERVER_PORT'] .
                              $_SERVER['REQUEST_URI'];
	    @$http_method or $http_method = $_SERVER['REQUEST_METHOD'];

	    // We weren't handed any parameters, so let's find the ones relevant to
	    // this request.
	    // If you run XML-RPC or similar you should use this to provide your own
	    // parsed parameter-list
	    if (!$parameters) {
	      // Find request headers
	      $request_headers = OAuthUtil::get_headers();

	      // Parse the query-string to find GET parameters
	      $parameters = OAuthUtil::parse_parameters($_SERVER['QUERY_STRING']);

	      // It's a POST request of the proper content-type, so parse POST
	      // parameters and add those overriding any duplicates from GET
	      if ($http_method == "POST"
	          && @strstr($request_headers["Content-Type"],
	                     "application/x-www-form-urlencoded")
	          ) {
	        $post_data = OAuthUtil::parse_parameters(
	          file_get_contents(self::$POST_INPUT)
	        );
	        $parameters = array_merge($parameters, $post_data);
	      }

	      // We have a Authorization-header with OAuth data. Parse the header
	      // and add those overriding any duplicates from GET or POST
	      if (@substr($request_headers['Authorization'], 0, 6) == "OAuth ") {
	        $header_parameters = OAuthUtil::split_header(
	          $request_headers['Authorization']
	        );
	        $parameters = array_merge($parameters, $header_parameters);
	      }
	    }
	    return new OAuthRequest($http_method, $http_url, $parameters);
  }

  /**
   * pretty much a helper function to set up the request
   */
  public static function from_consumer_and_token($consumer, $token, $http_method, $http_url, $parameters=NULL) {
    @$parameters or $parameters = array();
    $defaults = array("oauth_version" => OAuthRequest::$version,
                      "oauth_nonce" => OAuthRequest::generate_nonce(),
                      "oauth_timestamp" => OAuthRequest::generate_timestamp(),
                      "oauth_consumer_key" => $consumer->key);
    if ($token)
      $defaults['oauth_token'] = $token->key;

    $parameters = array_merge($defaults, $parameters);

    return new OAuthRequest($http_method, $http_url, $parameters);
  }

  public function set_parameter($name, $value, $allow_duplicates = true) {
    if ($allow_duplicates && isset($this->parameters[$name])) {
      // We have already added parameter(s) with this name, so add to the list
      if (is_scalar($this->parameters[$name])) {
        // This is the first duplicate, so transform scalar (string)
        // into an array so we can add the duplicates
        $this->parameters[$name] = array($this->parameters[$name]);
      }

      $this->parameters[$name][] = $value;
    } else {
      $this->parameters[$name] = $value;
    }
  }

  public function get_parameter($name) {
    return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
  }

  public function get_parameters() {
    return $this->parameters;
  }

  public function unset_parameter($name) {
    unset($this->parameters[$name]);
  }

  /**
   * The request parameters, sorted and concatenated into a normalized string.
   * @return string
   */
  public function get_signable_parameters() {
    // Grab all parameters
    $params = $this->parameters;

    // Remove oauth_signature if present
    // Ref: Spec: 9.1.1 ("The oauth_signature parameter MUST be excluded.")
    if (isset($params['oauth_signature'])) {
      unset($params['oauth_signature']);
    }

    return OAuthUtil::build_http_query($params);
  }

  /**
   * Returns the base string of this request
   *
   * The base string defined as the method, the url
   * and the parameters (normalized), each urlencoded
   * and the concated with &.
   */
  public function get_signature_base_string() {
    $parts = array(
      $this->get_normalized_http_method(),
      $this->get_normalized_http_url(),
      $this->get_signable_parameters()
    );

    $parts = OAuthUtil::urlencode_rfc3986($parts);

    return implode('&', $parts);
  }

  /**
   * just uppercases the http method
   */
  public function get_normalized_http_method() {
    return strtoupper($this->http_method);
  }

  /**
   * parses the url and rebuilds it to be
   * scheme://host/path
   */
  public function get_normalized_http_url() {
    $parts = parse_url($this->http_url);

    $port = @$parts['port'];
    $scheme = $parts['scheme'];
    $host = $parts['host'];
    $path = @$parts['path'];

    $port or $port = ($scheme == 'https') ? '443' : '80';

    if (($scheme == 'https' && $port != '443')
        || ($scheme == 'http' && $port != '80')) {
      $host = "$host:$port";
    }
    return "$scheme://$host$path";
  }

  /**
   * builds a url usable for a GET request
   */
  public function to_url() {
    $post_data = $this->to_postdata();
    $out = $this->get_normalized_http_url();
    if ($post_data) {
      $out .= '?'.$post_data;
    }
    return $out;
  }

  /**
   * builds the data one would send in a POST request
   */
  public function to_postdata() {
    return OAuthUtil::build_http_query($this->parameters);
  }

  /**
   * builds the Authorization: header
   */
  public function to_header() {
    $out ='Authorization: OAuth realm=""';
    $total = array();
    foreach ($this->parameters as $k => $v) {
      if (substr($k, 0, 5) != "oauth") continue;
      if (is_array($v)) {
        throw new OAuthException('Arrays not supported in headers');
      }
      $out .= ',' .
              OAuthUtil::urlencode_rfc3986($k) .
              '="' .
              OAuthUtil::urlencode_rfc3986($v) .
              '"';
    }
    return $out;
  }

  public function __toString() {
    return $this->to_url();
  }


  public function sign_request($signature_method, $consumer, $token) {
    $this->set_parameter(
      "oauth_signature_method",
      $signature_method->get_name(),
      false
    );
    $signature = $this->build_signature($signature_method, $consumer, $token);
    $this->set_parameter("oauth_signature", $signature, false);
  }

  public function build_signature($signature_method, $consumer, $token) {
    $signature = $signature_method->build_signature($this, $consumer, $token);
    return $signature;
  }

  /**
   * util function: current timestamp
   */
  private static function generate_timestamp() {
    return time();
  }

  /**
   * util function: current nonce
   */
  private static function generate_nonce() {
    $mt = microtime();
    $rand = mt_rand();

    return md5($mt . $rand); // md5s look nicer than numbers
  }
}

class OAuthServer {
  protected $timestamp_threshold = 300; // in seconds, five minutes
  protected $version = 1.0;             // hi blaine
  protected $signature_methods = array();

  protected $data_store;

  function __construct($data_store) {
    $this->data_store = $data_store;
  }

  public function add_signature_method($signature_method) {
    $this->signature_methods[$signature_method->get_name()] =
      $signature_method;
  }

  // high level functions

  /**
   * process a request_token request
   * returns the request token on success
   */
  public function fetch_request_token(&$request) {
    $this->get_version($request);

    $consumer = $this->get_consumer($request);

    // no token required for the initial token request
    $token = NULL;

    $this->check_signature($request, $consumer, $token);

    $new_token = $this->data_store->new_request_token($consumer);

    return $new_token;
  }

  /**
   * process an access_token request
   * returns the access token on success
   */
  public function fetch_access_token(&$request) {
    $this->get_version($request);

    $consumer = $this->get_consumer($request);

    // requires authorized request token
    $token = $this->get_token($request, $consumer, "request");


    $this->check_signature($request, $consumer, $token);

    $new_token = $this->data_store->new_access_token($token, $consumer);

    return $new_token;
  }

  /**
   * verify an api call, checks all the parameters
   */
  public function verify_request(&$request) {
    $this->get_version($request);
    $consumer = $this->get_consumer($request);
    $token = $this->get_token($request, $consumer, "access");
    $this->check_signature($request, $consumer, $token);
    return array($consumer, $token);
  }

  // Internals from here
  /**
   * version 1
   */
  private function get_version(&$request) {
    $version = $request->get_parameter("oauth_version");
    if (!$version) {
      $version = 1.0;
    }
    if ($version && $version != $this->version) {
      throw new OAuthException("OAuth version '$version' not supported");
    }
    return $version;
  }

  /**
   * figure out the signature with some defaults
   */
  private function get_signature_method(&$request) {
    $signature_method =
        @$request->get_parameter("oauth_signature_method");
    if (!$signature_method) {
      $signature_method = "PLAINTEXT";
    }
    if (!in_array($signature_method,
                  array_keys($this->signature_methods))) {
      throw new OAuthException(
        "Signature method '$signature_method' not supported " .
        "try one of the following: " .
        implode(", ", array_keys($this->signature_methods))
      );
    }
    return $this->signature_methods[$signature_method];
  }

  /**
   * try to find the consumer for the provided request's consumer key
   */
  private function get_consumer(&$request) {
    $consumer_key = @$request->get_parameter("oauth_consumer_key");
    if (!$consumer_key) {
      throw new OAuthException("Invalid consumer key");
    }

    $consumer = $this->data_store->lookup_consumer($consumer_key);
    if (!$consumer) {
      throw new OAuthException("Invalid consumer");
    }

    return $consumer;
  }

  /**
   * try to find the token for the provided request's token key
   */
  private function get_token(&$request, $consumer, $token_type="access") {
    $token_field = @$request->get_parameter('oauth_token');
    $token = $this->data_store->lookup_token(
      $consumer, $token_type, $token_field
    );
    if (!$token) {
      throw new OAuthException("Invalid $token_type token: $token_field");
    }
    return $token;
  }

  /**
   * all-in-one function to check the signature on a request
   * should guess the signature method appropriately
   */
  private function check_signature(&$request, $consumer, $token) {
    // this should probably be in a different method
    $timestamp = @$request->get_parameter('oauth_timestamp');
    $nonce = @$request->get_parameter('oauth_nonce');

    $this->check_timestamp($timestamp);
    $this->check_nonce($consumer, $token, $nonce, $timestamp);

    $signature_method = $this->get_signature_method($request);

    $signature = $request->get_parameter('oauth_signature');
    $valid_sig = $signature_method->check_signature(
      $request,
      $consumer,
      $token,
      $signature
    );

    if (!$valid_sig) {
      throw new OAuthException("Invalid signature");
    }
  }

  /**
   * check that the timestamp is new enough
   */
  private function check_timestamp($timestamp) {
    // verify that timestamp is recentish
    $now = time();
    if ($now - $timestamp > $this->timestamp_threshold) {
      throw new OAuthException(
        "Expired timestamp, yours $timestamp, ours $now"
      );
    }
  }

  /**
   * check that the nonce is not repeated
   */
  private function check_nonce($consumer, $token, $nonce, $timestamp) {
    // verify that the nonce is uniqueish
    $found = $this->data_store->lookup_nonce(
      $consumer,
      $token,
      $nonce,
      $timestamp
    );
    if ($found) {
      throw new OAuthException("Nonce already used: $nonce");
    }
  }

}

class OAuthDataStore {
  function lookup_consumer($consumer_key) {
    // implement me
  }

  function lookup_token($consumer, $token_type, $token) {
    // implement me
  }

  function lookup_nonce($consumer, $token, $nonce, $timestamp) {
    // implement me
  }

  function new_request_token($consumer) {
    // return a new token attached to this consumer
  }

  function new_access_token($token, $consumer) {
    // return a new access token attached to this consumer
    // for the user associated with this token if the request token
    // is authorized
    // should also invalidate the request token
  }

}

class OAuthUtil {
  public static function urlencode_rfc3986($input) {
  if (is_array($input)) {
    return array_map(array('OAuthUtil', 'urlencode_rfc3986'), $input);
  } else if (is_scalar($input)) {
    return str_replace(
      '+',
      ' ',
      str_replace('%7E', '~', rawurlencode($input))
    );
  } else {
    return '';
  }
}


  // This decode function isn't taking into consideration the above
  // modifications to the encoding process. However, this method doesn't
  // seem to be used anywhere so leaving it as is.
  public static function urldecode_rfc3986($string) {
    return urldecode($string);
  }

  // Utility function for turning the Authorization: header into
  // parameters, has to do some unescaping
  // Can filter out any non-oauth parameters if needed (default behaviour)
  public static function split_header($header, $only_allow_oauth_parameters = true) {
    $pattern = '/(([-_a-z]*)=("([^"]*)"|([^,]*)),?)/';
    $offset = 0;
    $params = array();
    while (preg_match($pattern, $header, $matches, PREG_OFFSET_CAPTURE, $offset) > 0) {
      $match = $matches[0];
      $header_name = $matches[2][0];
      $header_content = (isset($matches[5])) ? $matches[5][0] : $matches[4][0];
      if (preg_match('/^oauth_/', $header_name) || !$only_allow_oauth_parameters) {
        $params[$header_name] = OAuthUtil::urldecode_rfc3986($header_content);
      }
      $offset = $match[1] + strlen($match[0]);
    }

    if (isset($params['realm'])) {
      unset($params['realm']);
    }

    return $params;
  }

  // helper to try to sort out headers for people who aren't running apache
  public static function get_headers() {
    if (function_exists('apache_request_headers')) {
      // we need this to get the actual Authorization: header
      // because apache tends to tell us it doesn't exist
      return apache_request_headers();
    }
    // otherwise we don't have apache and are just going to have to hope
    // that $_SERVER actually contains what we need
    $out = array();
    foreach ($_SERVER as $key => $value) {
      if (substr($key, 0, 5) == "HTTP_") {
        // this is chaos, basically it is just there to capitalize the first
        // letter of every word that is not an initial HTTP and strip HTTP
        // code from przemek
        $key = str_replace(
          " ",
          "-",
          ucwords(strtolower(str_replace("_", " ", substr($key, 5))))
        );
        $out[$key] = $value;
      }
    }
    return $out;
  }

  // This function takes a input like a=b&a=c&d=e and returns the parsed
  // parameters like this
  // array('a' => array('b','c'), 'd' => 'e')
  public static function parse_parameters( $input ) {
    if (!isset($input) || !$input) return array();

    $pairs = split('&', $input);

    $parsed_parameters = array();
    foreach ($pairs as $pair) {
      $split = split('=', $pair, 2);
      $parameter = OAuthUtil::urldecode_rfc3986($split[0]);
      $value = isset($split[1]) ? OAuthUtil::urldecode_rfc3986($split[1]) : '';

      if (isset($parsed_parameters[$parameter])) {
        // We have already recieved parameter(s) with this name, so add to the list
        // of parameters with this name

        if (is_scalar($parsed_parameters[$parameter])) {
          // This is the first duplicate, so transform scalar (string) into an array
          // so we can add the duplicates
          $parsed_parameters[$parameter] = array($parsed_parameters[$parameter]);
        }

        $parsed_parameters[$parameter][] = $value;
      } else {
        $parsed_parameters[$parameter] = $value;
      }
    }
    return $parsed_parameters;
  }

  public static function build_http_query($params) {
    if (!$params) return '';

    // Urlencode both keys and values
    $keys = OAuthUtil::urlencode_rfc3986(array_keys($params));
    $values = OAuthUtil::urlencode_rfc3986(array_values($params));
    $params = array_combine($keys, $values);

    // Parameters are sorted by name, using lexicographical byte value ordering.
    // Ref: Spec: 9.1.1 (1)
    uksort($params, 'strcmp');

    $pairs = array();
    foreach ($params as $parameter => $value) {
      if (is_array($value)) {
        // If two or more parameters share the same name, they are sorted by their value
        // Ref: Spec: 9.1.1 (1)
        natsort($value);
        foreach ($value as $duplicate_value) {
          $pairs[] = $parameter . '=' . $duplicate_value;
        }
      } else {
        $pairs[] = $parameter . '=' . $value;
      }
    }
    // For each parameter, the name is separated from the corresponding value by an '=' character (ASCII code 61)
    // Each name-value pair is separated by an '&' character (ASCII code 38)
    return implode('&', $pairs);
  }
}

?>
