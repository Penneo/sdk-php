<?php

namespace Penneo\SDK\Tests\Unit;

use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use Penneo\SDK\ApiConnector;
use Penneo\SDK\OAuth\OAuth;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ApiConnectorTest extends TestCase
{
    public function tearDown(): void
    {
        $this->resetStaticProperties(ApiConnector::class);
        parent::tearDown();
    }

    protected function resetStaticProperties(string $className)
    {
        $reflectedClass = new ReflectionClass($className);
        $staticProperties = $reflectedClass->getProperties(\ReflectionProperty::IS_STATIC);

        foreach ($staticProperties as $property) {
            $property->setAccessible(true);
            $property->setValue(null);
        }
    }

    /**
     * @testWith ["v3", "sandbox", "sandbox.penneo.com"]
     *           ["v4", "production", "app.penneo.com"]
     */
    public function testInitializeOAuthUsesGuzzleMiddlewareProvidedByOauth(
        string $apiVersion,
        string $environment,
        string $hostname
    ) {
        $middlewareCalled = false;
        $middlewareMock = Middleware::mapRequest(
            function (Request $request) use ($apiVersion, &$middlewareCalled, $hostname) {
                $middlewareCalled = true;
                $headers = $request->getHeaders();

                $this->assertEquals('penneo/penneo-sdk-php v:>=v2.0.0 using OAuth', $headers['User-Agent'][0]);
                $this->assertEquals('application/json', $headers['Content-type'][0]);

                $this->assertEquals($hostname, $request->getUri()->getHost());
                $this->assertEquals('https', $request->getUri()->getScheme());
                $this->assertEquals("/api/{$apiVersion}/something", $request->getUri()->getPath());

                throw new \RuntimeException('cancel the request');
            }
        );

        $oauth = $this->createMock(OAuth::class);
        $oauth->method('getEnvironment')->willReturn($environment);

        $oauth->expects($this->once())
            ->method('getMiddleware')
            ->willReturn($middlewareMock);

        ApiConnector::initializeOAuth($oauth, $apiVersion);
        try {
            ApiConnector::callServer('something', ['somebody' => 'oncetoldme'], 'get');
        } catch (\RuntimeException $e) {
            if ($e->getMessage() !== 'cancel the request') {
                throw $e;
            }
        }

        $this->assertTrue($middlewareCalled, 'Middleware created by OAuth class was not called!');
    }
}
