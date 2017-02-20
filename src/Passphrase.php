<?php

declare(strict_types=1);

namespace Smolowik\Propel;

use Exception;

class Passphrase
{
    /**
     * @var Passphrase
     */
    protected static $instance;

    /**
     * @var string
     */
    protected $passphrase;

    /**
     * @param string $passphrase
     */
    public function __construct(string $passphrase)
    {
        $this->passphrase = $passphrase;
    }

    /**
     * @param string $passphrase
     *
     * @throws Exception
     */
    public static function createInstance(string $passphrase)
    {
        if (self::$instance !== null) {
            throw new Exception('Passphrase instance already initialized!');
        }

        self::$instance = new static($passphrase);
    }

    /**
     * @throws Exception
     *
     * @return Cipher
     */
    public static function getInstance(): Passphrase
    {
        if (!self::$instance) {
            throw new Exception('You must first create Passphrase instance before you can get it!');
        }

        return self::$instance;
    }

    /**
     * @return string
     */
    public function getPassphrase(): string
    {
        return $this->passphrase;
    }
}
