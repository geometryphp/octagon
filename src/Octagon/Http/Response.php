<?php

namespace Octagon\Http;

/**
 * Response represents an HTTP response.
 */

class Response
{

    /**
     * The HTTP response status codes.
     */
    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_PROCESSING = 102;                            // RFC 2518
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTI_STATUS = 207;                          // RFC 4918
    const HTTP_ALREADY_REPORTED = 208;                      // RFC 5842
    const HTTP_IM_USED = 226;                               // RFC 3229
    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_SWITCH_PROXY = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENT_REDIRECT = 308;                    // RFC ?
    const HTTP_BAD_REQUEST = 404;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_I_AM_A_TEAPOT = 418;                         // RFC 2324
    const HTTP_UNPROCESSABLE_ENTITY = 422;                  // RFC 4918
    const HTTP_LOCKED = 423;                                // RFC 4918
    const HTTP_FAILED_DEPENDENCY = 424;                     // RFC 4918
  //const HTTP_UNORDERED_COLLECTION = 425;                  // RFC 2817
    const HTTP_UPGRADE_REQUIRED = 426;                      // RFC 2817
    const HTTP_PRECONDITION_REQUIRED = 428;                 // RFC 6585
    const HTTP_TOO_MANY_REQUESTS = 429;                     // RFC 6585
    const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;       // RFC 6585
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_VARIANT_ALSO_NEGOTIATES = 506;               // RFC 2295
    const HTTP_INSUFFICIENT_STORAGE = 507;                  // RFC 4918
    const HTTP_LOOP_DETECTED = 508;                         // RFC 5842
    const HTTP_NOT_EXTENDED = 510;                          // RFC 2774
    const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;       // RFC 6585

    /**
     * The HTTP response reason phrases.
     *
     * @var array
     */
    private $_reasons = array (
        self::HTTP_CONTINUE => 'Continue',
        self::HTTP_SWITCHING_PROTOCOLS => 'Switching Protocols',
        self::HTTP_PROCESSING => 'Processing',
        self::HTTP_OK => 'OK',
        self::HTTP_CREATED => 'Created',
        self::HTTP_ACCEPTED => 'Accepted',
        self::HTTP_NON_AUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
        self::HTTP_NO_CONTENT => 'No Content',
        self::HTTP_RESET_CONTENT => 'Reset Content',
        self::HTTP_PARTIAL_CONTENT => 'Partial Content',
        self::HTTP_MULTI_STATUS => 'Multi-Status',                                          // RFC 4918
        self::HTTP_ALREADY_REPORTED => 'Already Reported',                                  // RFC 5842
        self::HTTP_IM_USED => 'IM Used',                                                    // RFC 3229
        self::HTTP_MULTIPLE_CHOICES => 'Multiple Choices',
        self::HTTP_MOVED_PERMANENTLY => 'Moved Permanently',
        self::HTTP_FOUND => 'Found',
        self::HTTP_SEE_OTHER => 'See Other',
        self::HTTP_NOT_MODIFIED => 'Not Modified',
        self::HTTP_USE_PROXY => 'Use Proxy',
        self::HTTP_SWITCH_PROXY => 'Switch Proxy (Unused)',
        self::HTTP_TEMPORARY_REDIRECT => 'Temporary Redirect',
        self::HTTP_PERMANENT_REDIRECT => 'Permanent Redirect',                              // RFC ?
        self::HTTP_BAD_REQUEST => 'Bad Request',
        self::HTTP_UNAUTHORIZED => 'Unauthorized',
        self::HTTP_PAYMENT_REQUIRED => 'Payment Required',
        self::HTTP_FORBIDDEN => 'Forbidden',
        self::HTTP_NOT_FOUND => 'Not Found',
        self::HTTP_METHOD_NOT_ALLOWED => 'Method Not Allowed',
        self::HTTP_NOT_ACCEPTABLE => 'Not Acceptable',
        self::HTTP_PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
        self::HTTP_REQUEST_TIMEOUT => 'Request Timeout',
        self::HTTP_CONFLICT => 'Conflict',
        self::HTTP_GONE => 'Gone',
        self::HTTP_LENGTH_REQUIRED => 'Length Required',
        self::HTTP_PRECONDITION_FAILED => 'Precondition Failed',
        self::HTTP_REQUEST_ENTITY_TOO_LARGE => 'Request Entity Too Large',
        self::HTTP_REQUEST_URI_TOO_LONG => 'Request-URI Too Long',
        self::HTTP_UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
        self::HTTP_REQUESTED_RANGE_NOT_SATISFIABLE => 'Requested Range Not Satisfiable',
        self::HTTP_EXPECTATION_FAILED => 'Expectation Failed',
        self::HTTP_I_AM_A_TEAPOT => 'I\'m a teapot',                                        // RFC 2324
        self::HTTP_UNPROCESSABLE_ENTITY => 'Unprocessable Entity',                          // RFC 4918
        self::HTTP_LOCKED => 'Locked',                                                      // RFC 4918
        self::HTTP_FAILED_DEPENDENCY => 'Failed Dependency',                                // RFC 4918
      //self::HTTP_UNORDERED_COLLECTION => 'Reserved',                                      // RFC 2817
        self::HTTP_UPGRADE_REQUIRED => 'Upgrade Required',                                  // RFC 2817
        self::HTTP_PRECONDITION_REQUIRED => 'Precondition Required',                        // RFC 6585
        self::HTTP_TOO_MANY_REQUESTS => 'Too Many Requests',                                // RFC 6585
        self::HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',    // RFC 6585
        self::HTTP_INTERNAL_SERVER_ERROR => 'Internal Server Error',
        self::HTTP_NOT_IMPLEMENTED => 'Not Implemented',
        self::HTTP_BAD_GATEWAY => 'Bad Gateway',
        self::HTTP_SERVICE_UNAVAILABLE => 'Service Unavailable',
        self::HTTP_GATEWAY_TIMEOUT => 'Gateway Timeout',
        self::HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version Not Supported',
        self::HTTP_VARIANT_ALSO_NEGOTIATES => 'Variant Also Negotiates',                    // RFC 2295
        self::HTTP_INSUFFICIENT_STORAGE => 'Insufficient Storage',                          // RFC 4918
        self::HTTP_LOOP_DETECTED => 'Loop Detected',                                        // RFC 5842
        self::HTTP_NOT_EXTENDED => 'Not Extended',                                          // RFC 2774
        self::HTTP_NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',    // RFC 6585
    );

    /**
     * The response headers.
     *
     * @var array
     */
    private $_headers;

    /**
     * The content of the response.
     *
     * @var string
     */
    private $_content;

    /**
     * The status code of the response.
     *
     * @var int
     */
    private $_statusCode;

    /**
     * The reason phrase of the response.
     *
     * @var string
     */
    private $_statusText;

    /**
     * The protocol version of the response.
     *
     * @var string
     */
    private $_protocolVersion;

    /**
     * Construct a new HTTP response.
     *
     * @param string $content  The content body of the response. By default, this value is an empty string.
     * @param int $status      The status of the response. By default, this value is 200/OK.
     * @param array $headers   The response headers. By default, this value is an empty array.
     *
     * @return void
     */
    public function __construct($content = '', $status = self::HTTP_OK, $headers = array())
    {
        $this->setHeaders($headers);
        $this->setContent($content);
        $this->setStatusCode($status);
        $this->setStatusText($status);
        $this->setProtocolVersion('1.0'); // TODO: replace this with a method that gets natural server protocol
    }

    /**
     * Send headers and content.
     *
     * @return void
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();
    }

    /**
     * Send headers.
     *
     * @return void
     */
    public function sendHeaders()
    {
        // Have headers already been sent? If headers have been sent, we can't
        // send our anymore header lines. Quit.
        if (headers_sent()) {
            return;
        }

        // Set the response status code
        http_response_code($this->getStatusCode());

        // Send the status code and reason phrase
        header('HTTP/' . $this->getProtocolVersion() . ' ' . http_response_code() . ' ' . $this->getStatusText());

        // Send out response headers
        foreach ($this->getHeaders() as $key=>$value)
        {
            header($key . ':' . $value);
        }

        // TODO: Send out cookies
        // (Do that here!)
    }

    /**
     * Send content.
     *
     * @return void;
     */
    public function sendContent()
    {
        echo $this->getContent();
    }

    /**
     * Set headers.
     *
     * @param array headers
     *
     * @return void
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;
    }

    /**
     * Get headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }


    /**
     * Set content.
     *
     * @param string $content
     *
     * @return void
     */
    public function setContent($content)
    {
        $this->_content = $content;
    }

    /**
     * Get content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->_content;
    }

    /**
     * Set protocol version.
     *
     * @param string $protocolVersion
     *
     * @return void
     */
    public function setProtocolVersion($protocolVersion)
    {
        $this->_protocolVersion = $protocolVersion;
    }

    /**
     * Get protocol version.
     *
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->_protocolVersion;
    }

    /**
     * Set status code.
     *
     * @param int $statusCode
     *
     * @return void
     */
    public function setStatusCode($statusCode)
    {
        $this->_statusCode = $statusCode;
    }

    /**
     * Get status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    /**
     * Set status text.
     *
     * @param int $statusCode
     *
     * @return void
     */
    public function setStatusText($statusCode)
    {
        $this->_statusText = $this->_reasons[$statusCode];
    }

    /**
     * Get status text.
     *
     * @return int
     */
    public function getStatusText()
    {
        return $this->_statusText;
    }

    /**
     * Redirect to URL.
     *
     * @param string $url The redirect URL.
     *
     * @return \Octagon\Routing\Response
     */
    public static function redirect($url)
    {
        $redirectResponse = new Response('', self::HTTP_FOUND, array('Location'=>$url));
        return $redirectResponse;
    }

}
