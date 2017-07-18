<?php

namespace Octagon\Routing;

use Octagon\Http\Request;

/**
 * A Route maps an HTTP request to an action.
 */

class Route
{

    /**
     * # Route
     *
     * A route is an association between an HTTP request and an action.
     * It route is a set of configurations that define how a request is mapped
     * to an action.
     *
     * ## Representing an HTTP request
     *
     * A route knows that an HTTP request has a method and URI, where a method is
     * a keyword GET, POST, PUT, HEAD, DELETE, HEAD, OPTIONS, or PATCH; and a URI
     * is composed of a scheme, port, host (authority), path, query, and fragment.
     * In order to represent an incoming request, a route stores that information
     * about the expected request.
     *
     * A route aims to be flexible in that a single route can match a combination
     * of one or more methods, one or more scheme/port pairs, a path, and a host
     * to the incoming HTTP request. (No decision on the handling of queries and
     * fragments have been made as yet.)
     *
     * Ultimately, the URI representations is compiled into a regex string that
     * makes performing a match an easy regex evaluation.
     *
     * ## Representing an action
     *
     * An action stores the identifier of the code to be executed if the incoming
     * HTTP request matches the route configuration.
     *
     * ## Old
     *
     * 1. A route has a URI, request method, a controller name (class name),
     *    and an action name (method name).
     * 2. A route can have a name.
     * 3. A route can define parameter whose value is optional. Such a route shall
     *    provide itself a default value in case no value is provided by the client
     *    then the parameter is assigned the provided default value.
     * 4. A route can also be given conditional rules. A conditional rule states
     *    that if the conditions of a rule is false then the current route fails
     *    to be a match. These are the conditional rules:
     *    - regex constraint on parameter value: This rule allows the programmer to
     *      specify a regex rule for the parameter value. If the parameter value
     *      does not match the specified regex, then the request doesn't match the
     *      controller.
     *
     *
     * ### New interface
     *
     * - path (string)
     * - action (string)
     * - defaults (array)
     *   - "param_name" (string) => "value" (string)
     * - requirements (array)
     *   - "param_name" (string) => "regex-pattern" (string)
     * - options (array)
     *   - "route name" (string) => "value" (string)
     * - host (string)
     * - schemes (array)
     *   - "scheme" => port (array)
     *     - "port number 1" (string/integer)
     *     - "port number 2" (string/integer)
     * - methods (array)
     *   - "GET" (string)
     *   - "POST" (string)
     *
     * ### Interface (deprecated)
     *
     * - methods (array)
     *     - method => null
     * - uri (array)
     *   - "scheme" => port-number
     *   - "host" => string
     *   - "path" => string
     * - action (array)
     *   - "class" => string
     *   - "action" (string)
     * - defaults (array):
     *   - ...
     * - requirements (array):
     *   - "param-name" => "regex-pattern"
     * - options (array):
     *   - name (array):
     *     - "name" =>
     *
     */

    const NAMESPACE_SEPARATOR = ':';

    const METHOD_DELIMITER = '@';

    public static $_supportedHttpMethods = "GET;POST;PUT;DELETE;HEAD;OPTIONS";

    /**
     * @var array The request method.
     */
    private $_methods;

    /**
     * @var array The route schemes or route scheme/port-number pairs.
     */
    private $_schemes;

    /**
     * @var string The host pattern for the route.
     */
    private $_host;

    /**
     * @var string The path pattern for the route.
     */
    private $_path;

    /**
     * @var array The route defaults.
     */
    private $_defaults;

    /**
     * @var array The conditional rules (regex contraints) for path parameters.
     */
    private $_requirements;

    /**
     * @var array The route options.
     */
    private $_options;

    /**
     * @var array The route action specifier.
     */
    private $_action;

    // the controller class
    private $_controller;

    // the controller method
    private $_method;

    /**
     * @var string The controller specifier.
     */
    private $_controllerSpecifier;

    /**
     * @var string The controller class specifier.
     */
    private $_classSpecifier;

    /**
     * @var string The controller action specifier.
     */
    private $_actionSpecifier;

    /**
     * @var string The relative path of the controller class file in the application's controller directory.
     */
    private $_controllerPath;

    /**
     * @var string The name of the controller class.
     */
    private $_class;

    /**
     * @var CompiledRoute The regex compilation of the route.
     */
    private $_compiledRoute;

    /**
     * Construct new route.
     *
     * @param string $path          The URI path.
     * @param string $action        The action.
     * @param string $defaults      The default definitions for route parameters if no definition is provided in the request.
     * @param array  $requirements  The format requirements for parameter values.
     * @param array  $options       Route parameter options.
     * @param string $host          The host of the route.
     * @param array  $schemes       The schemes that the route matches.
     * @param array  $methods       The methods that the route matches.
     */
    public function __construct($path, $action, $defaults = array(), $requirements = array(), $options = array(),  $host = '', $schemes = array(), $methods = array())
    {
        $this->setPath(Request::normalizePath($path));
        $this->setAction($action);
        $this->setDefaults($defaults);
        $this->setRequirements($requirements);
        $this->setOptions($options);
        $this->setHost($host);
        $this->setSchemes($schemes);
        $this->setMethods($methods);
        //$this->setControllerPath(self::_decodeControllerSpecifier( $this->getController()));
        //$this->setClass(self::_extractClass( $this->getController()));
        $this->compile(new RouteCompiler());
    }

    /**
     * Sets the path pattern.
     */
    public function setPath($pattern)
    {
        $this->_path = $pattern;
    }

    /**
     * Returns the path pattern.
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * Sets the action.
     */
    public function setAction($action)
    {
        $this->_action = $action;
    }

    /**
     * Returns the action.
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * Sets the defaults.
     */
    public function setDefaults($defaults)
    {
        $this->_defaults = $defaults;
    }

    /**
     * Returns the defaults.
     */
    public function getDefaults()
    {
        return $this->_defaults;
    }

    public function getDefault($name)
    {
        if ($this->hasDefault($name)) {
            return $this->_defaults[$name];
        }
        else {
            return null;
        }
    }

    public function hasDefault($name)
    {
        return array_key_exists($name, $this->_defaults);
    }

    /**
     * Sets the requirements.
     */
    public function setRequirements($requirements)
    {
        $this->_requirements = $requirements;
    }

    /**
     * Returns the requirements.
     */
    public function getRequirements()
    {
        return $this->_requirements;
    }

    public function getRequirement($name)
    {
        if ($this->hasRequirement($name)) {
            return $this->_requirements[$name];
        }
        else {
            return null;
        }
    }

    public function hasRequirement($name)
    {
        return array_key_exists($name, $this->_requirements);
    }

    /**
     * Sets the options.
     */
    public function setOptions($options)
    {
        $this->_options = $options;
    }

    /**
     * Returns the options.
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Sets the host pattern.
     *
     * @param string $pattern The pattern to set.
     */
    public function setHost($pattern)
    {
        $this->_host = $pattern;
    }

    /**
     * Returns the host pattern.
     *
     * @return string The host pattern.
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * Checks if route has host pattern
     *
     * @return bool TRUE if has host pattern; otherwise, FALSE is returned.
     */
    public function hasHost()
    {
        if (!empty($this->_host)) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Sets the route schemes.
     */
    public function setSchemes($schemes)
    {
        $this->_schemes = $schemes;
    }

    /**
     * Returns the route schemes.
     */
    public function getSchemes()
    {
        if ($this->hasSchemes()) {
            return $this->_schemes;
        }
        else {
            return null;
        }
    }

    public function hasSchemes()
    {
        if (!empty($this->_schemes)) {
            return true;
        }
        else {
            return false;
        }
    }

    public function getScheme($key)
    {
        if ($this->hasScheme($name)) {
            return $this->_schemes[$key];
        }
        else {
            return null;
        }
    }

    public function hasScheme($name)
    {
        return array_key_exists($name, $this->_schemes);
    }

    /**
     * Get ports by scheme
     */
    public function getPorts($scheme)
    {
        $schemes = $this->getSchemes();
        if (array_key_exists($schemes[$scheme])) {
            return $schemes[$scheme];
        }
        else {
            return null;
        }
    }

    public function hasPorts($scheme)
    {
    }

    /**
     * Sets the methods.
     *
     * @param array $methods The methods that match the route.
     */
    public function setMethods($methods)
    {
        // standardize method names and then assign them
        foreach ($methods as $method) {
            $methods[$method] = strtoupper($method);
        }
        $this->_methods = $methods;
    }

    /**
     * Returns the methods.
     *
     * @return array The route methods.
     */
    public function getMethods()
    {
        return $this->_methods;
    }

    /**
     * Check to see if a method exists
     *
     * @return bool ...
     */
    public function hasMethod($method)
    {
        if (array_key_exists(strtoupper($method), $this->_methods)) {
            return true;
        }
        else {
            return false;
        }
    }

    public static function isSupportedMethod($method) {
        if (in_array(strtoupper($method), self::SUPPORTED_HTTP_METHODS)) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Returns the action.
     */
    public function getActionMethod()
    {
        $pos = strpos($this->_action, self::METHOD_DELIMITER);
        $action = $this->_action;
        $actionMethod= substr($action, 0, $pos);
        return $actionMethod;
    }

    /**
     * Returns the controller as a namespace.
     */
    public function getController()
    {
        $pos = strpos($this->_action, self::METHOD_DELIMITER);
        $action = $this->_action;
        $controller = substr($action, $pos+1);
        $controller = self::_decodeControllerToClass($controller);
        return $controller;
    }

    /**
     * Set class.
     */
    public function setClass($class)
    {
        $this->_class = $class;
    }

    /**
     * Set class.
     */
    public function getClass()
    {
        return $this->_class;
    }

    /**
     * Get relative path of class file.
     */
    public function getControllerPath()
    {
        $pos = strpos($this->_action, self::METHOD_DELIMITER);
        $action = $this->_action;
        $controller = substr($action, $pos+1);
        $controller = self::_decodeControllerToPath($controller);
        return $controller;
    }

    /**
     * Get absolute path of class file.
     */
    public function fullControllerPath()
    {
        return APP_DIRECTORY . DS . $this->getControllerPath() . FILE_EXTENSION;
    }

    /**
     * Get route name.
     *
     * @param string $name The name to set.
     *
     * @return TBD.
     */
    public function setName($name)
    {
        $this->_options['name'] = $name;
    }

    public function getName()
    {
        if (isset($this->_options['name'])) {
            return $this->_options['name'];
        }
        else {
            // generate a standard name for the route
            return $this->getAction();
        }
    }

    public static function getSupportedHttpMethods()
    {
        return explode(';', $this->_supportedHttpMethods);
    }

    /**
     * Compiles the route
     *
     * @return CompiledRoute
     */
    public function compile(RouteCompiler $routeCompiler)
    {
        $this->_compiledRoute =  $routeCompiler->compile($this);
    }

    /**
     * Test to see if route has been compiledRoute
     *
     * @return bool Returns TRUE if route has already been compiled; returns FALSE otherwise.
     */
    public function isCompiled()
    {
        if (!isset($this->_compiledRoute) || empty($this->_compiledRoute)) {
            return false;
        }
        else {
            return true;
        }
    }

    public function getCompiledRegex()
    {
        if ($this->isCompiled()) {
            $compiledRoute = $this->_compiledRoute;
            return $compiledRoute->getRegex();
        }
        else {
            return null;
        }
    }

    public function getPathVariables()
    {
        if ($this->isCompiled()) {
            $compiledRoute = $this->_compiledRoute;
            return $compiledRoute->getPathVariables();
        }
        else {
            return null;
        }
    }

    public function getHostVariables()
    {
        if ($this->isCompiled()) {
            $compiledRoute = $this->_compiledRoute;
            return $compiledRoute->getHostVariables();
        }
        else {
            return null;
        }
    }

    /**
     * Utility: Is controller specifier syntactically valid?
     *
     * @param string $controllerSpecifier
     * @return bool True if valid; false if invalid.
     */
    private static function _isControllerSpecifier($controllerSpecifier)
    {
        // EBNF specification: {directory ":"} controller
        $pattern = "/((\w+){self::NAMESPACE_SEPARATOR})*(\w+)$/";
        if (preg_match($pattern, $controllerSpecifier)) {
            return true;
        }
        else {
            return false;
        }
    }

    private static function _getControllerSpecifier()
    {
        $action = $this->_action;
        $pos = strpos($action, self::METHOD_DELIMITER);
        $specifier = substr($action, $pos);
        return $specifier;
    }

    /**
     * Utility: Decodes the controller specifier to directory path.
     *
     * @param string $specifier
     */
    private static function _decodeControllerToPath($specifier)
    {
        return str_replace(self::NAMESPACE_SEPARATOR, DS, $specifier);
    }

    private static function _decodeControllerToClass($specifier)
    {
        return str_replace(self::NAMESPACE_SEPARATOR, '\\', $specifier);
    }

    /**
     * Utility: Returns the name of the class using the class specifier.
     */
    private static function _extractClass($specifier)
    {
        $i = strrpos($specifier, self::NAMESPACE_SEPARATOR);
        if ($i === false) {
            return $specifier;
        }
        else {
            return substr($specifier,$i+1);
        }
    }

}
