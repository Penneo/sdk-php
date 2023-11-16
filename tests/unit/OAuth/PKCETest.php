<?php

namespace Penneo\SDK\Tests\Unit\OAuth;

use Penneo\SDK\OAuth\PKCE\PKCE;
use Penneo\SDK\OAuth\PKCE\CodeChallenge;
use PHPUnit\Framework\TestCase;

class PKCETest extends TestCase
{
    public function testCreatesCodeVerifierString()
    {
        $pkce = new PKCE();

        $codeVerifier = $pkce->getCodeVerifier();

        $this->assertIsString($codeVerifier);
        $this->assertEquals(128, strlen($codeVerifier));
    }

    public function testCreatesSha256CodeChallengeStringForTheGivenCodeVerifier()
    {
        $pkce = new PKCE();

        $codeVerifier = $pkce->getCodeVerifier();
        $codeChallenge = $pkce->getCodeChallenge($codeVerifier);

        $this->assertInstanceOf(CodeChallenge::class, $codeChallenge);
        $this->assertIsString($codeChallenge->getCodeChallenge());
        $this->assertEquals(CodeChallenge::S256, $codeChallenge->getMethod());
        $this->assertEquals(43, strlen($codeChallenge->getCodeChallenge()));
    }
}
