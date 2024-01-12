<?php

namespace Penneo\SDK\Tests\Unit\OAuth\OAuth;

use Penneo\SDK\OAuth\UniqIdGenerator;
use PHPUnit\Framework\TestCase;

class UniqIdGeneratorTest extends TestCase
{
    public function testDoesNotReturnSameValueOnMultipleCalls() {
        $generator = new UniqIdGenerator();
        $this->assertNotEquals($generator->generate(), $generator->generate());
    }

    /** @dataProvider runTest100Times */
    public function testGeneratesAtLeast20CharacterLongString() {
        $generator = new UniqIdGenerator();
        $this->assertGreaterThanOrEqual(20, strlen($generator->generate()));
    }

    public function runTest100Times(): \Generator
    {
        for ($i = 0; $i < 100; $i++) {
            yield [];
        }
    }
}
