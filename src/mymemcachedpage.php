<?php

use \Slim\Http\Request as Request;
use \Slim\Http\Response as Response;


class mymemcachedpage{

    /**
     * @var array
     */
    protected $settings;

    /**
     * Constructor
     *
     * @param array $settings
     */
    public function __construct($settings = [])
    {
        $defaults = [
        ];
        $settings = array_merge($defaults, $settings);

        $this->settings = $settings;
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        $key = md5( $request->getMethod() . '||' . $request->getUri()->getPath() . '||' . $request->getBody()->getContents() );

        $item = $m->get($key);
        if ($m->getResultCode() == Memcached::RES_SUCCESS) {

        } else {
            // item does not exist ($item is probably false)
        }


        return $next($request, $response);
    }

}
