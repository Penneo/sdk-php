<?php

namespace Penneo\SDK\Tests\Integration;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Penneo\SDK\ApiConnector;
use Penneo\SDK\OAuth\OAuth;
use Penneo\SDK\OAuth\OAuthBuilder;
use Penneo\SDK\OAuth\PenneoTokens;
use Penneo\SDK\OAuth\TokenStorage;

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

    /** @var OAuth|null */
    private $oauth;

    /** @var string */
    private $codeExchangeUrl;

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

    /**
     * @When I prepare OAuth with the following details:
     */
    public function iBuildAnOauthUrlWithTheFollowingDetails(TableNode $table)
    {
        $details = $table->getRowsHash();

        $this->assertNotNull($details['clientId']);
        $this->assertNotNull($details['clientSecret']);
        $this->assertNotNull($details['environment']);
        $this->assertNotNull($details['redirectUri']);

        $this->oauth = OAuthBuilder::start()
            ->setClientId($details['clientId'])
            ->setClientSecret($details['clientSecret'])
            ->setEnvironment($details['environment'])
            ->setRedirectUri($details['redirectUri'])
            ->setTokenStorage(new class implements TokenStorage {
                function saveTokens(PenneoTokens $tokens) {}
                function getTokens(): ?PenneoTokens { return null; }
            })
            ->build();
    }

    /**
     * @When I request a code exchange URL with the following details:
     */
    public function iRequestACodeExchangeUrlWithTheFollowingDetails(TableNode $table) {
        $details = $table->getRowsHash();

        $this->assertNotNull($this->oauth);

        $this->codeExchangeUrl = $this->oauth->buildRedirectUrl(
            $details['state'],
            $details['scope']
        );
    }

    /**
     * @Then the resulting URL is :url
     */
    public function theResultingUrlIs(string $url)
    {
        $this->assertEquals($url, $this->codeExchangeUrl);
    }
}
