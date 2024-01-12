<?php

namespace Penneo\SDK\Tests\Unit\OAuth;

use Penneo\SDK\OAuth\OAuth;
use Penneo\SDK\PenneoSdkRuntimeException;
use PHPUnit\Framework\TestCase;

class OAuthBuilderTest extends TestCase
{
    use BuildsOAuth;

    public static function providerRequiredBuildParameters(): array
    {
        return [
            ['clientId'],
            ['clientSecret'],
            ['redirectUri'],
            ['tokenStorage'],
            ['environment'],
        ];
    }

    /** @dataProvider providerRequiredBuildParameters */
    public function testWhenBuildingWithMissingParameterAPenneoExceptionIsThrown(string $missingParameter)
    {
        $capitalized = ucfirst($missingParameter);

        $this->expectException(PenneoSdkRuntimeException::class);
        $this->expectExceptionMessage("Cannot build! Please set the {$missingParameter} with ->set{$capitalized}()!");

        $this->build([
            $missingParameter => null,
        ]);
    }

    /** @dataProvider providerRequiredBuildParameters */
    public function testWhenBuildingWithEmptyParameterAPenneoExceptionIsThrown(string $missingParameter)
    {
        $capitalized = ucfirst($missingParameter);

        $this->expectException(PenneoSdkRuntimeException::class);
        $this->expectExceptionMessage("Cannot build! Please set the {$missingParameter} with ->set{$capitalized}()!");

        $this->build([
            $missingParameter => '',
        ]);
    }

    /**
     * @testWith ["i dont know"]
     *           ["local"]
     *           ["??"]
     */
    public function testWhenBuildingWithUnknownEnvironmentThenAPenneoExceptionIsThrown(string $unknownEnvironment)
    {
        $this->expectException(PenneoSdkRuntimeException::class);
        $this->expectExceptionMessage("Cannot build! Unknown environment '$unknownEnvironment'!");

        $this->build([
            'environment' => $unknownEnvironment,
        ]);
    }

    public function testWhenBuildingWithAnInvalidRedirectUriAPenneoExceptionIsThrown()
    {
        $this->expectException(PenneoSdkRuntimeException::class);
        $this->expectExceptionMessage("Cannot build! The supplied redirect URI is not a valid URL!");

        $this->build([
            'redirectUri' => 'garbage',
        ]);
    }

    public function testWhenBuildingWithAnApiKeyButNoSecretThenAPenneoExceptionIsThrown()
    {
        $this->expectException(PenneoSdkRuntimeException::class);
        $this->expectExceptionMessage("Cannot build! Please set the apiSecret with ->setApiSecret()!");

        $this->build([
            'redirectUri' => 'garbage',
            'apiKey' => 'this_is_a_legit_api_key'
        ]);
    }

    public function testWhenBuildingWithAnApiSecretButNoApiKeyThenAPenneoExceptionIsThrown()
    {
        $this->expectException(PenneoSdkRuntimeException::class);
        $this->expectExceptionMessage("Cannot build! Please set the apiKey with ->setApiKey()!");

        $this->build([
            'redirectUri' => 'garbage',
            'apiSecret' => 'so_secret!'
        ]);
    }

    public function testBuildsWithLocalhostAsRedirectUriSuccessfully()
    {
        $oauth = $this->build([
            'redirectUri' => 'http://localhost',
        ]);
        $this->assertEquals(OAuth::class, get_class($oauth));
    }

    public function testBuildsWithValidParametersSuccessfully()
    {
        $oauth = $this->build();
        $this->assertEquals(OAuth::class, get_class($oauth));
    }
}
