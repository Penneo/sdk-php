<?php

namespace Penneo\SDK\Tests\Unit\OAuth;

use Penneo\SDK\OAuth\OAuth;
use Penneo\SDK\PenneoSDKException;
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
            ['environment'],
        ];
    }

    /** @dataProvider providerRequiredBuildParameters */
    public function testWhenBuildingWithMissingParameterAPenneoExceptionIsThrown(string $missingParameter)
    {
        $capitalized = ucfirst($missingParameter);

        $this->expectException(PenneoSDKException::class);
        $this->expectExceptionMessage("Cannot build! Please set the {$missingParameter} with ->set{$capitalized}()!");

        $this->build([
            $missingParameter => null,
        ]);
    }

    /** @dataProvider providerRequiredBuildParameters */
    public function testWhenBuildingWithEmptyParameterAPenneoExceptionIsThrown(string $missingParameter)
    {
        $capitalized = ucfirst($missingParameter);

        $this->expectException(PenneoSDKException::class);
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
        $this->expectException(PenneoSDKException::class);
        $this->expectExceptionMessage("Cannot build! Unknown environment '$unknownEnvironment'!");

        $this->build([
            'environment' => $unknownEnvironment,
        ]);
    }

    public function testWhenBuildingWithAnInvalidRedirectUriAPenneoExceptionIsThrown()
    {
        $this->expectException(PenneoSDKException::class);
        $this->expectExceptionMessage("Cannot build! The supplied redirect URI is not a valid URL!");

        $this->build([
            'redirectUri' => 'garbage',
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
