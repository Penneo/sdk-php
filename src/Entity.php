<?php
namespace Penneo\SDK;

use Penneo\SDK\ApiConnector;

abstract class Entity
{
	protected $id;
	protected static $propertyMapping = array(
		'create' => array(),
		'update' => array()
	);
	protected static $relativeUrl;

	public static function find($id)
	{
		$class = get_called_class();
		$object = new $class();
		$object->id = $id;
		if (!ApiConnector::readObject($object))
			throw new \Exception('Penneo: Could not find the requested '.$class.' (id = '.$id.')');
		
		return $object;
	}

	public static function findAll()
	{
		return self::findBy(array());
	}
	
	public static function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
	{
		$class = get_called_class();
	
		// Build query array
		$query = $criteria;
		if ($limit !== null) $query['limit'] = intval($limit);
		if ($offset !== null) $query['offset'] = intval($offset);
		
		// Build order by parameters.
		if ($orderBy !== null) {
			$sort = '';
			$order = '';
			foreach ($orderBy as $field => $dir) {
				$sort .= $field.',';
				$order .= $dir.',';
			}
			$query['sort'] = rtrim($sort, ',');
			$query['order'] = rtrim($order, ',');
		}

		$response = ApiConnector::callServer($class::$relativeUrl, null, 'get', array('query' => $query));
		if (!$response)
			throw new \Exception('Penneo: Internal problem encountered');

		$matches = $response->json();
		$result = array();

		foreach ($matches as $match) {
			$object = new $class();
			$object->__fromArray($match);
			$result[] = $object;
		}
		
		return $result;
	}
	
	public static function findOneBy(array $criteria, array $orderBy = null)
	{
		return self::findBy($criteria, $orderBy, 1);
	}

	public static function __callStatic($method, $arguments)
	{
		switch (true) {
			case (0 === strpos($method, 'findBy')):
				$by = substr($method, 6);
				$method = 'findBy';
				break;

			case (0 === strpos($method, 'findOneBy')):
				$by = substr($method, 9);
				$method = 'findOneBy';
				break;

			default:
				throw new \BadMethodCallException(
					"Undefined method '$method'. The method name must start with either findBy or findOneBy!"
				);
		}

		if (empty($arguments)) {
			throw \InvalidArgumentException('The method '.$method.$by.' requires parameters');
		}

		$fieldName = lcfirst($by);

		if (property_exists(get_called_class(), $fieldName)) {
			switch (count($arguments)) {
				case 1:
					return self::$method(array($fieldName => $arguments[0]));

				case 2:
					return self::$method(array($fieldName => $arguments[0]), $arguments[1]);

				case 3:
					return self::$method(array($fieldName => $arguments[0]), $arguments[1], $arguments[2]);

				case 4:
					return self::$method(array($fieldName => $arguments[0]), $arguments[1], $arguments[2], $arguments[3]);

				default:
					// Do nothing
			}
		}

		throw new \BadMethodCallException('Unexisting method: '.$method.$by);
	}

	public static function findLinkedEntity(Entity $parent, $type, $id)
	{
		$url  = $parent->getRelativeUrl().'/'.$parent->getId().'/'.$type::$relativeUrl.'/'.$id;

		$response = ApiConnector::callServer($url);
		if (!$response)
			throw new \Exception('Penneo: Internal problem encountered');
			
		$match = $response->json();
		if (!$match) return null;
		
		$object = new $type($parent);
		$object->__fromArray($match);
		return $object;
	}

	public static function getLinkedEntities(Entity $parent, $type)
	{
		$url  = $parent->getRelativeUrl().'/'.$parent->getId().'/'.$type::$relativeUrl;

		$response = ApiConnector::callServer($url);
		if (!$response)
			throw new \Exception('Penneo: Internal problem encountered');
			
		$matches = $response->json();
		$result = array();

		foreach ($matches as $match) {
			$object = new $type($parent);
			$object->__fromArray($match);
			$result[] = $object;
		}
		
		return $result;
	}

	public static function linkEntity(Entity $parent, Entity $child)
	{
		$url  = $parent->getRelativeUrl().'/'.$parent->getId().'/'.$child::$relativeUrl.'/'.$child->getId();

		$response = ApiConnector::callServer($url, null, 'LINK');
		if (!$response)
			throw new \Exception('Penneo: Internal problem encountered');
	
		return true;
	}

	public static function unlinkEntity(Entity $parent, Entity $child)
	{
		$url  = $parent->getRelativeUrl().'/'.$parent->getId().'/'.$child::$relativeUrl.'/'.$child->getId();

		$response = ApiConnector::callServer($url, null, 'UNLINK');
		if (!$response)
			throw new \Exception('Penneo: Internal problem encountered');
	
		return true;
	}

	public static function getAssets(Entity $parent, $assetName)
	{
		$url  = $parent->getRelativeUrl().'/'.$parent->getId().'/'.$assetName;
		
		$response = ApiConnector::callServer($url);
		if (!$response)
			throw new \Exception('Penneo: Internal problem encountered fetching assets: '.$assetName);
			
		$assets = $response->json();
		$result = array();

		foreach ($assets as $asset) {
			$result[] = $asset;
		}
		
		return $result;
	}
	
	public static function callAction(Entity $parent, $actionName)
	{
		$url  = $parent->getRelativeUrl().'/'.$parent->getId().'/'.$actionName;
		
		$response = ApiConnector::callServer($url, null, 'patch');
		if (!$response)
			throw new \Exception('Penneo: Internal problem encountered calling action: '.$actionName);
			
		return true;
	}

	public static function persist(Entity $object)
	{
		if (!ApiConnector::writeObject($object))
			throw new \Exception('Penneo: Could not persist the '.get_class($object));
	}
	
	public static function delete(Entity $object)
	{
		if (!ApiConnector::deleteObject($object))
			throw new \Exception('Penneo: Could not delete the '.get_class($object));
		
		unset($object->id);
	}

	public function __getMapping()
	{
		$class = get_called_class();
		$mapping = $class::$propertyMapping;
		if ($this->id) {
			return isset($mapping['update'])?$mapping['update']:null;
		}
		return isset($mapping['create'])?$mapping['create']:null;
	}

	public function __fromJson($json)
	{
		$data = json_decode($json, true);
		$this->__fromArray($data);
	}
	
	public function __fromArray(array $data)
	{
		foreach($data as $key => $val) {
			if (property_exists($this, $key)) {
				$this->$key = $val;
			}
		}
	}

	public function __getRequestData()
	{
		$data = array();
		$mapping = $this->__getMapping();
		if ($mapping === null) return null;
		
		foreach ($mapping as $key => $property) {
			// Process file entries
			$isFile = false;
			if ($property[0] == '@') {
				// This is a file.
				$isFile = true;
				$property = ltrim($property, '@');
			}
			
			// Decode the property value (if needed).
			$propValue = $this->__getPropertyValue($property);
			if ($propValue === null) continue;
			
			// Get file contents and base64 encode.
			if ($isFile) {
				if (!is_readable($propValue))
					continue;
				
				$propValue = base64_encode(file_get_contents($propValue));
			}
			
			if (is_int($key)) {
				$data[$property] = $propValue;
			} else {
				$data[$key] = $propValue;
			}
		}

		return json_encode($data);
	}
	
	public function __getPropertyValue($property)
	{
		// NOTE: Properties can actually be properties of properties.
		$bits = explode('->', $property);
		$propValue = $this;
		foreach ($bits as $bit) {
			if (property_exists($propValue, $bit)) {
				$propValue = $propValue->$bit;
			} elseif (method_exists($propValue, $bit)) {
				$propValue = $propValue->$bit();
			} else {
				// This entry can not be parsed
				return null;
			}
		}
		
		return $propValue;
	}
	
	public function getId()
	{
		return $this->id;
	}
	
	public function getParent()
	{
		return null;
	}
	
	public function getRelativeUrl()
	{
		$class = get_called_class();
		$parent = $this->getParent();
		$url = $class::$relativeUrl;
		
		if ($parent)
			$url = $parent::$relativeUrl.'/'.$parent->getId().'/'.$url;

		return $url;
	}
}
