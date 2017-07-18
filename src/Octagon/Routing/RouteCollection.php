<?php

namespace Octagon\Routing;

use Octagon\Routing\Route;
use Octagon\Http\Request;
use Octagon\Routing\Controller;

/**
 * RouteCollection stores the route entries for the application.
 */

class RouteCollection
{

    /**
     * A route scope is a grouping a like routes. Why do we need route groups?
     * We need route group to implement the DRY principle and therefore
     * reduce/remove the need to copy & paste route that live in the same scope.
     *
     * The scopes that routes may commonly live in includes the following:
     *
     * * Same controller namespace. Many controllers may live in the
     *   'App/Controller/Auth' namespace where the responsibilites of the
     *   controllers are to handle user authentication, for example.
     *
     * * Same host. Multiple route may live under the same host. For example:
     *   Let's say there is an `api.example.com` domain. Many route will live
     *   under subdomain. Using a scope stops the programmer from repeatedly
     *   writing `api.example.com` for every API endpoint.
     *
     * * Same path prefixes. Multiple routes may have the same route prefixes.
     *   Back to our API example: API are versioned using the 'v<number>' scheme
     *   where *<number>* represents a counting number like 1, 2, 3, etc.
     *   Our API has the endpoints `v1/Toyota/Corolla/engine`,
     *   `v1/Toyota/Corolla/right-door`, `v1/Toyota/Corolla/interior`, etc.
     *   Because it is of the best interest to make APIs backward-compatible,
     *   our programmer needs update the API and will begin to code the
     *   endpoints for the second version called `v2`. Instead of having to
     *   write the API endpoints one at a time, she could group the endpoints
     *   under a single scope using the prefix `v1` for version 1 and `v2` for
     *   version `v2`.
     *
     * * Same scheme or scheme/port-number pairs. Multiple routes may need to
     *   access the same scheme/port-number. For instance, we are building an
     *   ecommerce webapp and we need to secure the checkout process using
     *   TLS/SSL. We could put all the routes for the checkout under the
     *   HTTPS scheme and 443 port.
     *
     * (A route group has a name, namespace, domain, and route prefix.)
     */

    /**
     * Stores routes.
     */
    private $_routes = array();

    private $_namespaces = array();

    private $_named = array();

    private $_prefixes = array();

    private $_schemes = array();

    /**
     * Add new route.
     *
     * @param Route $route The route to add.
     * @param array $options The route options.
     *
     * @return Nothing.
     */
    public function add(Route $route, array $options = array())
    {
        $this->_routes[] = $route;

        if (isset($options['name'])) {
            if (array_key_exists($options['name'], $this->_named)) {
                // Report error: Duplicate name.
            }
            else {
                $name = $options['name'];
                $this->_named[$name] = $route;
            }
        }
    }

    public function getByNamespace($key)
    {

    }

    /**
     * Get route by name.
     */
    public function getByName($name)
    {
        if (array_key_exists($name, $this->_named)) {
            return $this->_named[$name];
        }
        else {
            return null;
        }
    }

    public function getByPrefix($key)
    {
        if (array_key_exists($key, $this->_prefixes)) {
            return $this->_prefixes[$key];
        }
        else {
            return null;
        }
    }

    public function getByScheme($key)
    {
        if (array_key_exists($key, $this->_schemes)) {
            return $this->_schemes[$key];
        }
        else {
            return null;
        }
    }

    /**
     * Get all routes.
     */
    public function all()
    {
        return $this->_routes;
    }

    public function find(Request $request)
    {
        // Search each route in the route collection for a match
        foreach($this->all() as $route) {
            // If the request path and current route path match then...
            $args = $this->match($route, $request->getUrl(), $request->getMethod());
            if ($args !== false) {
                // Yay, we found a hook.
                // Retrieve the args (if any) and save them to the Request object.

                //$registry = Registry::getInstance();
                //$request = $registry->getRequest();

                /*var_dump($route->getHostVariables());
                var_dump($route->getPathVariables());
                $args = array_merge($route->getHostVariables(), $route->getPathVariables());
                ksort($args);
                var_dump($args);*/
                $request->setParams($args);

                // pass the request to the hook
                $args = array('request'=>$request);

                // Set the properties required by the hook
                $controller = new Controller();
                $controller->setClass($route->getController());
                $controller->setAction($route->getActionMethod());
                $controller->setArgs($args);
                $controller->setPath($route->getControllerPath());
                $controller->setRoute($route);

                // Return the controller to the caller
                return $controller;
            }
        }
        // Let the caller know that we went through all the routes,
        // but unfortunately no matches were found.
        return null;
    }

    // Do the route URI and request method match?

    public function match($route, $requestUrl, $requestMethod)
    {
        if (!$route->hasMethod($requestMethod)) {
            return false;
        }

        $compiledRegex = $route->getCompiledRegex();
        if (preg_match("#^{$compiledRegex}$#", $requestUrl, $matches)) {
            // yay! it's a match
            ksort($matches);
            return $matches;
        }
        else {
            return false;
        }
    }

}
