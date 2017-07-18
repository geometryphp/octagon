<?php

namespace Octagon\Http;

use Octagon\Adt\Bag;

/**
 * Request represents an HTTP request.
 *
 * TODO:
 * - Expand Request with the right properties and methods so that
 * - Create a Factory for requests.
 */

class Request
{

    /**
     * @var string Protocol.
     */
    private $_protocol;

    /**
     * @var string The request method.
     */
    private $_method;

    /**
     * @var string Full request path.
     */
    private $_fullPath;

    /**
     * @var string The request path.
     */
    private $_path;

    /**
     * @var string The query string.
     */
    private $_queryString;

    /**
     * @var string The raw HTTP-request body.
     */
    private $_body;

    /**
     * @var \Octagon\Adt\Bag  Stores key-value pairs from $_GET.
     */
    private $_query;

    /**
     * @var \Octagon\Adt\Bag  Stores key-value pairs from $_POST.
     */
    private $_request;

    /**
     * @var \Octagon\Adt\Bag Stores key-value pairs from SERVER.
     */
    private $_server;

    /**
     * @var \Octagon\Adt\Bag Stores key-value pairs from FILES.
     */
    private $_files;

    /**
     * @var \Octagon\Adt\Bag Stores key-value pairs from COOKIE.
     */
    private $_cookies;

    /**
     * @var \Octagon\Adt\Bag Stores key-value pairs from (HEADERS).
     */
    private $_headers;

    /**
     * @var \Octagon\Adt\Bag Stores request parameters as key-value pairs in a bag.
     */
    private $_params;

    /**
     * Create new Request instance.
     *
     * @var array $query   Expects $_GET
     * @var array $request Expects $_POST
     * @var array $server  Expects $_SERVER
     * @var array $files   Expects $_FILES
     * @var array $cookies  Expects $_COOKIE
     * @var array $headers Expects $_HEADERS?
     *
     * @return void
     */
    public function __construct($query = array(), $request = array(), $server = array(), $files = array(), $cookies = array(), $headers = array())
    {
        $this->initialize($query, $request, $server, $files, $cookies, $headers);
    }

    /**
     * Initialize Request.
     *
     * @var array $query
     * @var array $request
     * @var array $server
     * @var array $files
     * @var array $cookies
     * @var array $headers
     *
     * @return void
     */
    public function initialize($query = array(), $request = array(), $server = array(), $files = array(), $cookies = array(), $headers = array())
    {
        if ($query !== null) {
            $this->setQuery(new Bag($query));
        }
        if ($request !== null) {
            $this->setRequest(new Bag($request));
        }
        if ($server !== null) {
            $this->setServer(new Bag($server));
        }
        if ($files !== null) {
            $this->setFiles(new Bag($files));
        }
        if ($cookies !== null) {
            $this->setCookies(new Bag($cookies));
        }
        if ($headers !== null) {
            $this->setHeaders(new Bag($headers));
        }

        $this->setProtocol(null);
        $this->setMethod(null);
        $this->setFullPath(null);
        $this->setPath(null);
        $this->setBody(null);
        $this->setQueryString(null);
        $this->setParams(array());
    }

    /**
     * Return a new request and capture from globals.
     *
     * @return \Octagon\Routing\Request
     */
    public static function capture()
    {
        $request = new Request($_GET, $_POST, $_SERVER, $_FILES, $_COOKIE, array()); // TODO: replace last array() with HEADER
        $request->setProtocol($request->server('SERVER_PROTOCOL'));
        $request->setMethod($request->server('REQUEST_METHOD'));
        $request->setFullPath($request->server('REQUEST_URI'));
        $request->setPath($request->server('PATH_INFO'));
        $request->setPath(self::normalizePath($request->getPath()));
        $request->setQueryString($request->server('QUERY_STRING'));
        $request->setBody(null); // TODO: get request body
        return $request;
    }

    /**
     * Set the request protocol.
     *
     * @var string $protocol Protocol to set.
     *
     * @return void
     */
    public function setProtocol($protocol)
    {
        $this->_protocol = $protocol;
    }

    /**
     * Get the request protocol.
     *
     * @return string
     */
    public function getProtocol()
    {
        return $this->_protocol;
    }

    /**
     * Get the protocol version.
     *
     * @return string
     */
    public function protocolVersion()
    {
        // Extract version from the protocol
        $i = strpos($this->_protocol,'/');
        $version = substr($this->_protocol, $i);
        return $version;
    }

    /**
     * Get scheme.
     *
     * @return string
     */
    public function getScheme()
    {
        if ($this->isHttps()) {
            return 'https';
        }
        else {
            return 'http';
        }
    }

    /**
     * Check if scheme is HTTPS.
     *
     * @return bool Returns `true` if HTTPS. Otherwise, returns `false`.
     */
    public function isHttps()
    {
        if (!empty($this->server('HTTPS'))) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Set the request method.
     *
     * @var string $method Method to set.
     *
     * @return void
     */
    public function setMethod($method)
    {
        $this->_method = $method;
    }

    /**
     * Get the request method.
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->_method;
    }

    /**
     * Set the full request path.
     *
     * @var string $fullPath Path to set.
     *
     * @return void
     */
    public function setFullPath($fullPath)
    {
        $this->_fullPath = $fullPath;
    }

    /**
     * Get the full request path.
     *
     * @return string
     */
    public function getFullPath()
    {
        return $this->_fullPath;
    }

    /**
     * Set the request path.
     *
     * @var string $path Path to set.
     *
     * @return void
     */
    public function setPath($path)
    {
        $this->_path = $path;
    }

    /**
     * Get the request path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
    * Utility: Normalize the given path.
    *
    * @var string $path Path to normalize.
    *
    * @return string Normalized path.
    */
    public static function normalizePath($path)
    {
        // Trim slashes and whitespaces from around the URI path because they are useless.
        $path = trim($path);
        $path = trim($path, '/');

        // Prepend a single leading slash
        $path = '/' . $path;

        // Return normalized path
        return $path;
    }

    /**
     * Set the query string.
     *
     * @var string $queryString Query string to set.
     *
     * @return void
     */
    public function setQueryString($queryString)
    {
        $this->_queryString = $queryString;
    }

    /**
     * Get the query string.
     *
     * @return string
     */
    public function getQueryString()
    {
        return $this->_queryString;
    }

    public function getUrl()
    {
        $scheme = $this->getScheme();
        $host = $this->getHost();
        $path = $this->getPath();
        $url = $scheme . "://" . $host . $path;
        return $url;
    }

    public function getHost()
    {
        return $this->server('HTTP_HOST');
    }

    /**
     * Set the request body.
     *
     * @var string $body Body text to set.
     *
     * @return void
     */
    public function setBody($body)
    {
        $this->_body = $body;
    }

    /**
     * Get the request body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->_body;
    }

    /**
     * Set the query bag.
     *
     * @var array $query
     *
     * @return void
     */
    public function setQuery($query)
    {
        $this->_query = $query;
    }

    /**
     * Get the query bag.
     *
     * @return \Octagon\Adt\Bag
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * Set the request bag.
     *
     * @var array $request
     *
     * @return void
     */
    public function setRequest($request)
    {
        $this->_request = $request;
    }

    /**
     * Get the request bag.
     *
     * @return \Octagon\Adt\Bag
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Get a request property by name.
     *
     * @return ...
     */
    public function request($name)
    {
        $properties = $this->getRequest();
        $property = $properties->get($name);
        return $property;
    }

    /**
     * Set the server bag.
     *
     * @var array $server
     *
     * @return void
     */
    public function setServer($server)
    {
        $this->_server = $server;
    }

    /**
     * Get the server bag.
     *
     * @return \Octagon\Adt\Bag
     */
    public function getServer()
    {
        return $this->_server;
    }

    /**
     * Get a server property by name.
     *
     * @return ...
     */
    public function server($name)
    {
        $properties = $this->getServer();
        $property = $properties->get($name);
        return $property;
    }

    /**
     * Set the files bag.
     *
     * @var array $files
     *
     * @return void
     */
    public function setFiles($files)
    {
        $this->_files = $files;
    }

    /**
     * Get the files bag.
     *
     * @return \Octagon\Adt\Bag
     */
    public function getFiles()
    {
        return $this->_files;
    }

    /**
     * Get file property by name.
     *
     * @return ...
     */
    public function file($name)
    {
        $files = $this->getFiles();
        $file = $files->get($name);
        return $file;
    }

    /**
     * Set the cookie bag.
     *
     * @var array $cookies
     *
     * @return void
     */
    public function setCookies($cookies)
    {
        $this->_cookies = $cookies;
    }

    /**
     * Get the cookie bag.
     *
     * @return \Octagon\Adt\Bag
     */
    public function getCookies()
    {
        return $this->_cookies;
    }

    /**
     * Get a cookie by name.
     *
     * @return ...
     */
    public function cookie($name)
    {
        $cookies = $this->getCookies();
        $cookies = $cookies->get($name);
        return $cookie;
    }

    /**
     * Set the headers bag.
     *
     * @var array $headers
     *
     * @return void
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;
    }

    /**
     * Get the headers bag.
     *
     * @return \Octagon\Adt\Bag
     */
    public function getHeaders()
    {
        return $this->_headers;
    }

    /**
     * Get a header by name.
     *
     * @return ...
     */
    public function header($key)
    {
        $headers = $this->getHeaders();
        $header = $headers->get($name);
        return $header;
    }

    /**
     * Set the parameters bag.
     *
     * @var array $params
     *
     * @return void
     */
    public function setParams($params = array())
    {
        $this->_params = new Bag($params);
    }

    /**
     * Get the parameters bag.
     *
     * @return \Octagon\Adt\Bag
     */
    public function getParams() {
        return $this->_params;
    }

    /**
     * Get a parameter by name.
     *
     * @var mixed $key Key to find parameter.
     *
     * @return mixed Value found by key.
     */
    public function param($key) {
        if ($this->hasParam($key)) {
            $params = $this->getParams();
            $param = $params->get($key);
            return $param;
        }
        else {
            return null;
        }

    }

    public function hasParam($key)
    {
        $params = $this->getParams();
        if (array_key_exists($key, $params->all())) {
            return true;
        }
        else {
            return false;
        }
    }

}
