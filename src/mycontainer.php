<?php

use Slim\Container;
use Slim\Views\Twig;
use SlimSession\Helper;

/**
 * services:
 * @property myconfig $config
 * @property mydb $db
 * @property myauth0 $auth0
 * @property myajax $ajax
 * @property mybugsnag $bugsnag
 * @property mymemcached $memcached
 * @property myredis $redis
 * @property myurlfor $urlfor
 * @property myfilters $filters
 * @property myrules $rules
 * @property myblockchain $blockchain
 * @property myi18n $i18n
 * @property mylist $list
 * @property myotp $otp
 * @property mypusher $pusher
 * @property myconfirm $confirm
 * @property mybreadcrumb $breadcrumb
 * @property mynotify $notify
 * @property mynexmo $nexmo
 * @property mysocial $social
 * @property mycidr $cidr
 * @property Helper $session
 * @property Twig $view
 *
 * objects:
 * @property mycalendar $calendar
 * @property mymedia $media
 * @property myinfopage $infopage
 * @property mychat $chat
 * @property myclipboard $clipboard
 * @property mygrid $grid
 * @property mymenu $menu
 * @property mymenuside $menuside
 * @property mymessage $message
 * @property mynavbar $navbar
 * @property mypanel $panel
 * @property mystats $stats
 * @property myform $form
 * @property myclient $client
 * @property myprogress $progress
 * @property myrating $rating
 *
 * properties:
 * @property boolean $isajax
 * @property boolean $isreferer
 * @property string $ipaddress
 * @property string $host
 */
class mycontainer extends Container {}
