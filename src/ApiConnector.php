<?php
namespace Penneo\SDK;

use Guzzle\Http\Client;
use Atst\Guzzle\Http\Plugin\WsseAuthPlugin;
use Penneo\SDK\Entity;

class ApiConnector
{
	static protected $endpoint = 'https://sandbox.penneo.com/api/v1';
	static protected $headers = array('Content-type' => 'application/json');
	static protected $lastError;
	static protected $client;
	static protected $debug = false;
	static protected $throwExceptions = false;
	
	/**
	 * Initialize the API connector class.
	 *
	 * @param string $key        Your Penneo API key
	 * @param string $secret     Your Penneo API secret
	 * @param string $endpoint   The API endpoint url. This defaults to the API sandbox.
	 */
	public static function initialize($key, $secret, $endpoint=null, $user=null, $headers=null)
	{
		if ($endpoint)
			self::$endpoint = $endpoint;
		if ($headers)
			self::$headers = array_merge($headers, self::$headers);
		if ($user)
			self::$headers['penneo-api-user'] = intval($user);
		
		$wsse = new WsseAuthPlugin($key, $secret);
		self::$client = new Client(self::$endpoint);
		self::$client->getEventDispatcher()->addSubscriber($wsse);
	}

	public static function enableDebug()
	{
		self::$debug = true;
	}

	public static function throwExceptions($value)
	{
		self::$throwExceptions = (bool) $value;
	}

	public static function readObject(Entity $object)
	{
		$response = self::callServer($object->getRelativeUrl().'/'.$object->getId());
		if ($response === false) return false;
		$object->__fromJson($response->getBody(true));
		return true;
	}

	public static function writeObject(Entity $object)
	{
		$data = $object->__getRequestData();
		if ($data === null) return false;

		if ($object->getId()) {
			// Update request
			$response = self::callServer($object->getRelativeUrl().'/'.$object->getId(), $data, 'put');
			if ($response === false) return false;
		} else {
			// Create request
			$response = self::callServer($object->getRelativeUrl(), $data, 'post');
			if ($response === false) return false;
			$object->__fromJson($response->getBody(true));
		}
		
		return true;
	}

	public static function deleteObject(Entity $object)
	{
		if (!self::callServer($object->getRelativeUrl().'/'.$object->getId(), null, 'delete')) return false;
		
		return true;
	}

	public static function callServer($url, $data=null, $method='get', $options=array())
	{
		try {
			$request = self::$client->createRequest($method, $url, self::$headers, $data, $options);
			return $request->send();
		} catch (\Exception $e) {
			if (self::$throwExceptions) {
				throw $e;
			}
			if (self::$debug) {
				print($request->getResponse());
			}
			return false;
		}
	}

	public static function getLastError()
	{
		return self::$lastError;
	}
}
