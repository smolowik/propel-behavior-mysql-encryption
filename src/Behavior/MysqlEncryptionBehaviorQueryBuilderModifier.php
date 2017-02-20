<?php

declare(strict_types=1);

namespace Smolowik\Propel\Behavior;

use Propel\Generator\Builder\Om\QueryBuilder;
use Propel\Generator\Model\Table;
use Propel\Generator\Util\PhpParser;

class MysqlEncryptionBehaviorQueryBuilderModifier
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
     * @param QueryBuilder $builder
     *
     * @return string
     */
    public function queryMethods(QueryBuilder $builder): string
    {
        $script = '';

        $script .= $this->addOrderBy($builder);

        return $script;
    }

    public function queryFilter(string &$script, QueryBuilder $builder)
    {
        $columns = explode(',', $this->behavior->getParameter('columns'));
        $parser = new PhpParser($script, true);

        foreach ($columns as $columnName) {
            $column = $this->table->getColumn($columnName);
            $filterByFunctionName = sprintf(
                'filterBy%s',
                $column->getPhpName()
            );
            $filterByFunction = $parser->findMethod($filterByFunctionName);

            $result = sprintf(
                "        return \$this->addHaving('%s', \$%s, \$comparison);",
                $column->getName(),
                $column->getCamelCaseName()
            );
            $lines = explode("\n", $filterByFunction);
            $lines[count($lines) - 2] = $result;
            $filterByFunction = implode("\n", $lines);
            $filterByFunction = str_replace('WHERE', 'HAVING', $filterByFunction);
            $parser->replaceMethod($filterByFunctionName, $filterByFunction);
        }
        $script = $parser->getCode();
    }

    private function addOrderBy(QueryBuilder $builder)
    {
        $script = '';

        $columns = explode(',', $this->behavior->getParameter('columns'));

        foreach ($columns as $columnName) {
            $column = $this->table->getColumn($columnName);
            $script .= $this->behavior->renderTemplate('addOrderBy', array(
                'columnName' => $column->getName(),
                'columnPhpName' => $column->getPhpName(),
            ));
        }

        return $script;
    }
}
