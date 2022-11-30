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

class ApiConnector
{
    /**
     * We should always keep this up to date. The '>=' is to allow for human error.
     */
    private const VERSION = '>=v2.0.0';

    /** @var string */
    protected static $endpoint;
    /** @var mixed[] */
    protected static $headers;
    /** @var Client */
    protected static $client;

    /** @var bool */
    protected static $throwExceptions = false;
    /** @var LoggerInterface */
    protected static $logger;

    protected static function getDefaultEndpoint(): string
    {
        return 'https://sandbox.penneo.com/api/v1/';
    }

    protected static function getDefaultHeaders(): array
    {
        return ['Content-type' => 'application/json'];
    }

    /**
     * Initialize the API connector class.
     *
     * @param string      $key      Your Penneo API key
     * @param string      $secret   Your Penneo API secret
     * @param string|null $endpoint The API endpoint url. This defaults to the API sandbox.
     * @param int|null    $user
     * @param array|null  $headers  Will be passed on to Guzzle
     */
    public static function initialize(
        string $key,
        string $secret,
        string $endpoint = null,
        int $user = null,
        array $headers = null
    ): void {
        self::$endpoint = self::fixEndpoint($endpoint ?: self::getDefaultEndpoint());

        self::$headers = array_merge(
            $headers ?: [],
            self::getDefaultHeaders(),
            self::getSpecificHeaders($key, $headers)
        );

        if ($user) {
            self::$headers['penneo-api-user'] = $user;
        }


        $wsse = new WsseMiddleware($key, $secret);
        $handler = HandlerStack::create();
        $handler->push(Middleware::mapRequest(function (RequestInterface $request) use ($wsse) {
            return $wsse->authorize($request);
        }));
        self::$client = new Client([
            'base_uri' => self::$endpoint,
            'handler' => $handler,
        ]);
        self::$logger = new NullLogger();
    }

    public static function throwExceptions(bool $value): void
    {
        self::$throwExceptions = $value;
    }

    public static function setLogger(LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    public static function readObject(Entity $object)
    {
        $response = self::callServer($object->getRelativeUrl() . '/' . $object->getId());
        if (!$response) {
            return false;
        }
        $object->__fromJson($response->getBody()->getContents());
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
            $response = self::callServer($object->getRelativeUrl() . '/' . $object->getId(), $data, 'put');
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
        if (!self::callServer($object->getRelativeUrl() . '/' . $object->getId(), null, 'delete')) {
            return false;
        }

        return true;
    }

    public static function callServer($url, $data = null, $method = 'get', $options = array()): ?Response
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

            $response = self::$client->request(
                $method,
                $url,
                $options + [
                    RequestOptions::HEADERS => self::$headers,
                    RequestOptions::BODY => self::sanitizeData($data),
                ]
            );

            if ($response instanceof Response) {
                // some logging implementation might not print the context, we put the request id in the log message
                // because it is important and we want to make sure it gets seen
                self::$logger->debug(
                    'response requestId=' . implode('', $response->getHeader('X-Penneo-Request-Id')),
                    ['method' => $method, 'url' => $url]
                );
            }
            return $response;
        } catch (RequestException $e) {
            $response = $e->getResponse();
            if ($response) {
                $body = $response->getBody();
                $message = $body ? $body->getContents() : null;
                self::$logger->error(
                    'response requestId=' . implode('', $response->getHeader('X-Penneo-Request-Id')),
                    ['method' => $method, 'url' => $url, 'raw' => $message]
                );
            }

            if (self::$throwExceptions) {
                throw $e;
            }

            return null;
        }
    }

    /**
     * @param string     $key
     * @param array|null $headers
     *
     * @return array<string, string>
     */
    private static function getSpecificHeaders(string $key, array $headers = null): array
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

    private static function fixEndpoint(string $uri): string
    {
        if ($uri !== '' && $uri[strlen($uri) - 1] !== '/') {
            return $uri . '/';
        }

        return $uri;
    }

    /**
     * Our ::callServer method supports being called with both array/object $data parameters, but also with directly
     * serialized data. This is for historical reasons in our own code bases.
     *
     * This method tries to make sure we only pass JSON to Guzzle.
     *
     * This problem likely snuck in with a Guzzle update, we have historically had this method be called in both ways
     * without problems.
     *
     * @param mixed $data
     *
     * @return string|null
     */
    private static function sanitizeData($data): ?string
    {
        if ($data !== null && !is_string($data)) {
            $serializedData = json_encode($data);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('JSON error: ' . json_last_error_msg());
            }

            return $serializedData;
        }

        return $data;
    }
}
