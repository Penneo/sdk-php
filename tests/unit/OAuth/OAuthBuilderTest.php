<?php

namespace Penneo\SDK\Tests\Unit\OAuth;

use Penneo\SDK\OAuth\OAuth;
use Penneo\SDK\PenneoSDKException;
use PHPUnit\Framework\TestCase;

class OAuthBuilderTest extends TestCase
{
    use BuildsOAuth;

    public function
    provider_required_build_parameters(): array
    {
        return [
            ['clientId'],
            ['clientSecret'],
            ['redirectUri'],
            ['tokenStorage'],
            ['environment'],
        ];
    }

    /** @dataProvider provider_required_build_parameters */
    public function
    test_building_with_missing_parameter_throws_exception(string $missingParameter)
    {
        $capitalized = ucfirst($missingParameter);

        $this->expectException(PenneoSDKException::class);
        $this->expectExceptionMessage("Cannot build! Please set the ${missingParameter} with ->set${capitalized}()!");

        $this->build([
            $missingParameter => null,
        ]);
    }

    /** @dataProvider provider_required_build_parameters */
    public function
    test_building_with_empty_parameter_throws_exception(string $missingParameter)
    {
        $capitalized = ucfirst($missingParameter);

        $this->expectException(PenneoSDKException::class);
        $this->expectExceptionMessage("Cannot build! Please set the ${missingParameter} with ->set${capitalized}()!");

        $this->build([
            $missingParameter => '',
        ]);
    }

    /**
     * @testWith ["i dont know"]
     *           ["local"]
     *           ["??"]
     */
    public function
    test_building_with_unknown_environment_throws_exception(string $unknownEnvironment)
    {
        $this->expectException(PenneoSDKException::class);
        $this->expectExceptionMessage("Cannot build! Unknown environment '$unknownEnvironment'!");

        $this->build([
            'environment' => $unknownEnvironment,
        ]);
    }

    public function
    test_building_with_invalid_redirectUri_throws_exception()
    {
        $this->expectException(PenneoSDKException::class);
        $this->expectExceptionMessage("Cannot build! The supplied redirect URI is not a valid URL!");

        $this->build([
            'redirectUri' => 'garbage',
        ]);
    }

    public function
    test_building_with_localhost_as_redirectUri_succeeds()
    {
        $oauth = $this->build([
            'redirectUri' => 'http://localhost',
        ]);
        $this->assertEquals(OAuth::class, get_class($oauth));
    }

    public function
    test_building_with_valid_parameters_succeeds()
    {
        $oauth = $this->build();
        $this->assertEquals(OAuth::class, get_class($oauth));
    }
}