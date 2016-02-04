<?php

namespace Penneo\SDK\Tests;

use Guzzle\Http\Message\Response;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Event\SuiteEvent;

/**
 * Defines application features from the specific context.
 */
class AbstractContext extends \PHPUnit_Framework_TestCase implements Context, SnippetAcceptingContext
{
    protected static $server;
    protected static $requests;
    protected static $response;
    protected static $entity;

    /**
     * Helper methods
     */

    protected function getLastRequest()
    {
        return self::$requests[0];
    }

    protected function getEntity()
    {
        return self::$entity;
    }

    protected function setEntity($entity)
    {
        self::$entity = $entity;
    }

    protected function getEntityField($field)
    {
        $getter = 'get' . $this->toCamelCase($field);
        return self::$entity->$getter();
    }

    protected function setEntityField($field, $value)
    {
        $setter = 'set' . $this->toCamelCase($field);
        self::$entity->$setter($value);
    }

    protected function prepareGetResponse($data)
    {
        self::$response = new Response(
            200,
            [
                'Content-Length' => strlen($data)
            ],
            $data
        );

        self::$server->enqueue([self::$response]);
    }

    protected function preparePostResponse($data)
    {
        $encodedData = json_encode($data);
        self::$response = new Response(
            201,
            [
                'Location' => self::$entity->getRelativeUrl().'/'.$data['id'],
                'Content-Length' => strlen($encodedData)
            ],
            $encodedData
        );
        self::$server->enqueue([self::$response]);
    }

    protected function preparePutResponse()
    {
        self::$response = new Response(
            204,
            [
                'Content-Length' => 0
            ]
        );
        self::$server->enqueue([self::$response]);
    }

    protected function prepareDeleteResponse()
    {
        self::$response = new Response(
            204,
            [
                'Content-Length' => 0
            ]
        );
        self::$server->enqueue([self::$response]);
    }

    protected function flushServer()
    {
        self::$requests = self::$server->getReceivedRequests(true);
        self::$server->flush();
    }

    private function toCamelCase($field)
    {
        $words  = explode('_', $field);
        $field  = implode('', array_map('ucfirst', $words));
        return $field;
    }
}
