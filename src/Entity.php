<?php

namespace Penneo\SDK;

abstract class Entity
{
    /** @var int|null */
    protected $id;

    /** @var array<array-key, mixed> */
    protected static $propertyMapping = array(
        'create' => [],
        'update' => []
    );

    /** @var string */
    protected static $relativeUrl;

    /**
     * @throws Exception
     */
    private static function persistedEntityId(Entity $entity): int
    {
        $id = $entity->getId();
        if ($id === null) {
            throw new Exception('Penneo: Entity must be persisted for this operation');
        }

        return $id;
    }

    /**
     * @param $id
     *
     * @return static
     *
     * @psalm-suppress UnsafeInstantiation
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
        return self::findBy([]);
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param int|null   $limit
     * @param int|null   $offset
     *
     * @return static[]
     * @throws \Exception
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null)
    {
        $class = get_called_class();

        // Build query array
        $query = $criteria;
        if ($limit !== null) {
            $query['limit'] = $limit;
        }
        if ($offset !== null) {
            $query['offset'] = $offset;
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

        $decoded = json_decode($response->getBody()->getContents(), true);
        if (!is_array($decoded)) {
            throw new Exception('Penneo: Internal problem encountered');
        }

        /** @var array<int|string, mixed> $matches */
        $matches = $decoded;

        if (count($matches) === 1 && isset($matches['items'])) {
            // In order to build the result (an array), we need an array.
            // But there might be an endpoint which returns an object (and not an array).
            // If that is the case and this object has one property called 'items',
            // let's use the value of that property to generate the result.
            $items = $matches['items'];
            $matches = is_array($items) ? $items : array($items);
        }

        $result = [];
        foreach ($matches as $match) {
            if (!is_array($match)) {
                continue;
            }
            /** @var static $instance */
            $instance = new $class();
            $instance->__fromArray($match);
            $result[] = $instance;
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
     * @template T of Entity
     * @param Entity $parent
     * @param class-string<T> $type
     * @param int|string $id
     *
     * @return T|null
     *
     * @psalm-suppress InvalidPropertyFetch
     */
    public static function findLinkedEntity(Entity $parent, string $type, $id)
    {
        $p = self::persistedEntityId($parent);
        $url = $parent->getRelativeUrl() . '/' . $p . '/' . $type::$relativeUrl . '/' . $id;

        $entity = self::getEntity($type, $url, $parent);
        if ($entity === false) {
            throw new Exception('Penneo: Internal problem encountered');
        }

        return $entity;
    }

    /**
     * @template T of Entity
     * @param Entity $parent
     * @param class-string<T> $type      Full class name of the linked entity type
     * @param null|string $url
     *    Force the use of a certain URL instead of the auto-detected one
     * @param array<string, scalar|list<scalar>> $getParams
     *    Associative GET parameters
     *
     * @return list<T>
     *
     * @throws Exception
     *
     * @psalm-suppress InvalidPropertyFetch
     */
    public static function getLinkedEntities(Entity $parent, string $type, $url = null, array $getParams = [])
    {
        if ($url == null) {
            $url = $parent->getRelativeUrl() . '/' . self::persistedEntityId($parent) . '/' . $type::$relativeUrl;
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

    /**
     * @template T of Entity
     * @param class-string<T> $type
     *
     * @return T|false|null
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function getEntity(string $type, string $url, ?Entity $parent = null)
    {
        $response = ApiConnector::callServer($url);
        if ($response === null) {
            return false;
        }

        $data = json_decode($response->getBody()->getContents(), true);
        if (!is_array($data) || !$data) {
            return null;
        }
        if ($parent) {
            /** @var T $entity */
            $entity = new $type($parent);
        } else {
            /** @var T $entity */
            $entity = new $type();
        }

        $entity->__fromArray($data);

        return $entity;
    }

    /**
     * @template T of Entity
     * @param class-string<T> $type
     *
     * @return list<T>|false
     *
     * @psalm-suppress UnsafeInstantiation
     */
    public static function getEntities(string $type, string $url, ?Entity $parent = null)
    {
        $response = ApiConnector::callServer($url);
        if (!$response) {
            return false;
        }

        $dataSets = json_decode($response->getBody()->getContents(), true);
        if (!is_array($dataSets)) {
            throw new Exception('Penneo: Internal problem encountered');
        }

        $entities = [];

        foreach ($dataSets as $data) {
            if (!is_array($data)) {
                continue;
            }
            if ($parent) {
                /** @var T $entity */
                $entity = new $type($parent);
            } else {
                /** @var T $entity */
                $entity = new $type();
            }
            $entity->__fromArray($data);
            $entities[] = $entity;
        }

        return $entities;
    }

    /**
     * @return true
     */
    public static function linkEntity(Entity $parent, Entity $child): bool
    {
        $p = self::persistedEntityId($parent);
        $c = self::persistedEntityId($child);
        $url = $parent->getRelativeUrl() . '/' . $p . '/' . $child::$relativeUrl . '/' . $c;

        $response = ApiConnector::callServer($url, null, 'LINK');
        if (!$response) {
            throw new Exception('Penneo: Internal problem encountered');
        }

        return true;
    }

    /**
     * @return true
     */
    public static function unlinkEntity(Entity $parent, Entity $child): bool
    {
        $p = self::persistedEntityId($parent);
        $c = self::persistedEntityId($child);
        $url = $parent->getRelativeUrl() . '/' . $p . '/' . $child::$relativeUrl . '/' . $c;

        $response = ApiConnector::callServer($url, null, 'UNLINK');
        if (!$response) {
            throw new Exception('Penneo: Internal problem encountered');
        }

        return true;
    }

    /**
     * @psalm-param 'errors'|'link'|'pdf' $assetName
     *
     * @psalm-return list<mixed>
     */
    public static function getAssets(Entity $parent, string $assetName): array
    {
        $url  = $parent->getRelativeUrl() . '/' . self::persistedEntityId($parent) . '/' . $assetName;

        $response = ApiConnector::callServer($url);
        if (!$response) {
            throw new Exception('Penneo: Internal problem encountered fetching assets: ' . $assetName);
        }

        $assets = json_decode($response->getBody()->getContents(), true);
        if (!is_array($assets)) {
            throw new Exception('Penneo: Internal problem encountered fetching assets: ' . $assetName);
        }
        $result = [];

        foreach ($assets as $asset) {
            $result[] = $asset;
        }

        return $result;
    }

    /**
     * Fetch raw binary content from an asset endpoint.
     *
     * Unlike getAssets(), the response body is returned as-is without JSON decoding.
     * This requires that the server returns binary (i.e. the request must NOT include
     * an Accept: application/json header, which is already the SDK default).
     *
     * @param Entity $parent
     * @param string $assetPath  Sub-path appended after the entity URL (e.g. "content")
     * @param array  $queryParams  Associative array of query-string parameters
     *
     * @return string Raw binary content
     * @throws Exception
     */
    public static function getBinaryContent(Entity $parent, string $assetPath, array $queryParams = []): string
    {
        $url = $parent->getRelativeUrl() . '/' . self::persistedEntityId($parent) . '/' . $assetPath;
        if ($queryParams) {
            $url .= '?' . http_build_query($queryParams);
        }

        $response = ApiConnector::callServer($url);
        if (!$response) {
            throw new Exception('Penneo: Internal problem encountered fetching content: ' . $assetPath);
        }

        return $response->getBody()->getContents();
    }

    /**
     * @param null|string[] $data
     *
     * @psalm-param array{token: string}|null $data
     */
    public static function callAction(
        Entity $parent,
        string $actionName,
        string $method = 'patch',
        ?array $data = null
    ): bool {
        $url  = $parent->getRelativeUrl() . '/' . self::persistedEntityId($parent) . '/' . $actionName;

        $response = ApiConnector::callServer($url, $data !== null ? json_encode($data) : null, $method);
        if (!$response) {
            throw new Exception('Penneo: Internal problem encountered calling action: ' . $actionName);
        }

        return true;
    }

    /**
     * @return void
     */
    public static function persist(Entity $object)
    {
        if (!ApiConnector::writeObject($object)) {
            throw new Exception('Penneo: Could not persist the ' . get_class($object));
        }
    }

    /**
     * @return void
     */
    public static function delete(Entity $object)
    {
        if (!ApiConnector::deleteObject($object)) {
            throw new Exception('Penneo: Could not delete the ' . get_class($object));
        }

        $object->id = null;
    }

    public function __getMapping(): ?array
    {
        $class = get_called_class();
        $mapping = $class::$propertyMapping;
        if ($this->id) {
            return isset($mapping['update']) ? $mapping['update'] : null;
        }
        return isset($mapping['create']) ? $mapping['create'] : null;
    }

    public function __fromJson($json): void
    {
        $data = json_decode((string) $json, true);
        if (!is_array($data)) {
            return;
        }
        $this->__fromArray($data);
    }

    public function __fromArray(array $data): void
    {
        foreach ($data as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $this->parseObjects($val, $this);
            }
        }
    }

    /**
     * @param mixed $data
     * @param static $parent
     *
     * @return mixed
     *
     * @psalm-suppress UnsafeInstantiation Hydration instantiates only validated Entity subclasses.
     */
    private function parseObjects($data, self $parent)
    {
        // If we don't have an array, we are done.
        if (!is_array($data)) {
            return $data;
        }

        // Check if we an object
        if (isset($data['sdkClassName'])) {
            $class = 'Penneo\\SDK\\' . (string) $data['sdkClassName'];
            if (!class_exists($class) || !is_subclass_of($class, self::class)) {
                return $data;
            }
            /** @var class-string<Entity> $class */
            /** @var Entity $obj */
            $obj = new $class($parent);
            $obj->__fromArray($data);
            return $obj;
        }

        // If we reach this point, parse all objects in the array.
        $parsedArray = [];
        foreach ($data as $key => $element) {
            $parsedArray[$key] = $this->parseObjects($element, $parent);
        }

        return $parsedArray;
    }

    /**
     * @return string|null JSON string or null when mapping is missing
     */
    public function __getRequestData(): ?string
    {
        $data = [];
        $mapping = $this->__getMapping();
        if ($mapping === null) {
            return null;
        }

        foreach ($mapping as $key => $property) {
            // Process file entries
            $isFile = false;
            if (is_string($property) && $property !== '' && $property[0] == '@') {
                // This is a file.
                $isFile = true;
                $property = ltrim($property, '@');
            }

            if (!is_string($property)) {
                continue;
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

                $raw = file_get_contents($propValue);
                if ($raw === false) {
                    continue;
                }
                $propValue = base64_encode($raw);
            }

            if (is_int($key)) {
                $data[$property] = $propValue;
            } else {
                $data[$key] = $propValue;
            }
        }

        $encoded = json_encode($data);
        if ($encoded === false) {
            return null;
        }

        return $encoded;
    }

    /**
     * @param int|string $property Property path with optional `->` chain
     *
     * @return mixed
     */
    public function __getPropertyValue($property)
    {
        // NOTE: Properties can actually be properties of properties.
        $bits = explode('->', (string) $property);
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

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Entity|null
     */
    public function getParent()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getRelativeUrl(): string
    {
        $class = get_called_class();
        $parent = $this->getParent();
        $url = $class::$relativeUrl;

        if ($parent) {
            $url = $parent::$relativeUrl . '/' . self::persistedEntityId($parent) . '/' . $url;
        }

        return $url;
    }
}
