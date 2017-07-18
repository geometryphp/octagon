<?php

namespace Octagon\Routing;

use Octagon\Http\Response;
use Octagon\View\View;
use Octagon\Core\Registry;

/**
 * Store information about an application controller.
 *
 * Controller contains information about an application controller.
 * It exists to store controller information for the dispatcher.
 * The dispatcher uses the information to dispatch the controller.
 */

class Controller
{

    /**
     * The class of the controller.
     *
     * @var string
     */
    private $_class;

    /**
     * The name of the controller's action method.
     *
     * @var string
     */
    private $_action;

    /**
     * The key-value pairs to be injected as variables into the controller.
     *
     * @var array
     */
    private $_args;

    /**
     * Absolute path of the controller's class file.
     *
     * @var string
     */
    private $_path;

    /**
     * Route mapped to the controller.
     *
     * @var \Octagon\Routing\Route
     */
    private $_route;

    /**
     * Create a controller hook.
     *
     * @param string $class   Class of the controller.
     * @param string $action  Name of the action method.
     * @param array $args     Arguments to pass to the controller.
     * @param string $path    Path to the controller's class file.
     * @param mixed $route    The Route object mapped to the controller.
     *
     * @return void
     */
    public function __construct($class = '', $action = '', $path = '', $args = array(), $route = null)
    {
        $this->setClass($class);
        $this->setAction($action);
        $this->setPath($path);
        $this->setArgs($args);
        $this->setRoute($route);
    }

    /**
     * Execute the controller.
     *
     * @return \Octagon\Http\Response The response from the controller.
     */
    public function run()
    {
        $class = $this->getClass();
        $instance = new $class();
        if (method_exists($instance, $this->getAction())) {
            $response = call_user_func_array( array($instance, $this->getAction()), $this->getArgs() );
        }
        else {
            // Push error
            $registry = Registry::getInstance();
            $response = $registry->get503();
            $response->send();

            $errmsg = 'Method "' . $this->getClass() . '::' . $this->getAction() . '" does not exist in class file "' . $this->getPath() . '"';
            trigger_error($errmsg, E_USER_ERROR);
        }
        return $response;
    }

    /**
     * Set the class of the controller.
     *
     * @param string $class
     *
     * @return void
     */
    public function setClass($class) {
        $this->_class = $class;
    }

    /**
     * Get the class of the controller.
     *
     * @return string
     */
    public function getClass() {
        return $this->_class;
    }

    /**
     * Set the name of the action method.
     *
     * @param string $action
     *
     * @return void
     */
    public function setAction($action) {
        $this->_action = $action;
    }

    /**
     * Get the controller's action method.
     *
     * @return string
     */
    public function getAction() {
        return $this->_action;
    }

    /**
     * Set the variables to be injected into the controller.
     *
     * @param array $args An array containing key-value pairs.
     *
     * @return void
     */
    public function setArgs($args) {
        $this->_args = $args;
    }

    /**
     * Get the array that contains the variables.
     *
     * @return array
     */
    public function getArgs() {
        return $this->_args;
    }

    /**
     * Set the path of the class file.
     *
     * @var string $path
     *
     * @return void
     */
    public function setPath($path) {
        $this->_path = $path;
    }

    /**
     * Get the path of the controller's class file.
     *
     * @return string
     */
    public function getPath() {
        return $this->_path;
    }

    /**
     * Store the given Route object.
     *
     * @param \Octagon\Routing\Route $route Route object to assign.
     *
     * @return void
     */
    public function setRoute($route) {
        $this->_route = $route;
    }

    /**
     * Get the Route object.
     *
     * @return \Octagon\Routing\Route
     */
    public function getRoute() {
        return $this->_route;
    }

}
