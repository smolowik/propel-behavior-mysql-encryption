<?php
$loader = require_once __DIR__ . '/../../vendor/autoload.php';
$passphrase = "bf794180-9dc5-4c22-abd8-580769156f1f";

\Smolowik\Propel\Passphrase::createInstance($passphrase);