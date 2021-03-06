<?php

namespace Octagon\Core;

use Octagon\Http\Request;
use Octagon\Http\Response;
use Octagon\Config\Config;
use Octagon\Core\Registry;
use Octagon\Routing\Router;
use Octagon\View\View;

/**
 * Registry implements the Registry pattern.
 *
 * The registry serves to provide global access to data through the layers
 * of the system. Two noncontiguous layers can share data using the registry.
 */

class Registry
{

    /**
     * @var \Octagon\Core\Registry  Stores the Registry instance to create a singleton.
     */
    static private $_instance = null;

    /**
     * @var \Octagon\Http\Request Stores Request instance.
     */
    private $_request = null;

    /**
     * @var \Octagon\Config\Config Stores Config instance.
     */
    private $_config = null;

    /**
     * @var \Octagon\Routing\Router Stores Router instance.
     */
    private $_router = null;

    /**
     * @var Twig_Environment Stores Twig instance.
     */
    private $_twig = null;

    /**
     * Hide constructor.
     */
    private function __construct() { }

    /**
     * Get Registry instance.
     *
     * This method gets the Registry instance stored in the object.
     * If an instance doesn't exist, it creates a new one.
     *
     * @return \Octagon\Core\Registry Returns Registry instance.
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Get Request instance.
     *
     * @return \Octagon\Http\Request
     */
    public function getRequest()
    {
        if ($this->_request === null) {
            $this->_request = Request::capture();
        }
        return $this->_request;
    }

    /**
     * Get Config instance.
     *
     * @return Octagon\Config\Config
     */
    public function getConfig()
    {
        if ($this->_config === null) {
            $this->_config = new Config( CONFIG_DIRECTORY . SLASH . CONFIG_FILE );
        }
        return $this->_config;
    }

    /**
     * Get Router instance.

     *
     * @return Octagon\Routing\Router
     */
     public function getRouter()
     {
         if ($this->_router === null) {
             $this->_router = new Router();
         }
         return $this->_router;
     }

    /**
     * Get Twig instance.
     *
     * @return Twig_Environment
     */
    public function getTwig()
    {
        if ($this->_twig === null) {
            // Tell Twig from what directory to load the templates
            $loader = new \Twig_Loader_Filesystem(TEMPLATE_DIRECTORY);

            // Do not cache during development, because Twig cache only compiles once
            if (DEVELOPMENT_ENVIRONMENT) {
                $this->_twig = new \Twig_Environment($loader);
            }
            else {
                $this->_twig = new \Twig_Environment($loader, array(
                    'cache' => TWIG_COMPILATION_CACHE_DIRECTORY
                ));
            }

            //------------------------------------------------------------------
            // Definition of custom Twig functions
            //------------------------------------------------------------------

            //------------------------------------------------------------------
            // The design of url() and path()
            //------------------------------------------------------------------
            //
            // ## Problem
            //
            // There needed to be a way to import links into the templates without
            // using Twig variables. The problem with Twig variables is that we
            // need to define each variable every time we need to display a URL.
            // For instance, the nav appears on every page and say it contains
            // 10 links that are generated by the router. We must define the
            // 10 Twig variables in the controller whose presentation contains
            // the nav. This is exhausting and repetitive. It goes against the
            // rule of DRY (Don't Repeat Yourself).
            //
            // ## Introducing url() and path()
            //
            // We use url() to output full URL and path() to output only the
            // path.
            //
            // The benefits of using these functions include
            //
            // 1) semantics; i.e. url() and path() offer semantic meaning;
            // seeing either one let's you know that a URI is output at that
            // place.
            //
            // 2) DRY; i.e. the functuions remove repetition from the
            // application code. The URL can be defined directly in the template,
            // and stops the developer from defining them in each application
            // controller as Twig variables, every time he needs to us them.

            // Anonymous functions

            /**
             * @param string $name
             * @param array $args
             * @param bool $full
             */
            $getUrl = function($name, array $args = array(), $full) {
                // These vars stores options for later when they are joined to the URL
                $query = array();
                $fragment = '';

                // There are special options hidden in the args variable.
                // We need to pass these options as part of the URL.
                // If there are any options, lets process them.
                // We shall remove any options from the args variable,
                // and leave only the args.
                if (array_key_exists(0, $args)) {
                    // Because Twig stores the actual array in an array at index 0,
                    // simplify life and make the access to the array with the variables easy.
                    $args = $args[0];
                    // Prepare the query
                    if (array_key_exists('_query', $args)) {
                        if (is_array($args['_query'])) {
                            $query = $args['_query'];
                        }
                        else {
                            // TODO: throw error here: query must be array.
                        }
                        unset($args['_query']); // remove query from the args
                    }

                    // Prepare fragment
                    // We use isset() in place of array_key_exists for optimization reasons.
                    if (isset($args['_fragment'])) {
                        if (!is_array($args['_fragment'])) {
                            $fragment = $args['_fragment'];
                        }
                        else {
                            // TODO: throw error here: query cannot be an array
                        }
                        unset($args['_fragment']); // remove fragment
                    }
                }

                // Prepare URL
                $url['name'] = $name;
                $url['?'] = $query;
                $url['#'] = $fragment;

                // Get the URL from the router.
                $registry = self::getInstance();
                $router = $registry->getRouter();
                $result = $router->url($url, $args, $full);
                if (empty($result)) {
                    return '';
                }
                else {
                    return $result;
                }
            };

            // Define url():
            // Has the following parameters:
            //   Required paremeters
            //   - string :name: the name of the route to use. must be a string.
            //   Optional paremeters
            //   - string|array :_query: defines a query string. can be a string or an array.
            //   - string :_fragment: defines a fragment. must be a string.
            //   Other parameters
            //   - the routes variables are passed as function parameters in Twig. for instance,
            //     a route named `get.foo.bar` has the path `foo/{id}`; when we use
            //     `url("get.foo.bar", id = 1234)`, the output is `foo/1234`. We use variadic functions
            //     so that any variable number of variables and arguments can be supplied to url().
            $this->_twig->addFunction(new \Twig_SimpleFunction('url', function ($name, array $args = array()) use ($getUrl) {
                    return $getUrl($name, $args, true);
                }, array('is_variadic'=>true))
            );

            // Define path()
            // (Documentation is the same as url(). See url()).
            $this->_twig->addFunction(new \Twig_SimpleFunction('path', function($name, array $args = array()) use ($getUrl) {
                    return $getUrl($name, $args, false);
                }, array('is_variadic'=>true))
            );

            /**
             * config() - Get configuration setting by name.
             *
             * We define the config() function.
             *
             * @param string $name The name of the configuration seting.
             *
             * @return null|string Returns a string if setting exists. Otherwise,
             * null is returned.
             */
            $this->_twig->addFunction(new \Twig_SimpleFunction('config', function($name) {
                    $registry = self::getInstance();
                    $config = $registry->getConfig();
                    if (empty($result = $config->get($name))) {
                        return "";
                    }
                    else {
                        return $result;
                    }
                })
            );
        }
        return $this->_twig;
    }

    /**
     * Get 404 Not Found message.
     *
     * @return string
     */
    public function get404()
    {
        $failSafe = '<h1>404 Not Found</h1><p>The resource you requested was not found at this URL.</p>';
        $content = $this->getContentByTemplate('404_template', $failSafe);
        $response = new Response($content, Response::HTTP_NOT_FOUND);
        return $response;
    }

    /**
     * Get fatal error message.
     *
     * @return string
     */
    public function get503()
    {
        $failSafe = '<h1>503 Service Unavailable</h1><p>Something went wrong.</p>';
        $content = $this->getContentByTemplate('503_template', $failSafe);
        $response = new Response($content, Response::HTTP_SERVICE_UNAVAILABLE);
        return $response;
    }

    /**
     * Render the given template or return the fail-safe message if the template doesn't exist.
     *
     * @param string $template Template to render.
     * @param string $failSafe Message to return if template doesn't exist.
     *
     * @return \Octagon\Http\Response
     */
    public function getContentByTemplate($template, $failSafe)
    {
        $registry = Registry::getInstance();
        $config = $registry->getConfig();
        if ($config->has($template)) {
            $view = new View($config->get($template));
            $content = $view->render();
        }
        else {
            $content = $failSafe;
        }
        return $content;
    }

    /**
     * Converts a file to URI data in base64
     *
     * @param string $mime The MIME for the data.
     * @param string $path Path of file to convert. This can be a local file or URL.
     *
     * @return $string File contents formatted as URI data.
     */
    public static function toUriData($mime, $path)
    {
        // TODO: Parse the MIME format; if invalid return null.
        $contents = file_get_contents($path);
        $base64_content = base64_encode($contents);
        $uriData = 'data:' . $mime . ';base64,' . $base64_content ;
        return $uriData;
    }

}
