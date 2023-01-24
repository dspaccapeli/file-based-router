<p align="center">
    <img src="https://raw.githubusercontent.com/dspaccapeli/file-based-router/main/media/example.png" width="600" alt="File-based Router">
</p>

------

# File-based Router
 Simple file-based router to automatically route requests to files in a directory

## Introduction

This **File-based router** is a simple routing system written in PHP that allows developers to easily handle URL routing in their PHP projects. It uses file-based routing rules to map URLs to specific controllers or functions, making it easy to add new routes and modify existing ones. 

The router is lightweight, easy to set up and can work seamlessly with any PHP framework or CMS. 
It consists of a single routing functions of around **20 LOCs** and can be understood in a couple of minutes. 

This repo wraps the package to make it easily installable via Composer. If you want to use the function directly you can copy it in your source code directly.
This readme file provides all the necessary information for installing, configuring and using the file-based router in your PHP project.

## Get Started

First, install File-Based Router via the [Composer](https://getcomposer.org/) package manager:

```bash
composer require dspaccapeli/file-based-router
```

Then, to use it in your project, simply require the Composer autoloader and call the `route` function in your entrypoint (usually the `index.php` file):

```php
// Import the Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

// Prepare the router
$url_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // e.g. "/" or "/home" or "/api/users"
$directory_path = 'insert here the absolute path to the directory containing the endpoints';

// Route the request
FileBasedRouter::route($url_path, $directory_path);
```

If this is yout folder structure:

```bash
├── www
│   └── index.php
└── controllers
    ├── home.php
    └── api
        └── users.php
```
Then the `$endpoints_abs_path` variable should be set to `__DIR__ . "/../controllers/"`.

```php
require __DIR__ . '/../vendor/autoload.php';

// Routing
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$endpoints_abs_path = 'insert here the absolute path to the directory containing the endpoints';

route($path, $endpoints_abs_path, function($path){
    return str_contains($path, '.');
}, $return_404, '.view');

FileBasedRouter::route($path, __DIR__ . "/../hello/");
```

## Usage

### `route` parameters

#### > `$url_path`
It is the path of the URL to be routed. It must be a `string`.

#### > `$controller_path`
It is the absolute path to the directory containing the endpoints. It must be a `string` and defaults to the directory `/var/www/html` if not specified. 

#### > `$filter_function`
It is a function that takes the `$url_path` as a parameter and returns a boolean. It can be used to filter out some URLs. It defaults to `null` if not specified.

If the function returns `true` on the `$url_path`, the router will call the `$fallback_handler` and return `false`.

I use it to filter out requests for the view files (`.view.php`), which are not meant to be called directly, but I leave co-located with the controller files for convenience.

#### > `$fallback_handler`
It is the function that is called if the file/endpoint does not exist. The default failure mode calls `http_response_code(404)` if not specified.


#### > `$view_suffix`
It is the suffix of the view files. It defaults to `null` if not specified. If it is set, the router will also include the view file corresponding to the requested URL. 

After including the controller file, the router will look for a file with the same name as the controller file but with the suffix specified in `$view_suffix` and include it if it exists.

This is useful if you want to separate the controller logic from the view logic. The controller will not have to manually call the view file. 

*The variables defined in the controller will be available in the view file.*

#### > `$extension`
It is the extension of the controller files. It defaults to `.php` if not specified.

### `route` returns

If the routing is successful, the router will include the file corresponding to the requested URL and exit the script and return `true`.
If the routing fails, the router will return `false` and the script will continue to run.

## Code

The router is a single function of around 20 lines of code. It is very easy to understand and modify.

Feel free to copy-paste it in your project and modify it to suit your needs. Contribute to the project if you want to improve it.

```php
function route (
    string $url_path, 
    string $controller_path = '/var/www/html', 
    ?callable $filter_function = null, 
    ?callable $fallback_handler = null,
    ?string $view_suffix = null,
    string $extension = '.php'
    ) : bool {
        // add index.php to the path if the path is a directory i.e. public/ -> public/index.php
        $url_path = substr($url_path, -1) === '/' ? $url_path . 'index' : $url_path;
        // if function exists and returns false, call fallback handler
        if (isset($filter_function) && $filter_function($url_path)){
            isset($fallback_handler) ? $fallback_handler() : http_response_code(404);
            return false;
        // if file exists redirect there
        } else if (file_exists($controller_path . $url_path . $extension)){
            // if file exists, require it
            require $controller_path . $url_path . $extension;
            if(isset($view_suffix) && file_exists($controller_path . $url_path . $view_suffix . $extension)){
                // if view suffix is set, require the view
                require $controller_path . $url_path . $view_suffix . $extension;
            }
            return true;
        } else {
            // if file doesn't exist, call fallback handler
            isset($fallback_handler) ? $fallback_handler() : http_response_code(404);
            return false;
        }
}
```

---

This File-Based Router is an open-sourced software licensed under the **[MIT license](https://opensource.org/licenses/MIT)**.

Information about the software license under which the router is released, including any restrictions on use and distribution.