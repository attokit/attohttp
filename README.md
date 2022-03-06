![attokit_attohttp logo](https://cgy.design/src/icon/logo/attokit/logo_attokit_attohttp.svg "attokit/attohttp")

Simple PHP http request and response library. Use it only in your own funny-stuff projects. ***DO NOT use in Production Environment!***  
Attohttp lib provides http request, router & response creations.  
It's ugly, but simple, decoupled, much easer then regular PHP frameworks. You can use it in whatever way you like. It works all fine with other attokit-libraries.  
  
Composer required. **[About Composer](https://getcomposer.org)**  


## Install

Use composer to install package.  

```

$ cd [project root]
$ composer require attokit/attohttp
$ composer dump

```

Director like `[project root]/vendor/attokit/attohttp`.  
If composer-autoload works fine, now you can use attohttp-classes in your own project.  


## Usage

You need config your server first, in order to make all http-requests be processed only by your index page.  

```

# Apache config
<IfModule mod_rewrite.c>
	RewriteEngine on
	RewriteBase /
	
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
</IfModule>

```

If you want to use `\Atto\AttoHttp\Router` to takeover all http-requests, simply add these codes to your index page.   

```php

require_once("vendor/attokit/attohttp/src/start.php");

```

If you just want to use some classes, use these codes.  

```php

use \Atto\AttoHttp\Request;
use \Atto\AttoHttp\Router;
use \Atto\AttoHttp\Response;

// use default request and response, which parsed from REQUEST_URI
$currentRequest = Request::current();
$route = Router::current();
$currentResponse = Response::current();
$currentResponse->export();
exit;

// you can create your customized request or response
$req = Request::current("/foo/bar?param=mork");
$route = Router::current();   //route come from customized url
$rep = Response::current();
$rep->export();

```

You can change export data format simply by using querystring `?format=foobar`.  
default export HTML, support following formats: `html, json, page, txt, xml, code, dump`.  
For instance, you can response 404 by using code: `Response::code(404)`  


## Create Customized Route

Route classes placed in direction `[project root]/route`.  
Customized route must extends from `\Atto\AttoHttp\route\Base`.  

```php

namespace \Atto\AttoHttp\route;

//response to url "https://host/myroute[/..]"
class MyRoute extends Base
{
  //route info
  public $intr = "route introduction";
  public $name = "MyRoute";
  public $appname = "attoapp name (if you also use attoapp)";
  public $key = "Atto/AttoHttp/route/MyRoute";
  
  //response to url "https://host/myroute"
  public function defaultMethod()
  { }
  
  //response to url "https://host/myroute/foo/$p1/$p2/..."
  public function foo($p1, $p2, ...)
  { }
  
}

```


## Other Attokits

working on it.  
