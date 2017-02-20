<?php

declare(strict_types=1);

namespace Smolowik\Propel\Behavior;

use Propel\Generator\Builder\Om\ObjectBuilder;
use Propel\Generator\Model\Table;
use Propel\Generator\Util\PhpParser;

class MysqlEncryptionBehaviorObjectBuilderModifier
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

    public function objectFilter(string &$script, ObjectBuilder $builder)
    {
        $parser = new PhpParser($script, true);
        $doInsertFunction = $parser->findMethod('doInsert');
        $buildCriteriaFunction = $parser->findMethod('buildCriteria');
        $defineVariable = "\$modifiedColumnsExpression = [];\r\n        \$index = 0;";
        $doInsertFunction = str_replace('$index = 0;', $defineVariable, $doInsertFunction);
        $builder->declareClass('Smolowik\\Propel\\Passphrase');

        $columns = explode(',', $this->behavior->getParameter('columns'));
        foreach ($this->table->getColumns() as $column) {
            $modifiedColumnsExpression = sprintf(
                "\$modifiedColumnsExpression[] = ':p' . \$index;\r\n            \$modifiedColumns[':p' . \$index++]  = '%s';",
                $column->getName()
            );
            if (in_array($column->getName(), $columns, true)) {
                $modifiedColumnsExpression = sprintf(
                    "\$modifiedColumnsExpression[] = 'AES_ENCRYPT(%s, UNHEX(SHA2(\\'' . %s . '\\',512)))';\r\n            \$modifiedColumns[':p' . \$index++]  = '%s';",
                    ":p' . \$index . '",
                    'Passphrase::getInstance()->getPassphrase()',
                    $column->getName()
                );

                $buildCriteriaFunction = str_replace(
                    sprintf(
                        '$this->%s',
                        $column->getName()
                    ),
                    sprintf(
                        "'AES_ENCRYPT(\'' . \$this->%s . '\', UNHEX(SHA2(\'' . %s . '\',512)))', Criteria::CUSTOM_EQUAL",
                        $column->getName(),
                        'Passphrase::getInstance()->getPassphrase()'
                    ),
                    $buildCriteriaFunction
                );
            }

            $doInsertFunction = str_replace(
                sprintf(
                    '$modifiedColumns[\':p\' . $index++]  = \'%s\';',
                    $column->getName()
                ),
                $modifiedColumnsExpression,
                $doInsertFunction
            );
        }
        $doInsertFunction = str_replace(
            'implode(\', \', array_keys($modifiedColumns))',
            'implode(\', \', $modifiedColumnsExpression)',
            $doInsertFunction
        );
        $parser->replaceMethod('doInsert', $doInsertFunction);
        $parser->replaceMethod('buildCriteria', $buildCriteriaFunction);
        $script = $parser->getCode();
    }
}
