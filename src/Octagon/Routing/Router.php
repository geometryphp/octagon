<?php

namespace Octagon\Routing;

use Octagon\Routing\RouteCollection;
use Octagon\Routing\RouteCompiler;
use Octagon\Routing\Controller;
use Octagon\Routing\Route;
use Octagon\Core\Registry;
use Octagon\Http\Request;

class Router
{

    /**
     * Instance of the RouteCollection that contains the application routes.
     *
     * @var RouteCollection
     */
    private $_routeCollection;

    /**
     * @var string The base URL.
     */
    private $_baseUrl;

    // Set route collection

    public function __construct()
    {
    }

    public function initialize(RouteCollection $collection)
    {
        $this->setRouteCollection($collection);
    }

    public function setRouteCollection(RouteCollection $collection)
    {
        $this->_routeCollection = $collection;
    }

    // Get route collection

    public function getRouteCollection()
    {
        return $this->_routeCollection;
    }

    public function getController(Request $request)
    {
        return $this->getRouteCollection()->find($request);
    }

    // Provides an interface for adding a route with the GET method, URI path, and action configurations.
    public function get($path, $action, $options = array())
    {
        $this->addRoute(["GET"], $path, $action, $options);
    }

    // Provides an interface for adding a route with the POST method, URI path, and action configurations.
    public function post($path, $action, $options = array())
    {
        $this->addRoute(["POST"], $path, $action, $options);
    }

    // Provides an interface for adding a route with the PUT method, URI path, and action configurations.
    public function put($path, $action, $options = array())
    {
        $this->addRoute(["PUT"], $path, $action, $options);
    }

    // Provides an interface for adding a route with the DELETE method, URI path, and action configurations.
    public function delete($path, $action, $options = array())
    {
        $this->addRoute(["DELETE"], $path, $action, $options);
    }

    // Provides an interface for adding a route with the HEAD method, URI path, and action configurations.
    public function head($path, $action, $options = array())
    {
        $this->addRoute(["HEAD"], $path, $action, $options);
    }

    // Provides an interface for adding a route with the OPTIONS method, URI path, and action configurations.
    public function options($path, $action, $options = array())
    {
        $this->addRoute(["OPTIONS"], $path, $action, $options);
    }

    /**
     * Provides an interface for adding a route with the multiple http methods, URI path, and action configurations.
     *
     * @param array $methods An array of the methods.
     * @param string $path
     * @param string $action
     */
    public function match($methods, $path, $action, $options)
    {
        $this->addRoute($methods, $path, $action, $options);
    }

    // Provides an interface for adding a route with the all supported http methods, URI path, and action configurations.
    public function all($path, $action, $options)
    {
        $this->addRoute(null, $path, $action, $options);
    }

    /*
    // Provides an interface for adding requirements
    public function where($requirements)
    {
    }

    // Provides an interface for giving a route a name.
    public function name($name)
    {

    }

    // Provides an interface for add schemes.
    // @TODO: This is a temp fix because we haven't yet implemented the Router::scope() method.
    public function scheme()
    {

    }

    // Provides an interface for add a host pattern.
    // @TODO: This is a temp fix because we haven't yet implemented the Router::scope() method.
    public function host($host)
    {

    }
    */

    // addRoute
    public function addRoute($methods, $path, $action, $options)
    {
        $collection = $this->getRouteCollection();

        // Build methods
        if (!empty($methods) && !is_array($methods)) {
            // throw error
            $methods = array(); // temporary; TODO: replace with exception throw
        }
        // if no methods are set explicitly, assume that developer wants to use all supported methods
        else if (empty($methods) && is_array($methods)) {
            $methods = Route::getSupportedHttpMethods();
        }

        // Build requirements
        if (array_key_exists('requirements', $options)) {
            $requirements = !empty($options['requirements']) ? $options['requirements'] : array();
        }
        else {
            $requirements = array();
        }

        // Build name
        if (array_key_exists('name', $options)) {
            $name = $options['name'];
        }
        else {
            $name = null;
        }

        // Build schemes
        if (array_key_exists('schemes', $options)) {
            $schemes = !empty($options['schemes']) ? $options['schemes'] : array();
        }
        else {
            $schemes = array();
        }

        // Build host
        if (array_key_exists('host', $options)) {
            $host = $options['host'];
        }
        else {
            $host = null;
        }

        $collection->add(
            new Route(
                $path,
                $action,
                $defaults = array(),
                $requirements,
                $options = array(
                    "name"=>$name,
                ),
                $host,
                $schemes,
                $methods
            ),
            $options
        );
    }

    // Get or set the full base URL to be used in generating the URL.
    // See CakePHP's fullBaseUrl() for inspiration.

    public static function fullBaseUrl()
    {
        $registry = Registry::getInstance();
        $config = $registry->getConfig();
        $baseUrl = $config->get('base_url');
        return $baseUrl;
    }

    /**
     * Generate URL by route name.
     *
     * The URL generator takes
     * - Should take the entire URL into consideration. It should consider:
     *   - protocol
     *   - base url
     *   - port
     *   - base path: This is a relative path. Example: `example.com/foo`, where URLS are generated as `example.com/foo/dashboard`.
     *   - path with arguments:
     *   - query (parameters)
     *   - hash fragment (anchor)
     *
     * Anatomy of the $url parameter:
     * - `name`
     * - `scheme`: indicates the protocol.
     * - `base`: relative to
     * - `ssl`
     * - `port`
     * - `query`
     * - `#`
     *
     * @param array|null $url  The URL to generate.
     * @param array      $args The arguments to substitute variables.
     * @param bool       $full Generate full URL.
     *
     * @return Returns string on success. Otherwise, returns either null on error or false if route cannot be found.
     *
     * @todo Allow the function to also generate URLS by 1) controller & action name,
     *  and allow function to generate 2) URL with strings. See CakePHP's url() for inspiration.
     *
     *  Example:
     *  1) self::url(['controller'=>'dashboard', 'action'=>'settings'], true) => http://example.com/admin/dashboard/settings
     *  2) self::url('path/to/data', true) => http://example.com/path/to/data
     */

    public function url($url, $args = array(), $full = false)
    {
        // is it a string?
        if (is_string($url)) {
            return $url;
        }
        // is array?
        else if (isset($url) && is_array($url)) {
            // Get registry and request
            $registry = Registry::getInstance();
            $request = $registry->getRequest();

            // If invalid name, quit
            if (!isset($url['name'])) {
                return false;
            }

            // Get route by name
            $routeCollection = $this->getRouteCollection();
            $route = $routeCollection->getByName($url['name']);

            // Return error if route cannot be found
            if ($route === null) {
                return false;
            }

            // Build path
            $path = RouteCompiler::subPatternArgs($route->getPath(), $args);

            // Build query string
            if (!isset($url['?']) || empty($url['?'])) {
                $params = '';
            }
            else {
                $params = '';
                foreach ($url['?'] as $key=>$val) {
                    $params .= sprintf('&%s=%s', $key, $val);
                }
                $params[0] = '?'; // replace first '&' with '?'.
            }

            // Build fragment
            if (!isset($url['#']) || empty($url['#'])) {
                $fragment = '';
            }
            else {
                $fragment = '#' . $url['#'];
            }

            if (!$full) {
                // Compose URL
                return $path . $params . $fragment;
            }
            else {
                // Build scheme
                // Use the request scheme
                $scheme = $request->getScheme();

                // Build host
                if ($route->hasHost()) {
                    $host = RouteCompiler::subPatternArgs($route->getHost(), $args);
                }
                // If no host is specified, use the request host
                else {
                    $host = $request->getHost();
                }

                // Compose URL
                $url = $scheme . '://' . $host . $path . $params . $fragment;

                // Return URL
                return $url;
            }
        }
        // any other form? report error!
        else {
            return false;
        }
    }

}
