<?php

namespace Octagon;

use Octagon\Routing\RouteTable;

class Route
{
    /**
     * The route table in which to store application routes.
     *
     * @param
     */
    private $_routeTable;

    /**
     * Construct a new route table.
     */
    public function __construct(\Octagon\Routing\RouteTable $routeTable)
    {
        $this->setRouteTable($routeTable);
    }

    /**
     * Register route and controller on GET method
     */
    public function get($routePath, $controllerSpecifier)
    {
        if (Route::isControllerSpecifier($controllerSpecifier)) {
            $this->_registerGet($routePath, $controllerSpecifier);
        }
        else {
            // push error
            $response = new \Octagon\Http\FatalErrorResponse(new \Octagon\View\View(DEFAULT_ERROR_TEMPLATE));
            $response->send();
            \Octagon\Error::trigger('Error registering route: \n' . ' GET ' . $routePath . '\n' . $controllerSpecifier);
        }
    }

    /**
     * Register route and controller on POST method
     */
    public function post($routePath, $controllerSpecifier)
    {
        if (\Octagon\Routing\Route::isControllerSpecifier($controllerSpecifier)) {
            $this->_registerPost($routePath, $controllerSpecifier);
        }
        else {
            // push error
            $response = new \Octagon\Http\FatalErrorResponse(new \Octagon\View\View(DEFAULT_ERROR_TEMPLATE));
            $response->send();
            \Octagon\Error::trigger('Error registering route: \n' . ' POST ' . $routePath . '\n' . $controllerSpecifier);
        }
    }

    /**
     * Register route and controller on PUT method
     */
    public function put($routePath, $controllerSpecifier)
    {
        if (\Octagon\Routing\Route::isControllerSpecifier($controllerSpecifier)) {
            $this->_registerPut($routePath, $controllerSpecifier);
        }
        else {
            // push error
            $response = new \Octagon\Http\FatalErrorResponse(new \Octagon\View\View(DEFAULT_ERROR_TEMPLATE));
            $response->send();
            \Octagon\Error::trigger('Error registering route: \n' . ' PUT ' . $routePath . '\n' . $controllerSpecifier);
        }
    }

    /**
     * Register route and controller on DELETE method
     */
    public function delete($routePath, $controllerSpecifier)
    {
        if (\Octagon\Routing\Route::isControllerSpecifier($controllerSpecifier)) {
            $this->_registerDelete($routePath, $controllerSpecifier);
        }
        else {
            // push error
            $response = new \Octagon\Core\HttpFatalErrorResponse(new \Octagon\Component\View(DEFAULT_ERROR_TEMPLATE));
            $response->send();
            \Octagon\Core\Error::trigger('Error registering route: \n' . ' DELETE ' . $routePath . '\n' . $controllerSpecifier);
        }
    }

    /**
     * Register a route on GET method
     */
    private function _registerGet($routePath, $controllerSpecifier)
    {
        $this->_register('GET', $routePath, $controllerSpecifier);
    }

    /**
     * Register a route on POST method
     */
    private function _registerPost($routePath, $controllerSpecifier)
    {
        $this->_register('POST', $routePath, $controllerSpecifier);
    }

    /**
     * Register a route on PUT method
     */
    private function _registerPut($routePath, $controllerSpecifier)
    {
        $this->_register('PUT', $routePath, $controllerSpecifier);
    }

    /**
     * Register a route on DELETE method
     */
    private function _registerDelete($routePath, $controllerSpecifier)
    {
        $this->_register('DELETE', $routePath, $controllerSpecifier);
    }

    /**
     * Register a route to the route table
     */
    private function _register($requestMethod, $routePath, $controllerSpecifier)
    {
        // Before we start, normalize the route path.
        $routePath = \Octagon\Http\Request::normalizeUriPath($routePath);

        // Register the route
        $route = new \Octagon\Routing\Route($requestMethod, $routePath, $controllerSpecifier);
        $routeTable = $this->getRouteTable();
        $routeTable->add($route);
    }

    /**
     * Set route table
     */
    public function setRouteTable(\Octagon\Routing\RouteTable $routeTable)
    {
        $this->_routeTable = $routeTable;
    }

    /**
     * Get route table
     */
    public function getRouteTable()
    {
        return $this->_routeTable;
    }

}
