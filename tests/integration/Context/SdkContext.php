<?php

namespace Penneo\SDK\Tests\Integration;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Hook\AfterSuite;
use Behat\Hook\BeforeSuite;
use Behat\Step\Then;
use Behat\Step\When;
use Penneo\SDK\ApiConnector;
use PHPUnit\Framework\Assert;
use Penneo\SDK\OAuth\OAuth;
use Penneo\SDK\OAuth\OAuthBuilder;
use Penneo\SDK\OAuth\Tokens\PenneoTokens;
use Penneo\SDK\OAuth\Tokens\TokenStorage;

/**
 * Defines application features from the specific context.
 */
class SdkContext extends AbstractContext
{
    #[BeforeSuite]
    public static function prepare(): void
    {
        self::startBootlegServer();
        ApiConnector::initializeWsse('apiKeyHere', 'apiSecretHere', self::getServerUrl());
    }

    #[AfterSuite]
    public static function cleanup(): void
    {
        self::stopBootlegServer();
    }

    #[When('I set entity property :property to :value')]
    public function iSetField($property, $value)
    {
        $this->setEntityField($property, $value);
    }

    #[Then('a :method request should be sent to :path')]
    public function requestShouldBeSentTo($method, $path)
    {
        $request = $this->getLastRequest();

        // Check that the request was generated correctly
        Assert::assertEquals($method, $request->getMethod());
        Assert::assertEquals($path, $request->getUri()->getPath());
    }

    #[Then('the request body should contain:')]
    public function requestBodyShouldContain(PyStringNode $body)
    {
        $request = $this->getLastRequest();

        Assert::assertJsonStringEqualsJsonString($body->getRaw(), (string)$request->getBody());
    }

    #[Then('entity property :property should contain :value')]
    public function propertyShouldContain($property, $value)
    {
        Assert::assertEquals($value, $this->getEntityField($property));
    }

    #[Then('entity property :property should be greater than zero')]
    public function propertyShouldBeGreaterThanZero($property)
    {
        Assert::assertTrue($this->getEntityField($property) > 0);
    }

    #[Then('entity property :property should be undefined')]
    public function propertyShouldBeUndefined($property)
    {
        Assert::assertNull($this->getEntityField($property));
    }
}
