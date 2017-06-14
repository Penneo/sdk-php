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

        self::$headers = self::getDefaultHeaders();
        if ($headers) {
            self::$headers = array_merge($headers, self::$headers);
        }

        if ($user) {
            self::$headers['penneo-api-user'] = intval($user);
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
            $request = self::$client->createRequest($method, $url, self::$headers, $data, $options);
            self::$logger->debug('request', [
                'method'  => $method,
                'url'     => $url,
                'headers' => self::$headers,
                'data'    => $data,
                'options' => $options,
            ]);
            return $request->send();
        } catch (\Exception $e) {
            $message  = null;
            $response = $request->getResponse();
            if ($response instanceof Response) {
                $message = $response->getMessage();
            }
            self::$logger->error("$method $url", [
                'body' => $message,
            ]);
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
