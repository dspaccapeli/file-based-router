<?php
final class FileBasedRouter {
    /**
     * Routes a file to a url path
     * @param string $url_path The path to the file to be routed to
     * @param string $controller_path The path to the directory containing the files to be routed to
     * @param callable|null $filter_function A function to filter the url path
     * @param callable|null $fallback_handler A function to handle the fallback
     * @param string|null $view_suffix The suffix to append to the url path to get the view, e.g. requires "home.view.php" after "home.php" if the requested file is "home" and the suffix is ".view"
     * @param string $extension The extension of the file to be routed to
     */
    public static function route (
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
}