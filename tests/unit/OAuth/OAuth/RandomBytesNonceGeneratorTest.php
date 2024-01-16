<?php

namespace Penneo\SDK\Tests\Unit\OAuth\OAuth;

use Penneo\SDK\OAuth\Nonce\RandomBytesNonceGenerator;
use PHPUnit\Framework\TestCase;

class RandomBytesNonceGeneratorTest extends TestCase
{
    public function testReturnsUniqueValues()
    {
        $nonces = [];
        $count = 100;

        $generator = new RandomBytesNonceGenerator();

        for ($i = 0; $i < $count; $i++) {
            $nonces[] = $generator->generate();
        }

        $this->assertEquals($count, count(array_unique($nonces)));
    }

    /** @dataProvider runTest100Times */
    public function testGenerates64CharacterLongString()
    {
        $generator = new RandomBytesNonceGenerator();
        $this->assertEquals(64, strlen($generator->generate()));
    }

    public function runTest100Times(): \Generator
    {
        for ($i = 0; $i < 100; $i++) {
            yield [];
        }
    }
}
