<?php

namespace Penneo\SDK;

abstract class Entity
{
    /** @var int */
    protected $id;
    protected static $propertyMapping = array(
        'create' => array(),
        'update' => array()
    );
    protected static $relativeUrl;

    /**
     * @param $id
     *
     * @return static
     */
    public static function find($id)
    {
        $class = get_called_class();
        $object = new $class();
        $object->id = $id;
        if (!ApiConnector::readObject($object)) {
            throw new Exception('Penneo: Could not find the requested ' . $class . ' (id = ' . $id . ')');
        }

        return $object;
    }

    /**
     * @return static[]
     * @throws \Exception
     */
    public static function findAll()
    {
        return self::findBy(array());
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param null       $limit
     * @param null       $offset
     *
     * @return static[]
     * @throws \Exception
     */
    public static function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
    {
        $class = get_called_class();

        // Build query array
        $query = $criteria;
        if ($limit !== null) {
            $query['limit'] = (int) $limit;
        }
        if ($offset !== null) {
            $query['offset'] = (int) $offset;
        }

        // Build order by parameters.
        if ($orderBy !== null) {
            $sort = '';
            $order = '';
            foreach ($orderBy as $field => $dir) {
                $sort .= $field . ',';
                $order .= $dir . ',';
            }
            $query['sort'] = rtrim($sort, ',');
            $query['order'] = rtrim($order, ',');
        }

        $response = ApiConnector::callServer($class::$relativeUrl, null, 'get', array('query' => $query));
        if (!$response) {
            throw new Exception('Penneo: Internal problem encountered');
        }

        $matches = json_decode($response->getBody()->getContents(), true);

        if (count($matches) === 1 && isset($matches['items'])) {
            // In order to build the result (an array), we need an array.
            // But there might be an endpoint which returns an object (and not an array).
            // If that is the case and this object has one property called 'items',
            // let's use the value of that property to generate the result.
            $matches = $matches['items'];
        }

        $result = array();
        foreach ($matches as $match) {
            $object = new $class();
            $object->__fromArray($match);
            $result[] = $object;
        }

        return $result;
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     *
     * @return static[]
     * @throws \Exception
     */
    public static function findOneBy(array $criteria, ?array $orderBy = null)
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
            throw new \InvalidArgumentException('The method ' . $method . $by . ' requires parameters');
        }

        $fieldName = lcfirst($by);

        if (property_exists(get_called_class(), $fieldName)) {
            switch (count($arguments)) {
                case 1:
                    return self::$method(array($fieldName => $arguments[0]));

                case 2:
                    return self::$method(array($fieldName => $arguments[0]), $arguments[1]);

                case 3:
                    return self::$method(
                        array($fieldName => $arguments[0]),
                        $arguments[1],
                        $arguments[2]
                    );

                case 4:
                    return self::$method(
                        array($fieldName => $arguments[0]),
                        $arguments[1],
                        $arguments[2],
                        $arguments[3]
                    );

                default:
                    // Do nothing
            }
        }

        throw new \BadMethodCallException('Unexisting method: ' . $method . $by);
    }

    /**
     * @param Entity $parent
     * @param        $type
     * @param        $id
     *
     * @return static|false|null
     */
    public static function findLinkedEntity(Entity $parent, $type, $id)
    {
        $url  = $parent->getRelativeUrl() . '/' . $parent->getId() . '/' . $type::$relativeUrl . '/' . $id;

        $entity = self::getEntity($type, $url, $parent);
        if ($entity === false) {
            throw new Exception('Penneo: Internal problem encountered');
        }

        return $entity;
    }

    /**
     * @param Entity $parent
     * @param string $type      Full class path of the linked entity type
     * @param null   $url       Force the use of a certain URL instead of the auto-detected one
     * @param array  $getParams Extra params to be added to the url; must be a associative array of GET params
     *
     * @return array|bool
     * @throws Exception
     */
    public static function getLinkedEntities(Entity $parent, $type, $url = null, array $getParams = array())
    {
        if ($url == null) {
            $url = $parent->getRelativeUrl() . '/' . $parent->getId() . '/' . $type::$relativeUrl;
        }

        if ($getParams) {
            $url .= '?' . http_build_query($getParams);
        }

        $entities = self::getEntities($type, $url, $parent);
        if ($entities === false) {
            throw new Exception('Penneo: Internal problem encountered');
        }

        return $entities;
    }

    public static function getEntity($type, $url, ?Entity $parent = null)
    {
        $response = ApiConnector::callServer($url);
        if ($response === null) {
            return false;
        }

        $data = json_decode($response->getBody()->getContents(), true);
        if (!$data) {
            return null;
        }
        if ($parent) {
            $entity = new $type($parent);
        } else {
            $entity = new $type();
        }

        $entity->__fromArray($data);

        return $entity;
    }

    public static function getEntities($type, $url, ?Entity $parent = null)
    {
        $response = ApiConnector::callServer($url);
        if (!$response) {
            return false;
        }

        $dataSets = json_decode($response->getBody()->getContents(), true);
        $entities = [];

        foreach ($dataSets as $data) {
            if ($parent) {
                $entity = new $type($parent);
            } else {
                $entity = new $type();
            }
            $entity->__fromArray($data);
            $entities[] = $entity;
        }

        return $entities;
    }

    public static function linkEntity(Entity $parent, Entity $child)
    {
        $url  = $parent->getRelativeUrl() . '/' . $parent->getId() . '/' . $child::$relativeUrl . '/' . $child->getId();

        $response = ApiConnector::callServer($url, null, 'LINK');
        if (!$response) {
            throw new Exception('Penneo: Internal problem encountered');
        }

        return true;
    }

    public static function unlinkEntity(Entity $parent, Entity $child)
    {
        $url  = $parent->getRelativeUrl() . '/' . $parent->getId() . '/' . $child::$relativeUrl . '/' . $child->getId();

        $response = ApiConnector::callServer($url, null, 'UNLINK');
        if (!$response) {
            throw new Exception('Penneo: Internal problem encountered');
        }

        return true;
    }

    public static function getAssets(Entity $parent, $assetName)
    {
        $url  = $parent->getRelativeUrl() . '/' . $parent->getId() . '/' . $assetName;

        $response = ApiConnector::callServer($url);
        if (!$response) {
            throw new Exception('Penneo: Internal problem encountered fetching assets: ' . $assetName);
        }

        $assets = json_decode($response->getBody()->getContents(), true);
        $result = array();

        foreach ($assets as $asset) {
            $result[] = $asset;
        }

        return $result;
    }

    public static function callAction(Entity $parent, string $actionName, string $method = 'patch', $data = null): bool
    {
        $url  = $parent->getRelativeUrl() . '/' . $parent->getId() . '/' . $actionName;

        $response = ApiConnector::callServer($url, $data !== null ? json_encode($data) : null, $method);
        if (!$response) {
            throw new Exception('Penneo: Internal problem encountered calling action: ' . $actionName);
        }

        return true;
    }

    public static function persist(Entity $object)
    {
        if (!ApiConnector::writeObject($object)) {
            throw new Exception('Penneo: Could not persist the ' . get_class($object));
        }
    }

    public static function delete(Entity $object)
    {
        if (!ApiConnector::deleteObject($object)) {
            throw new Exception('Penneo: Could not delete the ' . get_class($object));
        }

        $object->id = null;
    }

    public function __getMapping()
    {
        $class = get_called_class();
        $mapping = $class::$propertyMapping;
        if ($this->id) {
            return isset($mapping['update']) ? $mapping['update'] : null;
        }
        return isset($mapping['create']) ? $mapping['create'] : null;
    }

    public function __fromJson($json)
    {
        $data = json_decode($json, true);
        $this->__fromArray($data);
    }

    public function __fromArray(array $data)
    {
        foreach ($data as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $this->parseObjects($val, $this);
            }
        }
    }

    private function parseObjects($data, $parent)
    {
        // If we don't have an array, we are done.
        if (!is_array($data)) {
            return $data;
        }

        // Check if we an object
        if (isset($data['sdkClassName'])) {
            $class = 'Penneo\\SDK\\' . $data['sdkClassName'];
            $obj = new $class($parent);
            $obj->__fromArray($data);
            return $obj;
        }

        // If we reach this point, parse all objects in the array.
        $parsedArray = array();
        foreach ($data as $key => $element) {
            $parsedArray[$key] = $this->parseObjects($element, $parent);
        }

        return $parsedArray;
    }

    public function __getRequestData()
    {
        $data = array();
        $mapping = $this->__getMapping();
        if ($mapping === null) {
            return null;
        }

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
            if ($propValue === null) {
                continue;
            }

            // Get file contents and base64 encode.
            if ($isFile) {
                if (!is_readable($propValue)) {
                    continue;
                }

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
            if ($propValue === null) {
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

        if ($parent) {
            $url = $parent::$relativeUrl . '/' . $parent->getId() . '/' . $url;
        }

        return $url;
    }
}
