<?php
namespace Penneo\SDK;

use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;

use Atst\Guzzle\Http\Plugin\WsseAuthPlugin;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

use Penneo\SDK\Entity;

class ApiConnector
{
    /**
     * We should always keep this up to date. The '>=' is to allow for human error.
     */
    const VERSION = '>=v1.15.0';

    static protected $endpoint;
    static protected $headers;
    static protected $lastError;
    static protected $client;

    /**
     * @deprecated Use setLogger()
     */
    static protected $debug = false;
    static protected $throwExceptions = false;
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

        self::$headers = array_merge(
            $headers ?: [],
            self::getDefaultHeaders(),
            self::getSpecificHeaders($key, $headers)
        );

        if ($user) {
            self::$headers['penneo-api-user'] = (int) $user;
        }

        $wsse = new WsseAuthPlugin($key, $secret);
        self::$client = new Client(self::$endpoint);
        self::$client->getEventDispatcher()->addSubscriber($wsse);
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
            $object->__fromJson($response->getBody(true));
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
            $request = self::$client->createRequest($method, $url, self::$headers, $data, $options);
            $response = $request->send();
            if ($response instanceof Response) {
                self::$logger->debug('response', [
                    'method' => $method,
                    'url'    => $url,
                    'raw'    => $response->getMessage()
                ]);
            }
            return $response;
        } catch (\Exception $e) {
            $message  = null;
            $response = $request->getResponse();
            if ($response instanceof Response) {
                $message = $response->getMessage();
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

    /**
     * @param string     $key
     * @param array|null $headers
     *
     * @return array<string, string>
     */
    private static function getSpecificHeaders($key, array $headers = null)
    {
        $keyPart = substr($key, 0, 8);
        $setUserAgent = $headers && array_key_exists('User-Agent', $headers) ?
            $headers['User-Agent'] : 'n/a';
        $version = self::VERSION;

        return [
            // this helps us identify API users if we spot incorrect usage of the API or if we discover potential errors
            'User-Agent' => "penneo/penneo-sdk-php v:${version} key:${keyPart} ua:${setUserAgent}"
        ];
    }
}
