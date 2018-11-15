<?php
namespace Penneo\SDK;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Penneo\SDK\Entity;

class ApiConnector
{
    static protected $endpoint;
    static protected $headers;
    static protected $lastError;
    /** @var Client */
    static protected $client;

    /**
     * @deprecated Use setLogger()
     */
    static protected $debug = false;
    static protected $throwExceptions = false;
    /** @var LoggerInterface */
    static protected $logger;
    
    protected static function getDefaultEndpoint()
    {
        return 'https://sandbox.penneo.com/api/v1';
    }

    protected static function getDefaultHeaders()
    {
        return array('Content-type' => 'application/json');
    }

    /**
     * Initialize the API connector class.
     *
     * @param string $key        Your Penneo API key
     * @param string $secret     Your Penneo API secret
     * @param string $endpoint   The API endpoint url. This defaults to the API sandbox.
     */
    public static function initialize($key, $secret, $endpoint = null, $user = null, array $headers = null)
    {
        self::$endpoint = $endpoint ?: self::getDefaultEndpoint();

        self::$headers = self::getDefaultHeaders();
        if ($headers) {
            self::$headers = array_merge($headers, self::$headers);
        }

        if ($user) {
            self::$headers['penneo-api-user'] = intval($user);
        }

        $wsseHandler = new WSSEHandler($key, $secret);
        $handlerStack = HandlerStack::create();
        $handlerStack->push(Middleware::mapRequest(function (RequestInterface $request) use ($wsseHandler) {
            return $wsseHandler->addAuthHeaders($request);
        }));

        self::$client = new Client(['base_uri' => self::$endpoint, 'handler' => $handlerStack]);
        self::$logger = new NullLogger();
    }

    public static function enableDebug()
    {
        trigger_error(__FUNCTION__ . ' is deprecated. Use setLogger(Psr\Log\LoggerInterface $logger)');
        self::$debug = true;
    }

    public static function throwExceptions($value)
    {
        self::$throwExceptions = (bool) $value;
    }

    public static function setLogger(LoggerInterface $logger)
    {
        self::$logger = $logger;
    }

    public static function readObject(Entity $object)
    {
        $response = self::callServer($object->getRelativeUrl().'/'.$object->getId());
        if ($response === false) {
            return false;
        }
        $object->__fromJson($response->getBody(true));
        return true;
    }

    public static function writeObject(Entity $object)
    {
        $data = $object->__getRequestData();
        if ($data === null) {
            return false;
        }

        if ($object->getId()) {
            // Update request
            $response = self::callServer($object->getRelativeUrl().'/'.$object->getId(), $data, 'put');
            if ($response === false) {
                return false;
            }
        } else {
            // Create request
            $response = self::callServer($object->getRelativeUrl(), $data, 'post');
            if ($response === false) {
                return false;
            }
            $object->__fromJson((string) $response->getBody(true));
        }
        
        return true;
    }

    public static function deleteObject(Entity $object)
    {
        if (!self::callServer($object->getRelativeUrl().'/'.$object->getId(), null, 'delete')) {
            return false;
        }
        
        return true;
    }

    /**
     * @param        $url
     * @param mixed  $data    Will be encoded as JSON
     * @param string $method
     * @param array  $options
     *
     * @return bool|mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function callServer($url, $data = null, $method = 'get', $options = array())
    {
        try {
            self::$logger->debug(
                'request',
                [
                    'method'  => $method,
                    'url'     => $url,
                    'headers' => self::$headers,
                    'data'    => $data,
                    'options' => $options,
                ]
            );

            if ($data && !isset($options[RequestOptions::JSON])) {
                $options[RequestOptions::JSON] = $data;
            }
            $options[RequestOptions::HEADERS] = isset($options[RequestOptions::HEADERS]) ?
                $options[RequestOptions::HEADERS] : [];
            $options[RequestOptions::HEADERS] += self::$headers;

            $response = self::$client->request($method, $url, $options);

            if ($response instanceof Response) {
                self::$logger->debug('response', [
                    'method' => $method,
                    'url'    => $url,
                    'raw'    => (string) $response->getBody()
                ]);
            }
            return $response;
        } catch (\Exception $e) {
            $message  = null;

            if ($e instanceof RequestException && $e->hasResponse()) {
                $message = (string) $e->getResponse()->getBody();
                self::$logger->error('response', [
                    'method' => $method,
                    'url'    => $url,
                    'raw'    => $message
                ]);
            }

            if (self::$throwExceptions) {
                throw $e;
            }
            if (self::$debug) {
                print($message);
            }
            return false;
        }
    }

    public static function getLastError()
    {
        return self::$lastError;
    }
}
