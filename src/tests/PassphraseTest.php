<?php

namespace Smolowik\Propel\tests;

use Smolowik\Propel\Passphrase;
use PHPUnit_Framework_TestCase;

class PassphraseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_returns_passphrase () {
        $this->assertEquals("bf794180-9dc5-4c22-abd8-580769156f1f", Passphrase::getInstance()->getPassphrase());
    }
}