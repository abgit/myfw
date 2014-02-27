start
----
To use myfw all we need is to include myfw.php file and init myfw object.

```php
require 'myfw.php';

$app = new myfw();
   ...
$app->run();
```

environment
----
myfw can be use at least in two environments: web or cron.

**Web environment.** When used in web environment we need to add `get()`, `post()`, or `map()` methods and a last `run()` at end. `get()`, `post()` or `map()` methods require at least two arguments: url to match and function to execute.

```php
require 'myfw.php';

$app = new myfw();
$app->get( '/', function(){
  ...
});

$app->run();
```

**Cron environment.** When used in cron environment we just need to add `cron()` methods. Cron method have two arguments, a string passed when executing cron by command line and function to execute.

```php
require 'myfw.php';

$app = new myfw();
$app->cron( 'somearg', function(){
  ...
});

$app->cron( 'somearg2', function(){
  ...
});
```

To execute previous 'somearg' cron, we only need to load file and specify 'somearg' in command line:
`/bin/usr/php file.php somearg`.

hello world
----
Let's create a simple example. Load a template file from our template directory, assign an url variable and display.

```php
require 'myfw.php';

// init and set templates directory
$app = new myfw();
$app->config( 'templates.path', __DIR__ . '/templates' );

// on root
$app->get( '/', function() use ($app){
    $app->render( 'mytemplate', array( 'str' => 'Hello world' ) );
});

$app->run();
```

We need to inform myfw by using `config()` where are our template files so that `render()` can load them.

arguments
----
When using dynamic url's we must use variables and pass them in function. Example: display a news item based on url.

```php
require 'myfw.php';

// init and set template directory
$app = new myfw();
$app->config( 'templates.path', __DIR__ . '/templates' );

$app->get( '/news/:id', function($id) use ($app){
    $app->render( 'mytemplate', array( 'str' => 'My news id is ' . $id ) );
});

$app->run();
```

Because myfw is integrated with twig template engine, for previous example we need a `templates/mytemplate.tpl` file:

```python
{{ str }}
```

conditions
----
For each dynamic argument, we must add a pattern (eg: `:id` ), and a function argument (eg: `function($id)`).
As good practice, we should always filter these arguments to control argument limits, sql injections and other problems. myfw has the `setConditions()` method that should describe **all** arguments and be placed on top before any action.

```php
require 'myfw.php';

$app = new myfw();
$app->setConditions( array( 'id'   => '[0-9]{1,5}',
                            'arg1' => '[0-9a-zA-Z]{3,50}',
                            'arg2' => 'a|b' ) );

$app->get( '/news/:id', function($id) use ($app){
    ...
});

$app->get( '/other/:arg1/:arg2', function($arg1,$arg2) use ($app){
    ...
});

$app->run();
```

As alternative we can customize each action using `conditions()` method. Example:
```php
$app->get( '/news/:id', function($id) use ($app){
    ...
})->conditions( array( 'id' => '[0-9]{1,5}' ) );
```

mode
----
Most of times we have at least two environments: development (when our app is executed locally and some debugging is enabled) and production (when our app is executed on a remote server). This means that we need to assign distinct configuration options depending of our mode. To do this we only need to predefine config options using `configureMode()` for each mode and assign mode previously. Let's predefine 'development' and 'production' modes with different config settings:

```php
require 'myfw.php';

$app = new myfw();

// choose mode or just create a php.ini SLIM_MODE variable
$app->config( 'mode', 'production' );

$app->configureMode( 'production', function () use ($app) {
    $app->config( array(
        'log.enable' => true,
        'debug' => false
    ));
});

$app->configureMode( 'development', function () use ($app) {
    $app->config( array(
        'log.enable' => false,
        'debug' => true
    ));
});

...

$app->run();
```

group
----
Sometimes is useful to create url actions that have same prefix. Instead of specifying each action separately we can group them. This has several advantages: code becomes much cleaner and we can create global features that affect all sub actions.

```php
$app = new myfw();

$app->group('/backend', function() use ($app){

        $app->get('/adduser/:id', function ($id){
          ...
        });

        $app->get('/showuser/:id', function ($id){
          ...
        });
});

...

$app->run();
```

form
----
On myfw, creating a form and process all logic flow is very easy. All we need is to define form behaviour and myfw will process everything.
We should:

* create form elements, rules and (optional) filters;
* default values to show when form is displayed for first time;
* what to do when for is submitted and valid

```php
require 'myfw.php';

$app = new myfw();
$app->map( '/contact', function() use ($app){
  
  $app->form()->addText( ... )
              ->addSelect( ... )
              ...
              ->addSubmit();
  

  $app->form()->setDefault( function(){
     return array( ... );
  });

  $app->form()->onSubmittedAndValid( function() use ($app){
     ...
  });
  
  $app->render( 'mytplfile', array( 'form' => $app->form() ) );
)->via( 'GET', 'POST' );

$app->run();
```

Note that, we don't need to create a new variable to handle form object, all internal myfw features are handle dynamically (lazzy-loading). Form object has lots of features to explore.

db
----
myfw database handling is very special. Every call is computed as a mysql stored procedure call. No hardcoded sql, no orm. All sql code is stored in mysql database and all we need is to invoke a procedure. Why stored procedures? From [w3resouce.com](http://www.w3resource.com/mysql/mysql-procedure.php):

* _Stored procedures are fast. MySQL server takes some advantage of caching, just as prepared statements do. The main speed gain comes from reduction of network traffic. If you have a repetitive task that requires checking, looping, multiple statements, and no user interaction, do it with a single call to a procedure that's stored on the server._
* _Stored procedures are portable. When you write your stored procedure in SQL, you know that it will run on every platform that MySQL runs on, without obliging you to install an additional runtime-environment package, or set permissions for program execution in the operating system, or deploy different packages if you have different computer types. That's the advantage of writing in SQL rather than in an external language like Java or C or PHP._
* _Stored procedures are always available as 'source code' in the database itself. And it makes sense to link the data with the processes that operate on the data._
* _ Stored procedures are migratory! MySQL adheres fairly closely to the SQL:2003 standard. Others (DB2, Mimer) also adhere._ 



```php
// load a news item. call findnewsid() mysql procedure
$app->db()->findOne( $result, 'findnewsid(nid|int)', array( 'nid' => $id ) );
```

All we need is to use `db()` and because we only want to retrieve one row, we can use `findone()` method to execute mysql `findnewsid` procedure. Because this procedure required an integer argument we must describe it including argument name `nid` and argument type `int` and include our argument values list in a array. Array values keys must match argument names definition. db methods always return call state (`true` if call is executed and a valid result was sent from database, `false` if call could not be completed or an invalid result was sent from database). Call result is stored in `$result` variable passed as first argument.

**Additional methods**

Example: get all elements (select):
```php
// load all news. call findnews() mysql procedure
$app->db()->findAll( $results, 'findnews()' );
```

Example: update or insert elements:
```php
$app->db()->query( 'newsinsert(id|int,title|str|255)', array( 'id' => $id, 'title' => $title ) );
```



**Combining a db and a form object**

Here goes a simple example of how to combine a form object with a db object. We will create a form, get default values from database, assign db values to form elements and if form is submitted and form element values are valid we update our item in database.

```php
require 'myfw.php';

$app = new myfw();
$app->setConditions( array( 'id' => '[0-9]{1,5}' ) );

$app->map( '/item/:id', function($id) use ($app){

  $app->form()->addText( 'title', ... )->addSubmit();

  $app->form()->setDefault( function () use($app,$id){ 

        if( $app->db()->findOne( $result, 'findnewsid(nid|int)', array( 'nid' => $id )))
            return $result;        

        $app->form()->setErrorMessage( 'News item not available' )->hide();
  });

  $app->form()->onSubmittedAndValid( function() use ($app,$id){

        if( $app->db()->apply( 'newsitemupdate(nid|int,title|str|255)', array( 'nid' => $id, 'title' => $app->form()->getValue( 'title' ) ) )
            $app->form()->setSubmitMessage( 'News item details changed.' );
  });

  $app->render( ... );
)->via( 'GET', 'POST' );
```

i18n
----
Internationalization support is handle on php logic and template side. myfw uses php built-in gettext to handle translation with two functions `_` and `_n` both on php and tpl side. The main goal is to have a standard way in both sides compatible with PoEdit strings extration.

**php examples**

* simple
```php
 _( "hello world" );
```
* variables
```php
 _n( "welcome %s to %s", array( $name, $portal ) );
```
* singular/plural
```php
 _n( "1 orange", "lots of oranges", $counter );
```
* singular/plural translated with variables
```php
_n( "1 orange", "%s oranges", $counter, $counter );
```
* singular/plural translated with variables per context:
```php
_n( "1 orange in %s tree ", "%s oranges in %s trees", $counter, array( 'big' ), array( $counter, 'small' ) );
```

**tpl examples**

* simple
```python
 _( "hello world" );
```
* simples with variables
```python
 _n( "welcome %s to %s", [name, portal] );
```
* singular/plural
```python
 _n( "1 orange", "lots of oranges", counter );
```
* singular/plural translated with variables
```python
_n( "1 orange", "%s oranges", counter, counter );
```
* singular/plural translated with variables per context:
```python
_n( "1 orange in %s tree ", "%s oranges in %s trees", counter, ['big'], [counter, 'small'] );
```

**Configuration**

To init i18n support in myfw we need to setup i18n but setting translated files path for our `LC_MESSAGES/lang/*.mo` files by using i18n `setPath()` and optionally `domain` and `codeset`. Then, we only need to change language inside our actions.

```php
$app = new myfw();
$app->i18n()->setPath( __DIR__ . '/../i18n' );

$app->get('/:lang/something', function( $lang ) use ($app){
    $app->i18n()->setLang( $lang );
    ..
});
```

i18n() supports session too. This way, we can use session value if exists:
```php
$app->i18n()->setLang( 'en_US', true, true );
```
In previous example we are changing language to `en_US`, use session value if available (instead of `en_US`) and save `en_US` in session for future use.


rules
----
myfw has a built-in library to check patterns. These are just simple boolean methods that check an argument string and return true/false. These methods are integrated with form object when adding a form element so that we can check its value but we can use them alone too.

**form integration**

```php
$app->map( '/contact', function() use ($app){
  
  $app->form()->addText( 'myelement', 'Label', array( 'required' => 'element is required' ) );
              ...
              ->addSubmit();
  ...
)->via( 'GET', 'POST' );

$app->run();
```

When adding a form element we can assign an array of rules as 3rd element. This is an array where each key is the rule name (rules method name) and value is the error message on an array with message and additional options.

```php
$app->map( '/contact', function() use ($app){
  
  $app->form()->addText( 'myelement', 'Label', array( 'required' => 'element is required' ) );
              ->addText( 'mydescription', 'Description', array( 'required' => 'Descrition is required', maxlen => array( 'Description is too big', 200 ) ) );
              ...
              ->addSubmit();
  ...
)->via( 'GET', 'POST' );

$app->run();
```

**stand alone**


```php
if( $app->rules()->maxlen( $x, 250 ) ){
  ...
}
```

myfw **rules available**:

* required
* numeric
* maxlen
* regex
* email
* not_email
* money
* ip
* md5
* tag
* lettersonly
* character
* maxhyperlinks
* value
* maxlength
* minlength
* nopunctuation
* alphanumeric
* alphanumericstrict
* captcha
* selectvalid
* matchfield
* dontmatchfield
* fieldrequired
* httpurl
* domain
* subdomain
* hexcolor
	
filters
----
Like the rules library, filters library can be used when adding form elements. If form is submitted and valid, element values will be filtered before we retrieve values with `$arr = $app->form()->getValues()`.

```php
$app->form()->addText( 'myelement', 'Label', array(..), array( 'trim' ) );
```
myfw **rules available**:

* trim
* sha1
* fintval
* shortify
* hexcolor
* host
* domain
* markdown
* xss
		

session
----
Session library contains simple methods to handle php `$_SESSION`. Instead of direct access we should use this simple library to store, delete, get session information.

```php
// set $key 
$app->session()->set( $key, $label );

// set $key if not exists only
$app->session()->setcheck( $key, $label ); 

// get $key value
$k = $app->session()->get( $key );

// check if $key exists
if( $app->session()->exists( $key ) ){ .. }

// delete $key
if( $app->session()->delete( $key ) ){ .. }

// set pluralkey 'admin - x' ( same as $_SESSION[ 'admin' ][ 'x' ] )
$app->session()->set( array( 'admin', 'x' ), $valueA );
$app->session()->set( array( 'admin', 'y' ), $valueB );

// get pluralkey 'admin - x'
$valueA = $app->session()->get( array( 'admin', 'x' ) );
$valueB = $app->session()->get( array( 'admin', 'y' ) );
```

cache
----
This is a simple library to handle apc or redis operations. Before access, set or delete a key make sure to setup enviroment with `$app->config()`.

```php
// default apc ti to leave
$app->config( 'apc.ttl', 600 );

// default redis time to leave
$app->config( 'redis.ttl', 600 );

// cache set using apc
$app->cache()->set( APP_CACHEAPC, $key, $value );
or
$app->cache()->apcset( $key, $value );

// cache set using redis
$app->cache()->set( APP_CACHEREDIS, $key, $value );
or
$app->cache()->redisset( $key, $value );

// cache apc delete
$app->cache()->delete( APP_CACHEAPC, $key );

// cache redis delete
$app->cache()->delete( APP_CACHEREDIS, $key );
```

cart
----
Cart library handles simple cart functions based on session library. There are some methods available like `getItems`, `getTotalItems`, `getTotal`, `addItem`, `isItem`, `isItemValue`, `addExtra`, `getExtra`, `removeItem`, `updateItemProperty`, `getItemPropertyValue`, `checkItemProperty` and `clear`.

logging
----
Logging is useful when we need to check internal app behavior. Setup logging level in your config and just log.

```php
$app->config( 'log.init', true );
$app->config( 'log.level', \Slim\Log::DEBUG );

$app->cron( 'somecron', function() use ($app){
    ...
    $app->log()->debug( 'my critical log line' );
    ...
    $app->log()->warning( 'my warning log line' );
});
```

Currently, log uses an internal php `print` ouput, useful when using in a cron environment.

mailer
----
Mail engine is a very simple library to send emails. This library is integrated with rackspace mailgun api for delivery so need some additional `config()` params.

```php
// default params
$app->config( 'email.from', $from );
$app->config( 'email.to', $to );
$app->config( 'email.subject', $subject );

// mailgun specific
$app->config( 'email.mailgunkey', $key );
$app->config( 'email.mailgundomain', $domain );

// send email
$app->mailer()->send( $from, $to, $subject, $text );

// send simple email with default email.from config
$app->mailer()->sendsystem( $to, $subject, $text );

// send simple email with default email.from, email.to and email.subject config
$app->mailer()->sendinternal( $text );
```

virustotal
----
Virus total is a google webservice to check files and websites based on some antivirus categorization. To use this lib we must set virustotal api key and use getInfo method to assign a website info.

```php
// setup virustotal key
$app->config( 'vtotal.api', $key );

if( $app->vtotal()->getInfo( $info, "domain.com" ) ){
   // read $info
   ... 
}
```

transloadit
----
This library is the tranloadit webservice integration. All we need is to setup credentials and create an assembly using `createAssembly()` and/or request an assembly status info using `request()`.

```php
// setup key and secret
$app->config( 'transloadit.k', $key );
$app->config( 'transloadit.s', $secret );

// compute transloadit assembly
$assembly = array(
    'params' => array(
        'template_id' => 'abababababababababababababa',
            'steps' => array(
                'mystep' => array( 
                     'param' => $paramvar,
                      ),
                'otherstep' => array( 
                     'otherparam' => $otherparamvar
                      )
                ),
             )
     );

// transloadit assembly webservice call
if( $app->transloadit()->createAssembly( $result, $assembly ) ){
 ...
}
```

Transloadit `createAssembly()` just initialize the assembly. Then, transloadit webservice will process it and we need to retrieve results after some time. To do so, we need to use `request()` with our assembly url that we retrive in `createAssembly` `$result`.

```php
if( $app->transloadit()->request( $returninfo, $assemblyUrl ) ){
    // do something with $resultinfo
}
```

brightcloud
----
Brightcloud is a webservice to categorize domains. Currently library retrieves a domain info. We need to setup credentials and infor `getInfo()` method.

```php
$app->config( 'bcloud.k', $key );
$app->config( 'bcloud.s', $secret );

if( $app->bcloud()->getInfo( $result, "domain.com" ) ){
    // do something with $result
}
```
