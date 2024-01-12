<?php

namespace Penneo\SDK\Tests\Unit\OAuth\OAuth;

use Penneo\SDK\OAuth\Nonce\UniqIdNonceGenerator;
use PHPUnit\Framework\TestCase;

class UniqIdNonceGeneratorTest extends TestCase
{
    public function testDoesNotReturnSameValueOnMultipleCalls() {
        $generator = new UniqIdNonceGenerator();
        $this->assertNotEquals($generator->generate(), $generator->generate());
    }

    /** @dataProvider runTest100Times */
    public function testGenerates64CharacterLongString() {
        $generator = new UniqIdNonceGenerator();
        $this->assertEquals(64, strlen($generator->generate()));
    }

    public function runTest100Times(): \Generator
    {
        for ($i = 0; $i < 100; $i++) {
            yield [];
        }
    }
}
