<?php

declare(strict_types=1);

namespace Smolowik\Propel\Behavior;

use Propel\Generator\Builder\Om\TableMapBuilder;
use Propel\Generator\Model\Table;
use Propel\Generator\Util\PhpParser;

class MysqlEncryptionBehaviorTableMapBuilderModifier
{
    /**
     * @var MysqlEncryptionBehavior
     */
    protected $behavior;

    /**
     * @var Table
     */
    protected $table;

    /**
     * @param MysqlEncryptionBehavior $behavior
     */
    public function __construct(MysqlEncryptionBehavior $behavior)
    {
        $this->behavior = $behavior;
        $this->table = $behavior->getTable();
    }

    /**
     * @param string          $script
     * @param TableMapBuilder $builder
     */
    public function tableMapFilter(string &$script, TableMapBuilder $builder)
    {
        $columns = explode(',', $this->behavior->getParameter('columns'));
        $parser = new PhpParser($script, true);
        $addSelectColumns = $parser->findMethod('addSelectColumns');
        $builder->declareClass('Smolowik\\Propel\\Passphrase');

        foreach ($columns as $columnName) {
            $column = $this->table->getColumn($columnName);
            $addSelectColumnName = sprintf(
                '%s::%s',
                $builder->getUnqualifiedClassName(),
                $column->getConstantName()
            );
            $encryptionFunction = sprintf(
                '\'AES_DECRYPT(\' . %s . \', UNHEX(SHA2(\\\'\' . %s . \'\\\',512))) AS %s\'',
                $addSelectColumnName,
                'Passphrase::getInstance()->getPassphrase()',
                $column->getName()
            );
            $addSelectColumns = str_replace($addSelectColumnName, $encryptionFunction, $addSelectColumns);
            $alias = sprintf(
                '$alias . \'.%s\'',
                $column->getName()
            );
            $encryptionFunction = sprintf(
                '\'AES_DECRYPT(\' . %s . \', UNHEX(SHA2(\\\'\' . %s . \'\\\',512))) AS %s\'',
                $alias,
                'Passphrase::getInstance()->getPassphrase()',
                $column->getName()
            );
            $addSelectColumns = str_replace($alias, $encryptionFunction, $addSelectColumns);

            $parser->replaceMethod('addSelectColumns', $addSelectColumns);
        }
        $script = $parser->getCode();
    }
}
