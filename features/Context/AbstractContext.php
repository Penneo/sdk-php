<?php

namespace Penneo\SDK\Tests;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;

/**
 * Defines application features from the specific context.
 */
class AbstractContext extends TestCase implements Context, SnippetAcceptingContext
{
    /** @var BootlegServer|null */
    private static $server;
    /** @var RequestInterface */
    private static $requests;
    protected static $response;
    protected static $entity;

    /**
     * Helper methods
     */

    protected function getLastRequest(): RequestInterface
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

    protected function prepareGetResponse(string $data): void
    {
        $this->prepareResponse(200, $data);
    }

    protected function preparePostResponse(string $data): void
    {
        $this->prepareResponse(201, $data);

//        $encodedData = json_encode($data);
//
//        self::$response = new Response(
//            201,
//            [
//                'Location' => self::$entity->getRelativeUrl().'/'.$data['id'],
//                'Content-Length' => strlen($encodedData)
//            ],
//            $encodedData
//        );
//        self::$server->enqueue([self::$response]);
    }

    protected function preparePutResponse()
    {
        $this->prepareResponse(204, '');
    }

    protected function prepareDeleteResponse()
    {
        $this->prepareResponse(204, '');
    }

    protected function flushServer()
    {
        Assert::assertNotNull(self::$server);

        self::$requests = self::$server->readRequests();
//        self::$server->flush();
    }

    protected static function startBootlegServer(): void
    {
        Assert::assertNull(self::$server);
        self::$server = new BootlegServer();
    }

    protected static function stopBootlegServer(): void
    {
        Assert::assertNotNull(self::$server);

        self::$server->close();
        self::$server = null;
    }

    protected static function getServerUrl(): string
    {
        Assert::assertNotNull(self::$server);

        return self::$server->getUrl();
    }

    private function toCamelCase($field)
    {
        $words  = explode('_', $field);
        $field  = implode('', array_map('ucfirst', $words));
        return $field;
    }

    private function prepareResponse(int $status, $data): void
    {
        Assert::assertNotNull(self::$server);

        self::$response = new Response($status, ['Content-Length' => strlen($data)], $data);
        self::$server->enqueueResponse($status, $data);
    }
}
