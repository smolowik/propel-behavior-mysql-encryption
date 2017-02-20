<?php

namespace Smolowik\Propel\tests;

use PHPUnit_Framework_TestCase;
use Propel\Generator\Util\QuickBuilder;
use Propel\Runtime\ActiveQuery\Criteria;
use Smolowik\Propel\Passphrase;

class TableMapTest extends PHPUnit_Framework_TestCase
{
    public function setUp ()
    {
        if (!class_exists('Author')) {
            QuickBuilder::buildSchema(file_get_contents(__DIR__ . "/mock/author-schema.xml"));
        }
    }

    /**
     * @test
     */
    public function it_adds_decryption_to_select_columns ()
    {
        $criteria = $this->prophesize(Criteria::class);

        $criteria->addSelectColumn('author.id')
            ->shouldBeCalled();
        $criteria->addSelectColumn('AES_DECRYPT(author.first_name, UNHEX(SHA2(\'' . Passphrase::getInstance()->getPassphrase() . '\',512))) AS first_name')
            ->shouldBeCalled();
        $criteria->addSelectColumn('AES_DECRYPT(author.last_name, UNHEX(SHA2(\'' . Passphrase::getInstance()->getPassphrase() . '\',512))) AS last_name')
            ->shouldBeCalled();
        $criteria->addSelectColumn('AES_DECRYPT(author.email, UNHEX(SHA2(\'' . Passphrase::getInstance()->getPassphrase() . '\',512))) AS email')
            ->shouldBeCalled();

        \Map\AuthorTableMap::addSelectColumns($criteria->reveal());
    }

    /**
     * @test
     */
    public function it_has_proper_field_size_and_type ()
    {
        $tableMap = new \Map\AuthorTableMap();

        $firstNameColumn = $tableMap->getColumn('first_name');
        $this->assertEquals(112, $firstNameColumn->getSize());
        $this->assertEquals("VARCHAR", $firstNameColumn->getType());

        $lastNameColumn = $tableMap->getColumn('last_name');
        $this->assertEquals(256, $lastNameColumn->getSize());
        $this->assertEquals("LONGVARCHAR", $lastNameColumn->getType());

        $emailColumn = $tableMap->getColumn('email');
        $this->assertEquals(112, $emailColumn->getSize());
        $this->assertEquals("VARCHAR", $emailColumn->getType());
    }
}