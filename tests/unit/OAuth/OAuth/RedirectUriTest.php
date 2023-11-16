<?php

namespace Penneo\SDK\Tests\Unit\OAuth\OAuth;

use Penneo\SDK\OAuth\PKCE\PKCE;
use Penneo\SDK\Tests\Unit\OAuth\BuildsOAuth;
use Penneo\SDK\Tests\Unit\OAuth\TestsEnvironments;
use PHPUnit\Framework\TestCase;

class RedirectUriTest extends TestCase
{
    use BuildsOAuth;
    use TestsEnvironments;

    /** @dataProvider environmentProvider */
    public function testSuccessfullyBuildsUri(string $environment, string $expectedDomain)
    {
        $oauth = $this->build([
            'clientId' => '123',
            'clientSecret' => '456',
            'redirectUri' => 'https://google.com',
            'environment' => $environment
        ]);

        $pkce = new PKCE();
        $verifier = $pkce->getCodeVerifier();
        $codeChallenge = $pkce->getCodeChallenge($verifier);

        $url = $oauth->buildRedirectUrl(['full_access'], $codeChallenge, 'someState');

        $parsed = parse_url($url);
        $this->assertNotEmpty($parsed);

        $this->assertEquals('https', $parsed['scheme']);
        $this->assertEquals($expectedDomain, $parsed['host']);
        $this->assertEquals('/oauth/authorize', $parsed['path']);

        $this->assertStringContainsString('scope=full_access', $parsed['query']);
        $this->assertStringContainsString('client_id=123', $parsed['query']);
        $this->assertStringContainsString('redirect_uri=' . urlencode('https://google.com'), $parsed['query']);
        $this->assertStringContainsString('state=someState', $parsed['query']);

        $this->assertStringNotContainsString('client_secret', $parsed['query']);
        $this->assertStringNotContainsString('456', $parsed['query']);
        $this->assertStringContainsString("code_challenge={$codeChallenge->getCodeChallenge()}", $parsed['query']);
        $this->assertStringContainsString('code_challenge_method=S256', $parsed['query']);
    }

    public function testDoesNotAddEmptyStateParameter()
    {
        $oauth = $this->build([
            'clientId' => '123',
            'clientSecret' => '456',
            'redirectUri' => 'https://google.com',
            'environment' => 'sandbox'
        ]);

        $pkce = new PKCE();
        $verifier = $pkce->getCodeVerifier();
        $codeChallenge = $pkce->getCodeChallenge($verifier);

        $url = $oauth->buildRedirectUrl(['full_access'], $codeChallenge, '');

        $parsed = parse_url($url);
        $this->assertNotEmpty($parsed);

        $this->assertStringNotContainsString('state=', $parsed['query']);
    }
}
