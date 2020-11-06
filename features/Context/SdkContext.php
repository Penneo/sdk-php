<?php

namespace Penneo\SDK\Tests;

use Penneo\SDK\ApiConnector;

use Behat\Gherkin\Node\PyStringNode;

/**
 * Defines application features from the specific context.
 */
class SdkContext extends AbstractContext
{
    /**
     * @BeforeSuite
     */
    public static function prepare(): void
    {
        self::startBootlegServer();
        ApiConnector::initialize('apiKeyHere', 'apiSecretHere', self::getServerUrl());
    }

    /**
     * @AfterSuite
     */
    public static function cleanup(): void
    {
        self::stopBootlegServer();
    }

    /**
     * @When I set entity property :property to :value
     */
    public function iSetField($property, $value)
    {
        $this->setEntityField($property, $value);
    }

    /**
     * @Then a :method request should be sent to :path
     */
    public function requestShouldBeSentTo($method, $path)
    {
        $request = $this->getLastRequest();

        // Check that the request was generated correctly
        $this->assertEquals($method, $request->getMethod());
        $this->assertEquals($path, $request->getUri()->getPath());
    }

    /**
     * @Then the request body should contain:
     */
    public function requestBodyShouldContain(PyStringNode $body)
    {
        $request = $this->getLastRequest();

        $this->assertJsonStringEqualsJsonString($body->getRaw(), (string)$request->getBody());
    }

    /**
     * @Then entity property :property should contain :value
     */
    public function propertyShouldContain($property, $value)
    {
        $this->assertEquals($value, $this->getEntityField($property));
    }

    /**
     * @Then entity property :property should be greater than zero
     */
    public function propertyShouldBeGreaterThanZero($property)
    {
        $this->assertTrue($this->getEntityField($property) > 0);
    }

    /**
     * @Then entity property :property should be undefined
     */
    public function propertyShouldBeUndefined($property)
    {
        $this->assertNull($this->getEntityField($property));
    }
}
